<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: 404.php");
    exit();
}

require_once 'connect.php';

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM institut WHERE SifraInstituta = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $stmt->close();
    header("Location: institute.php"); // Refresh to prevent URL keeping the delete parameter
    exit();
}

// Handle Form Submission (Create or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sifra = $_POST['sifra'] ?? ''; // It will be empty on Create, filled on Update
    $naziv = trim($_POST['naziv']);
    $grad = trim($_POST['grad']);

    if (!empty($sifra)) {
        // UPDATE existing record
        $stmt = $conn->prepare("UPDATE institut SET NazivInstituta = ?, Grad = ? WHERE SifraInstituta = ?");
        $stmt->bind_param("ssi", $naziv, $grad, $sifra);
        $stmt->execute();
        $stmt->close();
    } else {
        // INSERT new record (SifraInstituta is Auto-Incremented by DB)
        $stmt = $conn->prepare("INSERT INTO institut (NazivInstituta, Grad) VALUES (?, ?)");
        $stmt->bind_param("ss", $naziv, $grad);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: institute.php"); // Refresh to clear form data
    exit();
}

// Fetch all institutes from DB
$sql = "SELECT SifraInstituta, NazivInstituta, Grad FROM institut";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Institutes</title>
    <link rel="stylesheet" href="../Style/institute.css">
</head>
<body>

    <!-- Top Navigation with Back Button -->
    <nav class="top-nav">
        <div class="nav-brand">🏛️ Institute Management</div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>
    </nav>

    <main class="main-container">
        
        <!-- Form Section -->
        <div class="form-card">
            <h2 id="form-title">Add New Institute</h2>
            <form action="institute.php" method="POST" id="instituteForm">
                
                <div class="input-row">
                    <!-- Readonly Code Field -->
                    <div class="input-group">
                        <label for="sifra">Institute Code (Auto-generated)</label>
                        <input type="text" id="sifra" name="sifra" placeholder="New Entry" readonly>
                    </div>

                    <!-- Name Field -->
                    <div class="input-group">
                        <label for="naziv">Institute Name</label>
                        <input type="text" id="naziv" name="naziv" required>
                    </div>

                    <!-- City Field -->
                    <div class="input-group">
                        <label for="grad">City</label>
                        <input type="text" id="grad" name="grad" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save" id="submitBtn">Save Institute</button>
                    <button type="button" class="btn-cancel" id="cancelBtn" style="display: none;" onclick="resetForm()">Cancel Edit</button>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-card">
            <h2>Institute List</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Institute Name</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['SifraInstituta']); ?></td>
                                <td><?php echo htmlspecialchars($row['NazivInstituta']); ?></td>
                                <td><?php echo htmlspecialchars($row['Grad']); ?></td>
                                <td class="action-cell">
                                    <!-- Edit uses JS to populate form -->
                                    <button class="btn-edit" onclick="editInstitute(<?php echo $row['SifraInstituta']; ?>, '<?php echo addslashes($row['NazivInstituta']); ?>', '<?php echo addslashes($row['Grad']); ?>')">Edit</button>
                                    <!-- Delete sends GET request -->
                                    <a href="institute.php?delete=<?php echo $row['SifraInstituta']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this institute?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No institutes found in the database.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- JavaScript to handle Edit population -->
    <script>
        function editInstitute(sifra, naziv, grad) {
            document.getElementById('sifra').value = sifra;
            document.getElementById('naziv').value = naziv;
            document.getElementById('grad').value = grad;
            
            document.getElementById('form-title').innerText = "Edit Institute";
            document.getElementById('submitBtn').innerText = "Update Institute";
            document.getElementById('cancelBtn').style.display = "inline-block";
            
            // Scroll back to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('instituteForm').reset();
            document.getElementById('sifra').value = ''; // Ensure it's empty for new creation
            document.getElementById('form-title').innerText = "Add New Institute";
            document.getElementById('submitBtn').innerText = "Save Institute";
            document.getElementById('cancelBtn').style.display = "none";
        }
    </script>

</body>
</html>