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
        
        $tableDef = "    protected \$guarded = [];\n    protected \$table = 'equuddbx_so_dc.$table';";
        
        // If it doesn't already have equuddbx_so_dc
        if (strpos($content, 'equuddbx_so_dc') === false) {
            // Replace the class opening brace
            $content = preg_replace('/class\s+'.$model.'\s+extends\s+Model\s*\{/', "class $model extends Model\n{\n$tableDef", $content);
            file_put_contents($path, $content);
            echo "Fixed $model\n";
        }
    }
}
