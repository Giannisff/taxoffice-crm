<?php
$mysqli = new mysqli(
    "localhost",
    "DB_USER",
    "DB_PASSWORD",
    "DB_NAME"
);

if ($mysqli->connect_error) {
    die("Database connection failed");
}

$mysqli->set_charset("utf8mb4");
