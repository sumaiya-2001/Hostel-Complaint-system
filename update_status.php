<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "UPDATE complaints SET status='$status' WHERE id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect back to admin page
        header("Location: admin.php");
        exit();
    } else {
        echo "Error updating status: " . $conn->error;
    }
} else {
    header("Location: admin.php");
    exit();
}

$conn->close();
?>