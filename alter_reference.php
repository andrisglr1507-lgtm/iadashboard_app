<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$pdo->exec("ALTER TABLE opname_sessions MODIFY COLUMN reference_id bigint(20) unsigned NULL DEFAULT NULL");
echo "Done.\n";
