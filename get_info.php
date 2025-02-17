<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to capture any unexpected output
ob_start();

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_all_userinfo') {
    $mysqli = require __DIR__ . "/database.php"; // Include the database connection

    // Check if email and password are provided
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query to get the user info if email and password match
        $sql = "SELECT email, name, password FROM user WHERE email = ? AND password = ?";
        $stmt = $mysqli->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $email, $password);

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                // Fetch the user info if a match is found
                if ($result->num_rows == 1) {
                    $userInfo = $result->fetch_assoc();
                    $response = ['status' => 'success', 'user_info' => $userInfo];
                } else {
                    $response = ['status' => 'error', 'message' => 'No matching user found or invalid credentials'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Database query failed'];
            }

            $stmt->close();
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to prepare the database query'];
        }

        $mysqli->close();
    }
}

// Capture any unexpected output
$unexpectedOutput = ob_get_clean();

if ($unexpectedOutput) {
    // Log the unexpected output for debugging (optional)
    error_log($unexpectedOutput);

    // Add the unexpected output to the response for debugging
    $response['unexpected_output'] = $unexpectedOutput;
}

// Return the response as a JSON object
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
