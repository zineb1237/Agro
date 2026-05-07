<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "127.0.0.1";
$port = 3307;
$db   = "agriculture_db";
$user = "root";
$pass = "root";

// Connexion à MySQL avec mysqli
$conn = new mysqli($host, $user, $pass, $db, $port);

// Vérifier la connexion
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Requête pour récupérer les produits
$sql = "SELECT id, fournisseur_id, nom, type, prixUnitaire, stock, description, dateAjout, promotion, certificationBio FROM produit";
$result = $conn->query($sql);

$produits = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Ajouter une icône par défaut selon le type
        $icon = '📦';
        $type = strtolower($row['type']);
        if (strpos($type, 'semence') !== false) $icon = '🌱';
        elseif (strpos($type, 'engrais') !== false) $icon = '🍃';
        elseif (strpos($type, 'materiel') !== false) $icon = '🚜';
        elseif (strpos($type, 'pesticide') !== false) $icon = '🧪';
        
        $produits[] = [
            "id" => (int)$row['id'],
            "fournisseur_id" => (int)$row['fournisseur_id'],
            "nom" => $row['nom'],
            "type" => $row['type'],
            "prix" => (float)$row['prixUnitaire'],
            "stock" => (int)$row['stock'],
            "description" => $row['description'],
            "dateAjout" => $row['dateAjout'],
            "promotion" => (bool)$row['promotion'],
            "certificationBio" => (bool)$row['certificationBio'],
            "icon" => $icon
        ];
    }
} elseif (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

echo json_encode($produits);

$conn->close();
?>
