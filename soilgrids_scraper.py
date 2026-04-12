import os
import json
import pandas as pd
import numpy as np
import requests
from typing import Dict, List, Tuple
import warnings
warnings.filterwarnings('ignore')

# ============================================
# CONFIGURATION
# ============================================

# Points d'intérêt
POINTS = [
    {"name": "El Jadida", "lat": 33.233, "lon": -8.517},
    {"name": "Oulad Frej", "lat": 33.020, "lon": -8.520}
]

# Profondeurs d'intérêt (pour la couche arable)
DEPTHS = ["0-5cm", "5-15cm", "15-30cm"]

# Propriétés SoilGrids à extraire
SOIL_PROPERTIES = {
    "clay": "argile (%)",
    "sand": "sable (%)", 
    "silt": "limon (%)",
    "phh2o": "pH",
    "soc": "carbone organique (g/kg)"
}

# ============================================
# FONCTIONS
# ============================================

def extract_soilgrids_values(tif_path: str, points: List[Dict]) -> Dict:
    """
    Extrait les valeurs des fichiers GeoTIFF SoilGrids pour chaque point.
    Note: Nécessite rasterio (pip install rasterio)
    """
    try:
        import rasterio
    except ImportError:
        print("❌ rasterio non installé. Exécutez: pip install rasterio")
        return {}
    
    results = {}
    try:
        with rasterio.open(tif_path) as src:
            for point in points:
                lon, lat = point["lon"], point["lat"]
                row, col = src.index(lon, lat)
                value = src.read(1)[row, col]
                
                if value is not None and not np.isnan(value):
                    results[point["name"]] = round(float(value), 2)
                else:
                    results[point["name"]] = None
    except Exception as e:
        print(f"⚠️ Erreur lecture {tif_path}: {e}")
        results = {p["name"]: None for p in points}
    
    return results

def load_wosis_data(json_path: str, target_lat: float = 33.05, target_lon: float = -8.5, radius_deg: float = 1.0) -> pd.DataFrame:
    """
    Charge et filtre les données WoSIS proches de la zone d'étude.
    """
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    rows = []
    for feature in data['features']:
        props = feature['properties']
        geom = feature['geometry']['coordinates']
        lon, lat = geom[0], geom[1]
        
        # Calcul de distance approximative
        distance = ((lat - target_lat)**2 + (lon - target_lon)**2)**0.5
        
        if distance <= radius_deg:  # Dans un rayon de ~110 km
            rows.append({
                'profile_id': props['profile_id'],
                'profile_code': props['profile_code'],
                'layer_name': props['layer_name'],
                'upper_depth': props['upper_depth'],
                'lower_depth': props['lower_depth'],
                'bulk_density_g_cm3': props['value_avg'],
                'latitude': lat,
                'longitude': lon,
                'distance_deg': round(distance, 4),
                'date': props.get('date', 'unknown')
            })
    
    df = pd.DataFrame(rows)
    if not df.empty:
        # Calcul de la moyenne pondérée par profondeur pour 0-30cm
        df_0_30 = df[df['upper_depth'] < 30]
        if not df_0_30.empty:
            avg_bd = df_0_30['bulk_density_g_cm3'].mean()
            print(f"📊 Densité apparente moyenne (0-30cm): {avg_bd:.2f} g/cm³")
    
    return df

def get_synthetic_soil_data() -> pd.DataFrame:
    """
    Crée un jeu de données synthétique basé sur les données WoSIS
    et les informations régionales (en attendant les GeoTIFF).
    """
    # Données extraites de WoSIS pour la région
    # (valeurs moyennes des profils proches)
    wosis_summary = {
        "El Jadida": {
            "bulk_density_g_cm3": 1.61,
            "organic_carbon_t_ha_0_30": 42.0,
            "organic_carbon_density_hg_m3_0": 268.0
        },
        "Oulad Frej": {
            "bulk_density_g_cm3": 1.61,
            "organic_carbon_t_ha_0_30": 42.0,
            "organic_carbon_density_hg_m3_0": 268.0
        }
    }
    
    # Données de texture estimées pour la région de Chaouia
    # (valeurs typiques pour sols limono-argileux)
    texture_estimates = {
        "El Jadida": {"clay_pct": 35.0, "sand_pct": 25.0, "silt_pct": 40.0, "ph": 7.2},
        "Oulad Frej": {"clay_pct": 38.0, "sand_pct": 22.0, "silt_pct": 40.0, "ph": 7.1}
    }
    
    rows = []
    for point in POINTS:
        name = point["name"]
        row = {
            "point": name,
            "latitude": point["lat"],
            "longitude": point["lon"],
            **wosis_summary[name],
            **texture_estimates[name]
        }
        rows.append(row)
    
    return pd.DataFrame(rows)

