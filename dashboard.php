<?php

session_start();

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM user WHERE id = {$_SESSION["user_id"]}";

    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $buyer = $user["isbuyer"];
        $admin = $user["isadmin"];
        $name = $user["name"];
        $email = $user["email"];

        $fingerprint = $user["fingerprint"];
        $fingerprint2 = $user["fingerprint_2"];
        $fingerprint3 = $user["fingerprint_3"];
    } else {
        echo "No user found.";
        exit;
    }
    
    // Enable error reporting for debugging purposes
    
    

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_all_hardware_ids') {
        $mysqli = require __DIR__ . "/database.php"; // Include the database connection
    
        // Query to get all fingerprints from the user table
        $sql = "SELECT fingerprint, fingerprint_2, fingerprint_3 FROM user";
        $result = $mysqli->query($sql);
    
        $fingerprints = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['fingerprint'])) {
                    $fingerprints[] = $row['fingerprint'];
                }
                if (!empty($row['fingerprint_2'])) {
                    $fingerprints[] = $row['fingerprint_2'];
                }
                if (!empty($row['fingerprint_3'])) {
                    $fingerprints[] = $row['fingerprint_3'];
                }
            }
        }
    
        // Close the connection
        $mysqli->close();
    
        // Convert the fingerprints array to a Lua table format
        $luaTable = "{" . implode(', ', array_map(function($id) { return "\"$id\""; }, $fingerprints)) . "}";
    
        // Return the Lua table as a JSON object
        header('Content-Type: application/json');
        echo json_encode(['lua_table' => $luaTable]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_user_details') {
        $mysqli = require __DIR__ . "/database.php"; // Include the database connection
    
        // Query to get the user's email, name, and password from the database
        $sql = "SELECT email, name, password FROM user WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $userDetails = [];
        if ($result->num_rows > 0) {
            $userDetails = $result->fetch_assoc();
        }
    
        // Close the connection
        $stmt->close();
        $mysqli->close();
    
        // Return the user details as a JSON object
        header('Content-Type: application/json');
        echo json_encode($userDetails);
        exit;
    }
    
    
    
    


    
    // Handle POST request to update fingerprints
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'set_fingerprints') {
        $newFingerprint1 = !empty($_POST["fingerprint1"]) ? $_POST["fingerprint1"] : $fingerprint;
        $newFingerprint2 = !empty($_POST["fingerprint2"]) ? $_POST["fingerprint2"] : $fingerprint2;
        $newFingerprint3 = !empty($_POST["fingerprint3"]) ? $_POST["fingerprint3"] : $fingerprint3;
    
        $updateSql = "UPDATE user SET fingerprint = ?, fingerprint_2 = ?, fingerprint_3 = ? WHERE id = ?";
        $stmt = $mysqli->prepare($updateSql);
        $stmt->bind_param("sssi", $newFingerprint1, $newFingerprint2, $newFingerprint3, $_SESSION["user_id"]);
    
        if ($stmt->execute()) {
            echo "Fingerprints updated successfully.";
    
            // Update Pastebin paste
            updatePastebinPaste($newFingerprint1, $newFingerprint2, $newFingerprint3);
        } else {
            echo "Error updating fingerprints: " . $mysqli->error;
        }
    
        $stmt->close();
    
        // Fetch updated fingerprints
        $sql = "SELECT fingerprint, fingerprint_2, fingerprint_3 FROM user WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $fingerprint = $user["fingerprint"];
            $fingerprint2 = $user["fingerprint_2"];
            $fingerprint3 = $user["fingerprint_3"];
        }
    
        $stmt->close();
    }
    
    $mysqli->close();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Bacon Hub Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
    
    .navbar {
            overflow: hidden;
            background-color: #333;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #7289da;
            color: white; /* Changed to white */
        }

        .middle-bar {
            width: 96%;
            height: 500px;
            background-color: #333;
            color: white;
            text-align: center;
            padding: 14px 16px;
            margin: 20px 0;
            font-size: 18px.
            position: relative;
        }

        .sidebar {
            width: 200px;
            height: 100%;
            background-color: #444;
            position: absolute;
            left: 0;
            top: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .sidebar button {
            width: 100%;
            padding: 10px;
            background-color: #555;
            color: white;
            border: none;
            cursor: pointer;
        }

        .sidebar button:hover {
            background-color: #666.
        }

        .editor {
            display: none;
            width: 100%;
            height: 400px;
            border: 2px solid #333;
            padding: 10px;
            margin: 20px 0;
            background-color: #f9f9f9;
            font-family: monospace;
            font-size: 16px;
            color: black;
        }

        .copy-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #7289DA;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .buyarea {
            border: 1px solid #000; /* Customize the frame's border */
            padding: 10px;
            margin-bottom: 10px; /* Space between elements */
            display: flex;
            flex-direction: column;
            width: 200px; /* Adjust the width as needed */
        }

        .buy-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #7289DA;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin-top: 10px;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #fff;
        }

        a {
            color: #7289da;
            text-decoration: none;
        }

        .input-container {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .input-container label {
        margin-right: 10px;
        width: 100px; /* Adjust the width as needed */
        text-align: right;
    }

    .input-container input {
        flex: 1;
    }

    .fingerprint {
            margin: 5px;
            padding: 5px;
            border: 1px solid #ccc;
            background-color: #333;
            color: #333;
            transition: background-color 0.3s, color 0.3s;
        }
        .fingerprint:hover {
            background-color: white;
            color: black;
        }




.update-form {
    margin-bottom: 10px;
}

.download-section {
    margin-top: 20px; /* Adjust this value as needed */
    display: flex;
    justify-content: flex-start;
}

.download-button {
    background-color: #007bff; /* Blue color */
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
}

.download-button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}



    </style>
