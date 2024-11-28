<?php

$title = 'Add User';
$subTitle = 'Admin Panel';
include './partials/layouts/layoutTop.php';

// Check the logged-in user's role
$loggedInUserRole = $_SESSION['role']; // Assuming the session holds the logged-in user's role

// Handle form submission for adding users
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if the logged-in user has permission to assign the role
    if ($loggedInUserRole == 'manager' && ($role == 'manager' || $role == 'admin')) {
        echo "<div class='alert alert-danger'>You cannot assign Manager or Admin roles as you are a Manager.</div>";
    } elseif ($loggedInUserRole == 'team_lead' && ($role == 'team_lead' || $role == 'manager' || $role == 'admin')) {
        echo "<div class='alert alert-danger'>You cannot assign Team Lead, Manager, or Admin roles as you are a Team Leader.</div>";
    } else {
        // Insert new user into the database if the role is valid
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>User added successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
}

?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="add_user" value="1">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select name="role" class="form-control" required>
                            <?php
                            // Role options based on logged-in user
                            if ($loggedInUserRole == 'admin') {
                                echo '<option value="user">User</option>';
                                echo '<option value="team_lead">Team Lead</option>';
                                echo '<option value="manager">Manager</option>';
                                echo '<option value="admin">Admin</option>';
                            } elseif ($loggedInUserRole == 'manager') {
                                echo '<option value="user">User</option>';
                                echo '<option value="team_lead">Team Lead</option>';
                            } elseif ($loggedInUserRole == 'team_lead') {
                                echo '<option value="user">User</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include './partials/layouts/layoutBottom.php' ?>
