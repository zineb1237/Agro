import pandas as pd
import numpy as np
import joblib
from datetime import datetime, timedelta
import json

# ============================================
# 1. CHARGEMENT DE VOS DONNÉES
# ============================================

# Vos données sol
sol_data = {
    "El Jadida": {
        "clay_pct": 35.0,
        "sand_pct": 25.0,
        "silt_pct": 40.0,
        "ph": 7.2,
        "organic_carbon_t_ha": 42.0,
        "bulk_density": 1.61
    },
    "Ouled Frej": {
        "clay_pct": 38.0,
        "sand_pct": 22.0,
        "silt_pct": 40.0,
        "ph": 7.1,
        "organic_carbon_t_ha": 42.0,
        "bulk_density": 1.61
    }
}

# Vos données météo (prévisions mensuelles)
meteo_data = {
    "El Jadida": {
        5: {"t_max": 22.1, "t_min": 16.6, "precip": 9.7},
        6: {"t_max": 24.0, "t_min": 19.1, "precip": 7.3},
        7: {"t_max": 25.3, "t_min": 20.4, "precip": 1.3},
        8: {"t_max": 25.9, "t_min": 20.6, "precip": 1.6},
        9: {"t_max": 25.0, "t_min": 19.5, "precip": 8.7},
        10: {"t_max": 24.4, "t_min": 18.0, "precip": 24.6},
        11: {"t_max": 21.7, "t_min": 14.4, "precip": 24.7},
        12: {"t_max": 19.3, "t_min": 12.0, "precip": 70.2},
        1: {"t_max": 18.6, "t_min": 10.8, "precip": 38.2},
        2: {"t_max": 18.6, "t_min": 11.6, "precip": 46.8},
        3: {"t_max": 19.5, "t_min": 9.5, "precip": 50.0},
        4: {"t_max": 22.3, "t_min": 12.8, "precip": 45.0}
    },
    "Ouled Frej": {
        5: {"t_max": 25.8, "t_min": 14.9, "precip": 6.1},
        6: {"t_max": 27.8, "t_min": 17.7, "precip": 3.3},
        7: {"t_max": 30.4, "t_min": 19.6, "precip": 0.1},
        8: {"t_max": 30.9, "t_min": 19.8, "precip": 0.7},
        9: {"t_max": 28.8, "t_min": 18.3, "precip": 10.1},
        10: {"t_max": 28.0, "t_min": 16.9, "precip": 18.4},
        11: {"t_max": 23.6, "t_min": 12.8, "precip": 18.7},
        12: {"t_max": 20.4, "t_min": 10.2, "precip": 57.2},
        1: {"t_max": 20.2, "t_min": 8.9, "precip": 36.5},
        2: {"t_max": 20.8, "t_min": 9.8, "precip": 39.5},
        3: {"t_max": 21.0, "t_min": 10.0, "precip": 45.0},
        4: {"t_max": 23.0, "t_min": 12.0, "precip": 40.0}
    }
}

