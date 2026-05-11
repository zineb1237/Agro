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

if ($data && !empty($data['cart'])) {
    $client_id = 1; // Simulé ou à récupérer via session
    $adresse = $conn->real_escape_string($data['adresse']);
    $date = date('Y-m-d');
    $total = 0;
    
    // Calculer le total
    foreach ($data['cart'] as $item) {
        $total += ($item['prix'] * $item['quantity']);
    }
    
    // 1. Créer la commande (Structure réelle: client_id, dateCommande, status, totalPrix, adresseLivraison)
    $sql_commande = "INSERT INTO commande (client_id, dateCommande, status, totalPrix, adresseLivraison) 
                     VALUES ($client_id, '$date', 'En attente', $total, '$adresse')";
    
    if ($conn->query($sql_commande)) {
        $commande_id = $conn->insert_id;
        
        // 2. Ajouter les produits de la commande
        foreach ($data['cart'] as $item) {
            $p_id = (int)$item['id'];
            $qty = (int)$item['quantity'];
            $prix = (float)$item['prix'];
            
            $sql_cp = "INSERT INTO commande_produit (commande_id, produit_id, quantite, prixUnitaire) 
                       VALUES ($commande_id, $p_id, $qty, $prix)";
            $conn->query($sql_cp);
            
            // Réduire le stock
            $conn->query("UPDATE produit SET stock = stock - $qty WHERE id = $p_id");
        }
        
        echo json_encode(["success" => true, "commande_id" => $commande_id]);
    } else {
        echo json_encode(["error" => "Erreur SQL: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "Données invalides"]);
}

$conn->close();
?>
