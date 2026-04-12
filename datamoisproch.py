import requests
import json
import csv
from datetime import datetime, timedelta
import time

# 🌐 APIs Open-Meteo
FORECAST_URL = "https://api.open-meteo.com/v1/forecast"
ARCHIVE_URL = "https://archive-api.open-meteo.com/v1/archive"

# 📍 Coordonnées GPS
locations = {
    "El Jadida": {"lat": 33.25, "lon": -8.50},
    "Ouled Frej": {"lat": 33.02, "lon": -8.52}
}

def get_historical_averages(city, coords, target_months):
    """Récupère les moyennes historiques pour les mois cibles"""
    current_year = datetime.now().year
    # Utiliser les 5 dernières années pour les moyennes
    start_year = current_year - 5
    
    all_data = []
    for month in target_months:
        # Construire une plage de dates pour ce mois sur plusieurs années
        monthly_data = []
        for year in range(start_year, current_year):
            start = f"{year}-{month:02d}-01"
            # Dernier jour du mois
            if month == 12:
                end = f"{year}-12-31"
            else:
                end = f"{year}-{month+1:02d}-01"
                # Soustraire un jour
                end_date_obj = datetime.strptime(end, "%Y-%m-%d") - timedelta(days=1)
                end = end_date_obj.strftime("%Y-%m-%d")
            
            url = f"{ARCHIVE_URL}?latitude={coords['lat']}&longitude={coords['lon']}&start_date={start}&end_date={end}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Africa/Casablanca"
            
            try:
                response = requests.get(url, timeout=30)
                if response.status_code == 200:
                    data = response.json()
                    if "daily" in data:
                        temps_max = data["daily"]["temperature_2m_max"]
                        temps_min = data["daily"]["temperature_2m_min"]
                        precip = data["daily"]["precipitation_sum"]
                        
                        # Moyennes pour cette année
                        if temps_max and temps_min and precip:
                            # Filtrer les valeurs None
                            temps_max_clean = [t for t in temps_max if t is not None]
                            temps_min_clean = [t for t in temps_min if t is not None]
                            precip_clean = [p for p in precip if p is not None]
                            
                            if temps_max_clean and temps_min_clean and precip_clean:
                                monthly_data.append({
                                    "year": year,
                                    "temp_max_avg": sum(temps_max_clean) / len(temps_max_clean),
                                    "temp_min_avg": sum(temps_min_clean) / len(temps_min_clean),
                                    "precip_sum": sum(precip_clean)
                                })
            except Exception as e:
                print(f"      Erreur historique {year}-{month}: {e}")
        
        # Calculer la moyenne sur toutes les années
        if monthly_data:
            all_data.append({
                "month": month,
                "temp_max_avg": sum(d["temp_max_avg"] for d in monthly_data) / len(monthly_data),
                "temp_min_avg": sum(d["temp_min_avg"] for d in monthly_data) / len(monthly_data),
                "precip_sum": sum(d["precip_sum"] for d in monthly_data) / len(monthly_data)
            })
    
    return all_data

def get_forecast_16days(city, coords):
    """Récupère les prévisions sur 16 jours"""
    url = f"{FORECAST_URL}?latitude={coords['lat']}&longitude={coords['lon']}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Africa/Casablanca&forecast_days=16"
    
    try:
        response = requests.get(url, timeout=30)
        if response.status_code == 200:
            data = response.json()
            if "daily" in data:
                return {
                    "dates": data["daily"]["time"],
                    "temps_max": data["daily"]["temperature_2m_max"],
                    "temps_min": data["daily"]["temperature_2m_min"],
                    "precip": data["daily"]["precipitation_sum"]
                }
    except Exception as e:
        print(f"      Erreur prévisions: {e}")
    return None

# 📆 Définir les mois à prévoir (les 6 prochains mois)
current_date = datetime.now()
current_month = current_date.month
current_year = current_date.year

months_to_forecast = []
for i in range(1, 11):  # 3 mois
    month = current_month + i
    year = current_year
    if month > 12:
        month -= 12
        year += 1
    months_to_forecast.append({"year": year, "month": month})

# Correction: Utiliser une boucle simple pour l'affichage
month_list = []
for m in months_to_forecast:
    month_list.append(f"{m['year']}-{m['month']:02d}")

print(f"📊 Génération des prévisions pour les 3 prochains mois")
print(f"📅 Mois à prévoir: {month_list}")
print(f"🌐 Utilisation: 16 jours prévisions + moyennes historiques\n")

all_predictions = []