# Variétés de blé
varietes = {
    "Achtar": {
        "cycle_jours": 190,
        "N_opt": 125,
        "P_opt": 70,
        "K_opt": 50,
        "eau_besoin_mm": 590,
        "rendement_potentiel": 55,
        "coeff_N": 2.8,
        "coeff_eau": 8.5,
        "stades": {
            "Demarrage": {"jours": 20, "pourcent_N": 30, "pourcent_eau": 15},
            "Tallage": {"jours": 35, "pourcent_N": 30, "pourcent_eau": 25},
            "Montaison": {"jours": 75, "pourcent_N": 40, "pourcent_eau": 35},
            "Epiaison": {"jours": 95, "pourcent_N": 0, "pourcent_eau": 25},
            "Floraison": {"jours": 110, "pourcent_N": 0, "pourcent_eau": 0}
        }
    },
    "Amal": {
        "cycle_jours": 195,
        "N_opt": 130,
        "P_opt": 65,
        "K_opt": 55,
        "eau_besoin_mm": 425,
        "rendement_potentiel": 50,
        "coeff_N": 3.0,
        "coeff_eau": 8.0,
        "stades": {
            "Demarrage": {"jours": 20, "pourcent_N": 30, "pourcent_eau": 15},
            "Tallage": {"jours": 38, "pourcent_N": 30, "pourcent_eau": 25},
            "Montaison": {"jours": 80, "pourcent_N": 40, "pourcent_eau": 35},
            "Epiaison": {"jours": 100, "pourcent_N": 0, "pourcent_eau": 25},
            "Floraison": {"jours": 115, "pourcent_N": 0, "pourcent_eau": 0}
        }
    },
    "Arrih": {
        "cycle_jours": 200,
        "N_opt": 140,
        "P_opt": 75,
        "K_opt": 65,
        "eau_besoin_mm": 475,
        "rendement_potentiel": 52,
        "coeff_N": 2.9,
        "coeff_eau": 8.2,
        "stades": {
            "Demarrage": {"jours": 25, "pourcent_N": 30, "pourcent_eau": 15},
            "Tallage": {"jours": 42, "pourcent_N": 30, "pourcent_eau": 25},
            "Montaison": {"jours": 88, "pourcent_N": 40, "pourcent_eau": 35},
            "Epiaison": {"jours": 110, "pourcent_N": 0, "pourcent_eau": 25},
            "Floraison": {"jours": 125, "pourcent_N": 0, "pourcent_eau": 0}
        }
    }
}

# ============================================
# 2. CHARGER LE MODÈLE ENTRAÎNÉ
# ============================================

try:
    model = joblib.load('crop_yield_model_maroc.pkl')
    scaler = joblib.load('scaler_maroc.pkl')
    feature_names = joblib.load('feature_names.pkl')
    print("✅ Modèle chargé avec succès")
except:
    print("⚠️ Modèle non trouvé. Veuillez d'abord entraîner le modèle avec essai1.py")
    exit()

# ============================================
# 3. FONCTIONS DE PRÉDICTION
# ============================================

def calculer_pluie_cycle(city, date_semis, date_recolte):
    """Calcule la pluie totale entre semis et récolte"""
    semis = datetime.strptime(date_semis, "%Y-%m-%d")
    recolte = datetime.strptime(date_recolte, "%Y-%m-%d")
    
    pluie_totale = 0
    current = semis
    
    while current <= recolte:
        mois = current.month
        if city in meteo_data and mois in meteo_data[city]:
            pluie_totale += meteo_data[city][mois]["precip"]
        current += timedelta(days=30)
    
    return pluie_totale

def calculer_besoin_eau(city, variete, date_semis, date_recolte, irrigation_deja_faite=0, type_culture="irrigue"):
    """Calcule le besoin en eau"""
    variete_info = varietes[variete]
    
    # Pluie sur le cycle
    pluie_totale = calculer_pluie_cycle(city, date_semis, date_recolte)
    
    # Besoin selon type de culture
    if type_culture == "bour":
        besoin_total = pluie_totale  # En bour, on dépend uniquement de la pluie
    else:
        besoin_total = variete_info["eau_besoin_mm"]
    
    eau_disponible = pluie_totale + irrigation_deja_faite
    deficit = max(0, besoin_total - eau_disponible)
    
    # Facteur de stress hydrique
    if eau_disponible >= besoin_total:
        stress = 1.0
    elif eau_disponible < besoin_total * 0.5:
        stress = 0.5
    else:
        stress = 0.5 + (eau_disponible / besoin_total) * 0.5
    
    return {
        "besoin_total_mm": round(besoin_total, 1),
        "pluie_cycle_mm": round(pluie_totale, 1),
        "irrigation_deja_faite_mm": irrigation_deja_faite,
        "irrigation_recommandee_mm": round(deficit, 1),
        "stress_hydrique": round(stress, 2),
        "type_culture": type_culture
    }

