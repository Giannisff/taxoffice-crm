<?php
require_once 'includes/db.php';
require_once "includes/auth.php";

$id = $_GET['id'] ?? null;
$deleteId = $_GET['delete'] ?? null;

//
// ΔΙΑΓΡΑΦΗ ΣΥΝΕΡΓΑΤΗ
//
if ($deleteId) {
    $stmt = $mysqli->prepare("DELETE FROM partners WHERE id = ?");
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    header('Location: partners.php');
    exit;
}

//
// ΑΡΧΙΚΕΣ ΤΙΜΕΣ ΦΟΡΜΑΣ
//
$partner = [
    'fullname' => '',
    'phone' => '',
    'email' => ''
];

//
// ΦΟΡΤΩΣΗ ΥΠΑΡΧΟΝΤΟΣ ΣΥΝΕΡΓΑΤΗ
//
if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM partners WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $partner = $stmt->get_result()->fetch_assoc();
}

//
// ΑΠΟΘΗΚΕΥΣΗ (ΝΕΟΣ / ΕΠΕΞΕΡΓΑΣΙΑ)
//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?: null;
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if ($id) {
        // ΕΝΗΜΕΡΩΣΗ
        $stmt = $mysqli->prepare("
            UPDATE partners SET fullname=?, phone=?, email=? WHERE id=?
        ");
        $stmt->bind_param('sssi', $fullname, $phone, $email, $id);

    } else {
        // ΚΑΤΑΧΩΡΗΣΗ ΝΕΟΥ
        $stmt = $mysqli->prepare("
            INSERT INTO partners (fullname, phone, email)
            VALUES (?,?,?)
        ");
        $stmt->bind_param('sss', $fullname, $phone, $email);
    }

    $stmt->execute();
    header('Location: partners.php');
    exit;
}

$pageTitle = $id ? 'Επεξεργασία Συνεργάτη' : 'Νέος Συνεργάτης';
require 'includes/header.php';
?>

<form method="post">
<input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

<div class="form-card">

    <div class="form-row">
        <div class="form-group">
            <label>Ονοματεπώνυμο</label>
            <input type="text" name="fullname" required
                   value="<?= htmlspecialchars($partner['fullname']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Τηλέφωνο</label>
            <input type="text" name="phone"
                   value="<?= htmlspecialchars($partner['phone']) ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($partner['email']) ?>">
        </div>
    </div>

    <div class="form-actions">
        <a href="partners.php" class="btn btn-secondary">Άκυρο</a>

        <?php if ($id): ?>
            <a href="partner_form.php?delete=<?= $id ?>" 
               class="btn btn-danger"
               onclick="return confirm('Διαγραφή συνεργάτη;');">
               Διαγραφή
            </a>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Αποθήκευση</button>
    </div>

</div>
</form>

<?php require 'includes/footer.php'; ?>
