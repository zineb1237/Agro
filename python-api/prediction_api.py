from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Literal
import joblib
import pandas as pd
import os

app = FastAPI()

# Modèle (mettez le chemin correct)
MODEL_PATH = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'backend', 'storage', 'models', 'modele_corrige.pkl')
if not os.path.exists(MODEL_PATH):
    print(f"⚠️ Modèle introuvable : {MODEL_PATH}")
    # Créer un modèle factice pour tester l'API
    model = None
else:
    model = joblib.load(MODEL_PATH)

# Définition des types de sol
SOL_PROPS = {
    'Argileux': (45, 20, 7.0),
    'Limoneux': (25, 30, 7.2),
    'Sableux': (10, 80, 6.8),
    'Argilo-limoneux': (35, 25, 7.1),
    'Sablo-limoneux': (15, 60, 6.9)
}

# Schéma des données d'entrée
class PredictionInput(BaseModel):
    culture: Literal['ble', 'pomme_de_terre', 'betterave', 'olivier']
    variete: str
    region: Literal['El Jadida', 'Ouled Frej']
    type_sol: str
    irrigation: int
    n_kg_ha: float
    p2o5_kg_ha: float
    k2o_kg_ha: float
    semis_jour: int

@app.post("/predict")
def predict(data: PredictionInput):
    # Vérifier le sol
    if data.type_sol not in SOL_PROPS:
        raise HTTPException(status_code=400, detail="Type de sol inconnu")
    clay, sand, ph = SOL_PROPS[data.type_sol]
    
    # Météo par défaut (vous pourrez les compléter avec vos moyennes)
    meteo = {
        'precip_total_mm': 250,
        'etp_total_mm': 500,
        'tmax_mean': 20,
        'tmin_mean': 12,
        'hot_days': 5,
        'rainy_days': 60,
        'precip_etp_ratio': 0.5,
        'days_in_period': 200
    }
    
    input_df = pd.DataFrame([{
        'culture': data.culture,
        'variete': data.variete,
        'region': data.region,
        'type_sol': data.type_sol,
        'clay_pct': clay,
        'sand_pct': sand,
        'ph': ph,
        'irrigation': data.irrigation,
        'n_kg_ha': data.n_kg_ha,
        'p2o5_kg_ha': data.p2o5_kg_ha,
        'k2o_kg_ha': data.k2o_kg_ha,
        'semis_jour': data.semis_jour,
        **meteo
    }])
    
    if model is None:
        # Fallback : rendement fictif
        rendement = 50.0
    else:
        rendement = model.predict(input_df)[0]
    
    return {"rendement_qx_ha": round(rendement, 1)}