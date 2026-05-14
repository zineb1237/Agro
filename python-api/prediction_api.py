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

# Valeurs optimales par culture (pour les recommandations)
OPTIMA = {
    'ble': {'n_opt': 80, 'p_opt': 50, 'k_opt': 80, 'semis_opt': 320},
    'pomme_de_terre': {'n_opt': 100, 'p_opt': 120, 'k_opt': 180, 'semis_opt': 320},
    'betterave': {'n_opt': 100, 'p_opt': 120, 'k_opt': 180, 'semis_opt': 289},
    'olivier': {'n_opt': 60, 'p_opt': 40, 'k_opt': 70, 'semis_opt': 320}
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

def generer_recommandations(data: PredictionInput):
    """Génère des conseils personnalisés basés sur les paramètres saisis."""
    recos = []
    culture = data.culture
    opt = OPTIMA[culture]
    clay, sand, ph = SOL_PROPS[data.type_sol]

    # Recommandations sur le type de sol
    if data.type_sol == 'Argileux':
        recos.append("Sol argileux : améliorez le drainage en incorporant du sable et du compost.")
    elif data.type_sol == 'Sableux':
        recos.append("Sol sableux : augmentez la matière organique et fractionnez l’irrigation pour éviter les pertes.")
    elif data.type_sol == 'Limoneux':
        recos.append("Sol limoneux : bon équilibre, maintenez une couverture végétale pour éviter l’érosion.")

    # Recommandations sur le pH
    if ph < 6.0:
        recos.append(f"pH trop acide ({ph}). Apportez de la chaux (2-3 t/ha) pour remonter à 6.5-7.0.")
    elif ph > 7.5:
        recos.append(f"pH trop alcalin ({ph}). Utilisez du soufre ou du sulfate d’ammonium pour acidifier légèrement.")

    # Recommandations NPK
    if data.n_kg_ha < opt['n_opt'] * 0.8:
        recos.append(f"Azote insuffisant ({data.n_kg_ha} kg/ha). Augmentez jusqu’à {opt['n_opt']} kg/ha.")
    elif data.n_kg_ha > opt['n_opt'] * 1.2:
        recos.append(f"Excès d’azote ({data.n_kg_ha} kg/ha). Réduisez à {opt['n_opt']} kg/ha pour éviter la verse.")
    
    if data.p2o5_kg_ha < opt['p_opt'] * 0.8:
        recos.append(f"Phosphore trop faible ({data.p2o5_kg_ha} kg/ha). Apportez {opt['p_opt'] - data.p2o5_kg_ha} kg/ha de P2O5 (superphosphate).")
    elif data.p2o5_kg_ha > opt['p_opt'] * 1.2:
        recos.append(f"Phosphore excessif. Réduisez les apports pour éviter le lessivage.")

    if data.k2o_kg_ha < opt['k_opt'] * 0.8:
        recos.append(f"Potassium faible ({data.k2o_kg_ha} kg/ha). Utilisez du K2O ( {opt['k_opt'] - data.k2o_kg_ha} kg/ha).")

    # Recommandations sur la date de semis
    diff = abs(data.semis_jour - opt['semis_opt'])
    if diff > 10:
        recos.append(f"Date de semis éloignée de l’optimale ({opt['semis_opt']}). Avancez ou retardez le semis de {diff} jours.")

    # Irrigation
    if data.irrigation == 0:
        recos.append("Culture en bour. En cas de sécheresse, une irrigation complémentaire (50-80 mm) peut sécuriser le rendement.")
    else:
        # Vérifier si l'irrigation est excessive (basé sur la météo par défaut, on peut améliorer)
        recos.append("Irrigation en place. Veillez à ne pas dépasser les besoins (environ 500 mm sur le cycle).")

    # Variété (conseil général)
    recos.append(f"Variété sélectionnée : {data.variete}. Choisissez une variété adaptée à votre sol et à votre climat.")

    # Retourner les 5 premières recommandations pour ne pas surcharger
    return recos[:5]

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
    
    # Générer les recommandations
    recommandations = generer_recommandations(data)
    
    return {
        "rendement_qx_ha": round(rendement, 1),
        "recommandations": recommandations
    }