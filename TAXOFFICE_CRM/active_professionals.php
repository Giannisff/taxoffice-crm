<?php
$pageTitle = "Ενεργοί Επαγγελματίες";
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

/* ============================================================
   ΣΤΑΤΙΣΤΙΚΑ ΠΛΗΘΟΥΣ
============================================================ */
$count_total    = $mysqli->query("SELECT COUNT(*) AS c FROM active_professionals")->fetch_assoc()['c'];
$count_active   = $mysqli->query("SELECT COUNT(*) AS c FROM active_professionals WHERE is_active = 1")->fetch_assoc()['c'];
$count_inactive = $mysqli->query("SELECT COUNT(*) AS c FROM active_professionals WHERE is_active = 0")->fetch_assoc()['c'];

/* ============================================================
   ΦΙΛΤΡΑ
============================================================ */
$search    = $_GET['search']    ?? "";
$has_vat   = $_GET['has_vat']   ?? "";
$vat_type  = $_GET['vat_type']  ?? "";
$payroll   = $_GET['payroll']   ?? "";

/* Default: Εμφάνιση μόνο ΕΝΕΡΓΩΝ */
if (!isset($_GET['is_active'])) {
    $is_active = "1";
} else {
    $is_active = $_GET['is_active'];
}

$where = " WHERE 1=1 ";

if ($search !== "") {
    $s = $mysqli->real_escape_string($search);
    $where .= " AND (fullname LIKE '%$s%' OR code LIKE '%$s%')";
}

if ($is_active !== "") {
    $a = (int)$is_active;
    $where .= " AND is_active = $a";
}

if ($has_vat !== "") {
    $v = (int)$has_vat;
    $where .= " AND has_vat = $v";
}

if ($vat_type !== "") {
    $v = $mysqli->real_escape_string($vat_type);
    $where .= " AND vat_type = '$v'";
}

if ($payroll !== "") {
    $p = (int)$payroll;
    $where .= " AND has_payroll = $p";
}

/* ============================================================
   QUERY
============================================================ */
$sql = "
    SELECT *
    FROM active_professionals
    $where
    ORDER BY code ASC
";
$result = $mysqli->query($sql);
$pros = [];
while ($row = $result->fetch_assoc()) {
    $pros[] = $row;
}

require "includes/header.php";
?>