for city, coords in locations.items():
    print(f"📍 Traitement pour {city}...")
    
    # 1. Récupérer les prévisions sur 16 jours
    print(f"   🔮 Récupération des prévisions sur 16 jours...")
    forecast = get_forecast_16days(city, coords)
    
    if forecast:
        print(f"      ✅ {len(forecast['dates'])} jours de prévisions récupérés")
    else:
        print(f"      ⚠️ Pas de prévisions récupérées")
    
    # 2. Récupérer les moyennes historiques pour chaque mois
    print(f"   📊 Récupération des moyennes historiques...")
    target_months = [m["month"] for m in months_to_forecast]
    historical_avg = get_historical_averages(city, coords, target_months)
    print(f"      ✅ {len(historical_avg)} mois de moyennes historiques")
    
    # 3. Construire les prédictions mois par mois
    for target in months_to_forecast:
        month = target["month"]
        year = target["year"]
        
        # Trouver les données historiques pour ce mois
        hist = next((h for h in historical_avg if h["month"] == month), None)
        
        # Si c'est le mois en cours et qu'on a des prévisions, les utiliser
        if year == current_year and month == current_month and forecast:
            # Utiliser les prévisions pour ce mois
            days_in_month = []
            for i, date in enumerate(forecast["dates"]):
                date_obj = datetime.strptime(date, "%Y-%m-%d")
                if date_obj.month == month:
                    if forecast["temps_max"][i] is not None:
                        days_in_month.append({
                            "date": date,
                            "temp_max": forecast["temps_max"][i],
                            "temp_min": forecast["temps_min"][i] if forecast["temps_min"][i] is not None else 0,
                            "precip": forecast["precip"][i] if forecast["precip"][i] is not None else 0
                        })
            
            if days_in_month:
                temp_max_avg = sum(d["temp_max"] for d in days_in_month) / len(days_in_month)
                temp_min_avg = sum(d["temp_min"] for d in days_in_month) / len(days_in_month)
                precip_sum = sum(d["precip"] for d in days_in_month)
                source = "prévisions réelles (16 jours)"
            else:
                # Fallback sur historique
                temp_max_avg = hist["temp_max_avg"] if hist else 0
                temp_min_avg = hist["temp_min_avg"] if hist else 0
                precip_sum = hist["precip_sum"] if hist else 0
                source = "moyenne historique"
        else:
            # Utiliser les moyennes historiques
            if hist:
                temp_max_avg = hist["temp_max_avg"]
                temp_min_avg = hist["temp_min_avg"]
                precip_sum = hist["precip_sum"]
                source = "moyenne historique (5 ans)"
            else:
                temp_max_avg = 0
                temp_min_avg = 0
                precip_sum = 0
                source = "données non disponibles"
        
        all_predictions.append({
            "city": city,
            "year": year,
            "month": month,
            "temperature_max_avg": round(temp_max_avg, 1),
            "temperature_min_avg": round(temp_min_avg, 1),
            "precipitation_total_mm": round(precip_sum, 1),
            "source": source
        })
    
    print(f"   ✅ Terminé pour {city}\n")

# Sauvegarde des prédictions
if all_predictions:
    # Sauvegarde JSON
    filename_json = f"monthly_forecast_{current_date.strftime('%Y_%m_%d')}.json"
    with open(filename_json, "w", encoding="utf-8") as json_file:
        json.dump(all_predictions, json_file, indent=4, ensure_ascii=False)
    print(f"✅ Prédictions mensuelles sauvegardées dans {filename_json}")
    
    # Sauvegarde CSV
    filename_csv = f"monthly_forecast_{current_date.strftime('%Y_%m_%d')}.csv"
    csv_columns = ["city", "year", "month", "temperature_max_avg", "temperature_min_avg", "precipitation_total_mm", "source"]
    with open(filename_csv, "w", newline="", encoding="utf-8") as csv_file:
        writer = csv.DictWriter(csv_file, fieldnames=csv_columns)
        writer.writeheader()
        for data in all_predictions:
            writer.writerow(data)
    print(f"✅ Prédictions mensuelles sauvegardées dans {filename_csv}")
    
    # Affichage
    print(f"\n📊 PRÉDICTIONS MENSUELLES:")
    for city in locations:
        print(f"\n   📍 {city}:")
        city_data = [d for d in all_predictions if d['city'] == city]
        for d in city_data:
            print(f"      • {d['year']}-{d['month']:02d}: T° max {d['temperature_max_avg']}°C, "
                  f"T° min {d['temperature_min_avg']}°C, Pluie {d['precipitation_total_mm']} mm")
            print(f"        Source: {d['source']}")
else:
    print("⚠️ Aucune prédiction générée")