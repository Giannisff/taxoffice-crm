<?php
$pageTitle = 'Ανάλυση Συνεργατών';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once "includes/auth.php";

$partnerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ---------------- ΛΙΣΤΑ ΣΥΝΕΡΓΑΤΩΝ ΜΕ ΣΥΝΟΛΑ ---------------- */
$sqlPartners = "
    SELECT p.*,
           COUNT(t.id) AS task_count,
           COALESCE(SUM(t.fee),0) AS total_fees,
           COALESCE(SUM(t.collected),0) AS total_collected
    FROM partners p
    LEFT JOIN tasks t ON t.partner_id = p.id
    GROUP BY p.id
    ORDER BY p.fullname
";
$resPartners = $mysqli->query($sqlPartners);

/* Αν υπάρχει επιλεγμένος συνεργάτης, φέρνουμε και τις εργασίες του */
$partner = null;
$tasksRes = null;

if ($partnerId > 0) {

    $stmt = $mysqli->prepare("SELECT * FROM partners WHERE id=?");
    $stmt->bind_param("i", $partnerId);
    $stmt->execute();
    $partner = $stmt->get_result()->fetch_assoc();

    if ($partner) {
        $stmt2 = $mysqli->prepare("
            SELECT t.*, c.name AS client_name
            FROM tasks t
            LEFT JOIN clients c ON c.id = t.client_id
            WHERE t.partner_id=?
            ORDER BY t.task_date DESC, t.id DESC
        ");
        $stmt2->bind_param("i", $partnerId);
        $stmt2->execute();
        $tasksRes = $stmt2->get_result();
    }
}

require 'includes/header.php';
?>

<div class="page-container">

    <div class="page-title">Ανάλυση Συνεργατών</div>

    <!-- Λίστα συνεργατών με συνολικά στοιχεία -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Συνεργάτης</th>
                    <th>Τηλέφωνο</th>
                    <th>Email</th>
                    <th>Αριθμός Εργασιών</th>
                    <th>Αμοιβές</th>
                    <th>Εισπράξεις</th>
                    <th>Υπόλοιπο</th>
                    <th style="text-align:center;">Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $resPartners->fetch_assoc()):
                $balance = $p['total_fees'] - $p['total_collected'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($p['fullname']) ?></td>
                    <td><?= htmlspecialchars($p['phone']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= (int)$p['task_count'] ?></td>
                    <td><?= formatMoney($p['total_fees']) ?></td>
                    <td><?= formatMoney($p['total_collected']) ?></td>
                    <td><?= formatMoney($balance) ?></td>
                    <td class="task-actions">
                        <a href="analysis_partners.php?id=<?= $p['id'] ?>"
                           class="icon-btn blue"
                           title="Εργασίες Συνεργάτη">
                            📋
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>


    <?php if ($partner && $tasksRes): ?>
        <br><br>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div class="page-title" style="margin-bottom:0; font-size:20px;">
                Εργασίες Συνεργάτη: <?= htmlspecialchars($partner['fullname']) ?>
            </div>

            <a href="analysis_partners.php" class="btn btn-secondary">
                ← Πίσω στη λίστα
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ημ/νία</th>
                        <th>Τίτλος</th>
                        <th>Πελάτης</th>
                        <th>Κατάσταση</th>
                        <th>Αμοιβή</th>
                        <th>Είσπραξη</th>
                        <th>Υπόλοιπο</th>
                        <th style="text-align:center;">Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($t = $tasksRes->fetch_assoc()):
                    $balance = $t['fee'] - $t['collected'];

                    // Status square χρώματα
                    $statusColor = "#fd7e14"; // default: Σε εξέλιξη
                    if ($t['status'] == "Ολοκληρωμένη") {
                        $statusColor = "#28a745";
                    } elseif ($t['status'] == "Αναμονή εξόφλησης") {
                        $statusColor = "#dc3545";
                    }
                ?>
                    <tr>
                        <td><?= greekDateFromDb($t['task_date']) ?></td>
                        <td class="tasks-table-title">
                            <span class="task-status-square" style="background: <?= $statusColor ?>;"></span>
                            <?= htmlspecialchars($t['title']) ?>
                        </td>
                        <td><?= htmlspecialchars($t['client_name']) ?></td>
                        <td><?= htmlspecialchars($t['status']) ?></td>
                        <td><?= number_format($t['fee'],2,',','.') ?> €</td>
                        <td><?= number_format($t['collected'],2,',','.') ?> €</td>
                        <td><?= number_format($balance,2,',','.') ?> €</td>
                        <td class="task-actions">
                            <a class="icon-btn gray" href="task_form.php?id=<?= $t['id'] ?>">👁</a>
                            <a class="icon-btn blue" href="task_form.php?id=<?= $t['id'] ?>">✏️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>
