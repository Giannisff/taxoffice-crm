<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once "../includes/auth.php";


header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=clients_" . date('Ymd_His') . ".xls");
echo "<meta charset='UTF-8'>";

$search   = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$balance  = $_GET['balance'] ?? '';
$sort     = $_GET['sort'] ?? '';

$where = " WHERE 1=1 ";

if ($search !== '') {
    $s = $mysqli->real_escape_string($search);
    $where .= " AND (
        c.name  LIKE '%$s%' OR
        c.afm   LIKE '%$s%' OR
        c.phone LIKE '%$s%' OR
        c.email LIKE '%$s%'
    )";
}
if ($category !== '') {
    $cat = $mysqli->real_escape_string($category);
    $where .= " AND c.category = '$cat'";
}
if ($balance === 'with') {
    $where .= " AND (COALESCE(t.fee,0) - COALESCE(t.collected,0)) > 0";
}
if ($balance === 'zero') {
    $where .= " AND (COALESCE(t.fee,0) - COALESCE(t.collected,0)) = 0";
}

$sql = "
    SELECT c.*,
           COALESCE(SUM(t.fee),0) AS total_fees,
           COALESCE(SUM(t.collected),0) AS total_collected,
           (COALESCE(SUM(t.fee),0) - COALESCE(SUM(t.collected),0)) AS balance,
           COUNT(t.id) AS task_count
    FROM clients c
    LEFT JOIN tasks t ON t.client_id = c.id
    $where
    GROUP BY c.id
";

switch ($sort) {
    case 'name_asc':  $sql .= " ORDER BY c.name ASC"; break;
    case 'name_desc': $sql .= " ORDER BY c.name DESC"; break;
    case 'balance':   $sql .= " ORDER BY balance DESC"; break;
    case 'tasks':     $sql .= " ORDER BY task_count DESC"; break;
    default:          $sql .= " ORDER BY c.name ASC";
}

$res = $mysqli->query($sql);

echo "<table border='1'>";
echo "<tr>
        <th>Επωνυμία</th>
        <th>ΑΦΜ</th>
        <th>Κατηγορία</th>
        <th>Τηλέφωνο</th>
        <th>Email</th>
        <th>Αμοιβές</th>
        <th>Εισπράξεις</th>
        <th>Υπόλοιπο</th>
        <th># Εργασιών</th>
      </tr>";

while ($c = $res->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".htmlspecialchars($c['name'])."</td>";
    echo "<td>".htmlspecialchars($c['afm'])."</td>";
    echo "<td>".htmlspecialchars($c['category'])."</td>";
    echo "<td>".htmlspecialchars($c['phone'])."</td>";
    echo "<td>".htmlspecialchars($c['email'])."</td>";
    echo "<td>".formatMoney($c['total_fees'])."</td>";
    echo "<td>".formatMoney($c['total_collected'])."</td>";
    echo "<td>".formatMoney($c['balance'])."</td>";
    echo "<td>".(int)$c['task_count']."</td>";
    echo "</tr>";
}
echo "</table>";
