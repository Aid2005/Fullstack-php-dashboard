<?php
session_start();

// Provjera da li je korisnik prijavljen
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: 404.php");
    exit();
}

require_once 'connect.php';

$errorMessage = "";
$successMessage = "";

// --- LOGIKA ZA DODAVANJE VIJESTI ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $naslov = trim($_POST['title']);
    $opis = trim($_POST['description']);

    if (!empty($naslov) && !empty($opis)) {
        $stmt = $conn->prepare("INSERT INTO vijest (Naslov, Opis) VALUES (?, ?)");
        $stmt->bind_param("ss", $naslov, $opis);
        
        if ($stmt->execute()) {
            $successMessage = "News successfully added!";
            // PRG (Post/Redirect/Get) patern da sprijecimo duplo slanje forme
            header("Location: news.php");
            exit();
        } else {
            $errorMessage = "Error adding news.";
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }
}

// Povlačenje vijesti iz baze
$sql = "SELECT ID, Naslov, Opis FROM vijest ORDER BY ID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Management</title>
    <link rel="stylesheet" href="../Style/news.css">
</head>
<body>

    <nav class="top-nav">
        <div class="nav-brand">📰 News Management</div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>
    </nav>

    <main class="main-container">
        
        <!-- FORMA ZA DODAVANJE -->
        <div class="form-card">
            <h2>Publish New Article</h2>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-alert"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form action="news.php" method="POST">
                <div class="input-group">
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="Enter news title...">
                </div>

                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" rows="5" required placeholder="Enter news description..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Publish News</button>
                </div>
            </form>
        </div>

        <!-- TABELA PRIKAZA -->
        <div class="table-card">
            <h2>Published News</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Alarming Words Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Definisane alarmantne rijeci
                            $pattern = '/(napad|terorizam|borba|bojkot|bomba|izdaja|udarac|prevara|sipka|kradja|zlocudan)/iu';

                            while ($row = $result->fetch_assoc()): 
                                // Prvo zastitimo text od XSS napada (pretvara <script> u bezopasan text)
                                $safe_opis = htmlspecialchars($row['Opis']);
                                
                                // Brojimo rijeci i bojimo ih u crveno
                                $count = 0;
                                $highlighted_opis = preg_replace(
                                    $pattern, 
                                    '<span class="alarming-word">$1</span>', 
                                    $safe_opis, 
                                    -1, 
                                    $count // Ova varijabla ce automatski dobiti broj pronadjenih rijeci
                                );
                            ?>
                                <tr>
                                    <td><?php echo $row['ID']; ?></td>
                                    <td><?php echo htmlspecialchars($row['Naslov']); ?></td>
                                    <td><?php echo $highlighted_opis; ?></td>
                                    <td>
                                        <?php if($count > 0): ?>
                                            <span class="badge-danger"><?php echo $count; ?> detected</span>
                                        <?php else: ?>
                                            <span class="badge-safe">0</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">No news published yet.</div>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>