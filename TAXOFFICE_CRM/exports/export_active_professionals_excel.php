<?php
require_once "../includes/db.php";
require_once "../vendor/autoload.php"; // PhpSpreadsheet
require_once "../includes/auth.php";


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/* -------------------------------------------------------------
   ΔΙΑΒΑΣΗ ΦΙΛΤΡΩΝ ΑΠΟ ΤΟ index.php
-------------------------------------------------------------- */
$partner_id = $_GET['partner_id'] ?? '';
$activeOnly = $_GET['active'] ?? '';
$vatFilter  = $_GET['vat'] ?? '';
$vatType    = $_GET['vat_type'] ?? '';
$payroll    = $_GET['payroll'] ?? '';

/* -------------------------------------------------------------
   ΧΤΙΖΟΥΜΕ ΤΟ WHERE
-------------------------------------------------------------- */
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

/* -------------------------------------------------------------
   QUERY
-------------------------------------------------------------- */
$sql = "
    SELECT *
    FROM active_professionals
    $where
    ORDER BY code ASC
";
$result = $mysqli->query($sql);

/* -------------------------------------------------------------
   ΔΗΜΙΟΥΡΓΙΑ EXCEL
-------------------------------------------------------------- */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Ενεργοί Επαγγελματίες");

/* -------------------------------------------------------------
   ΚΕΦΑΛΙΔΕΣ
-------------------------------------------------------------- */
$headers = [
    "A1" => "Κωδικός",
    "B1" => "Ονοματεπώνυμο",
    "C1" => "Ενεργός",
    "D1" => "Ημερομηνία Έναρξης",
    "E1" => "Ημερομηνία Διακοπής",
    "F1" => "ΦΠΑ",
    "G1" => "Τύπος ΦΠΑ",
    "H1" => "Μισθοδοσία"
];

foreach ($headers as $cell => $title) {
    $sheet->setCellValue($cell, $title);
}

/* Στυλ κεφαλίδων */
$sheet->getStyle("A1:H1")->getFont()->setBold(true);
$sheet->getStyle("A1:H1")->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFE2E3E5');

/* -------------------------------------------------------------
   ΓΕΜΙΣΜΑ ΓΡΑΜΜΩΝ
-------------------------------------------------------------- */
$rowNum = 2;

while ($row = $result->fetch_assoc()) {

    $sheet->setCellValue("A$rowNum", $row['code']);
    $sheet->setCellValue("B$rowNum", $row['fullname']);
    $sheet->setCellValue("C$rowNum", $row['is_active'] ? "ΝΑΙ" : "ΟΧΙ");
    $sheet->setCellValue("D$rowNum", $row['start_date']);
    $sheet->setCellValue("E$rowNum", $row['end_date']);
    $sheet->setCellValue("F$rowNum", $row['has_vat'] ? "ΝΑΙ" : "ΟΧΙ");
    $sheet->setCellValue("G$rowNum", $row['vat_type']);
    $sheet->setCellValue("H$rowNum", $row['has_payroll'] ? "ΝΑΙ" : "ΟΧΙ");

    $rowNum++;
}

/* -------------------------------------------------------------
   AUTO SIZE ΣΤΙΣ ΣΤΗΛΕΣ
-------------------------------------------------------------- */
foreach (range('A','H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/* -------------------------------------------------------------
   DOWNLOAD
-------------------------------------------------------------- */
$filename = "Ενεργοί_Επαγγελματίες.xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;


