import os
import tempfile
import base64
import logging
from fastapi import FastAPI, UploadFile, File, Form
from groq import Groq
from dotenv import load_dotenv
from gtts import gTTS

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("agrobot")

load_dotenv()
app = FastAPI()
client = Groq(api_key=os.getenv("GROQ_API_KEY"))

def text_to_speech_bytes(text, lang):
    """Convertit un texte en audio MP3 via gTTS et retourne les bytes."""
    if not text or len(text.strip()) < 5:
        logger.warning("Texte trop court ou vide pour la synthèse vocale.")
        return None
    try:
        gtts_lang = 'fr' if lang == 'fr' else 'ar'
        logger.info(f"Génération audio pour {len(text)} caractères (langue {gtts_lang})")
        tts = gTTS(text[:500], lang=gtts_lang, slow=False)
        with tempfile.NamedTemporaryFile(delete=False, suffix=".mp3") as fp:
            temp_path = fp.name
        tts.save(temp_path)
        with open(temp_path, "rb") as f:
            audio_bytes = f.read()
        os.unlink(temp_path)
        logger.info(f"Audio généré : {len(audio_bytes)} bytes")
        return audio_bytes
    except Exception as e:
        logger.error(f"Erreur TTS : {e}")
        return None

def detect_lang(text):
    return "ar" if any('\u0600' <= c <= '\u06FF' for c in text) else "fr"

@app.post("/ask")
async def ask(question: str = Form(...), history: str = Form("")):
    try:
        completion = client.chat.completions.create(
            model="llama-3.1-8b-instant",
            messages=[{"role": "user", "content": f"Tu es AgroBot, conseiller agricole pour Doukkala. Réponds en {detect_lang(question)} à: {question}"}],
            temperature=0.3,
            max_tokens=500
        )
        text = completion.choices[0].message.content
        logger.info(f"Question reçue : {question[:50]}... réponse générée ({len(text)} caractères)")
    except Exception as e:
        logger.error(f"Erreur Groq : {e}")
        text = f"❌ Erreur technique : {e}"
    # Génération audio systématique (sauf si réponse d'erreur)
    audio_bytes = None
    if not text.startswith("❌") and len(text) > 10:
        audio_bytes = text_to_speech_bytes(text, detect_lang(question))
    return {
        "text": text,
        "audio_base64": base64.b64encode(audio_bytes).decode() if audio_bytes else None
    }

@app.post("/transcribe")
async def transcribe(file: UploadFile = File(...)):
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".wav")
    tmp.write(await file.read())
    tmp.close()
    try:
        with open(tmp.name, "rb") as f:
            resp = client.audio.transcriptions.create(
                file=(tmp.name, f),
                model="whisper-large-v3",
                response_format="text"
            )
        text = str(resp).strip()
    except Exception as e:
        logger.error(f"Erreur transcription : {e}")
        text = ""
    finally:
        os.unlink(tmp.name)
    return {"text": text}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)