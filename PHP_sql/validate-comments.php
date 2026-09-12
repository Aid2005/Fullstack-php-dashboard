<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: 404.php");
    exit();
}

require_once 'connect.php';

// Fetch accepted comments and join with the seminar table to get the seminar name
$sql = "SELECT pk.IDK, pk.Tekst, pk.KreiranoAt, s.NazivSeminara 
        FROM pkomentar pk 
        LEFT JOIN seminar s ON pk.SifraSeminara = s.SifraSeminara 
        ORDER BY pk.KreiranoAt DESC";
        
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted Comments</title>
    <link rel="stylesheet" href="../Style/validate-comments.css">
</head>
<body>

    <nav class="top-nav">
        <div class="nav-brand">✅ Accepted Comments</div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>
    </nav>

    <main class="main-container">
        
        <!-- ACCEPTED COMMENTS TABLE -->
        <div class="table-card">
            <h2>Published & Accepted Comments</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Seminar Name</th>
                                <th>Comment Text</th>
                                <th>Accepted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['IDK']; ?></td>
                                    <td><?php echo htmlspecialchars($row['NazivSeminara']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Tekst']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($row['KreiranoAt'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">No accepted comments found in the database.</div>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>