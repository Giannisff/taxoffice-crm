<?php
$pageTitle = 'Πελάτες';
require_once 'includes/db.php';
require_once 'includes/functions.php';

/* ===================== ΦΙΛΤΡΑ ====================== */
$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$balanceF = $_GET['balance']  ?? ''; // positive / zero / all
$sort     = $_GET['sort']     ?? '';

$where = " WHERE 1=1 ";

if ($search !== '') {
    $s = $mysqli->real_escape_string($search);
    $where .= " AND (c.name LIKE '%$s%' OR c.afm LIKE '%$s%')";
}

if ($category !== '') {
    $c = $mysqli->real_escape_string($category);
    $where .= " AND c.category = '$c'";
}

/* Βάση query */
$sql = "
    SELECT c.*,
           COALESCE(SUM(t.fee),0) AS total_fees,
           COALESCE(SUM(t.collected),0) AS total_collected,
           COUNT(t.id) AS task_count
    FROM clients c
    LEFT JOIN tasks t ON t.client_id = c.id
    $where
    GROUP BY c.id
";

/* Ταξινόμηση */
$orderBy = " ORDER BY c.name ASC";

if ($sort === 'balance') {
    $orderBy = " ORDER BY (COALESCE(SUM(t.fee),0) - COALESCE(SUM(t.collected),0)) DESC";
} elseif ($sort === 'tasks') {
    $orderBy = " ORDER BY COUNT(t.id) DESC";
}

$sql .= $orderBy;

$res = $mysqli->query($sql);

/* Φιλτράρισμα υπολοίπου σε PHP, για απλότητα */
$clients = [];
while ($c = $res->fetch_assoc()) {
    $c['balance'] = $c['total_fees'] - $c['total_collected'];

    if ($balanceF === 'positive' && $c['balance'] <= 0) {
        continue;
    }
    if ($balanceF === 'zero' && $c['balance'] != 0) {
        continue;
    }
    // all -> όλα

    $clients[] = $c;
}

require 'includes/header.php';
?>

<style>
@media (max-width: 768px) {
    .desktop-table { display:none; }
    .mobile-cards { display:block; }
}
@media (min-width: 769px) {
    .desktop-table { display:block; }
    .mobile-cards { display:none; }
}

.mobile-card {
    background:#ffffff;
    border-radius:12px;
    padding:10px 12px;
    margin-bottom:10px;
    box-shadow:0 1px 4px rgba(15,23,42,0.10);
    font-size:13px;
}
.mobile-card-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:4px;
}
.mobile-card-title {
    font-weight:600;
}
.mobile-card-row {
    display:flex;
    justify-content:space-between;
    margin-top:2px;
}
.mobile-card-label {
    color:#6b7280;
    margin-right:4px;
}
.mobile-card-amounts {
    margin-top:6px;
    border-top:1px dashed #e5e7eb;
    padding-top:6px;
    display:flex;
    flex-wrap:wrap;
    row-gap:2px;
}
.mobile-card-amounts span {
    margin-right:10px;
}
.mobile-card-actions {
    margin-top:8px;
    text-align:right;
}
@media (max-width: 768px) {
    .mobile-card-actions .btn {
        padding:4px 8px;
        font-size:12px;
    }
}
</style>

