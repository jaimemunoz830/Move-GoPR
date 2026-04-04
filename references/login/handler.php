<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "user_creds";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: Please try again later.");
    }

    // Start session securely
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_POST['register'])){
        $username = trim($_POST['uname']);
        $password = $_POST['password'];

        $password = password_hash($password, PASSWORD_DEFAULT);
        $updt = $conn->prepare("INSERT INTO user_creds (username, password, status) VALUES (?,?,'user')");
        $updt->bind_param("ss", $username, $password);
        $updt->execute();

        $_SESSION['404popup'] = "User registered succesfully. Please login.";
        header("Location: loginpage.php");
        exit();
        $updt->close();
    }

    // Ensure POST values are set and sanitized
    if (isset($_POST['uname']) && isset($_POST['password'])) {
        $username = trim($_POST['uname']);
        $password = $_POST['password'];

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT password FROM user_creds WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];

            // Verify the hashed password
            if (password_verify($password, $hashedPassword)) {
                // Fetch user status securely
                $stmt = $conn->prepare("SELECT username, status FROM user_creds WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($result->num_rows > 0) {
                    $_SESSION['uname'] = $username;
                    $_SESSION['status'] = $row['status'];
                    if (trim($row['status']) === 'admin' || trim($row['status']) === 'viewer' || trim($row['status']) === 'user' ){
                        header("Location: welcome.php");
                        exit();
                    } else{
                        $_SESSION['404popup'] = "Invalid credentials.";
                        header("Location: loginpage.php");
                        exit();
                    }
                } else {
                    $_SESSION['404popup'] = "Invalid username.";
                    header("Location: loginpage.php");
                    exit();
                }
            } else {
                $_SESSION['404popup'] = "Invalid password.";
                header("Location: loginpage.php");
                exit();
            }
        } else {
            $_SESSION['404popup'] = "Invalid username or password.";
            header("Location: loginpage.php");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['404popup'] = "Please fill in all fields.";
        header("Location: loginpage.php");
        exit();
    }

    $conn->close();
?>
