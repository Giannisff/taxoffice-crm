<?php
require_once "../includes/db.php";
require_once "../vendor/autoload.php"; // PhpSpreadsheet
require_once "includes/auth.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

$pageTitle = "Εισαγωγή Ενεργών Επαγγελματιών";

$message = "";
$errors = [];
$inserted = 0;

/* ================================================================
   ΒΟΗΘΗΤΙΚΗ ΣΥΝΑΡΤΗΣΗ: Μετατροπή ημερομηνιών Excel → YYYY-MM-DD
================================================================ */
function convertDateExcelToDb($value)
{
    // Κενό → επιστροφή κενό
    if (trim($value) === "") return "";

    // Αν είναι ήδη YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }

    // Αν είναι DD/MM/YYYY
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
        [$d, $m, $y] = explode("/", $value);
        return "$y-$m-$d";
    }

    // Numeric Excel date (π.χ. 45123)
    if (is_numeric($value)) {
        $unix = ((int)$value - 25569) * 86400;
        return date("Y-m-d", $unix);
    }

    return ""; // fallback
}

/* ================================================================
   ΟΤΑΝ ΓΙΝΕΤΑΙ UPLOAD ΑΡΧΕΙΟΥ EXCEL
================================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["excel_file"])) {

    $file = $_FILES["excel_file"]["tmp_name"];

    if (!file_exists($file)) {
        $errors[] = "Το αρχείο δεν βρέθηκε.";
    } else {
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            /* =======================================================
               ΕΛΕΓΧΟΣ ΚΕΦΑΛΙΔΩΝ
            ======================================================= */
            $requiredHeaders = [
                "A" => "Κωδικός",
                "B" => "Ονοματεπώνυμο",
                "C" => "Ενεργός",
                "D" => "Ημερομηνία Έναρξης",
                "E" => "Ημερομηνία Διακοπής",
                "F" => "ΦΠΑ",
                "G" => "Τύπος ΦΠΑ",
                "H" => "Μισθοδοσία"
            ];

            foreach ($requiredHeaders as $col => $title) {
                if (trim($rows[1][$col]) !== $title) {
                    $errors[] = "Η στήλη $col πρέπει να είναι: $title";
                }
            }

            if (empty($errors)) {

                /* =======================================================
                   ΕΠΕΞΕΡΓΑΣΙΑ ΓΡΑΜΜΩΝ
                ======================================================= */
                for ($i = 2; $i <= count($rows); $i++) {

                    $code        = trim($rows[$i]["A"]);
                    $fullname    = trim($rows[$i]["B"]);
                    $is_active   = strtoupper(trim($rows[$i]["C"])) === "ΝΑΙ" ? 1 : 0;

                    // Μετατροπή ημερομηνιών
                    $start_date  = convertDateExcelToDb(trim($rows[$i]["D"]));
                    $end_date    = convertDateExcelToDb(trim($rows[$i]["E"]));

                    $has_vat     = strtoupper(trim($rows[$i]["F"])) === "ΝΑΙ" ? 1 : 0;
                    $vat_type    = trim($rows[$i]["G"]);
                    $has_payroll = strtoupper(trim($rows[$i]["H"])) === "ΝΑΙ" ? 1 : 0;

                    // Έλεγχος υποχρεωτικών πεδίων
                    if ($code === "" || $fullname === "") {
                        $errors[] = "Γραμμή $i: λείπει υποχρεωτικό πεδίο.";
                        continue;
                    }

                    // Έλεγχος διπλού κωδικού
                    $check = $mysqli->prepare("
                        SELECT id FROM active_professionals WHERE code = ?
                    ");
                    $check->bind_param("s", $code);
                    $check->execute();

                    if ($check->get_result()->num_rows > 0) {
                        $errors[] = "Γραμμή $i: ο κωδικός '$code' υπάρχει ήδη.";
                        continue;
                    }

                    // Εισαγωγή
                    $stmt = $mysqli->prepare("
                        INSERT INTO active_professionals
                        (code, fullname, is_active, start_date, end_date, has_vat, vat_type, has_payroll)
                        VALUES (?,?,?,?,?,?,?,?)
                    ");
                    $stmt->bind_param(
                        "ssississ",
                        $code, $fullname, $is_active, $start_date, $end_date,
                        $has_vat, $vat_type, $has_payroll
                    );
                    $stmt->execute();

                    $inserted++;
                }
            }

        } catch (Exception $e) {
            $errors[] = "Σφάλμα ανάγνωσης αρχείου: " . $e->getMessage();
        }
    }

    if ($inserted > 0) {
        $message = "Επιτυχής εισαγωγή: $inserted εγγραφές.";
    }
}

require "../includes/header.php";
?>

<div class="page-container">

    <h2>Εισαγωγή Ενεργών Επαγγελματιών</h2>

    <?php if ($message): ?>
        <div class="card card-green" style="margin-bottom:15px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="card card-yellow" style="margin-bottom:15px;">
            <b>Παρουσιάστηκαν σφάλματα:</b><br>
            <?php foreach ($errors as $err): ?>
                • <?= $err ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post" enctype="multipart/form-data">

            <div class="form-row">
                <div class="form-group">
                    <label>Επιλογή αρχείου Excel</label>
                    <input type="file" name="excel_file" accept=".xlsx" required>
                </div>
            </div>

            <div class="form-actions">
                <a href="../active_professionals.php" class="btn btn-secondary">Άκυρο</a>

                <button class="btn btn-primary">Εισαγωγή</button>
            </div>

        </form>
    </div>

</div>

<?php require "../includes/footer.php"; ?>

<?php
// Σωστό redirect μετά από 2''
if ($inserted > 0 && empty($errors)) {
    header("Refresh: 2; URL=http://localhost/taxoffice_crm/active_professionals.php");
    exit;
}
?>
