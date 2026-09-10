<?php
// Start the session to keep track of logged-in users
session_start();

// Include the database connection
require_once 'connect.php';

$errorMessage = ""; // Variable to store error messages

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // SQL query to fetch the user by their username (KIme)
    $sql = "SELECT KIme, LozinkaHasb FROM administrator WHERE KIme = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists in the database
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Verify the entered password against the hashed password in DB
            if (password_verify($password, $row['LozinkaHasb'])) {
                
                // Set session variables upon successful login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $row['KIme'];
                
                // Redirect to the dashboard
                header("Location: dashboard.php");
                exit(); 
            } else {
                $errorMessage = "Invalid password. Please try again.";
            }
        } else {
            $errorMessage = "User not found. Please check your username.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Database error. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Modern Portal</title>
    <!-- Connecting the modern blue CSS -->
    <link rel="stylesheet" href="../Style/LogIn.css">
</head>
<body>

    <!-- Main wrapper for centering -->
    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <h2>Admin Portal</h2>
                <p>Sign in to manage your dashboard</p>
            </div>

            <!-- Display error message if it exists -->
            <?php if (!empty($errorMessage)): ?>
                <div class="error-alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="LogIn.php" method="POST">
                
                <!-- Username Input -->
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="e.g. aid1" required autocomplete="off">
                </div>

                <!-- Password Input -->
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login">Sign In</button>

            </form>
        </div>
    </div>

</body>
</html>