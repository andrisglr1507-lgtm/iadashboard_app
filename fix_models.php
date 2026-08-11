<?php
$models = ['Branch', 'Warehouse', 'Bin', 'Product', 'OpnameTeam'];
foreach($models as $m) {
    $path = "app/Models/$m.php";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, '$guarded') === false) {
            $content = str_replace('use HasFactory;', "use HasFactory;\n    protected \$guarded = [];", $content);
            file_put_contents($path, $content);
        }
    }
}
echo "Models updated.";
