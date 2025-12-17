<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([]);
    exit;
}

$q = $mysqli->real_escape_string($q);

$sql = "
    SELECT id, name, afm
    FROM clients
    WHERE name LIKE '%$q%'
       OR afm LIKE '%$q%'
    ORDER BY name ASC
    LIMIT 20
";

$res = $mysqli->query($sql);

$clients = [];
while ($row = $res->fetch_assoc()) {
    $clients[] = $row;
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($clients);
exit;
