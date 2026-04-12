import requests
import json
import csv
import pandas as pd
from datetime import datetime
import time

# 🌐 API Open-Meteo (GRATUIT - pas de clé API nécessaire)
FORECAST_URL = "https://api.open-meteo.com/v1/forecast"
ARCHIVE_URL = "https://archive-api.open-meteo.com/v1/archive"

# 📍 Coordonnées GPS
locations = {
    "El Jadida": {"lat": 33.25, "lon": -8.50},
    "Ouled Frej": {"lat": 33.02, "lon": -8.52}
}

# 📆 Paramètres
start_date_histo = "2020-01-01"
end_date_histo = datetime.now().strftime("%Y-%m-%d")

print("=" * 60)
print("🌾 RÉCUPÉRATION DES DONNÉES CLIMATIQUES POUR CHATBOT AGRICOLE")
print("=" * 60)

# ============================================================
# 1. CLIMAT NORMAL (moyennes 30 ans)
# ============================================================
print("\n📊 1. CLIMAT NORMAL (moyennes 30 ans)")
print("-" * 40)

climat_normal = []

for city, coords in locations.items():
    print(f"   🌍 {city}...")
    
    # Récupère les moyennes sur 30 ans (past_days = 10950 = 30 ans)
    url = f"{FORECAST_URL}?latitude={coords['lat']}&longitude={coords['lon']}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Africa/Casablanca&past_days=10950&forecast_days=0"
    
    try:
        response = requests.get(url, timeout=30)
        
        if response.status_code == 200:
            data = response.json()
            
            if "daily" in data:
                # Convertir en DataFrame pour agréger par mois
                df = pd.DataFrame({
                    "date": data["daily"]["time"],
                    "temp_max": data["daily"]["temperature_2m_max"],
                    "temp_min": data["daily"]["temperature_2m_min"],
                    "precip": data["daily"]["precipitation_sum"]
                })
                
                df["date"] = pd.to_datetime(df["date"])
                df["mois"] = df["date"].dt.month
                
                # Agrégation par mois
                moyennes_mensuelles = df.groupby("mois").agg({
                    "temp_max": "mean",
                    "temp_min": "mean",
                    "precip": "mean"
                }).reset_index()
                
                for index, row in moyennes_mensuelles.iterrows():
                    climat_normal.append({
                        "city": city,
                        "type": "climat_normal_30ans",
                        "mois": int(row["mois"]),
                        "temperature_max_celsius": round(row["temp_max"], 1),
                        "temperature_min_celsius": round(row["temp_min"], 1),
                        "precipitation_mm": round(row["precip"], 1),
                        "source": "Open-Meteo (30 ans)"
                    })
                
                print(f"      ✅ Moyennes mensuelles calculées")
            else:
                print(f"      ⚠️ Pas de données daily")
        else:
            print(f"      ⚠️ Erreur: {response.status_code}")
    
    except Exception as e:
        print(f"      ⚠️ Erreur: {str(e)}")
    
    time.sleep(1)

# ============================================================
# 2. CLIMAT HISTORIQUE (2020-2025)
# ============================================================
print("\n📊 2. CLIMAT HISTORIQUE (2020-2025)")
print("-" * 40)

all_data = []
annees_histo = range(2020, 2026)  # 2020 à 2025 inclus

for city, coords in locations.items():
    print(f"   🌍 {city}...")
    
    for annee in annees_histo:
        start = f"{annee}-01-01"
        end = f"{annee}-12-31"
        
        url = f"{ARCHIVE_URL}?latitude={coords['lat']}&longitude={coords['lon']}&start_date={start}&end_date={end}&daily=temperature_2m_max,temperature_2m_min,relative_humidity_2m_max,relative_humidity_2m_min,precipitation_sum,wind_speed_10m_max&timezone=Africa/Casablanca"
        
        try:
            response = requests.get(url, timeout=30)
            
            if response.status_code == 200:
                data = response.json()
                
                if "daily" in data:
                    dates = data["daily"]["time"]
                    temps_max = data["daily"]["temperature_2m_max"]
                    temps_min = data["daily"]["temperature_2m_min"]
                    humidity_max = data["daily"]["relative_humidity_2m_max"]
                    humidity_min = data["daily"]["relative_humidity_2m_min"]
                    precipitation = data["daily"]["precipitation_sum"]
                    wind_speed = data["daily"]["wind_speed_10m_max"]
                    
                    for i, date in enumerate(dates):
                        all_data.append({
                            "city": city,
                            "type": "climat_historique",
                            "date": date,
                            "annee": annee,
                            "temperature_max_celsius": temps_max[i],
                            "temperature_min_celsius": temps_min[i],
                            "humidity_max_percent": humidity_max[i],
                            "humidity_min_percent": humidity_min[i],
                            "precipitation_mm": precipitation[i] if precipitation[i] is not None else 0,
                            "wind_speed_kmh": wind_speed[i]
                        })
                    
                    print(f"      ✅ {annee}: {len(dates)} jours")
                else:
                    print(f"      ⚠️ {annee}: Pas de données daily")
            else:
                print(f"      ⚠️ {annee}: Erreur {response.status_code}")
        
        except Exception as e:
            print(f"      ⚠️ {annee}: {str(e)}")
        
        time.sleep(1)

# ============================================================
# 3. CLIMAT RÉCENT (2026 - aujourd'hui)
# ============================================================
print("\n📊 3. CLIMAT RÉCENT (2026 - aujourd'hui)")
print("-" * 40)

