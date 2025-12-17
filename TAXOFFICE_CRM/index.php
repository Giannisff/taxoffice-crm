<?php
$pageTitle = 'Αρχική';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once "includes/auth.php";

/* ---------------- DASHBOARD STATS ---------------- */
$res = $mysqli->query("
    SELECT 
        COUNT(*) AS total_tasks,
        SUM(CASE WHEN status='Ολοκληρωμένη' THEN 1 ELSE 0 END) AS completed_tasks,
        SUM(CASE WHEN status='Σε εξέλιξη' THEN 1 ELSE 0 END) AS inprogress_tasks,
        SUM(CASE WHEN status='Αναμονή εξόφλησης' THEN 1 ELSE 0 END) AS pending_tasks,
        COALESCE(SUM(fee),0) AS total_fees,
        COALESCE(SUM(collected),0) AS total_collected
    FROM tasks
");
$stats = $res->fetch_assoc();
$balance = $stats['total_fees'] - $stats['total_collected'];

require 'includes/header.php';
?>

<!-- ===========================================================
     DASHBOARD CARDS
=========================================================== -->
<div class="cards-row">
    <div class="card card-blue">
        <h3>Σύνολο Εργασιών</h3>
        <div class="card-value"><?= (int)$stats['total_tasks'] ?></div>
    </div>

    <div class="card card-green">
        <h3>Ολοκληρωμένες</h3>
        <div class="card-value"><?= (int)$stats['completed_tasks'] ?></div>
    </div>

    <div class="card card-orange">
        <h3>Σε εξέλιξη</h3>
        <div class="card-value"><?= (int)$stats['inprogress_tasks'] ?></div>
    </div>

    <div class="card card-yellow">
        <h3>Αναμονή εξόφλησης</h3>
        <div class="card-value"><?= (int)$stats['pending_tasks'] ?></div>
    </div>
</div>

<div class="cards-row">
    <div class="card card-purple">
        <h3>Σύνολο Αμοιβών</h3>
        <div class="card-value"><?= formatMoney($stats['total_fees']) ?></div>
    </div>

    <div class="card card-teal">
        <h3>Εισπράξεις</h3>
        <div class="card-value"><?= formatMoney($stats['total_collected']) ?></div>
    </div>

    <div class="card card-dark">
        <h3>Υπόλοιπο</h3>
        <div class="card-value"><?= formatMoney($balance) ?></div>
    </div>
</div>

<!-- ===========================================================
     ΦΙΛΤΡΑ ΕΡΓΑΣΙΩΝ
=========================================================== -->
<h3 style="margin-top:25px;">Φίλτρα Εργασιών</h3>

<form method="get" id="taskFilters">
<div class="filters-container">
    <div class="filters-row">

        <input type="text" name="search" placeholder="Αναζήτηση...">

        <select name="status">
            <option value="">Κατάσταση</option>
            <option>Σε εξέλιξη</option>
            <option>Ολοκληρωμένη</option>
            <option>Αναμονή εξόφλησης</option>
        </select>

        <select name="client">
            <option value="">Πελάτης</option>
            <?php
            $cl = $mysqli->query("SELECT id,name FROM clients ORDER BY name");
            while ($c = $cl->fetch_assoc()):
            ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <select name="partner">
            <option value="">Συνεργάτης</option>
            <?php
            $pr = $mysqli->query("SELECT id,fullname FROM partners ORDER BY fullname");
            while ($p = $pr->fetch_assoc()):
            ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['fullname']) ?></option>
            <?php endwhile; ?>
        </select>

        <select name="date_mode">
            <option value="">Ημερομηνίες</option>
            <option value="today">Σήμερα</option>
            <option value="week">Αυτή την εβδομάδα</option>
            <option value="month">Αυτόν τον μήνα</option>
            <option value="range">Εύρος ημερομηνιών</option>
        </select>

        <input type="date" name="date_from" style="min-width:150px;">
        <input type="date" name="date_to" style="min-width:150px;">

        <!-- ✔️ ΔΙΟΡΘΩΜΕΝΑ ΚΟΥΜΠΙΑ -->
        <button type="button" id="btnTasksExcel" class="filter-btn" onclick="exportTasksExcel()">
            ⬇ Excel
        </button>

        <button type="button" id="btnTasksPDF" class="filter-btn" style="background:#dc3545;"
            onclick="exportTasksPDF()">
            ⬇ PDF
        </button>

    </div>
</div>
</form>

<!-- ===========================================================
     ΦΙΛΤΡΑ ΠΕΛΑΤΩΝ
=========================================================== -->
<h3 style="margin-top:35px;">Φίλτρα Πελατών</h3>

<form method="get" id="clientFilters">
<div class="filters-container">
    <div class="filters-row">

        <input type="text" name="search" placeholder="Αναζήτηση...">

        <select name="category">
            <option value="">Κατηγορία</option>
            <option>Ιδιώτης</option>
            <option>Επιχείρηση</option>
        </select>

        <select name="balance">
            <option value="">Υπόλοιπο</option>
            <option value="positive">Με Υπόλοιπο</option>
            <option value="zero">Μηδενικό</option>
            <option value="all">Όλοι</option>
        </select>

        <select name="sort">
            <option value="">Ταξινόμηση</option>
            <option value="name">A–Ω</option>
            <option value="balance">Υπόλοιπο</option>
            <option value="tasks">Αριθμός εργασιών</option>
        </select>

        <button type="submit" formaction="exports/export_clients_excel.php" formtarget="_blank" class="filter-btn">
            ⬇ Excel
        </button>

        <button type="submit" formaction="exports/export_clients_pdf.php" formtarget="_blank"
            class="filter-btn" style="background:#dc3545;">
            ⬇ PDF
        </button>

    </div>
</div>
</form>

<!-- ===========================================================
     ΦΙΛΤΡΑ ΣΥΝΕΡΓΑΤΩΝ
=========================================================== -->
<h3 style="margin-top:35px;">Ανάλυση Συνεργατών</h3>

<form method="get" id="partnerAnalysisFilters">
<div class="filters-container">
    <div class="filters-row">

        <select name="partner_id">
            <option value="">Επιλογή συνεργάτη...</option>
            <?php
            $pr = $mysqli->query("SELECT id, fullname FROM partners ORDER BY fullname");
            while ($p = $pr->fetch_assoc()):
            ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['fullname']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" formaction="exports/export_analysis_partners_excel.php" formtarget="_blank"
            class="filter-btn">
            ⬇ Excel
        </button>

        <button type="submit" formaction="exports/export_analysis_partners_pdf.php" formtarget="_blank"
            class="filter-btn" style="background:#dc3545;">
            ⬇ PDF
        </button>

    </div>
</div>
</form>

<?php require 'includes/footer.php'; ?>

<!-- ===========================================================
     JAVASCRIPT — EXPORT FIX
=========================================================== -->
<script>
function exportTasksExcel() {
    const form = document.getElementById("taskFilters");
    form.action = "exports/export_tasks_excel.php";
    form.target = "_blank";
    form.submit();
}

function exportTasksPDF() {
    const form = document.getElementById("taskFilters");
    form.action = "exports/export_tasks_pdf.php";
    form.target = "_blank";
    form.submit();
}
</script>
