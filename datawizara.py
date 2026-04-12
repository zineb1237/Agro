import pandas as pd

# Charger le fichier Excel fourni
fichier_exel = r"C:\Users\lessa\Downloads\indicateurs-sectoriels_fr.xlsx"
df = pd.read_excel(fichier_exel, header=3)

# Afficher les premières lignes
print(df.head())
print("Colonnes :", df.columns.tolist()[:12])

# Indicateurs d'intérêt
indicateurs = [
    "Moyenne des précipitations nationales (En mm)",
    "Blé dur",
    "Blé tendre",
    "Superficie Cultivée totale"
]

# Filtrer les lignes utiles
agri_df = df[df['Unnamed: 0'].isin(indicateurs)].copy()

# Renommer la colonne de gauche
agri_df.rename(columns={'Unnamed: 0': 'indicateur'}, inplace=True)

# Reshape pour obtenir une table long format
valeurs = [c for c in agri_df.columns if c not in ['indicateur', 'Unnamed: 1']]
long_df = agri_df.melt(id_vars=['indicateur'], value_vars=valeurs, var_name='periode', value_name='valeur')

# Supprimer les valeurs manquantes
long_df = long_df.dropna(subset=['valeur'])

# Sauvegarder en CSV pour utilisation ML
long_df.to_csv("dataset_agriculture.csv", index=False)
print("✔ dataset_agriculture.csv créé avec", len(long_df), "lignes")