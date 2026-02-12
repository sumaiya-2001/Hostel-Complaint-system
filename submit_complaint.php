<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $reg_number = mysqli_real_escape_string($conn, $_POST['reg_number']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $faculty = mysqli_real_escape_string($conn, $_POST['faculty']);
    $hostel = mysqli_real_escape_string($conn, $_POST['hostel']);
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    $complaint_type = mysqli_real_escape_string($conn, $_POST['complaint_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    
    // Get current date
    $complaint_date = date('Y-m-d');
    
    // Default status
    $status = "Pending";
    
    // Generate a unique complaint ID
    $complaint_id = "COMP" . date('Ymd') . rand(1000, 9999);
    
    // Insert into database
    $sql = "INSERT INTO complaints (complaint_id, name, reg_number, year, faculty, hostel, room_number, 
            complaint_type, description, priority, complaint_date, status) 
            VALUES ('$complaint_id', '$name', '$reg_number', '$year', '$faculty', '$hostel', 
            '$room_number', '$complaint_type', '$description', '$priority', '$complaint_date', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        // Success - show receipt
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Complaint Submitted</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>
            <div class="container">
                <div class="success-message">
                    <h2>✅ Complaint Submitted Successfully!</h2>
                    
                    <div class="receipt">
                        <h3>Complaint Receipt</h3>
                        <table>
                            <tr><td><strong>Complaint ID:</strong></td><td><?php echo $complaint_id; ?></td></tr>
                            <tr><td><strong>Name:</strong></td><td><?php echo htmlspecialchars($name); ?></td></tr>
                            <tr><td><strong>Reg. Number:</strong></td><td><?php echo htmlspecialchars($reg_number); ?></td></tr>
                            <tr><td><strong>Year & Faculty:</strong></td><td><?php echo htmlspecialchars($year) . " - " . htmlspecialchars($faculty); ?></td></tr>
                            <tr><td><strong>Hostel & Room:</strong></td><td><?php echo htmlspecialchars($hostel) . " - Room " . htmlspecialchars($room_number); ?></td></tr>
                            <tr><td><strong>Complaint Type:</strong></td><td><?php echo htmlspecialchars($complaint_type); ?></td></tr>
                            <tr><td><strong>Priority:</strong></td><td><?php echo htmlspecialchars($priority); ?></td></tr>
                            <tr><td><strong>Date:</strong></td><td><?php echo $complaint_date; ?></td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="status-pending">Pending</span></td></tr>
                        </table>
                        
                        <div class="instructions">
                            <p><strong>⚠️ Please Note:</strong></p>
                            <p>1. Save your Complaint ID for tracking</p>
                            <p>2. Complaints are usually addressed within 24-48 hours</p>
                            <p>3. Contact hostel office for urgent matters</p>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="index.html" class="btn">Submit Another Complaint</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
} else {
    header("Location: index.html");
    exit();
}
?>