<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$sql = "
ALTER TABLE products 
ADD COLUMN packname VARCHAR(150) AFTER barcode,
MODIFY COLUMN uom INT DEFAULT 1;
";
$pdo->exec($sql);
echo "Tabel products berhasil di-update (packname added, uom to INT)!";