</head>
<body>
    <title>Bacon Hub Dashboard</title>
    <div class="navbar">
        <a href="index.php">Main</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="features.php">Features</a>
        <a href="Free Edition Features.php">Free Features</a>
        <a href="https://baconhub.gitbook.io/v2" target="_blank">Docs</a>
        <a href="discord.php" target="_blank" >Discord</a>
    </div>
    <h1>Dashboard</h1>

    <?php if (isset($user)): ?>

    
        <p>Hello <?= htmlspecialchars($user["name"]) ?></p>


    <div class="middle-bar">
        <div class="sidebar">
            <?php if ($admin): ?>
            <button onclick="ShowAdminPanel()">Admin</button>
    <?php endif; ?>
    
            <button onclick="ShowAcPanelNew()">Account</button>
            <button onclick="ShowBuyerPanel()">Purchase</button>

    <?php if ($buyer): ?>
        <button onclick="ShowWhitelistPanel()">Whitelist</button>

            <button onclick="ShowLoaderEditor()">Script</button>
    <?php endif; ?>



            
        </div>
    </div>
    <?php else: ?>
        <p><a href="login.php">Log in</a> or <a href="signup.html">sign up</a></p>
    <?php endif; ?>





    
    <script>

document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'purchase') {
            ShowBuyerPanel();
        }
    });
    
        const fingerprint = "<?= htmlspecialchars($fingerprint) ?>";
        const fingerprint2 = "<?= htmlspecialchars($fingerprint2) ?>";
        const fingerprint3 = "<?= htmlspecialchars($fingerprint3) ?>";

        function ShowAdminPanel() {
            RemoveCodeEditors();
    if (!document.querySelector(".middle-bar .buyarea")) {
        const buyarea = document.createElement("div");
        buyarea.className = "buyarea";
        document.querySelector(".middle-bar").appendChild(buyarea);

        const title = document.createElement("h3");
        title.textContent = "Copy All HardwareIds";
        buyarea.appendChild(title);

        const copyButton = document.createElement("button");
        copyButton.className = "buy-button";
        copyButton.textContent = "Copy";
        buyarea.appendChild(copyButton);

        copyButton.addEventListener("click", function() {
            fetch(window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "action=get_all_hardware_ids"
            })
            .then(response => response.json())
            .then(data => {
                setClipboard(data.lua_table);
                alert("Hardware IDs copied to clipboard!");
            });
        });
    }
}

