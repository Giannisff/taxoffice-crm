<?php
require_once "../includes/db.php";
require_once "../vendor/autoload.php"; // Dompdf
require_once "../includes/auth.php";


use Dompdf\Dompdf;

// -------------------------------------------------------------
// ΦΙΛΤΡΑ
// -------------------------------------------------------------
$partner_id = $_GET['partner_id'] ?? '';
$activeOnly = $_GET['active'] ?? '';
$vatFilter  = $_GET['vat'] ?? '';
$vatType    = $_GET['vat_type'] ?? '';
$payroll    = $_GET['payroll'] ?? '';

// -------------------------------------------------------------
// WHERE
// -------------------------------------------------------------
$where = " WHERE 1=1 ";

if ($activeOnly === "1") {
    $where .= " AND is_active = 1 ";
}

if ($vatFilter === "yes") {
    $where .= " AND has_vat = 1 ";
} elseif ($vatFilter === "no") {
    $where .= " AND has_vat = 0 ";
}

if ($vatType !== "") {
    $vt = $mysqli->real_escape_string($vatType);
    $where .= " AND vat_type = '$vt' ";
}

if ($payroll === "yes") {
    $where .= " AND has_payroll = 1 ";
} elseif ($payroll === "no") {
    $where .= " AND has_payroll = 0 ";
}

// -------------------------------------------------------------
// QUERY
// -------------------------------------------------------------
$sql = "
    SELECT *
    FROM active_professionals
    $where
    ORDER BY code ASC
";
$res = $mysqli->query($sql);

// -------------------------------------------------------------
// HTML TEMPLATE ΓΙΑ PDF
// -------------------------------------------------------------
$html = '
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #111;
    }
    h2 {
        text-align: center;
        margin-bottom: 15px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }
    th {
        background: #e2e3e5;
        padding: 8px;
        border: 1px solid #999;
        font-size: 12px;
    }
    td {
        padding: 6px;
        border: 1px solid #ccc;
    }
</style>
</head>
<body>

<h2>ΑΝΑΦΟΡΑ ΕΝΕΡΓΩΝ ΕΠΑΓΓΕΛΜΑΤΙΩΝ</h2>

<table>
<thead>
<tr>
    <th>Κωδ.</th>
    <th>Ονοματεπώνυμο</th>
    <th>Ενεργός</th>
    <th>Έναρξη</th>
    <th>Διακοπή</th>
    <th>ΦΠΑ</th>
    <th>Τύπος ΦΠΑ</th>
    <th>Μισθ.</th>
</tr>
</thead>
<tbody>
';

// -------------------------------------------------------------
// ΓΕΜΙΣΜΑ ΠΙΝΑΚΑ
// -------------------------------------------------------------
while ($row = $res->fetch_assoc()) {

    $html .= "
    <tr>
        <td>{$row['code']}</td>
        <td>".htmlspecialchars($row['fullname'])."</td>
        <td>".($row['is_active'] ? "ΝΑΙ" : "ΟΧΙ")."</td>
        <td>{$row['start_date']}</td>
        <td>{$row['end_date']}</td>
        <td>".($row['has_vat'] ? 'ΝΑΙ' : 'ΟΧΙ')."</td>
        <td>{$row['vat_type']}</td>
        <td>".($row['has_payroll'] ? 'ΝΑΙ' : 'ΟΧΙ')."</td>
    </tr>";
}

$html .= "</tbody></table></body></html>";


// -------------------------------------------------------------
// DOMPDF
// -------------------------------------------------------------
$dompdf = new Dompdf([
    "defaultFont" => "DejaVu Sans"
]);

$dompdf->loadHtml($html, "UTF-8");
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

$dompdf->stream("Ενεργοί_Επαγγελματίες.pdf", [
    "Attachment" => true
]);
exit;


