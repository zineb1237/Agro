import pandas as pd
import joblib
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline

df = pd.read_csv('dataset_synthetique_corrige.csv')

# Features (on garde toutes, y compris météo)
features = ['culture', 'variete', 'region', 'type_sol', 'clay_pct', 'sand_pct', 'ph',
            'irrigation', 'n_kg_ha', 'p2o5_kg_ha', 'k2o_kg_ha', 'semis_jour',
            'precip_total_mm', 'etp_total_mm', 'tmax_mean', 'tmin_mean', 'hot_days',
            'rainy_days', 'precip_etp_ratio', 'days_in_period']
X = df[features]
y = df['rendement_qx_ha']

categorical = ['culture', 'variete', 'region', 'type_sol']
numeric = [c for c in features if c not in categorical]

preprocessor = ColumnTransformer([
    ('cat', OneHotEncoder(handle_unknown='ignore'), categorical),
    ('num', 'passthrough', numeric)
])

model = RandomForestRegressor(n_estimators=100, random_state=42, max_depth=10)
pipeline = Pipeline([('prep', preprocessor), ('model', model)])

pipeline.fit(X, y)
joblib.dump(pipeline, 'modele_corrige.pkl')
print("Modèle corrigé sauvegardé sous 'modele_corrige.pkl'")

# Test simple
test_data = pd.DataFrame([{
    'culture': 'ble', 'variete': 'Marzak', 'region': 'El Jadida', 'type_sol': 'Limoneux',
    'clay_pct': 25, 'sand_pct': 30, 'ph': 7.2, 'irrigation': 1,
    'n_kg_ha': 80, 'p2o5_kg_ha': 50, 'k2o_kg_ha': 80, 'semis_jour': 320,
    'precip_total_mm': 300, 'etp_total_mm': 500, 'tmax_mean': 20, 'tmin_mean': 12,
    'hot_days': 2, 'rainy_days': 60, 'precip_etp_ratio': 0.6, 'days_in_period': 190
}])
pred = pipeline.predict(test_data)[0]
print(f"Exemple prédiction blé : {pred:.1f} qx/ha (attendu ~60 max)")