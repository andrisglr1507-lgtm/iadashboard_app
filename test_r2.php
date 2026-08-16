<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$session = \App\Models\OpnameSession::where("status", "ACTIVE")->first();
$user = \App\Models\User::where("username", "toni66")->first();
if(!$user) { echo "User not found"; exit; }
$myRecountsRaw = \Illuminate\Support\Facades\DB::table("opname_recount_assignments")
    ->where("session_id", $session->id)
    ->where("assigned_to", $user->id)
    ->whereIn("status", ["PENDING", "ASSIGNED", "IN_PROGRESS"])
    ->get();
echo "Recounts: " . count($myRecountsRaw) . PHP_EOL;

$myAreas = \App\Models\OpnameUserArea::where("session_id", $session->id)
    ->where("user_id", $user->id)
    ->get();
echo "Role: " . ($myAreas->isNotEmpty() ? $myAreas->first()->team_role : "UNASSIGNED") . PHP_EOL;

foreach($myRecountsRaw as $r) {
   echo $r->location_code . " | " . $r->id_product . PHP_EOL;
}
