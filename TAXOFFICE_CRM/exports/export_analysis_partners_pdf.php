<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once "../includes/auth.php";


$partner_id = $_GET['partner_id'] ?? null;
$partner_name = 'Όλοι οι συνεργάτες';

$where = '';
if ($partner_id) {
    $partner_id = (int)$partner_id;
    $where = " WHERE t.partner_id = $partner_id ";
    $p = $mysqli->query("SELECT fullname FROM partners WHERE id = $partner_id")->fetch_assoc();
    if ($p) {
        $partner_name = $p['fullname'];
    }
}

$sql = "
    SELECT t.*, c.name AS client_name
    FROM tasks t
    JOIN clients c ON c.id = t.client_id
    $where
    ORDER BY t.task_date DESC
";

$res = $mysqli->query($sql);

$total_fee = 0;
$total_collected = 0;
?>
<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <title>Ανάλυση Συνεργάτη - <?= htmlspecialchars($partner_name) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h2, h3 {
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        th {
            background: #f0f0f0;
        }
        tfoot td {
            font-weight: bold;
            background: #f9f9f9;
        }
        .note {
            margin-top: 15px;
            font-size: 11px;
            color: #555;
        }
    </style>
</head>
<body>

<h2>Ανάλυση Συνεργάτη</h2>
<h3><?= htmlspecialchars($partner_name) ?></h3>

<table>
    <thead>
        <tr>
            <th>Ημερομηνία</th>
            <th>Τίτλος</th>
            <th>Πελάτης</th>
            <th>Κατάσταση</th>
            <th>Αμοιβή</th>
            <th>Είσπραξη</th>
            <th>Υπόλοιπο</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $res->fetch_assoc()): 
        $balance = $row['fee'] - $row['collected'];
        $total_fee += $row['fee'];
        $total_collected += $row['collected'];
    ?>
        <tr>
            <td><?= greekDateFromDb($row['task_date']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['client_name']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= number_format($row['fee'], 2, ',', '.') ?> €</td>
            <td><?= number_format($row['collected'], 2, ',', '.') ?> €</td>
            <td><?= number_format($balance, 2, ',', '.') ?> €</td>
        </tr>
    <?php endwhile; ?>
    </tbody>
    <tfoot>
        <?php $final_balance = $total_fee - $total_collected; ?>
        <tr>
            <td colspan="4">Σύνολα</td>
            <td><?= number_format($total_fee, 2, ',', '.') ?> €</td>
            <td><?= number_format($total_collected, 2, ',', '.') ?> €</td>
            <td><?= number_format($final_balance, 2, ',', '.') ?> €</td>
        </tr>
    </tfoot>
</table>

<p class="note">
    Για αποθήκευση σε PDF: <strong>Αρχείο → Εκτύπωση → Προορισμός: Αποθήκευση ως PDF</strong>.
</p>

</body>
</html>