<style>
/* ===== DASHBOARD BOXES ===== */
.stats-grid {
    display:flex;
    gap:15px;
    margin-bottom:20px;
}
.stat-box {
    flex:1;
    padding:18px;
    border-radius:12px;
    color:white;
    text-align:center;
    font-size:15px;
    font-weight:600;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
.stat-num {
    font-size:26px;
    font-weight:700;
    margin-top:4px;
}
.stat-blue { background:#0d6efd; }
.stat-green { background:#28a745; }
.stat-red { background:#dc3545; }

/* ===== MOBILE CARDS ===== */
@media (max-width: 768px) {
    .desktop-table { display:none; }
    .mobile-cards { display:block; }
    .stats-grid { flex-direction:column; }
}
@media (min-width: 769px) {
    .desktop-table { display:block; }
    .mobile-cards { display:none; }
}

.mobile-card {
    background:#fff;
    border-radius:12px;
    padding:10px 12px;
    margin-bottom:10px;
    box-shadow:0 1px 4px rgba(15,23,42,0.10);
    font-size:13px;
}

.mobile-card-header {
    display:flex;
    justify-content:space-between;
    margin-bottom:4px;
}

.mobile-card-actions {
    text-align:right;
    margin-top:8px;
}

@media (max-width: 768px) {
    .mobile-card-actions .icon-btn {
        width:30px;
        height:30px;
        font-size:16px;
    }
}
</style>

<div class="page-container">

    <!-- ============================================================
         ΣΤΑΤΙΣΤΙΚΑ ΚΟΥΤΙΑ
    ============================================================= -->
    <div class="stats-grid">
        <div class="stat-box stat-blue">
            Σύνολο Επαγγελματιών
            <div class="stat-num"><?= $count_total ?></div>
        </div>

        <div class="stat-box stat-green">
            Ενεργοί
            <div class="stat-num"><?= $count_active ?></div>
        </div>

        <div class="stat-box stat-red">
            Ανενεργοί
            <div class="stat-num"><?= $count_inactive ?></div>
        </div>
    </div>

    <!-- ============================================================
         ΚΟΥΜΠΙΑ ΕΝΕΡΓΕΙΩΝ
    ============================================================= -->
    <div class="top-actions">

        <a href="active_professional_form.php"
           class="new-task-btn"
           style="background:#28a745;">
           + Νέος Επαγγελματίας
        </a>

        <a href="imports/import_active_professionals.php" class="btn btn-warning">
            ⬆ Εισαγωγή από Excel
        </a>

        <a href="exports/export_active_professionals_excel.php?<?= http_build_query($_GET) ?>"
           target="_blank"
           class="btn btn-primary">
           ⬇ Εξαγωγή Excel
        </a>

        <a href="exports/export_active_professionals_pdf.php?<?= http_build_query($_GET) ?>"
           target="_blank"
           class="btn btn-danger">
           ⬇ Εξαγωγή PDF
        </a>

    </div>

    <!-- ============================================================
         ΦΙΛΤΡΑ
    ============================================================= -->
    <form method="get">
        <div class="filters-container">
            <div class="filters-row">

                <input type="text" name="search" placeholder="Αναζήτηση..."
                       value="<?= htmlspecialchars($search) ?>">

                <select name="is_active">
                    <option value="">Ενεργός / Ανενεργός</option>
                    <option value="1" <?= $is_active==="1" ? "selected" : "" ?>>Ενεργός</option>
                    <option value="0" <?= $is_active==="0" ? "selected" : "" ?>>Ανενεργός</option>
                </select>

                <select name="has_vat">
                    <option value="">ΦΠΑ</option>
                    <option value="1" <?= $has_vat==="1" ? "selected" : "" ?>>ΝΑΙ</option>
                    <option value="0" <?= $has_vat==="0" ? "selected" : "" ?>>ΟΧΙ</option>
                </select>

                <select name="vat_type">
                    <option value="">Τύπος ΦΠΑ</option>
                    <option value="Μηνιαίο"    <?= $vat_type==="Μηνιαίο" ? "selected" : "" ?>>Μηνιαίο</option>
                    <option value="Τριμηνιαίο" <?= $vat_type==="Τριμηνιαίο" ? "selected" : "" ?>>Τριμηνιαίο</option>
                </select>

                <select name="payroll">
                    <option value="">Μισθοδοσία</option>
                    <option value="1" <?= $payroll==="1" ? "selected" : "" ?>>ΝΑΙ</option>
                    <option value="0" <?= $payroll==="0" ? "selected" : "" ?>>ΟΧΙ</option>
                </select>

                <button class="filter-btn" type="submit">🔍</button>

            </div>
        </div>
    </form>

    <!-- ============================================================
         DESKTOP TABLE
    ============================================================= -->
    <div class="table-wrapper desktop-table">
        <table>
            <thead>
                <tr>
                    <th>Κωδικός</th>
                    <th>Ονοματεπώνυμο</th>
                    <th>Ενεργός</th>
                    <th>Έναρξη</th>
                    <th>Διακοπή</th>
                    <th>ΦΠΑ</th>
                    <th>Τύπος ΦΠΑ</th>
                    <th>Μισθοδοσία</th>
                    <th style="text-align:center;">Ενέργειες</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($pros as $row): ?>
                <?php $statusColor = $row["is_active"] ? "#28a745" : "#dc3545"; ?>
                <tr>
                    <td><?= htmlspecialchars($row["code"]) ?></td>
                    <td><?= htmlspecialchars($row["fullname"]) ?></td>

                    <td>
                        <span class="task-status-square" style="background: <?= $statusColor ?>;"></span>
                        <?= $row["is_active"] ? "Ενεργός" : "Ανενεργός" ?>
                    </td>

                    <td><?= $row["start_date"] ? greekDateFromDb($row["start_date"]) : "" ?></td>
                    <td><?= $row["end_date"]   ? greekDateFromDb($row["end_date"])   : "" ?></td>

                    <td><?= $row["has_vat"] ? "ΝΑΙ" : "ΟΧΙ" ?></td>
                    <td><?= htmlspecialchars($row["vat_type"]) ?></td>

                    <td><?= $row["has_payroll"] ? "ΝΑΙ" : "ΟΧΙ" ?></td>

                    <td class="task-actions">
                        <a class="icon-btn blue" href="active_professional_form.php?id=<?= $row['id'] ?>">✏️</a>
                        <a class="icon-btn red"
                           href="active_professionals.php?delete=<?= $row['id'] ?>"
                           onclick="return confirm('Διαγραφή επαγγελματία;')">🗑</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================================================
         MOBILE CARD VIEW
    ============================================================= -->
    <div class="mobile-cards">
        <?php foreach ($pros as $row): ?>
            <?php $statusColor = $row["is_active"] ? "#22c55e" : "#ef4444"; ?>
            <div class="mobile-card">

                <div class="mobile-card-header">
                    <div class="mobile-card-title">
                        <?= htmlspecialchars($row["code"]) ?> – <?= htmlspecialchars($row["fullname"]) ?>
                    </div>
                    <div style="font-size:12px; color:<?= $statusColor ?>;">
                        <?= $row["is_active"] ? "Ενεργός" : "Ανενεργός" ?>
                    </div>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Έναρξη:</span>
                    <span><?= $row["start_date"] ? greekDateFromDb($row["start_date"]) : "-" ?></span>
                </div>

                <?php if ($row["end_date"]): ?>
                <div class="mobile-card-row">
                    <span class="mobile-card-label">Διακοπή:</span>
                    <span><?= greekDateFromDb($row["end_date"]) ?></span>
                </div>
                <?php endif; ?>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">ΦΠΑ:</span>
                    <span>
                        <?= $row["has_vat"] ? "ΝΑΙ" : "ΟΧΙ" ?>
                        <?php if ($row["vat_type"]): ?>
                            (<?= htmlspecialchars($row["vat_type"]) ?>)
                        <?php endif; ?>
                    </span>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Μισθοδοσία:</span>
                    <span><?= $row["has_payroll"] ? "ΝΑΙ" : "ΟΧΙ" ?></span>
                </div>

                <div class="mobile-card-actions">
                    <a class="icon-btn blue" href="active_professional_form.php?id=<?= $row['id'] ?>">✏️</a>
                    <a class="icon-btn red"
                       href="active_professionals.php?delete=<?= $row['id'] ?>"
                       onclick="return confirm('Διαγραφή επαγγελματία;')">🗑</a>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require "includes/footer.php"; ?>
