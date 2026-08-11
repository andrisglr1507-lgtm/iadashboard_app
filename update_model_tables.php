<?php
$models = [
    'Branch' => 'branches',
    'Warehouse' => 'warehouses',
    'Bin' => 'bins',
    'Product' => 'products',
    'Device' => 'devices',
    'OpnameTeam' => 'opname_teams',
    'OpnameTeamMember' => 'opname_team_members',
    'OpnameReference' => 'opname_references',
    'OpnameReferenceDetail' => 'opname_reference_details',
    'OpnameSession' => 'opname_sessions',
    'OpnameAssignment' => 'opname_assignments',
    'OpnameCount' => 'opname_counts',
    'OpnameResult' => 'opname_results',
    'SyncLog' => 'sync_logs',
    'SyncQueue' => 'sync_queue',
    'AuditLog' => 'audit_logs'
];

foreach($models as $model => $table) {
    $path = "app/Models/$model.php";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Add protected $table if it doesn't exist
        if (strpos($content, '$table =') === false) {
            $tableDef = "protected \$table = 'equuddbx_so_dc.$table';";
            
            // Check if $guarded exists to insert after it, else after HasFactory
            if (strpos($content, 'protected $guarded') !== false) {
                $content = str_replace('protected $guarded = [];', "protected \$guarded = [];\n    $tableDef", $content);
            } else {
                $content = str_replace('use HasFactory;', "use HasFactory;\n    protected \$guarded = [];\n    $tableDef", $content);
            }
            file_put_contents($path, $content);
            echo "Updated $model with table $table\n";
        }
    } else {
        echo "Model $model not found.\n";
    }
}
