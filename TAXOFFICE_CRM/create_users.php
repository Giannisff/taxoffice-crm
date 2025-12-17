<?php
require_once "includes/db.php";

echo "<pre>";

// --- Users we want to create ---
$users = [
    ["username" => "xrisoula", "fullname" => "Xrisoula", "password" => "Xrisoula2025!", "role" => "user"],
    ["username" => "baso",     "fullname" => "Baso",     "password" => "Baso2025!",     "role" => "user"],
    ["username" => "litsa",    "fullname" => "Litsa",    "password" => "Litsa2025!",    "role" => "user"],
    ["username" => "makis",    "fullname" => "Makis",    "password" => "Makis2025!",    "role" => "user"],
];

foreach ($users as $u) {

    // Check if user already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $u["username"]);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        echo "⚠️ User already exists: {$u['username']} — SKIPPING\n";
        continue;
    }

    // Create safe bcrypt hash
    $hash = password_hash($u["password"], PASSWORD_BCRYPT);

    // Insert user
    $stmt = $mysqli->prepare("
        INSERT INTO users (username, password, fullname, role)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $u["username"], $hash, $u["fullname"], $u["role"]);

    if ($stmt->execute()) {
        echo "✅ Created user: {$u['username']} — HASH: $hash\n";
    } else {
        echo "❌ ERROR creating {$u['username']} : " . $stmt->error . "\n";
    }
}

echo "\nDONE.\n</pre>";
