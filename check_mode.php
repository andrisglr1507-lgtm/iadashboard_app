<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$res = $pdo->query("SHOW COLUMNS FROM opname_sessions LIKE 'reference_id'")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
