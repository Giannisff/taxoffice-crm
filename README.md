# TAXOFFICE CRM

Το **TAXOFFICE CRM** είναι μια δωρεάν web εφαρμογή CRM, σχεδιασμένη ειδικά για λογιστικά – φοροτεχνικά γραφεία.  
Στόχος της είναι η οργάνωση εργασιών, πελατών, επαγγελματιών και ραντεβού, με έμφαση στην απλότητα και την καθημερινή χρήση.

Η εφαρμογή διατίθεται **δωρεάν σε συναδέλφους** για προσωπική ή επαγγελματική χρήση.

---

## 🚀 Χαρακτηριστικά

- Διαχείριση Εργασιών (CRM)
- Διαχείριση Πελατών
- Διαχείριση Ενεργών / Ανενεργών Επαγγελματιών
- MyDiary – Προσωπικό ημερολόγιο ανά χρήστη
- Ραντεβού & σημειώσεις ημέρας
- Οικονομική παρακολούθηση (αμοιβές – εισπράξεις – υπόλοιπα)
- Εξαγωγές σε Excel & PDF
- Responsive σχεδίαση (Desktop & Mobile)
- Ασφαλές Login με hashed passwords

---

## 🛠️ Τεχνολογίες

- PHP 8.2+
- MySQL / MariaDB
- HTML / CSS / JavaScript
- Bootstrap 5
- Composer (προαιρετικά – για Excel exports)
- PhpSpreadsheet

---

## 📁 Δομή Project (ενδεικτικά)

/
├── includes/
│ ├── db.sample.php
│ ├── auth.php
│ └── functions.php
├── ajax/
│ └── search_clients.php
├── database/
│ └── schema.sql
├── exports/
├── imports/
├── vendor/
├── login.php
├── dashboard.php
├── tasks.php
├── mydiary.php
└── README.md

yaml
Αντιγραφή κώδικα

---

## 📦 Εγκατάσταση

### 🔹 Βήμα 1 – Κατέβασμα Κώδικα

```bash
git clone https://github.com/your-username/taxoffice-crm.git
ή κατεβάστε το ZIP από το GitHub και αποσυμπιέστε το.

🔹 Βήμα 2 – Βάση Δεδομένων
Δημιουργήστε νέα βάση MySQL (π.χ. taxoffice_crm)

Εισάγετε το αρχείο:

pgsql
Αντιγραφή κώδικα
database/schema.sql
ℹ️ Το αρχείο περιλαμβάνει μόνο τη δομή, χωρίς δεδομένα.

🔹 Βήμα 3 – Ρύθμιση Σύνδεσης Βάσης
Αντιγράψτε το αρχείο:

bash
Αντιγραφή κώδικα
includes/db.sample.php
Μετονομάστε το σε:

bash
Αντιγραφή κώδικα
includes/db.php
Συμπληρώστε τα στοιχεία σύνδεσης:

php
Αντιγραφή κώδικα
<?php
$mysqli = new mysqli(
    "localhost",
    "db_user",
    "db_password",
    "db_name"
);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
🔹 Βήμα 4 – Composer (προαιρετικό)
Απαραίτητο μόνο αν χρησιμοποιείτε εξαγωγές Excel:

bash
Αντιγραφή κώδικα
composer install
Αν δεν χρειάζεστε Excel exports, μπορείτε να παραλείψετε αυτό το βήμα.

🔹 Βήμα 5 – Δημιουργία Πρώτου Χρήστη
Εκτελέστε στη βάση δεδομένων:

sql
Αντιγραφή κώδικα
INSERT INTO users (username, password, fullname, role)
VALUES (
  'admin',
  '$2y$10$HASHED_PASSWORD',
  'Administrator',
  'admin'
);
⚠️ Ο κωδικός ΠΡΕΠΕΙ να είναι hashed.

Παράδειγμα δημιουργίας hash σε PHP:

php
Αντιγραφή κώδικα
password_hash('your_password', PASSWORD_DEFAULT);
🔹 Βήμα 6 – Πρόσβαση στην Εφαρμογή
Ανοίξτε στον browser:

pgsql
Αντιγραφή κώδικα
http://your-domain/login.php
Συνδεθείτε με τον χρήστη που δημιουργήσατε.

📅 MyDiary
Το MyDiary είναι προσωπικό ημερολόγιο ανά χρήστη και περιλαμβάνει:

Προβολή εργασιών CRM ανά ημέρα

Προσωπικά ραντεβού

Σημειώσεις ημέρας

Μηνιαία προβολή (calendar ή list view)

🔐 Ασφάλεια
Password hashing (password_hash)

Sessions

SQL prepared statements

Έλεγχος πρόσβασης χρηστών

🤝 Συνεισφορά
Pull requests και προτάσεις βελτίωσης είναι ευπρόσδεκτες.
Η εφαρμογή εξελίσσεται με βάση πραγματικές ανάγκες λογιστικών γραφείων.

📄 Άδεια Χρήσης
MIT License

Η εφαρμογή διατίθεται δωρεάν, χωρίς καμία εγγύηση.
Μπορείτε να τη χρησιμοποιήσετε, να τη τροποποιήσετε και να τη διανείμετε ελεύθερα.

📬 Επικοινωνία
Για απορίες, προτάσεις ή βελτιώσεις, μπορείτε να ανοίξετε issue στο GitHub repository.



---