def calculer_besoin_engrais(variete, rendement_objectif, N_deja_apporte=0, pluie_totale=0):
    """Calcule le besoin en engrais"""
    variete_info = varietes[variete]
    
    # Besoin N selon rendement objectif
    besoin_N = rendement_objectif * variete_info["coeff_N"]
    besoin_N = max(variete_info["N_opt"] * 0.7, min(besoin_N, variete_info["N_opt"]))
    
    # Lessivage selon pluie
    if pluie_totale > 300:
        lessivage = besoin_N * 0.15
    elif pluie_totale > 200:
        lessivage = besoin_N * 0.10
    else:
        lessivage = 0
    
    N_restant = besoin_N - N_deja_apporte + lessivage
    N_restant = max(0, min(N_restant, besoin_N))
    
    # Répartition par stade
    repartition = {}
    for stade, info in variete_info["stades"].items():
        repartition[stade] = round(N_restant * info["pourcent_N"] / 100, 1)
    
    return {
        "N_besoin_kg_ha": round(besoin_N, 1),
        "P_besoin_kg_ha": variete_info["P_opt"],
        "K_besoin_kg_ha": variete_info["K_opt"],
        "N_restant_kg_ha": round(N_restant, 1),
        "lessivage_kg_ha": round(lessivage, 1),
        "repartition_N": repartition
    }

def predire_rendement(city, variete, mois, N_apporte, irrigation_supp=0):
    """Prédit le rendement avec le modèle entraîné"""
    sol = sol_data[city]
    meteo = meteo_data[city].get(mois, meteo_data[city][11])
    
    # Pluie avec irrigation
    precip = meteo["precip"] + irrigation_supp
    
    # Créer les features
    test_data = pd.DataFrame({
        'temp_max': [meteo["t_max"]],
        'temp_min': [meteo["t_min"]],
        'precipitation_mm': [precip],
        'clay_pct': [sol["clay_pct"]],
        'sand_pct': [sol["sand_pct"]],
        'ph': [sol["ph"]],
        'organic_carbon': [sol["organic_carbon_t_ha"]],
        'N_apporte_kg_ha': [N_apporte],
        f'City_{city}': [1],
        f'Variete_{variete}': [1]
    })
    
    # Ajouter colonnes manquantes
    for col in feature_names:
        if col not in test_data.columns:
            test_data[col] = 0
    
    test_data = test_data[feature_names]
    test_scaled = scaler.transform(test_data)
    rendement = model.predict(test_scaled)[0]
    
    return max(15, min(varietes[variete]["rendement_potentiel"], rendement))

def prediction_complete(city, variete, date_semis, date_recolte, type_culture="irrigue",
                        rendement_objectif=45, N_deja_apporte=0, irrigation_deja_faite=0):
    """Prédiction complète"""
    
    # 1. Calcul eau
    eau = calculer_besoin_eau(city, variete, date_semis, date_recolte, irrigation_deja_faite, type_culture)
    
    # 2. Calcul engrais
    engrais = calculer_besoin_engrais(variete, rendement_objectif, N_deja_apporte, eau["pluie_cycle_mm"])
    
    # 3. Prédiction rendement
    N_total = N_deja_apporte + engrais["N_restant_kg_ha"]
    irrigation_totale = irrigation_deja_faite + eau["irrigation_recommandee_mm"]
    
    # Mois de la récolte
    mois_recolte = datetime.strptime(date_recolte, "%Y-%m-%d").month
    
    rendement = predire_rendement(city, variete, mois_recolte, N_total, eau["irrigation_recommandee_mm"])
    
    return {
        "region": city,
        "variete": variete,
        "type_culture": type_culture,
        "date_semis": date_semis,
        "date_recolte": date_recolte,
        "irrigation": eau,
        "fertilisation": engrais,
        "rendement": {
            "objectif_qx_ha": rendement_objectif,
            "prediction_qx_ha": round(rendement, 1),
            "potentiel_max": varietes[variete]["rendement_potentiel"]
        }
    }

# ============================================
# 4. INTERFACE UTILISATEUR
# ============================================