function setClipboard(value) {
    const tempInput = document.createElement("textarea");
    tempInput.value = value;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
}

function ShowAcPanelNew()
{
    ShowAcPanel("<?php echo $email; ?>", "<?php echo $name; ?>", "");
}

function ShowAcPanel(email, username, password) {
    RemoveCodeEditors();

    if (!document.querySelector('.middle-bar .account-panel')) {
        const accountPanel = document.createElement("div");
        accountPanel.className = "account-panel";
        document.querySelector('.middle-bar').appendChild(accountPanel);

        // Username input
        const usernameContainer = document.createElement("div");
        usernameContainer.className = "input-container";
        accountPanel.appendChild(usernameContainer);

        const usernameLabel = document.createElement("label");
        usernameLabel.textContent = "Username:";
        usernameLabel.htmlFor = "username-input";
        usernameContainer.appendChild(usernameLabel);

        const usernameInput = document.createElement("input");
        usernameInput.type = "text";
        usernameInput.id = "username-input";
        usernameInput.className = "username-input";
        usernameInput.value = username; // Set default username
        usernameInput.autocomplete = "username"; // Ensure browser treats this as a username
        usernameContainer.appendChild(usernameInput);

        // Email input
        const emailContainer = document.createElement("div");
        emailContainer.className = "input-container";
        accountPanel.appendChild(emailContainer);

        const emailLabel = document.createElement("label");
        emailLabel.textContent = "Email:";
        emailLabel.htmlFor = "email-input";
        emailContainer.appendChild(emailLabel);

        const emailInput = document.createElement("input");
        emailInput.type = "email";
        emailInput.id = "email-input";
        emailInput.className = "email-input";
        emailInput.value = email; // Set default email
        emailInput.autocomplete = "email"; // Ensure browser treats this as an email
        emailContainer.appendChild(emailInput);

        // Password input
        const passwordContainer = document.createElement("div");
        passwordContainer.className = "input-container";
        accountPanel.appendChild(passwordContainer);

        const passwordLabel = document.createElement("label");
        passwordLabel.textContent = "Password:";
        passwordLabel.htmlFor = "password-input";
        passwordContainer.appendChild(passwordLabel);

        const passwordInput = document.createElement("input");
        passwordInput.type = "password";
        passwordInput.id = "password-input";
        passwordInput.className = "password-input";
        passwordInput.value = password; // Set default password
        passwordInput.autocomplete = "current-password"; // Ensure browser treats this as a password
        passwordContainer.appendChild(passwordInput);

        // Save button
        const saveButton = document.createElement("button");
        saveButton.className = "save-button";
        saveButton.textContent = "Save Changes";
        accountPanel.appendChild(saveButton);

        // Logout button
        const logoutButton = document.createElement("button");
        logoutButton.className = "save-button";
        logoutButton.textContent = "Logout";
        logoutButton.onclick = function() {
            window.location.href = "logout.php"; // Adjust this path to your actual logout script
        };
        accountPanel.appendChild(logoutButton);
    }
}



















function BuyFunction() {
    if (!document.querySelector(".middle-bar .buyarea")) {
        const buyarea = document.createElement("div");
        buyarea.className = "buyarea";
        document.querySelector(".middle-bar").appendChild(buyarea);

        const title = document.createElement("h3");
        title.textContent = "Buy With Robux";
        buyarea.appendChild(title);

        const copyButton = document.createElement("a");
        copyButton.className = "buy-button";
        copyButton.textContent = "Buy";
        copyButton.href = "https://www.youtube.com";
        buyarea.appendChild(copyButton);
    }
}
function removeDownloadButton() {
    const downloadButton = document.querySelector(".buy-button");
    if (downloadButton) {
        downloadButton.remove();
    }
}

function ShowWhitelistPanel() {
    RemoveCodeEditors();
    WhitelistFunction();



}

