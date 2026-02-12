<?php
session_start();
include 'database.php';

// Check if password is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    // Set admin password (change this in production)
    $admin_password = "admin123";
    
    if ($password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Wrong password!";
    }
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <h2>🔐 Admin Login</h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Enter admin password" required>
                    </div>
                    <button type="submit" class="submit-btn">Login</button>
                </form>
                <p class="note">Password: admin123</p>
                <a href="index.html" class="back-link">← Back to Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Admin is logged in - Show dashboard
// Get statistics
$total = $conn->query("SELECT COUNT(*) as total FROM complaints")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) as pending FROM complaints WHERE status='Pending'")->fetch_assoc()['pending'];

// Handle filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) && $_GET['status'] != 'all' ? 
    " AND status = '" . mysqli_real_escape_string($conn, $_GET['status']) . "'" : '';

// Build query
$sql = "SELECT * FROM complaints WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR room_number LIKE '%$search%' OR complaint_id LIKE '%$search%')";
}
$sql .= $status_filter . " ORDER BY complaint_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-content">
                <h1>🔧 Admin Dashboard</h1>
                <p>Manage hostel complaints</p>
            </div>
            <div class="admin-nav">
                <a href="index.html" class="nav-btn">🏠 Home</a>
                <a href="?logout=true" class="nav-btn logout">🚪 Logout</a>
            </div>
        </header>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Complaints</h3>
                <p class="count"><?php echo $total; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <p class="count pending"><?php echo $pending; ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <input type="text" name="search" placeholder="Search complaints..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="all">All Status</option>
                    <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="In Progress" <?php echo (isset($_GET['status']) && $_GET['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
                <button type="submit" class="filter-btn">Search</button>
                <a href="admin.php" class="clear-btn">Clear</a>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['complaint_id']; ?></td>
                            <td><?php echo $row['complaint_date']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['name']); ?><br>
                                <small><?php echo htmlspecialchars($row['reg_number']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['hostel']); ?><br>
                                <small><?php echo htmlspecialchars($row['room_number']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['complaint_type']); ?></td>
                            <td>
                                <form action="update_status.php" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $row['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Completed" <?php echo $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="delete_complaint.php?id=<?php echo $row['id']; ?>" 
                                   class="delete-btn"
                                   onclick="return confirm('Delete this complaint?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <p class="table-info">Showing <?php echo $result->num_rows; ?> complaints</p>
            <?php else: ?>
                <p class="no-data">No complaints found.</p>
            <?php endif; ?>
        </div>

        <footer>
            <p>© 2024 Hostel Complaint System</p>
        </footer>
    </div>
</body>
</html>
<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.html");
    exit();
}
$conn->close();
?>