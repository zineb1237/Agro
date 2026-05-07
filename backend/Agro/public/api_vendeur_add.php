<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$host = "127.0.0.1";
$port = 3307;
$db   = "agriculture_db";
$user = "root";
$pass = "root";

$conn = new mysqli($host, $user, $pass, $db, $port);

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $nom = $conn->real_escape_string($data['nom']);
    $v_id = (int)$data['vendeur_id'];
    $type = $conn->real_escape_string($data['type']);
    $prix = (float)$data['prix'];
    $stock = (int)$data['stock'];
    $desc = $conn->real_escape_string($data['description']);
    $promo = (int)$data['promotion'];
    $bio = (int)$data['certificationBio'];
    $date = date('Y-m-d');

    $sql = "INSERT INTO produit (fournisseur_id, nom, type, prixUnitaire, stock, description, dateAjout, promotion, certificationBio) 
            VALUES ($v_id, '$nom', '$type', $prix, $stock, '$desc', '$date', $promo, $bio)";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

$conn->close();
?>