climat_recent = []

for city, coords in locations.items():
    print(f"   🌍 {city}...")
    
    start_2026 = "2026-01-01"
    end_today = datetime.now().strftime("%Y-%m-%d")
    
    url = f"{ARCHIVE_URL}?latitude={coords['lat']}&longitude={coords['lon']}&start_date={start_2026}&end_date={end_today}&daily=temperature_2m_max,temperature_2m_min,relative_humidity_2m_max,relative_humidity_2m_min,precipitation_sum,wind_speed_10m_max&timezone=Africa/Casablanca"
    
    try:
        response = requests.get(url, timeout=30)
        
        if response.status_code == 200:
            data = response.json()
            
            if "daily" in data:
                dates = data["daily"]["time"]
                temps_max = data["daily"]["temperature_2m_max"]
                temps_min = data["daily"]["temperature_2m_min"]
                humidity_max = data["daily"]["relative_humidity_2m_max"]
                humidity_min = data["daily"]["relative_humidity_2m_min"]
                precipitation = data["daily"]["precipitation_sum"]
                wind_speed = data["daily"]["wind_speed_10m_max"]
                
                for i, date in enumerate(dates):
                    climat_recent.append({
                        "city": city,
                        "type": "climat_recent",
                        "date": date,
                        "temperature_max_celsius": temps_max[i],
                        "temperature_min_celsius": temps_min[i],
                        "humidity_max_percent": humidity_max[i],
                        "humidity_min_percent": humidity_min[i],
                        "precipitation_mm": precipitation[i] if precipitation[i] is not None else 0,
                        "wind_speed_kmh": wind_speed[i]
                    })
                
                print(f"      ✅ {len(dates)} jours récupérés")
            else:
                print(f"      ⚠️ Pas de données daily")
        else:
            print(f"      ⚠️ Erreur: {response.status_code}")
    
    except Exception as e:
        print(f"      ⚠️ Erreur: {str(e)}")
    
    time.sleep(1)

# ============================================================
# 4. AGRÉGATIONS MENSUELLES
# ============================================================
print("\n📊 4. AGRÉGATIONS MENSUELLES")
print("-" * 40)

agregations_mensuelles = []

for city in locations.keys():
    city_data = [d for d in all_data if d['city'] == city]
    
    if city_data:
        df = pd.DataFrame(city_data)
        df["date"] = pd.to_datetime(df["date"])
        df["mois"] = df["date"].dt.month
        df["annee"] = df["date"].dt.year
        
        # Moyennes par mois sur toute la période
        moyennes = df.groupby("mois").agg({
            "temperature_max_celsius": "mean",
            "temperature_min_celsius": "mean",
            "precipitation_mm": "mean"
        }).reset_index()
        
        for index, row in moyennes.iterrows():
            agregations_mensuelles.append({
                "city": city,
                "type": "moyennes_mensuelles_2020_2025",
                "mois": int(row["mois"]),
                "temperature_max_celsius": round(row["temperature_max_celsius"], 1),
                "temperature_min_celsius": round(row["temperature_min_celsius"], 1),
                "precipitation_mm": round(row["precipitation_mm"], 1),
                "source": "Open-Meteo (2020-2025)"
            })
        
        print(f"   ✅ {city}: agrégations mensuelles calculées")

# ============================================================
# 5. SAUVEGARDE
# ============================================================
print("\n📊 5. SAUVEGARDE DES DONNÉES")
print("-" * 40)

# Structure finale
resultat = {
    "region": "Doukkala (El Jadida - Ouled Frej)",
    "date_extraction": datetime.now().isoformat(),
    "sources": {
        "api": "Open-Meteo",
        "climat_normal": "30 ans (past_days=10950)",
        "climat_historique": "2020-2025",
        "climat_recent": "2026 à aujourd'hui"
    },
    "climat_normal": climat_normal,
    "climat_historique": all_data,
    "climat_recent": climat_recent,
    "moyennes_mensuelles": agregations_mensuelles
}

# Sauvegarde JSON
with open("climat_complet_oulad_frej_eljadida.json", "w", encoding="utf-8") as f:
    json.dump(resultat, f, indent=2, ensure_ascii=False)
print("✅ climat_complet_oulad_frej_eljadida.json")

# Sauvegarde CSV
csv_columns = ["city", "type", "date", "annee", "mois", "temperature_max_celsius", "temperature_min_celsius", "precipitation_mm", "humidity_max_percent", "humidity_min_percent", "wind_speed_kmh"]
with open("climat_complet.csv", "w", newline="", encoding="utf-8") as f:
    writer = csv.DictWriter(f, fieldnames=csv_columns)
    writer.writeheader()
    for d in all_data + climat_recent:
        row = {col: d.get(col, "") for col in csv_columns}
        writer.writerow(row)
print("✅ climat_complet.csv")

# ============================================================
# 6. STATISTIQUES
# ============================================================
print("\n📊 6. STATISTIQUES FINALES")
print("-" * 40)

print(f"   • Climat normal: {len(climat_normal)} enregistrements")
print(f"   • Climat historique (2020-2025): {len(all_data)} enregistrements")
print(f"   • Climat récent (2026): {len(climat_recent)} enregistrements")
print(f"   • Moyennes mensuelles: {len(agregations_mensuelles)} enregistrements")

print("\n" + "=" * 60)
print("✅ RÉCUPÉRATION TERMINÉE !")
print("=" * 60)