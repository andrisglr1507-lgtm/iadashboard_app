<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$res = $pdo->query("SHOW TABLES LIKE '%user%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
