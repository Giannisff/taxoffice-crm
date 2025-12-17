<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once "includes/auth.php";

$id = (int)($_GET['id'] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM partners WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$partner = $stmt->get_result()->fetch_assoc();

if (!$partner) {
    die('Συνεργάτης δεν βρέθηκε');
}

$pageTitle = 'Εργασίες Συνεργάτη: ' . $partner['fullname'];

$sql = "
    SELECT t.*, c.name AS client_name
    FROM tasks t
    JOIN clients c ON c.id = t.client_id
    WHERE t.partner_id = ?
    ORDER BY t.task_date DESC
";
$stmt2 = $mysqli->prepare($sql);
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res = $stmt2->get_result();

require 'includes/header.php';
?>

<a href="partners.php" class="btn btn-secondary" style="margin-bottom:12px;">← Επιστροφή στην Αναφορά</a>

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
            <th>Ενέργειες</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($t = $res->fetch_assoc()):
        $balance = $t['fee'] - $t['collected'];
    ?>
        <tr>
            <td><?= greekDateFromDb($t['task_date']) ?></td>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td><?= htmlspecialchars($t['client_name']) ?></td>
            <td><?= htmlspecialchars($t['status']) ?></td>
            <td><?= formatMoney($t['fee']) ?></td>
            <td><?= formatMoney($t['collected']) ?></td>
            <td><?= formatMoney($balance) ?></td>
            <td>
                <a href="task_form.php?id=<?= $t['id'] ?>" class="btn btn-secondary">Επεξεργασία</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php require 'includes/footer.php'; ?>
