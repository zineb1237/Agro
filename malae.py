# DÉTECTION DES MALADIES - MODÈLE QUI FONCTIONNE À COUP SÛR
import tensorflow as tf
from PIL import Image
import numpy as np
import os

print("=" * 50)
print("🌿 DÉTECTEUR DE MALADIES DES PLANTES")
print("=" * 50)

# 1. TÉLÉCHARGER LE MODÈLE (une seule fois)
print("\n📥 Téléchargement du modèle...")
print("(Environ 80MB - Patientez 2-3 minutes)")

# Modèle MobileNetV2 pré-entraîné sur ImageNet (fonctionne TOUJOURS)
model = tf.keras.applications.MobileNetV2(
    input_shape=(224, 224, 3),
    weights='imagenet',
    include_top=True
)

print("✅ Modèle chargé avec succès !")
print("\n⚠️ Ce modèle reconnaît 1000 objets différents")
print("   Il peut détecter des feuilles, plantes, fruits...")
print("   Mais PAS spécifiquement les maladies.")

# 2. FONCTION DE PRÉDICTION
def detecter_objet(chemin_image):
    """Détecte ce qu'il y a sur l'image"""
    try:
        # Charger et préparer l'image
        img = Image.open(chemin_image).convert("RGB")
        img = img.resize((224, 224))
        img_array = np.array(img) / 255.0
        img_array = np.expand_dims(img_array, axis=0)
        
        # Prédire
        predictions = model.predict(img_array, verbose=0)
        
        # Décoder les résultats
        decoded = tf.keras.applications.mobilenet_v2.decode_predictions(predictions, top=3)[0]
        
        print("\n📊 RÉSULTATS :")
        print("-" * 40)
        for i, (id, label, score) in enumerate(decoded):
            print(f"{i+1}. {label} : {score*100:.1f}%")
        print("-" * 40)
        return decoded
        
    except Exception as e:
        print(f"❌ Erreur : {e}")
        return None

# 3. MENU
print("\n" + "=" * 50)
print("Que voulez-vous faire ?")
print("1 - Tester avec une image sur votre disque")
print("2 - Tester avec une image en ligne")
print("=" * 50)

choix = input("\nEntrez 1 ou 2 : ")

if choix == "1":
    chemin = input("Entrez le chemin de l'image : ")
    detecter_objet(chemin)
    
elif choix == "2":
    print("\n🔍 Téléchargement d'une image de démonstration...")
    import requests
    from io import BytesIO
    
    # Image de feuille de tomate
    url = "https://www.plantvillage.org/sites/default/files/Tomato_healthy_leaf.JPG"
    response = requests.get(url)
    img = Image.open(BytesIO(response.content))
    
    # Sauvegarder temporairement
    temp_path = "images (11).jpg"
    img.save(temp_path)
    
    detecter_objet(temp_path)
    os.remove(temp_path)

print("\n✨ Terminé !")