<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once "../includes/auth.php";


$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$balanceFilter = $_GET['balance'] ?? '';

$where = " WHERE 1=1 ";

if ($search !== '') {
    $s = $mysqli->real_escape_string($search);
    $where .= " AND c.name LIKE '%$s%'";
}

if ($category !== '') {
    $cat = $mysqli->real_escape_string($category);
    $where .= " AND c.category = '$cat'";
}

$sql = "
    SELECT c.*,
           COALESCE(SUM(t.fee),0) AS total_fees,
           COALESCE(SUM(t.collected),0) AS total_collected
    FROM clients c
    LEFT JOIN tasks t ON t.client_id = c.id
    $where
    GROUP BY c.id
    ORDER BY c.name
";
$res = $mysqli->query($sql);
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Αναφορά Πελατών</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2 { margin-bottom: 10px; }
        table { width:100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border:1px solid #000; padding:4px 6px; }
        th { background:#f0f0f0; }
        .note { margin-top: 15px; font-size: 11px; color:#555; }
    </style>
</head>
<body>

<h2>Αναφορά Πελατών</h2>

<table>
    <thead>
        <tr>
            <th>Επωνυμία</th>
            <th>ΑΦΜ</th>
            <th>Κατηγορία</th>
            <th>Τηλέφωνο</th>
            <th>Email</th>
            <th>Αμοιβές</th>
            <th>Εισπράξεις</th>
            <th>Υπόλοιπο</th>
        </tr>
    </thead>

    <tbody>
    <?php 
    $total_fee = 0;
    $total_col = 0;

    while ($c = $res->fetch_assoc()):
        $balance = $c['total_fees'] - $c['total_collected'];

        $total_fee += $c['total_fees'];
        $total_col += $c['total_collected'];
    ?>
        <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['afm']) ?></td>
            <td><?= htmlspecialchars($c['category']) ?></td>
            <td><?= htmlspecialchars($c['phone']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= formatMoney($c['total_fees']) ?></td>
            <td><?= formatMoney($c['total_collected']) ?></td>
            <td><?= formatMoney($balance) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>

    <tfoot>
        <tr>
            <td colspan="5" style="font-weight:bold;">Σύνολα</td>
            <td><?= formatMoney($total_fee) ?></td>
            <td><?= formatMoney($total_col) ?></td>
            <td><?= formatMoney($total_fee - $total_col) ?></td>
        </tr>
    </tfoot>

</table>

<p class="note">
    Για αποθήκευση σε PDF: <strong>Αρχείο → Εκτύπωση → Αποθήκευση ως PDF</strong>
</p>

</body>
</html>
