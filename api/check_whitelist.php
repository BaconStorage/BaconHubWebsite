<?php
$servername = "sql205.infinityfree.com";
$username = "if0_38043310";
$password = "SQzNPYGNQbo2v";
$dbname = "if0_38043310_Users";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['serial_key'])) {
    $serial_key = $_POST['serial_key'];
    $sql = "SELECT is_whitelisted FROM whitelist WHERE serial_key = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $serial_key);
    $stmt->execute();
    $stmt->bind_result($is_whitelisted);
    $stmt->fetch();
    $stmt->close();

    if ($is_whitelisted) {
        echo "You are whitelisted!";
    } else {
        echo "You are not whitelisted.";
    }
} else {
    echo "Please enter a serial key.";
}

$conn->close();
?>