def afficher_resultat(resultat):
    """Affiche les résultats de manière lisible"""
    print("\n" + "=" * 70)
    print(f"📍 {resultat['region']} - {resultat['variete']} ({resultat['type_culture']})")
    print("=" * 70)
    
    print(f"\n📅 DATES:")
    print(f"   Semis: {resultat['date_semis']}")
    print(f"   Récolte: {resultat['date_recolte']}")
    print(f"   Durée: {varietes[resultat['variete']]['cycle_jours']} jours")
    
    print(f"\n💧 EAU:")
    print(f"   Besoin total: {resultat['irrigation']['besoin_total_mm']} mm")
    print(f"   Pluie sur cycle: {resultat['irrigation']['pluie_cycle_mm']} mm")
    print(f"   Irrigation recommandée: {resultat['irrigation']['irrigation_recommandee_mm']} mm")
    print(f"   Stress hydrique: {resultat['irrigation']['stress_hydrique']}")
    
    print(f"\n🧪 FERTILISATION:")
    print(f"   Azote (N): {resultat['fertilisation']['N_besoin_kg_ha']} kg/ha")
    print(f"   Phosphore (P): {resultat['fertilisation']['P_besoin_kg_ha']} kg/ha")
    print(f"   Potassium (K): {resultat['fertilisation']['K_besoin_kg_ha']} kg/ha")
    print(f"   Restant à apporter: {resultat['fertilisation']['N_restant_kg_ha']} kg N/ha")
    print(f"   Répartition N:")
    for stade, quantite in resultat['fertilisation']['repartition_N'].items():
        if quantite > 0:
            print(f"      • {stade}: {quantite} kg/ha")
    
    print(f"\n📊 RENDEMENT:")
    print(f"   Objectif: {resultat['rendement']['objectif_qx_ha']} qx/ha")
    print(f"   Prédiction: {resultat['rendement']['prediction_qx_ha']} qx/ha")
    print(f"   Potentiel max: {resultat['rendement']['potentiel_max']} qx/ha")
    
    print("\n💡 RECOMMANDATIONS:")
    if resultat['irrigation']['irrigation_recommandee_mm'] > 0:
        print(f"   💧 Irriguez {resultat['irrigation']['irrigation_recommandee_mm']} mm pendant le cycle")
    else:
        print(f"   💧 Pluie suffisante, pas d'irrigation nécessaire")
    
    if resultat['fertilisation']['N_restant_kg_ha'] > 0:
        print(f"   🧪 Apportez {resultat['fertilisation']['N_restant_kg_ha']} kg N/ha")
        print(f"      → Urée (46%): {round(resultat['fertilisation']['N_restant_kg_ha']/0.46, 1)} kg/ha")
    
    if resultat['rendement']['prediction_qx_ha'] >= resultat['rendement']['objectif_qx_ha']:
        print(f"   ✅ Objectif atteignable")
    else:
        print(f"   ⚠️ Objectif difficile à atteindre")
        print(f"      → Augmentez irrigation ou fertilisation")
    
    print("=" * 70)

# ============================================
# 5. EXEMPLE D'UTILISATION
# ============================================

if __name__ == "__main__":
    print("\n" + "=" * 70)
    print("🌾 PRÉDICTION DE RENDEMENT - EL JADIDA / OULED FREJ")
    print("=" * 70)
    
    # Exemple 1: Blé Achtar en irrigué
    resultat1 = prediction_complete(
        city="El Jadida",
        variete="Achtar",
        date_semis="2026-11-15",
        date_recolte="2027-05-24",
        type_culture="irrigue",
        rendement_objectif=45
    )
    afficher_resultat(resultat1)
    
    # Exemple 2: Blé Amal en bour (pluvial)
    resultat2 = prediction_complete(
        city="Ouled Frej",
        variete="Amal",
        date_semis="2026-11-20",
        date_recolte="2027-06-05",
        type_culture="bour",
        rendement_objectif=35
    )
    afficher_resultat(resultat2)
    
    # Exemple 3: Blé Arrih avec fertilisation déjà faite
    resultat3 = prediction_complete(
        city="El Jadida",
        variete="Arrih",
        date_semis="2026-12-10",
        date_recolte="2027-06-15",
        type_culture="irrigue",
        rendement_objectif=50,
        N_deja_apporte=50,
        irrigation_deja_faite=30
    )
    afficher_resultat(resultat3)