import requests
import json
import csv
from datetime import datetime, timedelta
import time

# 🌐 API Open-Meteo (GRATUIT - pas de clé API nécessaire)
API_URL = "https://archive-api.open-meteo.com/v1/archive"

# 📍 Coordonnées GPS
locations = {
    "El Jadida": {"lat": 33.25, "lon": -8.50},
    "Ouled Frej": {"lat": 33.02, "lon": -8.52}
}

# 📆 Paramètres de récupération depuis 2020
start_date = "2020-01-01"
end_date = datetime.now().strftime("%Y-%m-%d")

print(f"📊 Récupération des données de {start_date} à {end_date}...")
print(f"🌐 Utilisation de l'API Open-Meteo (gratuit)\n")

all_data = []

for city, coords in locations.items():
    print(f"🌍 Récupération pour {city}...")
    
    # Construction de l'URL avec les variables disponibles
    url = f"{API_URL}?latitude={coords['lat']}&longitude={coords['lon']}&start_date={start_date}&end_date={end_date}&daily=temperature_2m_max,temperature_2m_min,relative_humidity_2m_max,relative_humidity_2m_min,precipitation_sum,wind_speed_10m_max&timezone=Africa/Casablanca"
    
    try:
        response = requests.get(url, timeout=30)
        
        if response.status_code == 200:
            data = response.json()
            
            # Extraction des données quotidiennes
            dates = data["daily"]["time"]
            temps_max = data["daily"]["temperature_2m_max"]
            temps_min = data["daily"]["temperature_2m_min"]
            humidity_max = data["daily"]["relative_humidity_2m_max"]
            humidity_min = data["daily"]["relative_humidity_2m_min"]
            precipitation = data["daily"]["precipitation_sum"]
            wind_speed = data["daily"]["wind_speed_10m_max"]
            
            # Construction des enregistrements
            for i, date in enumerate(dates):
                record = {
                    "city": city,
                    "date": date,
                    "temperature_max_celsius": temps_max[i],
                    "temperature_min_celsius": temps_min[i],
                    "humidity_max_percent": humidity_max[i],
                    "humidity_min_percent": humidity_min[i],
                    "precipitation_mm": precipitation[i],
                    "wind_speed_kmh": wind_speed[i]
                }
                all_data.append(record)
            
            print(f"   ✅ {len(dates)} jours importés")
        else:
            print(f"   ⚠️ Erreur {response.status_code}")
            print(f"   {response.text[:300]}")
    
    except Exception as e:
        print(f"   ⚠️ Erreur: {str(e)}")
    
    time.sleep(1)


print(f"\n✅ Récupération terminée!")
print(f"   • Enregistrements: {len(all_data)}")

# 🔹 Sauvegarde en JSON
with open("weather_data.json", "w", encoding="utf-8") as json_file:
    json.dump(all_data, json_file, indent=4, ensure_ascii=False)
print("\n✅ Données sauvegardées en weather_data.json")

# 🔹 Sauvegarde en CSV
csv_columns = ["city", "date", "temperature_max_celsius", "temperature_min_celsius", "humidity_max_percent", "humidity_min_percent", "precipitation_mm", "wind_speed_kmh"]
with open("weather_data.csv", "w", newline="", encoding="utf-8") as csv_file:
    writer = csv.DictWriter(csv_file, fieldnames=csv_columns)
    writer.writeheader()
    for data in all_data:
        writer.writerow(data)
print("✅ Données sauvegardées en weather_data.csv")
print(f"\n📊 Statistiques:")
print(f"   • Total enregistrements: {len(all_data)}")
print(f"   • Villes: {len(locations)}")
for city in locations:
    count = len([d for d in all_data if d['city'] == city])
    print(f"     - {city}: {count} jours")