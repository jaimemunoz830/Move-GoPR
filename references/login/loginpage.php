<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        } 
       h2 {
            text-align: center;
            color: #333;
        } 
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        } 
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus {
            border-color: #007BFF;
            outline: none;
        } 
        button {
            width: 33.33%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .button-container{
            display: flex;
            gap: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php
        session_start(); // Resume the session

        // Fetch the message from the session variable
        $message = isset($_SESSION['404popup']) ? htmlspecialchars($_SESSION['404popup']) : '';

        // Clear the session variable after fetching
        unset($_SESSION['404popup']);
    ?>
    <script>
        const popupMessage = "<?php echo $message; ?>";
        if (popupMessage) {
            alert(popupMessage);
        }
    </script>
    <div class="form-container">
        <h2>Order Form</h2>
        <form action="handler.php" method="POST">
            <div class="form-group">
                <label for="uname">Username</label>
                <input type="text" id="uname" name="uname" >
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" >
            </div>
            <div class="button-container"> 
                <button type="submit" name="login">Login</button>
                <button type="submit" name="register">Register</button>
                <button type="reset">Reset</button>
            </div>
            <!-- create a register button that inserts a new user into the database, 
                then sends a messages of "pending status review by admin" -->
        </form>
    </div> 
</body>
</html>
