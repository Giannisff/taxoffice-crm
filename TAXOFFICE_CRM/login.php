<?php
session_start();
require_once "includes/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Fetch user
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Validate password
        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["username"]  = $user["username"];
            $_SESSION["fullname"]  = $user["fullname"];
            $_SESSION["role"]      = $user["role"];

            header("Location: index.php");
            exit;
        }
    }

    $error = "Λάθος στοιχεία σύνδεσης!";
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Σύνδεση</title>
<link rel="stylesheet" href="assets/style.css">

<style>
body {
    background:#f0f0f5;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    font-family:Arial;
}
.login-box {
    background:white;
    padding:30px;
    width:350px;
    border-radius:10px;
    box-shadow:0 3px 8px rgba(0,0,0,0.2);
}
input {
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:5px;
    border:1px solid #ccc;
}
button {
    width:100%;
    padding:12px;
    background:#007bff;
    border:none;
    color:white;
    border-radius:5px;
    margin-top:15px;
    cursor:pointer;
}
button:hover {
    background:#006fe0;
}
.error {
    color:#d9534f;
    margin-bottom:10px;
    font-size:15px;
}
</style>
</head>

<body>

<div class="login-box">
    <h2>Σύνδεση</h2>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Όνομα χρήστη" required>
        <input type="password" name="password" placeholder="Κωδικός" required>
        <button type="submit">Είσοδος</button>
    </form>
</div>

</body>
</html>

