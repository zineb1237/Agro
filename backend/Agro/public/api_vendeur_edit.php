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

if ($data && isset($data['id'])) {
    $id = (int)$data['id'];
    $nom = $conn->real_escape_string($data['nom']);
    $type = $conn->real_escape_string($data['type']);
    $prix = (float)$data['prix'];
    $stock = (int)$data['stock'];
    $desc = $conn->real_escape_string($data['description']);
    $promo = (int)$data['promotion'];
    $bio = (int)$data['certificationBio'];

    $sql = "UPDATE produit SET 
            nom = '$nom', 
            type = '$type', 
            prixUnitaire = $prix, 
            stock = $stock, 
            description = '$desc', 
            promotion = $promo, 
            certificationBio = $bio 
            WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
} else {
    echo json_encode(["error" => "Données invalides"]);
}

$conn->close();
?>
