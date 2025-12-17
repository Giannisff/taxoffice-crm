<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";


header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=analysis_partners.xls");
echo "\xEF\xBB\xBF"; // UTF-8 BOM για ελληνικά

$partner_id = $_GET['partner_id'] ?? null;
$where = "";

if ($partner_id) {
    $partner_id = (int)$partner_id;
    $where = " WHERE t.partner_id = $partner_id ";
}

// Λήψη στοιχείων συνεργάτη
$partner_name = "Όλοι οι συνεργάτες";
if ($partner_id) {
    $p = $mysqli->query("SELECT fullname FROM partners WHERE id=$partner_id")->fetch_assoc();
    if ($p) $partner_name = $p['fullname'];
}

// Λήψη εργασιών συνεργάτη
$sql = "
    SELECT t.*, c.name AS client_name
    FROM tasks t
    JOIN clients c ON c.id = t.client_id
    $where
    ORDER BY t.task_date DESC
";

$res = $mysqli->query($sql);

// Πίνακας Excel
echo "<table border='1'>
        <tr style='background:#d0d0d0; font-weight:bold;'>
            <td colspan='7'>Ανάλυση Συνεργάτη: $partner_name</td>
        </tr>
        <tr>
            <th>Ημερομηνία</th>
            <th>Τίτλος</th>
            <th>Πελάτης</th>
            <th>Κατάσταση</th>
            <th>Αμοιβή</th>
            <th>Είσπραξη</th>
            <th>Υπόλοιπο</th>
        </tr>
";

$total_fee = 0;
$total_collected = 0;

while ($row = $res->fetch_assoc()) {

    $balance = $row['fee'] - $row['collected'];
    $total_fee += $row['fee'];
    $total_collected += $row['collected'];

    echo "<tr>
            <td>".greekDateFromDb($row['task_date'])."</td>
            <td>".htmlspecialchars($row['title'])."</td>
            <td>".htmlspecialchars($row['client_name'])."</td>
            <td>".htmlspecialchars($row['status'])."</td>
            <td>".number_format($row['fee'],2,',','.')."</td>
            <td>".number_format($row['collected'],2,',','.')."</td>
            <td>".number_format($balance,2,',','.')."</td>
          </tr>";
}

$final_balance = $total_fee - $total_collected;

echo "
<tr style='font-weight:bold; background:#f0f0f0;'>
    <td colspan='4'>Σύνολα</td>
    <td>".number_format($total_fee,2,',','.')."</td>
    <td>".number_format($total_collected,2,',','.')."</td>
    <td>".number_format($final_balance,2,',','.')."</td>
</tr>
</table>";
?>