function WhitelistFunction() {
    if (!document.querySelector(".middle-bar .buyarea")) {
        const middleBar = document.querySelector(".middle-bar");

        // Create and append the buyarea section
        const buyarea = document.createElement("div");
        buyarea.className = "buyarea";
        middleBar.appendChild(buyarea);

        const fingerprints = [fingerprint, fingerprint2, fingerprint3];
        const truncateLength = 10; // Adjust this value to show desired number of characters

        fingerprints.forEach(fp => {
            const truncatedFingerprint = fp.length > truncateLength ? fp.substring(0, truncateLength) + '...' : fp;
            const fingerprintDiv = document.createElement("div");
            fingerprintDiv.className = "fingerprint";
            fingerprintDiv.textContent = truncatedFingerprint;
            buyarea.appendChild(fingerprintDiv);
        });

        // Add a form to update fingerprints
        const form = document.createElement("form");
        form.onsubmit = updateFingerprints;
        form.className = "update-form";
        form.innerHTML = `
            <input type="text" name="fingerprint1" placeholder="New Fingerprint 1">
            <input type="text" name="fingerprint2" placeholder="New Fingerprint 2">
            <input type="text" name="fingerprint3" placeholder="New Fingerprint 3">
            <input type="hidden" name="action" value="set_fingerprints">
            <button type="submit">Update Fingerprints</button>
        `;
        buyarea.appendChild(form);

        // Check if the "Download Whitelist" button already exists
        if (!document.querySelector(".middle-bar .buy-button")) {
            // Create and append the "Download Whitelist" button inside .middle-bar
            const downloadButton = document.createElement("div");
            downloadButton.className = "buy-button";
            downloadButton.textContent = "Download Whitelist";
            downloadButton.onclick = DownloadWhitelist;
            middleBar.appendChild(downloadButton);
        }

        // Set an interval to refresh fingerprints
        setInterval(fetchFingerprints, 1000); // Refresh every 5 seconds
    }
}

function fetchFingerprints() {
    fetch('') // Empty string to send request to the same PHP file
        .then(response => response.json())
        .then(data => {
            const fingerprints = [data.fingerprint, data.fingerprint2, data.fingerprint3];
            const fingerprintDivs = document.querySelectorAll('.fingerprint');
            fingerprintDivs.forEach((div, index) => {
                div.textContent = fingerprints[index];
            });
        })
        .catch(error => {
            console.error('Error fetching fingerprints:', error);
        });
}

