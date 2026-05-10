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

if ($data && isset($data['commande_id'])) {
    $c_id = (int)$data['commande_id'];
    
    $sql = "UPDATE commande SET status = 'Livré' WHERE id = $c_id";
    
    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
} else {
    echo json_encode(["error" => "ID de commande manquant"]);
}

$conn->close();
?>
