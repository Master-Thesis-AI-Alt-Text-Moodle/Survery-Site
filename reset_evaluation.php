<?php
session_start();

// This should be a secure, hard-to-guess value
$adminPassword = 'your_secure_admin_password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $adminPassword) {
        // Reset the evaluation status
        unset($_SESSION['evaluation_completed']);
        echo json_encode(["success" => true, "message" => "Evaluation status reset successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid password"]);
    }
} else {
    // Display a simple form for password entry
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Evaluation Status</title>
    </head>
    <body>
        <h1>Reset Evaluation Status</h1>
        <form id="resetForm">
            <label for="password">Admin Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Reset</button>
        </form>
        <div id="message"></div>

        <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('reset_evaluation.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('message').textContent = data.message || data.error;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        </script>
    </body>
    </html>
    HTML;
}
?>