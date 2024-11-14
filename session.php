<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

// If we get here, user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thread Coworking - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .welcome {
            color: #333;
        }

        .welcome span {
            color: #764ba2;
            font-weight: bold;
        }

        .logout-btn {
            padding: 10px 20px;
            background-color: #ff4444;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #cc0000;
        }

        .session-info {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .session-info h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .info-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="welcome">Welcome to <span>Thread Coworking</span></h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="session-info">
            <h2>Your Session Information</h2>
            <?php
            // Display all session variables
            foreach ($_SESSION as $key => $value) {
                echo "<div class='info-item'>";
                echo "<span class='info-label'>" . htmlspecialchars($key) . ":</span>";
                echo "<span>" . htmlspecialchars($value) . "</span>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
