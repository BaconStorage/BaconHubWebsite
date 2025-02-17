<?php

include 'database.php'; // Make sure you've included the database connection
$mysqli = require __DIR__ . "/database.php";
$sql = "SELECT id, name, whitelisted FROM user"; // Replace 'current_username' with the actual username

$result = $mysqli->query($sql);



if ($result && $result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $whitelisted = $row['whitelisted'];
    $username = $row['name'];
    echo "User '$username' is " . ($whitelisted ? 'whitelisted' : 'not whitelisted');
} else {
    echo "User '$username' not found.";
}
?>

?>
