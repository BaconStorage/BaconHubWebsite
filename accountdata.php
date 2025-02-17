<?php

if (empty($_POST["email"])) {
    die("Email is required");
}

if ( ! filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

$mysqli = require __DIR__ . "/database.php";

$sql = "SELECT name, password_hash FROM user WHERE email = ?";
        
$stmt = $mysqli->stmt_init();

if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("s", $_POST["email"]);

if ($stmt->execute()) {

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Return user information
        $user_info = [
            'email' => $_POST["email"],
            'username' => $row['name'],
            'password' => $row['password_hash'] // Note: Passwords should not be returned directly for security reasons
        ];

        echo json_encode($user_info);
    } else {
        die("User not found");
    }

} else {
    die("SQL error: " . $mysqli->error);
}
