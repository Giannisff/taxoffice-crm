<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';


header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=tasks_" . date('Ymd_His') . ".xls");
echo "<meta charset='UTF-8'>";

$search     = $_GET['search'] ?? '';
$status     = $_GET['status'] ?? '';
$client_id  = $_GET['client'] ?? '';
$partner_id = $_GET['partner'] ?? '';

$date_range_type = $_GET['date_range_type'] ?? '';
$date_from       = $_GET['date_from'] ?? '';
$date_to         = $_GET['date_to'] ?? '';

$where = " WHERE 1=1 ";

if ($search !== '') {
    $s = $mysqli->real_escape_string($search);
    $where .= " AND t.title LIKE '%$s%'";
}
if ($status !== '') {
    $s = $mysqli->real_escape_string($status);
    $where .= " AND t.status = '$s'";
}
if ($client_id !== '') {
    $cid = (int)$client_id;
    $where .= " AND t.client_id = $cid";
}
if ($partner_id !== '') {
    $pid = (int)$partner_id;
    $where .= " AND t.partner_id = $pid";
}

// Ημερομηνίες
if ($date_range_type == "today") {
    $where .= " AND t.task_date = CURDATE() ";
} elseif ($date_range_type == "this_week") {
    $where .= " AND YEARWEEK(t.task_date, 1) = YEARWEEK(CURDATE(), 1) ";
} elseif ($date_range_type == "this_month") {
    $where .= " AND MONTH(t.task_date)=MONTH(CURDATE()) AND YEAR(t.task_date)=YEAR(CURDATE()) ";
} elseif ($date_range_type == "prev_month") {
    $where .= " AND MONTH(t.task_date)=MONTH(CURDATE() - INTERVAL 1 MONTH)
                AND YEAR(t.task_date)=YEAR(CURDATE() - INTERVAL 1 MONTH) ";
} elseif ($date_range_type == "custom" && $date_from && $date_to) {
    $f = $mysqli->real_escape_string($date_from);
    $t = $mysqli->real_escape_string($date_to);
    $where .= " AND t.task_date BETWEEN '$f' AND '$t' ";
}

$sql = "
    SELECT t.*,
           c.name AS client_name,
           p.fullname AS partner_name
    FROM tasks t
    JOIN clients c ON c.id = t.client_id
    LEFT JOIN partners p ON p.id = t.partner_id
    $where
    ORDER BY t.task_date DESC, t.id DESC
";

$res = $mysqli->query($sql);

echo "<table border='1'>";
echo "<tr>
        <th>Ημ/νία</th>
        <th>Τίτλος</th>
        <th>Πελάτης</th>
        <th>Συνεργάτης</th>
        <th>Κατάσταση</th>
        <th>Αμοιβή</th>
        <th>Είσπραξη</th>
        <th>Υπόλοιπο</th>
      </tr>";

while ($row = $res->fetch_assoc()) {
    $balance = $row['fee'] - $row['collected'];
    echo "<tr>";
    echo "<td>" . greekDateFromDb($row['task_date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['partner_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "<td>" . number_format($row['fee'],2,',','.') . "</td>";
    echo "<td>" . number_format($row['collected'],2,',','.') . "</td>";
    echo "<td>" . number_format($balance,2,',','.') . "</td>";
    echo "</tr>";
}
echo "</table>";
