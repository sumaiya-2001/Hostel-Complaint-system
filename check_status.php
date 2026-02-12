<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $complaint_id = mysqli_real_escape_string($conn, $_POST['complaint_id']);
    
    $sql = "SELECT * FROM complaints WHERE complaint_id = '$complaint_id'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $response = [
            'success' => true,
            'complaint_id' => $row['complaint_id'],
            'name' => $row['name'],
            'hostel' => $row['hostel'],
            'room_number' => $row['room_number'],
            'complaint_type' => $row['complaint_type'],
            'complaint_date' => $row['complaint_date'],
            'status' => $row['status'],
            'priority' => $row['priority']
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Complaint not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>