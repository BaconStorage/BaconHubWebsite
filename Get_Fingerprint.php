<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
$mysqli = require __DIR__ . "/database.php";

// Check if the connection is established
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        echo json_encode(['error' => 'Email and password are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    // Authenticate user
    $stmt = $mysqli->prepare("SELECT password FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!$hashed_password || !password_verify($password, $hashed_password)) {
        echo json_encode(['error' => 'Authentication failed']);
        exit;
    }

    // SQL query to select fingerprints
    $sql = "SELECT fingerprint_1, fingerprint_2, fingerprint_3 FROM Fingerprints WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the query was successful
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'No fingerprints found for this user']);
    } else {
        $fingerprints = $result->fetch_assoc();
        if ($fingerprints) {
            echo json_encode($fingerprints);
        } else {
            echo json_encode(['error' => 'Could not fetch fingerprints']);
        }
    }

    $stmt->close();
    $mysqli->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
