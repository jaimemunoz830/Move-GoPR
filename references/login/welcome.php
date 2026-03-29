<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body style="text-align:center">
    <?php
        session_start();
        $uname = $_SESSION['uname'];
        $status = $_SESSION['password'];
        echo"<h1> Welcome ".htmlspecialchars($uname)."</h1>";
        
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "user_creds";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: Please try again later.");
        }
        if ($status === 'admin'){
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
                $targetuname = $_POST['username'];
            
                // Check if the username exists
                $stmt = $conn->prepare("SELECT username FROM user_creds WHERE username = ?");
                $stmt->bind_param("s", $targetuname);
                $stmt->execute();
                $result = $stmt->get_result();
            
                if ($result->num_rows > 0) {
                    // Proceed with deletion
                    $stmt = $conn->prepare("DELETE FROM user_creds WHERE username = ?");
                    $stmt->bind_param("s", $targetuname);
                    $stmt->execute();
                } else {
                    echo "Error: User does not exist.";
                }
            
                $stmt->close();
            
                // Refresh the page after deletion
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
                $currentUsername = $_POST['current_username'];  // Existing username
                $updateField = $_POST['update_field'];  // Either 'username' or 'status'
                $newValue = $_POST['new_value'];  // New input value
            
                // Check if input is empty
                if (!empty($newValue) && ($updateField === "username" || $updateField === "status")) {
                    // Prepare dynamic update statement
                    $stmt = $conn->prepare("UPDATE user_creds SET $updateField = ? WHERE username = ?");
                    $stmt->bind_param("ss", $newValue, $currentUsername);
                    $stmt->execute();
                    $stmt->close();
            
                    // Refresh the page after updating
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit();
                } else {
                    echo "Error: Please enter a valid value.";
                }
            }

            $sql = "SELECT username, password, status FROM user_creds";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Username</th><th>Status</th><th>Delete</th><th>Edit</th></tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>
                                <button type='submit' name='delete'>Delete</button>
                            </form>
                        </td>";
                    echo "<td>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='current_username' value='" . htmlspecialchars($row['username']) . "'>
                                <select name='update_field'>
                                    <option value='username'>Username</option>
                                    <option value='status'>Status</option>
                                </select>
                                <input type='text' name='new_value' placeholder='New Value'>
                                <button type='submit' name='update'>Update</button>
                            </form>
                    </td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Something went wrong, please try again later."; // Database empty
            }

            // Close connection
            $conn->close();
        }

        if ($status === 'user'){
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
                $targetuname = $_POST['username'];
            
                // Check if the username exists
                $stmt = $conn->prepare("SELECT username FROM user_creds WHERE username = ?");
                $stmt->bind_param("s", $targetuname);
                $stmt->execute();
                $result = $stmt->get_result();
            
                if ($result->num_rows > 0) {
                    // Proceed with deletion
                    $stmt = $conn->prepare("DELETE FROM user_creds WHERE username = ?");
                    $stmt->bind_param("s", $targetuname);
                    $stmt->execute();
                } else {
                    echo "Error: User does not exist.";
                }
            
                $stmt->close();
            
                // Refresh the page after deletion
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
                $currentUsername = $_POST['current_username'];  // Existing username
                $updateField = $_POST['update_field'];  // Either 'username' or 'status'
                $newValue = $_POST['new_value'];  // New input value
                if ($updateField === "password"){
                    $newValue = password_hash($newValue, PASSWORD_DEFAULT);
                }
            
                // Check if input is empty
                if (!empty($newValue) && ($updateField === "username" || $updateField === "password")) {
                    // Prepare dynamic update statement
                    $stmt = $conn->prepare("UPDATE user_creds SET $updateField = ? WHERE username = ?");
                    $stmt->bind_param("ss", $newValue, $currentUsername);
                    $stmt->execute();
                    $stmt->close();
            
                    // Return to login since user isnt available
                    header("Location: loginpage.php");
                    exit();
                } else {
                    echo "Error: Please enter a valid value.";
                }
            }

            $sql = $conn->prepare("SELECT * FROM user_creds WHERE username = ?");
            $sql->bind_param("s",$uname);
            $sql->execute();
            $result = $sql->get_result();

            if ($result->num_rows > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Username</th><th>Password</th><th>Delete</th><th>Edit</th></tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['password']) . "</td>";
                    echo "<td>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='username' value='" . htmlspecialchars($row['username']) . "'>
                                <button type='submit' name='delete'>Delete</button>
                            </form>
                        </td>";
                    echo "<td>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='current_username' value='" . htmlspecialchars($row['username']) . "'>
                                <select name='update_field'>
                                    <option value='username'>Username</option>
                                    <option value='password'>Password</option>
                                </select>
                                <input type='text' name='new_value' placeholder='New Value'>
                                <button type='submit' name='update'>Update</button>
                            </form>
                    </td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Something went wrong, please try again later."; // Database empty
            }

            // Close connection
            $conn->close();
        }
        if ($status === 'viewer'){ 
            $sql = "SELECT username, status FROM user_creds";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Username</th><th>Status</th></tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Something went wrong, please try again later."; // Database empty
            }

            // Close connection
            $conn->close();
        }
    ?>
</body>
</html>
