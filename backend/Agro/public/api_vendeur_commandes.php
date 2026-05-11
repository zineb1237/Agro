<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "127.0.0.1";
$port = 3307;
$db   = "agriculture_db";
$user = "root";
$pass = "root";

$conn = new mysqli($host, $user, $pass, $db, $port);

$vendeur_id = isset($_GET['vendeur_id']) ? (int)$_GET['vendeur_id'] : 1;

// Cette requête suppose que vous avez des tables 'commande' et 'commande_produit'
// et une table 'users' ou 'agriculteur' pour le nom de l'agriculteur.
// Alignement avec la structure réelle : client_id, dateCommande, status, totalPrix, adresseLivraison
// On utilise LEFT JOIN pour ne pas perdre de lignes si une jointure échoue
$sql = "SELECT c.id, c.dateCommande as date, c.status, c.adresseLivraison as adresse, 
               u.nom as agriculteur_nom, 
               cp.produit_id, cp.quantite, cp.prixUnitaire, p.nom as produit_nom
        FROM commande c
        LEFT JOIN agriculteur u ON c.client_id = u.id
        INNER JOIN commande_produit cp ON c.id = cp.commande_id
        INNER JOIN produit p ON cp.produit_id = p.id
        WHERE p.fournisseur_id = $vendeur_id
        ORDER BY c.dateCommande DESC, c.id DESC";

$result = $conn->query($sql);

$commandes = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        if (!isset($commandes[$id])) {
            $commandes[$id] = [
                "id" => $id,
                "date" => $row['date'],
                "status" => $row['status'],
                "adresse" => $row['adresse'],
                "agriculteur_nom" => $row['agriculteur_nom'] ?? 'Agriculteur #'.$row['client_id'],
                "produits" => [],
                "total" => 0
            ];
        }
        $commandes[$id]["produits"][] = [
            "id" => $row['produit_id'],
            "nom" => $row['produit_nom'],
            "quantite" => (int)$row['quantite'],
            "prixUnitaire" => (float)$row['prixUnitaire']
        ];
        $commandes[$id]["total"] += ($row['quantite'] * $row['prixUnitaire']);
    }
} elseif (!$result) {
    echo json_encode(["error" => "Erreur SQL: " . $conn->error]);
    exit;
}

echo json_encode(array_values($commandes));
$conn->close();
?>
