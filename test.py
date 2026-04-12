# Importer les fonctions depuis essai1.py
from essai1 import prediction_complete, afficher_resultat
import joblib

print("=" * 70)
print("🌾 TEST SANS IRRIGATION ET SANS AZOTE DÉJÀ FAITS")
print("=" * 70)

# TEST 1: El Jadida - Achtar (sans rien)
print("\n📍 TEST 1: El Jadida - Achtar")
print("   Semis: 15 novembre 2026")
print("   Récolte: 24 mai 2027")
print("   Type: Irrigué")
print("   Azote déjà apporté: 0 kg/ha")
print("   Irrigation déjà faite: 0 mm")
print("   Objectif rendement: 45 qx/ha")

resultat = prediction_complete(
    city="El Jadida",
    variete="Achtar",
    date_semis="2026-11-15",
    date_recolte="2027-05-24",
    type_culture="irrigue",
    rendement_objectif=45,
    N_deja_apporte=0,
    irrigation_deja_faite=0
)
afficher_resultat(resultat)

# TEST 2: El Jadida - Achtar (bour/pluvial)
print("\n" + "=" * 70)
print("📍 TEST 2: El Jadida - Achtar (BOUR - sans irrigation)")
print("   Semis: 15 novembre 2026")
print("   Récolte: 24 mai 2027")
print("   Type: Bour (pluvial)")
print("   Azote déjà apporté: 0 kg/ha")
print("   Irrigation déjà faite: 0 mm")
print("   Objectif rendement: 35 qx/ha")

resultat = prediction_complete(
    city="El Jadida",
    variete="Achtar",
    date_semis="2026-11-15",
    date_recolte="2027-05-24",
    type_culture="bour",
    rendement_objectif=35,
    N_deja_apporte=0,
    irrigation_deja_faite=0
)
afficher_resultat(resultat)

# TEST 3: Ouled Frej - Amal (sans rien)
print("\n" + "=" * 70)
print("📍 TEST 3: Ouled Frej - Amal")
print("   Semis: 20 novembre 2026")
print("   Récolte: 5 juin 2027")
print("   Type: Irrigué")
print("   Azote déjà apporté: 0 kg/ha")
print("   Irrigation déjà faite: 0 mm")
print("   Objectif rendement: 40 qx/ha")

resultat = prediction_complete(
    city="Ouled Frej",
    variete="Amal",
    date_semis="2026-11-20",
    date_recolte="2027-06-05",
    type_culture="irrigue",
    rendement_objectif=40,
    N_deja_apporte=0,
    irrigation_deja_faite=0
)
afficher_resultat(resultat)

print("\n" + "=" * 70)
print("✅ TESTS TERMINÉS")
print("=" * 70)