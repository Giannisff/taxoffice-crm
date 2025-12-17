<?php
require_once "includes/db.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'] ?? 0;
if (!$userId) {
    http_response_code(403);
    exit("Not logged in");
}

$id          = $_POST["id"] ?? 0;
$title       = $_POST["title"] ?? "";
$start_time  = $_POST["start_time"] ?? null;
$end_time    = $_POST["end_time"] ?? null;
$description = $_POST["description"] ?? "";
$event_date  = $_POST["event_date"] ?? null;

$id = (int)$id;

if (!$id || !$event_date) {
    http_response_code(400);
    exit("Missing fields");
}

$stmt = $mysqli->prepare("
    UPDATE diary_events
    SET title=?, start_time=?, end_time=?, description=?, event_date=?
    WHERE id=? AND user_id=?
");

$stmt->bind_param(
    "sssssis",
    $title,
    $start_time,
    $end_time,
    $description,
    $event_date,
    $id,
    $userId
);

$stmt->execute();

echo "OK";
