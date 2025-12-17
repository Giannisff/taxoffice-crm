<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

/* ----------------------------------------------------------
   ΔΙΑΓΡΑΦΗ
----------------------------------------------------------- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: tasks.php");
    exit;
}

/* ----------------------------------------------------------
   ΕΠΕΞΕΡΓΑΣΙΑ / ΝΕΑ ΕΡΓΑΣΙΑ
----------------------------------------------------------- */
$id = $_GET['id'] ?? null;

$task = [
    'task_date'    => date('Y-m-d'),
    'title'        => '',
    'client_id'    => 0,
    'client_name'  => '',
    'partner_id'   => '',
    'status'       => 'Σε εξέλιξη',
    'fee'          => 0,
    'collected'    => 0,
    'notes'        => ''
];

if ($id) {
    $id = (int)$id;

    // Φέρνουμε και client_name για το input
    $stmt = $mysqli->prepare("
        SELECT t.*, c.name AS client_name
        FROM tasks t
        LEFT JOIN clients c ON c.id = t.client_id
        WHERE t.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $task = $row;
        if (!isset($task['client_name'])) $task['client_name'] = '';
    }
}

/* ----------------------------------------------------------
   ΑΠΟΘΗΚΕΥΣΗ
----------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_task'])) {

    $task_date  = $_POST['task_date'] ?? date('Y-m-d');
    $title      = trim($_POST['title'] ?? '');
    $client_id  = (int)($_POST['client_id'] ?? 0);
    $partner_id = ($_POST['partner_id'] ?? '') !== '' ? (int)$_POST['partner_id'] : null;
    $status     = $_POST['status'] ?? 'Σε εξέλιξη';
    $fee        = (float)str_replace(',', '.', ($_POST['fee'] ?? '0'));
    $collected  = (float)str_replace(',', '.', ($_POST['collected'] ?? '0'));
    $notes      = $_POST['notes'] ?? '';

    if ($client_id <= 0) {
        // Αν θες μπορείς να βάλεις validation μήνυμα. Προς το παρόν απλά δεν σώζουμε.
        header("Location: task_form.php" . ($id ? "?id=".(int)$id : ""));
        exit;
    }

    if ($id) {
        $stmt = $mysqli->prepare("
            UPDATE tasks
            SET task_date=?, title=?, client_id=?, partner_id=?, status=?, fee=?, collected=?, notes=?, updated_at=NOW()
            WHERE id=?
        ");

        // ΣΩΣΤΟ types: s s i i s d d s i  => "ssiisddsi"
        $stmt->bind_param(
            "ssiisddsi",
            $task_date,
            $title,
            $client_id,
            $partner_id,
            $status,
            $fee,
            $collected,
            $notes,
            $id
        );

    } else {
        $stmt = $mysqli->prepare("
            INSERT INTO tasks (task_date, title, client_id, partner_id, status, fee, collected, notes)
            VALUES (?,?,?,?,?,?,?,?)
        ");

        // ΣΩΣΤΟ types: s s i i s d d s => "ssiisdds"
        $stmt->bind_param(
            "ssiisdds",
            $task_date,
            $title,
            $client_id,
            $partner_id,
            $status,
            $fee,
            $collected,
            $notes
        );
    }

    $stmt->execute();
    header("Location: tasks.php");
    exit;
}

/* ----------------------------------------------------------
   ΛΙΣΤΑ ΣΥΝΕΡΓΑΤΩΝ
----------------------------------------------------------- */
$partners = $mysqli->query("SELECT id, fullname FROM partners ORDER BY fullname");

$pageTitle = ($id ? "Επεξεργασία Εργασίας" : "Νέα Εργασία");
require "includes/header.php";
?>

<!-- Bootstrap CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Dropdown αποτελεσμάτων πελάτη */
#client_results .client-item {
    padding: 10px 10px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}
#client_results .client-item:hover {
    background: #f8fafc;
}
</style>

<script>
// Καλείται από save_client_ajax.php μέσα από iframe (όπως το έχεις)
function addClientToDropdown(id, name) {
    document.getElementById("client_search").value = name;
    document.getElementById("client_id").value = id;

    const box = document.getElementById("client_results");
    box.style.display = "none";
    box.innerHTML = "";

    const modal = bootstrap.Modal.getInstance(document.getElementById("addClientModal"));
    if (modal) modal.hide();
}
</script>

<!-- ============================================
     ΦΟΡΜΑ ΕΡΓΑΣΙΑΣ
