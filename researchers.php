<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: LogIn.php");
    exit();
}

require_once 'connect.php';

$errorMessage = "";

// --- DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);
    
    // Find the researcher to refund their salary back to the seminar budget
    $stmt = $conn->prepare("SELECT Plata, SifraSeminara FROM istrazivac WHERE SifraIstrazivaca = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $refund_amount = $row['Plata'];
        $seminar_id = $row['SifraSeminara'];
        
        // Refund budget
        $refund_stmt = $conn->prepare("UPDATE seminar SET Budzet = Budzet + ? WHERE SifraSeminara = ?");
        $refund_stmt->bind_param("di", $refund_amount, $seminar_id);
        $refund_stmt->execute();
        
        // Delete researcher
        $del_stmt = $conn->prepare("DELETE FROM istrazivac WHERE SifraIstrazivaca = ?");
        $del_stmt->bind_param("i", $id_to_delete);
        $del_stmt->execute();
    }
    header("Location: researchers.php");
    exit();
}

// --- FORM SUBMISSION LOGIC (Create or Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sifra = $_POST['sifra'] ?? '';
    $ime = trim($_POST['ime']);
    $datum_rodjenja = $_POST['datum_rodjenja'];
    $datum_zaposlenja = $_POST['datum_zaposlenja'];
    $plata = floatval($_POST['plata']);
    $sifra_instituta = intval($_POST['sifra_instituta']);
    $sifra_seminara = intval($_POST['sifra_seminara']);

    // Get current seminar budget
    $stmt_budzet = $conn->prepare("SELECT Budzet FROM seminar WHERE SifraSeminara = ?");
    $stmt_budzet->bind_param("i", $sifra_seminara);
    $stmt_budzet->execute();
    $seminar = $stmt_budzet->get_result()->fetch_assoc();
    $trenutni_budzet = $seminar['Budzet'] ?? 0;

    if (!empty($sifra)) {
        // UPDATE MODE
        // 1. Get old researcher data to calculate budget difference
        $stmt_old = $conn->prepare("SELECT Plata, SifraSeminara FROM istrazivac WHERE SifraIstrazivaca = ?");
        $stmt_old->bind_param("i", $sifra);
        $stmt_old->execute();
        $old_data = $stmt_old->get_result()->fetch_assoc();
        
        $available_budget = $trenutni_budzet;
        if ($old_data['SifraSeminara'] == $sifra_seminara) {
            $available_budget += $old_data['Plata']; // Add old salary back virtually for check
        }

        if ($plata > $available_budget) {
            $errorMessage = "Error: Salary ({$plata} €) exceeds the available seminar budget!";
        } else {
            // Refund old salary to old seminar
            $conn->query("UPDATE seminar SET Budzet = Budzet + {$old_data['Plata']} WHERE SifraSeminara = {$old_data['SifraSeminara']}");
            
            // Deduct new salary from new seminar
            $conn->query("UPDATE seminar SET Budzet = Budzet - {$plata} WHERE SifraSeminara = {$sifra_seminara}");

            // Update researcher
            $stmt = $conn->prepare("UPDATE istrazivac SET ImeIstrazivaca=?, DatumRodjenja=?, DatumZaposlenja=?, Plata=?, SifraInstituta=?, SifraSeminara=? WHERE SifraIstrazivaca=?");
            $stmt->bind_param("sssdiis", $ime, $datum_rodjenja, $datum_zaposlenja, $plata, $sifra_instituta, $sifra_seminara, $sifra);
            $stmt->execute();
            header("Location: researchers.php");
            exit();
        }
    } else {
        // CREATE MODE
        if ($plata > $trenutni_budzet) {
            $errorMessage = "Error: Salary ({$plata} €) is greater than the seminar's budget ({$trenutni_budzet} €)!";
        } else {
            // Insert Researcher
            $stmt = $conn->prepare("INSERT INTO istrazivac (ImeIstrazivaca, DatumRodjenja, DatumZaposlenja, Plata, SifraInstituta, SifraSeminara) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssddd", $ime, $datum_rodjenja, $datum_zaposlenja, $plata, $sifra_instituta, $sifra_seminara);
            
            if ($stmt->execute()) {
                // Deduct budget
                $deduct = $conn->prepare("UPDATE seminar SET Budzet = Budzet - ? WHERE SifraSeminara = ?");
                $deduct->bind_param("di", $plata, $sifra_seminara);
                $deduct->execute();
                
                header("Location: researchers.php");
                exit();
            }
        }
    }
}

// Fetch Lists for Dropdowns
$institutes = $conn->query("SELECT SifraInstituta, NazivInstituta FROM institut");
$seminars = $conn->query("SELECT SifraSeminara, NazivSeminara, Budzet FROM seminar");

// Fetch Researchers with JOIN for table display
$sql = "SELECT i.SifraIstrazivaca, i.ImeIstrazivaca, i.DatumRodjenja, i.DatumZaposlenja, i.Plata, 
               i.SifraInstituta, i.SifraSeminara, inst.NazivInstituta, sem.NazivSeminara 
        FROM istrazivac i
        LEFT JOIN institut inst ON i.SifraInstituta = inst.SifraInstituta
        LEFT JOIN seminar sem ON i.SifraSeminara = sem.SifraSeminara";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Researchers</title>
    <link rel="stylesheet" href="researchers.css">
