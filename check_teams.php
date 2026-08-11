<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=equuddbx_so_dc', 'root', '');
$teams = $pdo->query("SHOW COLUMNS FROM opname_teams")->fetchAll(PDO::FETCH_ASSOC);
$members = $pdo->query("SHOW COLUMNS FROM opname_team_members")->fetchAll(PDO::FETCH_ASSOC);
print_r(['teams' => $teams, 'members' => $members]);