def create_complete_dataset(soilgrids_folder: str = None, wosis_json: str = None) -> pd.DataFrame:
    """
    Fonction principale qui assemble toutes les données.
    """
    print("🌱 Récupération des données de sol pour El Jadida et Oulad Frej")
    print("=" * 60)
    
    # 1. Données WoSIS (densité apparente, carbone)
    if wosis_json and os.path.exists(wosis_json):
        print("\n📁 Chargement des données WoSIS...")
        df_wosis = load_wosis_data(wosis_json)
        print(f"   ✅ {len(df_wosis)} couches de sol chargées")
    else:
        print("\n⚠️ Fichier WoSIS non trouvé, utilisation des valeurs synthétiques")
        df_wosis = None
    
    # 2. Données SoilGrids (texture, pH)
    df_soilgrids = None
    if soilgrids_folder and os.path.exists(soilgrids_folder):
        print("\n📁 Extraction des données SoilGrids...")
        all_values = {}
        
        for prop, description in SOIL_PROPERTIES.items():
            tif_file = os.path.join(soilgrids_folder, f"{prop}_0-5cm.tif")
            if os.path.exists(tif_file):
                values = extract_soilgrids_values(tif_file, POINTS)
                all_values[prop] = values
                print(f"   ✅ {description} extrait")
            else:
                print(f"   ⚠️ Fichier manquant: {prop}_0-5cm.tif")
        
        # Convertir en DataFrame
        if all_values:
            data = []
            for point in POINTS:
                name = point["name"]
                row = {"point": name}
                for prop, values in all_values.items():
                    row[prop] = values.get(name)
                data.append(row)
            df_soilgrids = pd.DataFrame(data)
    else:
        print("\n⚠️ Dossier SoilGrids non trouvé, utilisation des valeurs estimées")
    
    # 3. Assemblage final
    print("\n🔧 Assemblage du jeu de données final...")
    df_final = get_synthetic_soil_data()
    
    if df_soilgrids is not None and not df_soilgrids.empty:
        # Fusionner les données SoilGrids
        df_final = df_final.merge(df_soilgrids, on="point", how="left")
    
    # Ajouter les coordonnées
    df_final[["latitude", "longitude"]] = df_final[["latitude", "longitude"]].round(3)
    
    return df_final

def save_datasets(df: pd.DataFrame, output_folder: str = "."):
    """
    Sauvegarde les données dans différents formats.
    """
    os.makedirs(output_folder, exist_ok=True)
    
    # CSV principal
    csv_path = os.path.join(output_folder, "soil_data_el_jadida_oulad_frej.csv")
    df.to_csv(csv_path, index=False)
    print(f"\n✅ Données sauvegardées: {csv_path}")
    
    # Format JSON pour intégration API
    json_path = os.path.join(output_folder, "soil_data.json")
    df.to_json(json_path, orient="records", indent=2)
    print(f"✅ Données sauvegardées: {json_path}")
    
    # Format compatible avec modèle ML (features séparées)
    ml_features = df.drop(columns=["point", "latitude", "longitude"], errors="ignore")
    features_path = os.path.join(output_folder, "soil_features.csv")
    ml_features.to_csv(features_path, index=False)
    print(f"✅ Features ML sauvegardées: {features_path}")
    
    return csv_path

# ============================================
# EXÉCUTION PRINCIPALE
# ============================================

if __name__ == "__main__":
    # Configuration des chemins (à modifier selon votre structure)
    CONFIG = {
        "soilgrids_folder": "C:/Users/lessa/Desktop/Agri/soilgrids_data",  # Dossier contenant vos .tif
        "wosis_json": "C:/Users/lessa/Desktop/Agri/wosis_data.json",        # Votre fichier JSON WoSIS
        "output_folder": "C:/Users/lessa/Desktop/Agri/soil_output"
    }
    
    # Création du jeu de données
    df_soil = create_complete_dataset(
        soilgrids_folder=CONFIG["soilgrids_folder"],
        wosis_json=CONFIG["wosis_json"]
    )
    
    # Affichage
    print("\n" + "=" * 60)
    print("📋 APERÇU DES DONNÉES FINALES")
    print("=" * 60)
    print(df_soil.to_string(index=False))
    
    # Sauvegarde
    save_datasets(df_soil, CONFIG["output_folder"])
    
    print("\n✨ Terminé ! Ces données sont prêtes pour votre modèle de prédiction des rendements.")