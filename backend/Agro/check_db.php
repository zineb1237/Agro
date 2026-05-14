<?php
require 'backend/Agro/vendor/autoload.php';
$app = require_once 'backend/Agro/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $columns = Schema::getColumnListing('produit');
    echo "Columns in 'produit': " . implode(', ', $columns) . "\n";
    
    $hasTokens = Schema::hasTable('personal_access_tokens');
    echo "Has 'personal_access_tokens' table: " . ($hasTokens ? 'Yes' : 'No') . "\n";
    
    $columnsCommande = Schema::getColumnListing('commande');
    echo "Columns in 'commande': " . implode(', ', $columnsCommande) . "\n";
    
    $hasCP = Schema::hasTable('commande_produit');
    echo "Has 'commande_produit' table: " . ($hasCP ? 'Yes' : 'No') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
