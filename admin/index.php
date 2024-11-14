<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ./login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'ThreadCoworking';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all users
    $stmt = $pdo->query("SELECT * FROM Users ORDER BY user_id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all threads with user information
    $threadsQuery = "SELECT t.*, u.username, d.name as domain_name 
                    FROM Threads t 
                    JOIN Users u ON t.user_id = u.user_id 
                    JOIN Domains d ON t.domain_id = d.domain_id 
                    ORDER BY t.created_at DESC";
    $threadsStmt = $pdo->query($threadsQuery);
    $threads = $threadsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all domains
    $domainsQuery = "SELECT * FROM Domains ORDER BY domain_id DESC";
    $domainsStmt = $pdo->query($domainsQuery);
    $domains = $domainsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TBC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        /* Navbar Styles */
        .navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #764ba2;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #764ba2;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-name {
            color: #333;
            font-weight: 500;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #cc0000;
        }

        /* Main Content Styles */
        .main-content {
            margin-top: 100px;
            padding: 2rem;
        }

        .users-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background-color: #764ba2;
            color: white;
            font-weight: 500;
        }

        .users-table tr:hover {
            background-color: #f9f9f9;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .role-admin {
            background-color: #ff4444;
            color: white;
        }

        .role-user {
            background-color: #4CAF50;
            color: white;
        }

        .content-section {
            margin-bottom: 2rem;
        }

        .content-section h2 {
            color: #333;
            margin-bottom: 1rem;
        }

        .threads-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .empty-message {
            padding: 2rem;
            text-align: center;
            color: #666;
            font-style: italic;
        }

        .view-btn, .delete-btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 0.25rem;
        }

        .view-btn {
            background-color: #4CAF50;
            color: white;
        }

        .delete-btn {
            background-color: #ff4444;
            color: white;
        }

        .view-btn:hover {
            background-color: #45a049;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .add-thread-btn {
            padding: 0.5rem 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .add-thread-btn:hover {
            background-color: #45a049;
        }

        .domains-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .domains-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .domains-table th,
        .domains-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .domains-table th {
            background-color: #764ba2;
            color: white;
            font-weight: 500;
        }

        .domains-table tr:hover {
            background-color: #f9f9f9;
        }

        .add-domain-btn {
            padding: 0.5rem 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .add-domain-btn:hover {
            background-color: #45a049;
        }

        .edit-btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 0.25rem;
            background-color: #ffa500;
            color: white;
        }

        .edit-btn:hover {
            background-color: #ff8c00;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="logo">TBC</a>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="settings.php">Settings</a>
        </div>
        <div class="user-section">
            <span class="admin-name">Admin: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Add Threads Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>Threads Overview</h2>
                <a href="addthread.php" class="add-thread-btn">Add New Thread</a>
            </div>
            <div class="threads-table">
                <?php if (empty($threads)): ?>
                    <div class="empty-message">
                        <p>No threads have been created yet.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Domain</th>
                                <th>Upvotes</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($threads as $thread): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($thread['thread_id']); ?></td>
                                    <td><?php echo htmlspecialchars($thread['title']); ?></td>
                                    <td><?php echo htmlspecialchars($thread['username']); ?></td>
                                    <td><?php echo htmlspecialchars($thread['domain_name']); ?></td>
                                    <td><?php echo htmlspecialchars($thread['upvote_count']); ?></td>
                                    <td><?php echo htmlspecialchars($thread['created_at']); ?></td>
                                    <td>
                                        <button class="view-btn" onclick="viewThread(<?php echo $thread['thread_id']; ?>)">View</button>
                                        <button class="delete-btn" onclick="deleteThread(<?php echo $thread['thread_id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Domains Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>Domains Overview</h2>
                <a href="adddomain.php" class="add-domain-btn">Add New Domain</a>
            </div>
            <div class="domains-table">
                <?php if (empty($domains)): ?>
                    <div class="empty-message">
                        <p>No domains have been created yet.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($domains as $domain): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($domain['domain_id']); ?></td>
                                    <td><?php echo htmlspecialchars($domain['name']); ?></td>
                                    <td><?php echo htmlspecialchars($domain['description']); ?></td>
                                    <td>
                                        <button class="edit-btn" onclick="editDomain(<?php echo $domain['domain_id']; ?>)">Edit</button>
                                        <button class="delete-btn" onclick="deleteDomain(<?php echo $domain['domain_id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Users table section (previous code) -->
        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function viewThread(threadId) {
            // Implement view functionality
            window.location.href = `view_thread.php?id=${threadId}`;
        }

        function deleteThread(threadId) {
            if (confirm('Are you sure you want to delete this thread?')) {
                // Implement delete functionality
                window.location.href = `delete_thread.php?id=${threadId}`;
            }
        }

        function editDomain(domainId) {
            window.location.href = `edit_domain.php?id=${domainId}`;
        }

        function deleteDomain(domainId) {
            if (confirm('Are you sure you want to delete this domain? This will also delete all associated threads.')) {
                window.location.href = `delete_domain.php?id=${domainId}`;
            }
        }

        // Add scroll effect for navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            } else {
                navbar.style.backgroundColor = 'white';
            }
        });
    </script>
</body>
</html>
