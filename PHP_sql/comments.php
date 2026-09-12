<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: 404.php");
    exit();
}

require_once 'connect.php';

// --- COMMENT MODERATION LOGIC (ACCEPT/REJECT) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'odbi') {
        // Delete the rejected comment from the database
        $stmt = $conn->prepare("DELETE FROM komentar WHERE IDK = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
    } elseif ($action === 'prihvati') {
        // 1. Fetch the comment from the pending 'komentar' table
        $stmt = $conn->prepare("SELECT * FROM komentar WHERE IDK = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // 2. Move it to the accepted comments table ('pkomentar')
            $insert = $conn->prepare("INSERT INTO pkomentar (IDK, SifraSeminara, Tekst, KreiranoAt) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiss", $row['IDK'], $row['SifraSeminara'], $row['Tekst'], $row['KreiranoAt']);
            
            if ($insert->execute()) {
                // 3. Delete from the original table only after a successful copy
                $del = $conn->prepare("DELETE FROM komentar WHERE IDK = ?");
                $del->bind_param("i", $id);
                $del->execute();
            }
        }
    }
    
    // Redirect to avoid form resubmission on page refresh (PRG pattern)
    header("Location: comments.php");
    exit();
}

// --- ADD NEW COMMENT LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sifra_seminara = intval($_POST['sifra_seminara']);
    $tekst = trim($_POST['tekst']);
    $ocjena = $_POST['ocjena']; 
    
    // Ensure mutually exclusive flags based on sentiment
    $pozitivno = ($ocjena === 'pozitivno') ? 1 : 0;
    $negativno = ($ocjena === 'negativno') ? 1 : 0;
    $kreirano_at = date('Y-m-d H:i:s'); // Current timestamp

    if (!empty($tekst) && $sifra_seminara > 0) {
        $stmt = $conn->prepare("INSERT INTO komentar (SifraSeminara, Tekst, Pozitivno, Negativno, KreiranoAt) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $sifra_seminara, $tekst, $pozitivno, $negativno, $kreirano_at);
        $stmt->execute();
        
        // Redirect after successful insert
        header("Location: comments.php");
        exit();
    }
}

// Fetch seminars for the dropdown menu
$seminars = $conn->query("SELECT SifraSeminara, NazivSeminara FROM seminar");

// Fetch pending comments for the table (JOIN with seminar to get the seminar name)
$sql = "SELECT k.IDK, k.Tekst, k.Pozitivno, k.Negativno, k.KreiranoAt, s.NazivSeminara 
        FROM komentar k 
        LEFT JOIN seminar s ON k.SifraSeminara = s.SifraSeminara 
        ORDER BY k.KreiranoAt DESC";
$comments = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Moderation</title>
    <link rel="stylesheet" href="../Style/comments.css">
</head>
<body>

    <nav class="top-nav">
        <div class="nav-brand">💬 Comment Moderation</div>
        <div class="nav-user">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>
    </nav>

    <main class="main-container">
        
        <!-- ADD COMMENT FORM -->
        <div class="form-card">
            <h2>Add New Comment</h2>

            <form action="comments.php" method="POST">
                
                <div class="input-group">
                    <label>Select Seminar</label>
                    <select name="sifra_seminara" required>
                        <option value="">-- Choose Seminar --</option>
                        <?php while ($sem = $seminars->fetch_assoc()): ?>
                            <option value="<?php echo $sem['SifraSeminara']; ?>">
                                <?php echo htmlspecialchars($sem['NazivSeminara']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Comment Text</label>
                    <textarea name="tekst" rows="3" required placeholder="Write comment here..."></textarea>
                </div>

                <div class="radio-group">
                    <label class="radio-label positive">
                        <input type="radio" name="ocjena" value="pozitivno" required> 
                        👍 Positive
                    </label>
                    <label class="radio-label negative">
                        <input type="radio" name="ocjena" value="negativno" required> 
                        👎 Negative
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Submit Comment</button>
                </div>
            </form>
        </div>

        <!-- PENDING COMMENTS TABLE -->
        <div class="table-card">
            <h2>Pending Comments</h2>
            
            <?php if ($comments && $comments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>IDK</th>
                                <th>Seminar</th>
                                <th>Comment Text</th>
                                <th>Sentiment</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $comments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['IDK']; ?></td>
                                    <td><?php echo htmlspecialchars($row['NazivSeminara']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Tekst']); ?></td>
                                    <td>
                                        <?php if ($row['Pozitivno'] == 1): ?>
                                            <span class="badge-safe">Positive</span>
                                        <?php else: ?>
                                            <span class="badge-danger">Negative</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($row['KreiranoAt'])); ?></td>
                                    <td class="action-cell">
                                        <a href="comments.php?action=prihvati&id=<?php echo $row['IDK']; ?>" class="btn-accept">✔ Accept</a>
                                        <a href="comments.php?action=odbi&id=<?php echo $row['IDK']; ?>" class="btn-reject" onclick="return confirm('Are you sure you want to reject and delete this comment?');">✖ Reject</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">No pending comments for review.</div>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>