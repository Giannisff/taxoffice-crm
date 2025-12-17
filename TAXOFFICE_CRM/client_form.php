<?php
require_once 'includes/db.php';
require_once "includes/auth.php";

$id = $_GET['id'] ?? null;
$deleteId = $_GET['delete'] ?? null;

if ($deleteId) {
    $stmt = $mysqli->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    header('Location: clients.php');
    exit;
}

$client = [
    'name' => '',
    'afm' => '',
    'category' => 'Ιδιώτης',
    'phone' => '',
    'email' => ''
];

if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?: null;
    $name = $_POST['name'];
    $afm = $_POST['afm'];
    $category = $_POST['category'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if ($id) {
        $stmt = $mysqli->prepare("
            UPDATE clients SET name=?, afm=?, category=?, phone=?, email=? WHERE id=?
        ");
        $stmt->bind_param('sssssi', $name, $afm, $category, $phone, $email, $id);
    } else {
        $stmt = $mysqli->prepare("
            INSERT INTO clients (name, afm, category, phone, email)
            VALUES (?,?,?,?,?)
        ");
        $stmt->bind_param('sssss', $name, $afm, $category, $phone, $email);
    }
    $stmt->execute();
    header('Location: clients.php');
    exit;
}

$pageTitle = $id ? 'Επεξεργασία Πελάτη' : 'Νέος Πελάτης';
require 'includes/header.php';
?>

<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="form-card">
        <div class="form-row">
            <div class="form-group">
                <label>Επωνυμία</label>
                <input type="text" name="name" value="<?= htmlspecialchars($client['name']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>ΑΦΜ</label>
                <input type="text" name="afm" value="<?= htmlspecialchars($client['afm']) ?>">
            </div>
            <div class="form-group">
                <label>Κατηγορία</label>
                <select name="category">
                    <option <?= $client['category']=='Ιδιώτης' ? 'selected' : '' ?>>Ιδιώτης</option>
                    <option <?= $client['category']=='Επιχείρηση' ? 'selected' : '' ?>>Επιχείρηση</option>
                    <option <?= $client['category']=='Λοιπά' ? 'selected' : '' ?>>Λοιπά</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Τηλέφωνο</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($client['phone']) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="clients.php" class="btn btn-secondary">Άκυρο</a>
            <?php if ($id): ?>
                <a href="client_form.php?delete=<?= $id ?>" class="btn btn-danger"
                   onclick="return confirm('Διαγραφή πελάτη;')">Διαγραφή</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Αποθήκευση</button>
        </div>
    </div>
</form>

<?php require 'includes/footer.php'; ?>