function updateFingerprints(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'set_fingerprints'); // Add action to the form data
    fetch('', { // Empty string to send request to the same PHP file
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            console.log('Fingerprints updated successfully.');
            fetchFingerprints(); // Refresh fingerprints immediately after update
        } else {
            console.error('Failed to update fingerprints.');
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}



        function ShowCodeEditor() {
            if (!document.querySelector('.middle-bar textarea')) {
                const text = 'loadstring(game:HttpGet("https://pastebin.com/raw/BWf9P7hR"))()';
                const textarea = document.createElement("textarea");
                textarea.value = text;
                document.querySelector('.middle-bar').appendChild(textarea);

                const copyButton = document.createElement("a");
                copyButton.className = "copy-button";
                copyButton.textContent = "Copy Code";
                copyButton.onclick = copyToClipboard;
                textarea.insertAdjacentElement('afterend', copyButton);
            }
        }
                function ShowLoader() {
            if (!document.querySelector('.middle-bar textarea')) {
                const text = 'loadstring(game:HttpGet("https://pastebin.com/raw/i3SUJ0iN"))()';
                const textarea = document.createElement("textarea");
                textarea.value = text;
                document.querySelector('.middle-bar').appendChild(textarea);

                const copyButton = document.createElement("a");
                copyButton.className = "copy-button";
                copyButton.textContent = "Copy Code";
                copyButton.onclick = copyToClipboard;
                textarea.insertAdjacentElement('afterend', copyButton);

// Create the download button
const DownloadButton = document.createElement("a");
DownloadButton.className = "copy-button";
DownloadButton.textContent = "Download Code";
DownloadButton.onclick = DownloadLoader;

// Add CSS to offset the button by 1 pixel
DownloadButton.style.position = "relative";
DownloadButton.style.right = "7px";

// Insert the button after the textarea
textarea.insertAdjacentElement('afterend', DownloadButton);


            }
        }

        function RemoveCodeEditor() {
            const textarea = document.querySelector('.middle-bar textarea');
            if (textarea) {
                document.querySelector('.middle-bar').removeChild(textarea);
            }
            const copyButton = document.querySelector('.middle-bar .copy-button');
            if (copyButton) {
                document.querySelector('.middle-bar').removeChild(copyButton);
            }
        }
        function RemoveLoaderCodeEditor() {
            const textarea = document.querySelector('.middle-bar textarea');
            if (textarea) {
                document.querySelector('.middle-bar').removeChild(textarea);
            }
            const copyButton = document.querySelector('.middle-bar .copy-button');
            if (copyButton) {
                document.querySelector('.middle-bar').removeChild(copyButton);
            }
        }
        
        function RemoveAccountPanel(){
            const accountPanel = document.querySelector('.middle-bar .account-panel');
            if (accountPanel) {
                document.querySelector('.middle-bar').removeChild(accountPanel);
            }
        }

        function RemoveWhitelistPanel()
        {
    const accountPanel = document.querySelector('.middle-bar .buyarea');
    if (accountPanel) {
        accountPanel.remove();
    }



        }
        function RemoveCodeEditors() {
            RemoveCodeEditor()
            RemoveLoaderCodeEditor()
            RemoveAccountPanel()
            RemoveWhitelistPanel()
            removeDownloadButton()
    const buyarea = document.querySelector('.middle-bar .buyarea');
    if (buyarea) {
        document.querySelector('.middle-bar').removeChild(buyarea);
    }
    function removeDownloadButton() {
    const downloadButton = document.querySelector(".middle-bar .buy-button");
    if (downloadButton) {
        downloadButton.remove();
    }
}


        }
        function ShowLoaderEditor() {
            RemoveCodeEditors()
            ShowLoader()
        }
        function ShowBuyerPanel() {
            RemoveCodeEditors()
            BuyFunction()
        }
        function copyToClipboard() {
            const textarea = document.querySelector('.middle-bar textarea');
            if (textarea) {
                textarea.select();
                document.execCommand('copy');
            }
        }
        function DownloadLoader() {

                // Create a Blob with the Lua script content
const luaScript = new Blob([`loadstring(game:HttpGet("https://pastebin.com/raw/i3SUJ0iN"))()`], { type: 'text/plain' });

// Create a link element
const downloadLink = document.createElement('a');

// Set the download attribute with a filename
downloadLink.download = 'Bacon Hub V2.lua';

// Create a URL for the Blob and set it as the href attribute
downloadLink.href = window.URL.createObjectURL(luaScript);

// Append the link to the body (required for Firefox)
document.body.appendChild(downloadLink);

// Programmatically click the link to trigger the download
downloadLink.click();

// Remove the link from the document
document.body.removeChild(downloadLink);

        }

        function DownloadWhitelist() {

// Create a Blob with the Lua script content
const luaScript = new Blob([`loadstring(game:HttpGet("https://pastebin.com/raw/sWAwid6F"))()`], { type: 'text/plain' });

// Create a link element
const downloadLink = document.createElement('a');

// Set the download attribute with a filename
downloadLink.download = 'Bacon Hub Whitelist Gui.lua';

// Create a URL for the Blob and set it as the href attribute
downloadLink.href = window.URL.createObjectURL(luaScript);

// Append the link to the body (required for Firefox)
document.body.appendChild(downloadLink);

// Programmatically click the link to trigger the download
downloadLink.click();

// Remove the link from the document
document.body.removeChild(downloadLink);

}
    </script>
</body>
</html>










    
    
    
    
    
    
    
    
    
    
    