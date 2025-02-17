<?php
include 'database.php'; // Make sure you've included the database connection
$mysqli = require __DIR__ . "/database.php";
$sql = "SELECT id, name,isbuyer FROM user";


$result = $mysqli->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Bacon Hub Users</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data</title>
    <!-- Add any additional CSS or JavaScript links here -->
</head>
<body>
    <!-- Your table goes here -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Buyer</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['isbuyer']; ?></td>

                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <!-- Add any other content or scripts as needed -->
</body>
</html>
