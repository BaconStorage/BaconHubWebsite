<?php
// Include the database connection
$mysqli = require __DIR__ . "/database.php";

// Check if the connection is established
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// SQL query to select the email, fingerprint_1, fingerprint_2, and fingerprint_3 columns from the Fingerprints table
$sql = "SELECT email, fingerprint_1, fingerprint_2, fingerprint_3 FROM Fingerprints";

// Execute the SQL query and get the result
$result = $mysqli->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Site Users</title>
    <!-- Add any additional CSS or JavaScript links here -->
</head>
<body>
    <!-- Your table goes here -->
    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>Fingerprint 1</th>
                <th>Fingerprint 2</th>
                <th>Fingerprint 3</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['fingerprint_1']; ?></td>
                    <td><?php echo $row['fingerprint_2']; ?></td>
                    <td><?php echo $row['fingerprint_3']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <!-- Add any other content or scripts as needed -->
</body>
</html>
