<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$res = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='opname_references' AND COLUMN_NAME='warehouse_id'")->fetchAll();
print_r($res);
$res2 = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='opname_sessions' AND COLUMN_NAME='warehouse_id'")->fetchAll();
print_r($res2);
