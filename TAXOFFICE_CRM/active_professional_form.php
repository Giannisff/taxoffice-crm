<?php
$pageTitle = "Επαγγελματίας";
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

/* ============================================================
   ΦΟΡΤΩΣΗ ΔΕΔΟΜΕΝΩΝ (ΑΝ ΕΙΝΑΙ EDIT)
============================================================ */
$id = $_GET['id'] ?? null;
$pro = null;

if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM active_professionals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $pro = $stmt->get_result()->fetch_assoc();
}

/* ============================================================
   ΔΙΑΓΡΑΦΗ
============================================================ */
if (isset($_GET['delete']) && $id) {
    $stmt = $mysqli->prepare("DELETE FROM active_professionals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: active_professionals.php");
    exit;
}

/* ============================================================
   ΑΠΟΘΗΚΕΥΣΗ (NEW + EDIT)
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $code        = $_POST["code"];
    $fullname    = $_POST["fullname"];
    $is_active   = isset($_POST["is_active"]) ? 1 : 0;
    $start_date  = $_POST["start_date"] ?: null;
    $end_date    = $_POST["end_date"] ?: null;
    $has_vat     = isset($_POST["has_vat"]) ? 1 : 0;
    $vat_type    = $_POST["vat_type"];
    $has_payroll = isset($_POST["has_payroll"]) ? 1 : 0;

    if ($id) {
        // UPDATE
        $stmt = $mysqli->prepare("
            UPDATE active_professionals
            SET code=?, fullname=?, is_active=?, start_date=?, end_date=?, 
                has_vat=?, vat_type=?, has_payroll=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssississi",
            $code, $fullname, $is_active, $start_date, $end_date,
            $has_vat, $vat_type, $has_payroll, $id
        );
        $stmt->execute();
    } else {
        // INSERT
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
    }

    header("Location: active_professionals.php");
    exit;
}

require "includes/header.php";
?>

<style>
/* MOBILE SUMMARY CARD */
.summary-card {
    background:white;
    padding:14px;
    border-radius:12px;
    box-shadow:0 1px 4px rgba(0,0,0,0.12);
    margin-bottom:18px;
}
.summary-row {
    display:flex;
    justify-content:space-between;
    margin:4px 0;
    font-size:14px;
}
.summary-label { color:#6b7280; }

/* RESPONSIVE FORM */
.form-box {
    background:white;
    padding:22px;
    border-radius:12px;
    box-shadow:0 1px 4px rgba(0,0,0,0.12);
}
.form-row {
    margin-bottom:15px;
}
.form-row label {
    display:block;
    font-weight:600;
    margin-bottom:4px;
}
.form-row input,
.form-row select {
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}
.save-btn {
    background:#28a745;
    color:white;
    padding:12px 20px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
}
.save-btn:hover { background:#1f8a39; }

.delete-btn {
    background:#dc3545;
    color:white;
    padding:12px 20px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

@media(min-width:900px){
    .form-row-half { display:flex; gap:20px; }
    .form-row-half .form-row { flex:1; }
}
</style>

<div class="page-container">

    <h2 style="margin-bottom:20px;">
        <?= $id ? "Επεξεργασία Επαγγελματία" : "Νέος Επαγγελματίας" ?>
    </h2>

    <!-- ============================================================
         MOBILE SUMMARY CARD (ΜΟΝΟ ΑΝ ΕΙΝΑΙ EDIT)
    ============================================================= -->
    <?php if ($pro): ?>
    <div class="summary-card">

        <div class="summary-row">
            <span class="summary-label">Κωδικός:</span>
            <span><?= htmlspecialchars($pro["code"]) ?></span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Ονοματεπώνυμο:</span>
            <span><?= htmlspecialchars($pro["fullname"]) ?></span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Κατάσταση:</span>
            <span style="color:<?= $pro["is_active"] ? '#28a745':'#dc3545' ?>;">
                <?= $pro["is_active"] ? "Ενεργός" : "Ανενεργός" ?>
            </span>
        </div>

        <div class="summary-row">
            <span class="summary-label">ΦΠΑ:</span>
            <span>
                <?= $pro["has_vat"] ? "ΝΑΙ" : "ΟΧΙ" ?>
                <?= $pro["vat_type"] ? "(" . htmlspecialchars($pro["vat_type"]) . ")" : "" ?>
            </span>
        </div>

        <div class="summary-row">
            <span class="summary-label">Μισθοδοσία:</span>
            <span><?= $pro["has_payroll"] ? "ΝΑΙ" : "ΟΧΙ" ?></span>
        </div>

    </div>
    <?php endif; ?>

    <!-- ============================================================
         ΦΟΡΜΑ
    ============================================================= -->
    <form method="post" class="form-box">

        <div class="form-row">
            <label>Κωδικός</label>
            <input type="text" name="code"
                   value="<?= $pro["code"] ?? "" ?>" required>
        </div>

        <div class="form-row">
            <label>Ονοματεπώνυμο</label>
            <input type="text" name="fullname"
                   value="<?= $pro["fullname"] ?? "" ?>" required>
        </div>

        <div class="form-row-half">

            <div class="form-row">
                <label>Έναρξη</label>
                <input type="date" name="start_date"
                       value="<?= $pro["start_date"] ?? "" ?>">
            </div>

            <div class="form-row">
                <label>Διακοπή</label>
                <input type="date" name="end_date"
                       value="<?= $pro["end_date"] ?? "" ?>">
            </div>

        </div>

        <div class="form-row">
            <label><input type="checkbox" name="is_active"
                   <?= isset($pro["is_active"]) && $pro["is_active"] ? "checked" : "" ?>>
                   Ενεργός</label>
        </div>

        <div class="form-row">
            <label><input type="checkbox" name="has_vat"
                   <?= isset($pro["has_vat"]) && $pro["has_vat"] ? "checked" : "" ?>>
                   Υπόκειται σε ΦΠΑ</label>
        </div>

        <div class="form-row">
            <label>Τύπος ΦΠΑ</label>
            <select name="vat_type">
                <option value="">—</option>
                <option value="Μηνιαίο"
                    <?= ($pro["vat_type"] ?? "") === "Μηνιαίο" ? "selected" : "" ?>>Μηνιαίο</option>
                <option value="Τριμηνιαίο"
                    <?= ($pro["vat_type"] ?? "") === "Τριμηνιαίο" ? "selected" : "" ?>>Τριμηνιαίο</option>
            </select>
        </div>

        <div class="form-row">
            <label>
                <input type="checkbox" name="has_payroll"
                    <?= isset($pro["has_payroll"]) && $pro["has_payroll"] ? "checked" : "" ?>>
                Μισθοδοσία
            </label>
        </div>

        <br>

        <button type="submit" class="save-btn">💾 Αποθήκευση</button>

        <?php if ($pro): ?>
            <a href="active_professional_form.php?delete=<?= $pro['id'] ?>"
               class="delete-btn"
               onclick="return confirm('Σίγουρα διαγραφή;')">🗑 Διαγραφή</a>
        <?php endif; ?>

    </form>

</div>

<?php require "includes/footer.php"; ?>
