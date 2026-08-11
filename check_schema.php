<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$stmt1 = $pdo->query('SHOW COLUMNS FROM opname_sessions');
echo "== opname_sessions ==\n";
print_r($stmt1->fetchAll(PDO::FETCH_ASSOC));
