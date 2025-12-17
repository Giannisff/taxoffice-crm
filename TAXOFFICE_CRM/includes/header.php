<?php 
if (!isset($pageTitle)) $pageTitle = "TaxOffice CRM"; 
session_start();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle ?></title>

    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/style.css">

    <style>
        body {
            margin: 0;
            background: #f5f6fa;
            font-family: 'Inter', sans-serif;
        }

        /* =========================================
           DESKTOP TOP MENU
        ========================================== */
        .top-menu {
            width: 100%;
            background: #1f1f1f;
            padding: 14px 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.25);
        }

        .menu-logo {
            font-size: 20px;
            font-weight: 600;
            margin-right: 30px;
            white-space: nowrap;
        }

        .menu-links {
            display: flex;
            gap: 25px;
        }

        .menu-links a {
            text-decoration: none;
            color: #d4d4d4;
            padding-bottom: 2px;
            border-bottom: 2px solid transparent;
            transition: 0.2s;
            font-size: 15px;
            font-weight: 400;
        }

        .menu-links a:hover,
        .menu-links a.active {
            color: white;
            border-bottom: 2px solid #3fa9f5;
        }

        .menu-user {
            margin-left: auto;
            color: #d4d4d4;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .menu-user a {
            color: #3fa9f5;
            text-decoration: none;
        }
        .menu-user a:hover {
            text-decoration: underline;
        }


        /* =========================================
           MOBILE MENU
        ========================================== */

        /* Hide desktop menu on small screens */
        @media (max-width: 850px) {
            .menu-links,
            .menu-user {
                display: none;
            }
        }

        /* Hamburger */
        .hamburger {
            display: none;
            cursor: pointer;
            font-size: 26px;
            margin-left: auto;
        }

        @media (max-width: 850px) {
            .hamburger {
                display: block;
            }
        }

        /* Mobile drawer */
        .mobile-menu {
            display: none;
            flex-direction: column;
            background: #1f1f1f;
            padding: 15px 20px;
            position: absolute;
            top: 60px;
            width: 100%;
            left: 0;
            z-index: 999;
        }

        .mobile-menu a {
            padding: 10px 0;
            text-decoration: none;
            color: #e1e1e1;
            font-size: 17px;
            border-bottom: 1px solid #333;
        }

        .mobile-menu a:last-child {
            border: none;
        }

        .mobile-user-box {
            padding: 12px 0;
            margin-top: 10px;
            border-top: 1px solid #444;
            color: #ccc;
            font-size: 15px;
        }

        .mobile-user-box a {
            color: #3fa9f5;
        }

    </style>

    <script>
        function toggleMobileMenu() {
            let m = document.getElementById("mobileMenu");
            m.style.display = (m.style.display === "flex") ? "none" : "flex";
        }
    </script>

</head>
<body>

<header>
    <div class="top-menu">

        <div class="menu-logo">TaxOffice CRM</div>

        <!-- DESKTOP MENU -->
        <nav class="menu-links">
            <a href="index.php" class="<?= $pageTitle=='Αρχική' ? 'active':'' ?>">Αρχική</a>
            <a href="tasks.php" class="<?= $pageTitle=='Εργασίες' ? 'active':'' ?>">Εργασίες</a>
            <a href="clients.php" class="<?= $pageTitle=='Πελάτες' ? 'active':'' ?>">Πελάτες</a>
            <a href="partners.php" class="<?= $pageTitle=='Συνεργάτες' ? 'active':'' ?>">Συνεργάτες</a>
            <a href="calendar.php" class="<?= $pageTitle=='Ημερολόγιο Εργασιών' ? 'active':'' ?>">Ημερολόγιο</a>
            <a href="active_professionals.php" class="<?= $pageTitle=='Ενεργοί Επαγγελματίες' ? 'active':'' ?>">Ενεργοί Επαγγελματίες</a>
                <a href="mydiary.php" class="<?= $pageTitle=='MyDiary' ? 'active':'' ?>">MyDiary</a>

        </nav>

        <!-- USER (desktop) -->
        <div class="menu-user">
            Συνδεδεμένος ως: <b><?= $_SESSION['fullname'] ?></b> |
            <a href="logout.php">Αποσύνδεση</a>
        </div>

        <!-- HAMBURGER (mobile) -->
        <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

    </div>

    <!-- MOBILE MENU (drawer) -->
    <div class="mobile-menu" id="mobileMenu">

        <a href="index.php">Αρχική</a>
        <a href="tasks.php">Εργασίες</a>
        <a href="clients.php">Πελάτες</a>
        <a href="partners.php">Συνεργάτες</a>
        <a href="calendar.php">Ημερολόγιο</a>
        <a href="active_professionals.php">Ενεργοί Επαγγελματίες</a>
        <a href="mydiary.php">MyDiary</a>
        <div class="mobile-user-box">
            Συνδεδεμένος ως:<br>
            <b><?= $_SESSION['fullname'] ?></b><br>
            <a href="logout.php">Αποσύνδεση</a>
        </div>

    </div>
</header>

<main>
<div class="page-container">


