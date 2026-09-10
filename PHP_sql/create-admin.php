<?php
    // Include the database connection file
    require_once 'connect.php';

    // Array storing the 3 admin accounts to be inserted
    $admins = [
        ['username' => 'aid1', 'password' => '11111111'],
        ['username' => 'aid2', 'password' => '22222222'],
        ['username' => 'aid3', 'password' => '33333333']
    ];

    // SQL query using placeholders (?) to prevent SQL injection
    // Updated to match your actual column names: KIme and LozinkaHasb
    $sql = "INSERT INTO administrator (KIme, LozinkaHasb) VALUES (?, ?)";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Loop through each admin record in the array
        foreach ($admins as $admin) {
            $kIme = $admin['username'];
            
            // Hash the password securely before inserting it into the 'LozinkaHasb' column
            // This is crucial for security - never store plain text passwords!
            $hashedPassword = password_hash($admin['password'], PASSWORD_DEFAULT);

            // Bind string parameters to the prepared statement ("ss" = 2 strings)
            $stmt->bind_param("ss", $kIme, $hashedPassword);

            // Execute the insertion statement
            if ($stmt->execute()) {
                echo "Admin user '{$kIme}' created successfully.<br>";
            } else {
                echo "Failed to create user '{$kIme}': " . $stmt->error . "<br>";
            }
        }

        // Close the prepared statement to free up resources
        $stmt->close();
    } else {
        // Show error if the query couldn't be prepared (usually a typo in table/column names)
        echo "Query preparation failed: " . $conn->error;
    }

    // Close the active database connection
    $conn->close();
?>