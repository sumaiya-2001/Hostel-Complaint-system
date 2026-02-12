<?php
include 'database.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "DELETE FROM complaints WHERE id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect back to admin page
        header("Location: admin.php");
        exit();
    } else {
        echo "Error deleting complaint: " . $conn->error;
    }
} else {
    header("Location: admin.php");
    exit();
}

$conn->close();
?>