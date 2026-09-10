<?php
// Pokretanje sesije za provjeru da li je admin prijavljen
session_start();

// Ako korisnik nije prijavljen, vrati ga na stranicu za prijavu
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: 404.php");
    exit();
}

// Preuzimanje imena prijavljenog admina za prikaz
$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Povezivanje modernog CSS-a -->
    <link rel="stylesheet" href="../Style/dashboard.css">
</head>
<body>

    <!-- Navigacijski bar na vrhu -->
    <nav class="top-nav">
        <div class="nav-brand">Admin Portal</div>
        <div class="nav-user">
            <span>Welcome, <strong><?php echo htmlspecialchars($admin_name); ?></strong></span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </nav>

    <!-- Glavni sadržaj sa karticama -->
    <main class="dashboard-container">
        <header class="dashboard-header">
            <h1>Dashboard Overview</h1>
            <p>Select a module to manage your system</p>
        </header>

        <!-- Grid sa 6 kartica -->
        <div class="dashboard-grid">
            
            <a href="institute.php" class="dash-card">
                <div class="card-icon">🏛️</div>
                <h3>Institute</h3>
                <p>Manage institute details and information</p>
            </a>

            <a href="researchers.php" class="dash-card">
                <div class="card-icon">👨‍🔬</div>
                <h3>Researchers</h3>
                <p>Manage researcher profiles and data</p>
            </a>

            <a href="seminars.php" class="dash-card">
                <div class="card-icon">📚</div>
                <h3>Seminars</h3>
                <p>Create and edit seminar events</p>
            </a>

            <a href="news.php" class="dash-card">
                <div class="card-icon">📰</div>
                <h3>News</h3>
                <p>Publish and manage news announcements</p>
            </a>

            <a href="validate-comments.php" class="dash-card">
                <div class="card-icon">🛡️</div>
                <h3>Validate Comments</h3>
                <p>Review and approve new comments</p>
            </a>

            <a href="comments.php" class="dash-card">
                <div class="card-icon">💬</div>
                <h3>All Comments</h3>
                <p>Manage all published user comments</p>
            </a>

        </div>
    </main>

</body>
</html>