<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$sql = "ALTER TABLE bins ADD COLUMN ganjil_genap VARCHAR(20) NULL, ADD COLUMN level INT NULL";
$pdo->exec($sql);
echo "Columns added to bins table.\n";
