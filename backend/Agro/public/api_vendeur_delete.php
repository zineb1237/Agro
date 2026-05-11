<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$host = "127.0.0.1";
$port = 3307;
$db   = "agriculture_db";
$user = "root";
$pass = "root";

$conn = new mysqli($host, $user, $pass, $db, $port);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // On vérifie si le produit n'est pas lié à des commandes avant de supprimer
    // (Ou on utilise ON DELETE CASCADE si configuré en DB)
    $sql = "DELETE FROM produit WHERE id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Erreur lors de la suppression : " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "ID manquant"]);
}

$conn->close();
?>
