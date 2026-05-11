<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "127.0.0.1";
$port = 3307;
$db   = "agriculture_db";
$user = "root";
$pass = "root";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$vendeur_id = isset($_GET['vendeur_id']) ? (int)$_GET['vendeur_id'] : 1;

$sql = "SELECT * FROM produit WHERE fournisseur_id = $vendeur_id";
$result = $conn->query($sql);

$produits = [];
while($row = $result->fetch_assoc()) {
    $icon = '📦';
    $type = strtolower($row['type']);
    if (strpos($type, 'semence') !== false) $icon = '🌱';
    elseif (strpos($type, 'engrais') !== false) $icon = '🍃';
    elseif (strpos($type, 'materiel') !== false) $icon = '🚜';
    elseif (strpos($type, 'pesticide') !== false) $icon = '🧪';

    $produits[] = [
        "id" => (int)$row['id'],
        "nom" => $row['nom'],
        "type" => $row['type'],
        "prix" => (float)$row['prixUnitaire'],
        "stock" => (int)$row['stock'],
        "icon" => $icon
    ];
}

echo json_encode($produits);
$conn->close();
?>