<div class="page-container">

    <div class="new-task-top">
        <a href="client_form.php" class="new-task-btn" style="background:#28a745;">
            + Νέος Πελάτης
        </a>
    </div>

    <!-- ΦΙΛΤΡΑ ΠΕΛΑΤΩΝ -->
    <form method="get">
    <div class="filters-container">
        <div class="filters-row">

            <input type="text" name="search" placeholder="Αναζήτηση..."
                   value="<?= htmlspecialchars($search) ?>">

            <select name="category">
                <option value="">Κατηγορία</option>
                <option value="Ιδιώτης"   <?= $category==='Ιδιώτης'   ? 'selected' : '' ?>>Ιδιώτης</option>
                <option value="Επιχείρηση" <?= $category==='Επιχείρηση' ? 'selected' : '' ?>>Επιχείρηση</option>
                <option value="Εταιρεία"   <?= $category==='Εταιρεία'   ? 'selected' : '' ?>>Εταιρεία</option>
            </select>

            <select name="balance">
                <option value="">Υπόλοιπο</option>
                <option value="positive" <?= $balanceF==='positive' ? 'selected' : '' ?>>Με Υπόλοιπο</option>
                <option value="zero"     <?= $balanceF==='zero'     ? 'selected' : '' ?>>Μηδενικό</option>
                <option value="all"      <?= $balanceF==='all'      ? 'selected' : '' ?>>Όλοι</option>
            </select>

            <select name="sort">
                <option value="">Ταξινόμηση</option>
                <option value="name"    <?= $sort==='name'    ? 'selected' : '' ?>>A–Ω</option>
                <option value="balance" <?= $sort==='balance' ? 'selected' : '' ?>>Υπόλοιπο</option>
                <option value="tasks"   <?= $sort==='tasks'   ? 'selected' : '' ?>>Αριθμός εργασιών</option>
            </select>

            <button class="filter-btn" type="submit">🔍</button>
        </div>
    </div>
    </form>

    <!-- ΠΙΝΑΚΑΣ DESKTOP -->
    <div class="table-wrapper desktop-table">
        <table>
            <thead>
                <tr>
                    <th>Επωνυμία</th>
                    <th>ΑΦΜ</th>
                    <th>Κατηγορία</th>
                    <th>Τηλέφωνο</th>
                    <th>Email</th>
                    <th>Αμοιβές</th>
                    <th>Εισπράξεις</th>
                    <th>Υπόλοιπο</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['afm']) ?></td>
                    <td><?= htmlspecialchars($c['category']) ?></td>
                    <td><?= htmlspecialchars($c['phone']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= formatMoney($c['total_fees']) ?></td>
                    <td><?= formatMoney($c['total_collected']) ?></td>
                    <td><?= formatMoney($c['balance']) ?></td>
                    <td class="task-actions">
                <a class="icon-btn blue" href="client_form.php?id=<?= $c['id'] ?>">✏️</a>
                <a class="icon-btn gray" href="tasks.php?client=<?= $c['id'] ?>">📄</a>
                <a class="icon-btn red"
                   href="client_form.php?delete=<?= $c['id'] ?>"
                   onclick="return confirm('Διαγραφή πελάτη;');">🗑</a>
            </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- MOBILE CARDS -->
    <div class="mobile-cards">
        <?php foreach ($clients as $c): ?>
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <div class="mobile-card-title"><?= htmlspecialchars($c['name']) ?></div>
                    <?php if ($c['afm']): ?>
                        <div style="font-size:12px;color:#6b7280;">ΑΦΜ: <?= htmlspecialchars($c['afm']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mobile-card-row">
                    <span class="mobile-card-label">Κατηγορία:</span>
                    <span><?= htmlspecialchars($c['category']) ?></span>
                </div>

                <?php if ($c['phone']): ?>
                <div class="mobile-card-row">
                    <span class="mobile-card-label">Τηλέφωνο:</span>
                    <span><?= htmlspecialchars($c['phone']) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($c['email']): ?>
                <div class="mobile-card-row">
                    <span class="mobile-card-label">Email:</span>
                    <span><?= htmlspecialchars($c['email']) ?></span>
                </div>
                <?php endif; ?>

                <div class="mobile-card-amounts">
                    <span><strong>Αμοιβές:</strong> <?= formatMoney($c['total_fees']) ?></span>
                    <span><strong>Εισπράξεις:</strong> <?= formatMoney($c['total_collected']) ?></span>
                    <span><strong>Υπόλοιπο:</strong> <?= formatMoney($c['balance']) ?></span>
                </div>

                <div class="mobile-card-actions">
                    <a href="client_form.php?id=<?= $c['id'] ?>" class="btn btn-secondary">Επεξεργασία</a>
                    <a href="tasks.php?client=<?= $c['id'] ?>" class="btn btn-warning">Εργασίες</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require 'includes/footer.php'; ?>
