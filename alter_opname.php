<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
try {
    $pdo->exec("ALTER TABLE opname_references DROP FOREIGN KEY fk_reference_warehouse");
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE opname_references DROP COLUMN warehouse_id");
    echo "warehouse_id dropped from opname_references\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE opname_sessions DROP FOREIGN KEY fk_session_warehouse");
} catch(Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE opname_sessions DROP COLUMN warehouse_id");
    echo "warehouse_id dropped from opname_sessions\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }
