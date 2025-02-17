<?php
require 'vendor/autoload.php'; // Adjust the path if needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    echo "Email: $email<br>"; // Debug statement
    $token = bin2hex(random_bytes(50)); // Generate a unique token
    echo "Token: $token<br>"; // Debug statement

    // Save the token and email in the database
    $mysqli = require __DIR__ . "/database.php";
    echo "Database connection established.<br>"; // Debug statement

    // Check for a successful connection
    if ($mysqli->connect_error) {
        error_log("Connection failed: " . $mysqli->connect_error);
        die("Connection failed: " . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("UPDATE user SET reset_token=?, token_expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email=?");
    if (!$stmt) {
        error_log("Preparation failed: " . $mysqli->error);
        die("Preparation failed: " . $mysqli->error);
    }
    echo "Prepared statement created.<br>"; // Debug statement

    $stmt->bind_param('ss', $token, $email);
    if (!$stmt->execute()) {
        error_log("Execution failed: " . $stmt->error);
        die("Execution failed: " . $stmt->error);
    }
    echo "Statement executed.<br>"; // Debug statement

    if ($stmt->affected_rows > 0) {
        // Send the email using PHPMailer
        $resetLink = "http://baconhub.infinityfreeapp.com/reset-password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: $resetLink";

        $mail = new PHPMailer(true);

        try {
            // Enable verbose debug output
            $mail->SMTPDebug = 2; // Change to 3 for more detailed debug output
            $mail->Debugoutput = 'html'; // Output format for debugging messages

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com'; // Set the SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'BaconHub_AcReset@outlook.com'; // SMTP username
            $mail->Password = 'b0DtOResCKAPnA0Rrq8P'; // SMTP password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            // Recipients
            $mail->setFrom('no-reply@baconhub.000.pe', 'BaconHub');
            $mail->addAddress($email); // Add a recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            echo "Password reset link has been sent to your email.";
        } catch (Exception $e) {
            error_log("Failed to send email. Mailer Error: " . $mail->ErrorInfo);
            echo "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Invalid email address.";
    }

    $stmt->close();
    $mysqli->close();
}
?>