</head>
<body>

    <nav class="top-nav">
        <div class="nav-brand">👨‍🔬 Researcher Management</div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>
    </nav>

    <main class="main-container">
        
        <div class="form-card">
            <h2 id="form-title">Add New Researcher</h2>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-alert"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form action="researchers.php" method="POST" id="researcherForm">
                <div class="input-grid">
                    
                    <div class="input-group">
                        <label>Code (Auto-generated)</label>
                        <input type="text" id="sifra" name="sifra" readonly placeholder="New Entry">
                    </div>

                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" id="ime" name="ime" required>
                    </div>

                    <div class="input-group">
                        <label>Date of Birth</label>
                        <input type="date" id="datum_rodjenja" name="datum_rodjenja" required>
                    </div>

                    <div class="input-group">
                        <label>Date of Employment</label>
                        <input type="date" id="datum_zaposlenja" name="datum_zaposlenja" required>
                    </div>

                    <div class="input-group">
                        <label>Salary (€)</label>
                        <input type="number" id="plata" name="plata" step="0.01" min="0" required>
                    </div>

                    <div class="input-group">
                        <label>Institute</label>
                        <select id="sifra_instituta" name="sifra_instituta" required>
                            <option value="">-- Select Institute --</option>
                            <?php 
                            // Reset pointer just in case and loop
                            $institutes->data_seek(0);
                            while ($inst = $institutes->fetch_assoc()): ?>
                                <option value="<?php echo $inst['SifraInstituta']; ?>">
                                    <?php echo htmlspecialchars($inst['NazivInstituta']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Seminar</label>
                        <select id="sifra_seminara" name="sifra_seminara" required>
                            <option value="">-- Select Seminar --</option>
                            <?php 
                            $seminars->data_seek(0);
                            while ($sem = $seminars->fetch_assoc()): ?>
                                <option value="<?php echo $sem['SifraSeminara']; ?>">
                                    <?php echo htmlspecialchars($sem['NazivSeminara']); ?> (Budget: <?php echo $sem['Budzet']; ?>€)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save" id="submitBtn">Save Researcher</button>
                    <button type="button" class="btn-cancel" id="cancelBtn" style="display: none;" onclick="resetForm()">Cancel</button>
                </div>
            </form>
        </div>

        <div class="table-card">
            <h2>Researcher List</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>DOB</th>
                                <th>Employed</th>
                                <th>Salary</th>
                                <th>Institute</th>
                                <th>Seminar</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['SifraIstrazivaca']; ?></td>
                                    <td><?php echo htmlspecialchars($row['ImeIstrazivaca']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($row['DatumRodjenja'])); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($row['DatumZaposlenja'])); ?></td>
                                    <td><?php echo number_format($row['Plata'], 2); ?> €</td>
                                    <td><?php echo htmlspecialchars($row['NazivInstituta'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['NazivSeminara'] ?? 'N/A'); ?></td>
                                    <td class="action-cell">
                                        <button class="btn-edit" onclick="editResearcher(
                                            <?php echo $row['SifraIstrazivaca']; ?>, 
                                            '<?php echo addslashes($row['ImeIstrazivaca']); ?>', 
                                            '<?php echo $row['DatumRodjenja']; ?>',
                                            '<?php echo $row['DatumZaposlenja']; ?>',
                                            <?php echo $row['Plata']; ?>,
                                            <?php echo $row['SifraInstituta']; ?>,
                                            <?php echo $row['SifraSeminara']; ?>
                                        )">Edit</button>
                                        <a href="researchers.php?delete=<?php echo $row['SifraIstrazivaca']; ?>" class="btn-delete" onclick="return confirm('Are you sure? Their salary will be refunded to the seminar budget.');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">No researchers found.</div>
            <?php endif; ?>
        </div>

    </main>

    <script>
        function editResearcher(sifra, ime, dob, doj, plata, inst_id, sem_id) {
            document.getElementById('sifra').value = sifra;
            document.getElementById('ime').value = ime;
            document.getElementById('datum_rodjenja').value = dob;
            document.getElementById('datum_zaposlenja').value = doj;
            document.getElementById('plata').value = plata;
            document.getElementById('sifra_instituta').value = inst_id;
            document.getElementById('sifra_seminara').value = sem_id;
            
            document.getElementById('form-title').innerText = "Edit Researcher";
            document.getElementById('submitBtn').innerText = "Update Researcher";
            document.getElementById('cancelBtn').style.display = "inline-block";
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('researcherForm').reset();
            document.getElementById('sifra').value = '';
            document.getElementById('form-title').innerText = "Add New Researcher";
            document.getElementById('submitBtn').innerText = "Save Researcher";
            document.getElementById('cancelBtn').style.display = "none";
        }
    </script>
</body>
</html>