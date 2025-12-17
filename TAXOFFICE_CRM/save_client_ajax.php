<?php
require_once "includes/db.php";
require_once "includes/auth.php";

// --- Λήψη δεδομένων από modal ---
$name     = trim($_POST['name'] ?? '');
$afm      = trim($_POST['afm'] ?? '');
$category = trim($_POST['category'] ?? 'Ιδιώτης');
$phone    = trim($_POST['phone'] ?? '');
$email    = trim($_POST['email'] ?? '');

// --- Ασφάλεια: αν δεν υπάρχει όνομα, σταμάτα ---
if ($name === '') {
    exit("<script>alert('Το όνομα είναι υποχρεωτικό');</script>");
}

// --- Εισαγωγή πελάτη ---
$stmt = $mysqli->prepare("
    INSERT INTO clients (name, afm, category, phone, email)
    VALUES (?,?,?,?,?)
");
$stmt->bind_param("sssss", $name, $afm, $category, $phone, $email);
$stmt->execute();

$newId = $stmt->insert_id;

// --- ΕΠΙΣΤΡΟΦΗ SCRIPT ΠΡΟΣ ΤΟ task_form.php ---
echo "
<script>
// Προσθήκη στο dropdown
window.parent.addClientToDropdown($newId, '".addslashes($name)."');

// Κλείσιμο modal
window.parent.document.querySelector('#addClientModal .btn-close').click();

// Καθαρισμός φόρμας του modal
window.parent.document.getElementById('clientModalForm').reset();
</script>
";
?>
