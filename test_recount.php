<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\ = \App\Models\OpnameSession::where('status', 'ACTIVE')->first();
\ = \App\Models\User::where('username', 'toni66')->first();
if(!\) { echo 'User not found'; exit; }
\ = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
    ->where('session_id', \->id)
    ->where('assigned_to', \->id)
    ->whereIn('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS'])
    ->get();
echo 'Recounts: ' . count(\) . PHP_EOL;

\ = \App\Models\OpnameUserArea::where('session_id', \->id)
    ->where('user_id', \->id)
    ->get();
echo 'Role: ' . (\->isNotEmpty() ? \->first()->team_role : 'UNASSIGNED') . PHP_EOL;

foreach(\ as \) {
   echo \->location_code . ' | ' . \->id_product . PHP_EOL;
}
