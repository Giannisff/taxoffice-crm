<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: /login.php");
    exit;
}
