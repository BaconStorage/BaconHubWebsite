<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $mysqli = require __DIR__ . "/database.php";


    $stmt = $mysqli->prepare("UPDATE user SET password=?, reset_token=NULL, token_expiry=NULL WHERE reset_token=? AND token_expiry > NOW()");
    $stmt->bind_param('ss', $new_password, $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Password has been reset successfully.";
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
}
?>
