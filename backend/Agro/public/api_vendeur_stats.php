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

// 1. Total des ventes (somme des prix unitaires * quantité pour les produits de ce vendeur)
$sql_ventes = "SELECT SUM(cp.prixUnitaire * cp.quantite) as total 
               FROM commande_produit cp 
               JOIN produit p ON cp.produit_id = p.id 
               WHERE p.fournisseur_id = $vendeur_id";
$res_ventes = $conn->query($sql_ventes);
$total_ventes = $res_ventes->fetch_assoc()['total'] ?? 0;

// 2. Nombre de produits actifs
$sql_produits = "SELECT COUNT(*) as total FROM produit WHERE fournisseur_id = $vendeur_id AND stock > 0";
$res_produits = $conn->query($sql_produits);
$produits_actifs = $res_produits->fetch_assoc()['total'] ?? 0;

// 3. Commandes livrées
$sql_livrees = "SELECT COUNT(DISTINCT c.id) as total 
                FROM commande c 
                JOIN commande_produit cp ON c.id = cp.commande_id 
                JOIN produit p ON cp.produit_id = p.id 
                WHERE p.fournisseur_id = $vendeur_id AND c.status = 'Livré'";
$res_livrees = $conn->query($sql_livrees);
$commandes_livrees = $res_livrees->fetch_assoc()['total'] ?? 0;

// 4. Clients uniques
$sql_clients = "SELECT COUNT(DISTINCT c.client_id) as total 
                FROM commande c 
                JOIN commande_produit cp ON c.id = cp.commande_id 
                JOIN produit p ON cp.produit_id = p.id 
                WHERE p.fournisseur_id = $vendeur_id";
$res_clients = $conn->query($sql_clients);
$clients_uniques = $res_clients->fetch_assoc()['total'] ?? 0;

// 5. Top Produits
$sql_top = "SELECT p.nom, SUM(cp.quantite) as total_vendus, SUM(cp.prixUnitaire * cp.quantite) as revenu 
            FROM commande_produit cp 
            JOIN produit p ON cp.produit_id = p.id 
            WHERE p.fournisseur_id = $vendeur_id 
            GROUP BY p.id 
            ORDER BY total_vendus DESC 
            LIMIT 5";
$res_top = $conn->query($sql_top);
$top_produits = [];
while($row = $res_top->fetch_assoc()) {
    $top_produits[] = $row;
}

echo json_encode([
    "total_ventes" => (float)$total_ventes,
    "produits_actifs" => (int)$produits_actifs,
    "commandes_livrees" => (int)$commandes_livrees,
    "clients_uniques" => (int)$clients_uniques,
    "top_produits" => $top_produits
]);

$conn->close();
?>
