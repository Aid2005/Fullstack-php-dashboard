<?php
// Start session and verify admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: 404.php");
    exit();
}

require_once 'connect.php';

$errorMessage = "";

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM seminar WHERE SifraSeminara = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $stmt->close();
    header("Location: seminars.php");
    exit();
}

// Handle Form Submission (Create or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sifra = $_POST['sifra'] ?? '';
    $naziv = trim($_POST['naziv']);
    $budzet = floatval($_POST['budzet']);

    // Server-side validation: Budget cannot be less than 0
    if ($budzet < 0) {
        $errorMessage = "Budget cannot be a negative number!";
    } else {
        if (!empty($sifra)) {
            // UPDATE existing record
            $stmt = $conn->prepare("UPDATE seminar SET NazivSeminara = ?, Budzet = ? WHERE SifraSeminara = ?");
            $stmt->bind_param("sdi", $naziv, $budzet, $sifra);
            $stmt->execute();
            $stmt->close();
        } else {
            // INSERT new record
            $stmt = $conn->prepare("INSERT INTO seminar (NazivSeminara, Budzet) VALUES (?, ?)");
            $stmt->bind_param("sd", $naziv, $budzet);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: seminars.php");
        exit();
    }
}

// Fetch all seminars from DB
$sql = "SELECT SifraSeminara, NazivSeminara, Budzet FROM seminar";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Seminars</title>
    <link rel="stylesheet" href="../Style/seminars.css">
</head>
<body>

    <!-- Top Navigation with Back Button -->
    <nav class="top-nav">
        <div class="nav-brand">📚 Seminar Management</div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>
    </nav>

    <main class="main-container">
        
        <!-- Form Section -->
        <div class="form-card">
            <h2 id="form-title">Add New Seminar</h2>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <form action="seminars.php" method="POST" id="seminarForm">
                
                <div class="input-row">
                    <!-- Readonly Code Field -->
                    <div class="input-group">
                        <label for="sifra">Seminar Code (Auto-generated)</label>
                        <input type="text" id="sifra" name="sifra" placeholder="New Entry" readonly>
                    </div>

                    <!-- Name Field -->
                    <div class="input-group">
                        <label for="naziv">Seminar Name</label>
                        <input type="text" id="naziv" name="naziv" required>
                    </div>

                    <!-- Budget Field (min=0 enforces positive numbers in HTML) -->
                    <div class="input-group">
                        <label for="budzet">Budget (€)</label>
                        <input type="number" id="budzet" name="budzet" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save" id="submitBtn">Save Seminar</button>
                    <button type="button" class="btn-cancel" id="cancelBtn" style="display: none;" onclick="resetForm()">Cancel Edit</button>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-card">
            <h2>Seminar List</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Seminar Name</th>
                            <th>Budget (€)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['SifraSeminara']); ?></td>
                                <td><?php echo htmlspecialchars($row['NazivSeminara']); ?></td>
                                <td><?php echo number_format($row['Budzet'], 2); ?> €</td>
                                <td class="action-cell">
                                    <button class="btn-edit" onclick="editSeminar(<?php echo $row['SifraSeminara']; ?>, '<?php echo addslashes($row['NazivSeminara']); ?>', <?php echo $row['Budzet']; ?>)">Edit</button>
                                    <a href="seminars.php?delete=<?php echo $row['SifraSeminara']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this seminar?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No seminars found in the database.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- JavaScript to populate form fields for edit mode -->
    <script>
        function editSeminar(sifra, naziv, budzet) {
            document.getElementById('sifra').value = sifra;
            document.getElementById('naziv').value = naziv;
            document.getElementById('budzet').value = budzet;
            
            document.getElementById('form-title').innerText = "Edit Seminar";
            document.getElementById('submitBtn').innerText = "Update Seminar";
            document.getElementById('cancelBtn').style.display = "inline-block";
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('seminarForm').reset();
            document.getElementById('sifra').value = '';
            document.getElementById('form-title').innerText = "Add New Seminar";
            document.getElementById('submitBtn').innerText = "Save Seminar";
            document.getElementById('cancelBtn').style.display = "none";
        }
    </script>

</body>
</html>