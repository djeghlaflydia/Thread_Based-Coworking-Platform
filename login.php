<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'ThreadCoworking';
$username = 'root';
$password = '';
//PHP Data Objects
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Login validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $error = null;

    // Check if email exists and get user data
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify password
        if ($password == $user['password']) {
            // Check if user is admin
            if ($user['role'] === 'admin') {
                header("Location: ./admin/login.php");
                exit();
            }

            // Set session variables for non-admin users
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            // Add any other session variables you need

            header("Location: ./index.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Email not found";
    }

    if ($error) {
        header("Location: login.php?error=" . urlencode($error));
        exit();
    }
}

// Get error message from URL if it exists
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thread Coworking - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .logo span {
            color: #764ba2;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            outline: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 10px rgba(118, 75, 162, 0.2);
        }

        .input-group label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: 0;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 12px;
            color: #764ba2;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: #764ba2;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            background: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
        }

        .create-account {
            text-align: center;
        }

        .create-account a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .create-account a:hover {
            color: #667eea;
            text-decoration: underline;
        }

        .error-message {
            color: #ff4444;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        /* Loading animation for button */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .error-banner {
            background-color: #ff4444;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
            display: <?php echo $error_message ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Welcome to <span>Thread Coworking</span></h1>
            <p>Please login to continue</p>
        </div>
        <!-- Add error banner -->
        <div class="error-banner">
            <?php echo $error_message; ?>
        </div>
        <!-- Modified form to use POST method and point to same page -->
        <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " required>
                <label for="email">Email Address</label>
                <div class="error-message" id="emailError">Please enter a valid email address</div>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required>
                <label for="password">Password</label>
                <div class="error-message" id="passwordError">Password must be at least 6 characters</div>
            </div>
            <button type="submit" class="login-btn" id="loginBtn">Connect</button>
            <div class="create-account">
                <p>Don't have an account? <a href="#">Create Account</a></p>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');

            // Add floating label effect
            const inputs = document.querySelectorAll('.input-group input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });

            // Form submission handling
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                let isValid = true;

                // Reset errors
                emailError.style.display = 'none';
                passwordError.style.display = 'none';

                // Email validation
                if (!isValidEmail(email)) {
                    emailError.style.display = 'block';
                    isValid = false;
                }

                // Password validation
                if (password.length < 6) {
                    passwordError.style.display = 'block';
                    isValid = false;
                }

                if (isValid) {
                    // Show loading state
                    loginBtn.classList.add('loading');
                    loginBtn.textContent = '';

                    // Simulate API call
                    setTimeout(() => {
                        loginBtn.classList.remove('loading');
                        loginBtn.textContent = 'Connect';
                        // Here you would typically handle the actual login logic
                        console.log('Login attempt with:', { email, password });
                    }, 2000);
                }
            });

            // Email validation helper
            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            // Add some nice hover effects
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('mouseover', function() {
                    if (!this.matches(':focus')) {
                        this.style.borderColor = '#667eea';
                    }
                });
                input.addEventListener('mouseout', function() {
                    if (!this.matches(':focus')) {
                        this.style.borderColor = '#ddd';
                    }
                });
            });
        });
    </script>
</body>
</html> 