============================================ -->
<form method="post">
    <input type="hidden" name="save_task" value="1">

    <div class="form-card">

        <div class="form-row">
            <div class="form-group">
                <label>Ημερομηνία</label>
                <input type="date" name="task_date" style="height:48px;"
                       value="<?= htmlspecialchars($task['task_date']) ?>">
            </div>

            <div class="form-group">
                <label>Τίτλος</label>
                <input type="text" name="title" style="height:48px;"
                       value="<?= htmlspecialchars($task['title']) ?>">
            </div>
        </div>

        <!-- Πελάτης + Προσθήκη + AJAX Search -->
        <div class="form-row">

            <div class="form-group" style="position:relative;">
                <label>Πελάτης</label>

                <div style="display:flex; gap:8px;">
                    <input type="text"
                           id="client_search"
                           placeholder="Αναζήτηση πελάτη..."
                           autocomplete="off"
                           style="height:48px;"
                           value="<?= htmlspecialchars($task['client_name'] ?? '') ?>">

                    <input type="hidden"
                           name="client_id"
                           id="client_id"
                           value="<?= (int)($task['client_id'] ?? 0) ?>">

                    <button type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#addClientModal"
                            style="height:48px;">+</button>
                </div>

                <div id="client_results"
                     style="
                        position:absolute;
                        top:100%;
                        left:0;
                        right:0;
                        background:#fff;
                        border:1px solid #ddd;
                        border-radius:6px;
                        z-index:9999;
                        display:none;
                        max-height:240px;
                        overflow-y:auto;">
                </div>
            </div>

            <div class="form-group">
                <label>Συνεργάτης</label>
                <select name="partner_id" style="height:48px;">
                    <option value="">—</option>
                    <?php while ($p = $partners->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>" <?= ((string)$p['id'] === (string)($task['partner_id'] ?? '')) ? "selected":"" ?>>
                            <?= htmlspecialchars($p['fullname']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Κατάσταση</label>
                <select name="status" style="height:48px;">
                    <option <?= ($task['status']=="Σε εξέλιξη" ? "selected":"") ?>>Σε εξέλιξη</option>
                    <option <?= ($task['status']=="Ολοκληρωμένη" ? "selected":"") ?>>Ολοκληρωμένη</option>
                    <option <?= ($task['status']=="Αναμονή εξόφλησης" ? "selected":"") ?>>Αναμονή εξόφλησης</option>
                </select>
            </div>

        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Αμοιβή (€)</label>
                <input type="text" name="fee" style="height:48px;"
                       value="<?= number_format((float)$task['fee'],2,',','.') ?>">
            </div>

            <div class="form-group">
                <label>Είσπραξη (€)</label>
                <input type="text" name="collected" style="height:48px;"
                       value="<?= number_format((float)$task['collected'],2,',','.') ?>">
            </div>

            <div class="form-group">
                <label>Υπόλοιπο (€)</label>
                <?php $balance = (float)$task['fee'] - (float)$task['collected']; ?>
                <input type="text" disabled style="height:48px; background:#eee;"
                       value="<?= number_format($balance,2,',','.') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Παρατηρήσεις</label>
                <textarea name="notes" rows="4"><?= htmlspecialchars($task['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="tasks.php" class="btn btn-secondary">Άκυρο</a>
            <button class="btn btn-primary" type="submit">Αποθήκευση</button>
        </div>

    </div>
</form>

<!-- ============================================
     BOOTSTRAP MODAL — ΝΕΟΣ ΠΕΛΑΤΗΣ
============================================ -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="clientModalForm"
                  method="post"
                  action="save_client_ajax.php"
                  target="clientSaveFrame">

                <div class="modal-header">
                    <h5 class="modal-title">Νέος Πελάτης</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label>Επωνυμία</label>
                    <input type="text" name="name" class="form-control" required>

                    <label class="mt-2">ΑΦΜ</label>
                    <input type="text" name="afm" class="form-control">

                    <label class="mt-2">Κατηγορία</label>
                    <select name="category" class="form-control" required>
                        <option value="Ιδιώτης">Ιδιώτης</option>
                        <option value="Επιχείρηση">Επιχείρηση</option>
                    </select>

                    <label class="mt-2">Τηλέφωνο</label>
                    <input type="text" name="phone" class="form-control">

                    <label class="mt-2">Email</label>
                    <input type="email" name="email" class="form-control">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
                    <button class="btn btn-primary" type="submit">Αποθήκευση</button>
                </div>

            </form>

            <iframe name="clientSaveFrame" style="display:none;"></iframe>

        </div>
    </div>
</div>

<script>
let timer = null;

const input = document.getElementById("client_search");
const box   = document.getElementById("client_results");

function hideResults() {
    box.style.display = "none";
}

function renderResults(data) {
    box.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
        box.innerHTML = "<div style='padding:10px;color:#6b7280;'>Δεν βρέθηκαν αποτελέσματα</div>";
        box.style.display = "block";
        return;
    }

    data.forEach(c => {
        const div = document.createElement("div");
        div.className = "client-item";
        div.innerHTML = `<strong>${c.name}</strong>` + (c.afm ? `<br><small>${c.afm}</small>` : "");
        div.onclick = () => {
            input.value = c.name;
            document.getElementById("client_id").value = c.id;
            hideResults();
        };
        box.appendChild(div);
    });

    box.style.display = "block";
}

input.addEventListener("input", function () {
    const q = this.value.trim();

    // αν αλλάξει το κείμενο, καθάρισε το client_id για να μην μείνει "λάθος" παλιός πελάτης
    document.getElementById("client_id").value = "";

    clearTimeout(timer);

    if (q.length < 2) {
        hideResults();
        return;
    }

    timer = setTimeout(() => {
        fetch("ajax/search_clients.php?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(renderResults)
            .catch(() => {
                box.innerHTML = "<div style='padding:10px;color:#dc3545;'>Σφάλμα αναζήτησης</div>";
                box.style.display = "block";
            });
    }, 250);
});

// Κλείσιμο dropdown όταν κάνεις click έξω
document.addEventListener("click", function(e) {
    if (!box.contains(e.target) && e.target !== input) {
        hideResults();
    }
});
</script>

<?php require "includes/footer.php"; ?>
