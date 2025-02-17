<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Array of valid serial keys
$valid_serial_keys = array(
    '1234-5678-ABCD-EFGH',
    '8765-4321-HGFE-DCBA',
    // Add more serial keys here
);

// Function to check if a serial key is valid
function is_valid_serial_key($serial_key, $valid_serial_keys) {
    return in_array($serial_key, $valid_serial_keys);
}

// Example usage
header('Content-Type: application/json'); // Ensure the response is in JSON format

$response = array();

if (isset($_GET['serialkey'])) {
    $user_serial_key = $_GET['serialkey'];
    $response['debug'] = 'Serial key received: ' . $user_serial_key; // Debug message

    if (is_valid_serial_key($user_serial_key, $valid_serial_keys)) {
        $response['status'] = 'success';
        $response['message'] = 'Serial key is valid. Access granted.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid serial key. Access denied.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'No serial key provided.';
    $response['debug'] = '$_GET contents: ' . json_encode($_GET); // Debug message
}

echo json_encode($response);
?>
