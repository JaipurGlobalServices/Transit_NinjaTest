<?php
session_start();
include 'config.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Handle form submission for adding users
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>User added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Handle form submission for updating users
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET username=?, email=?, role=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $role, $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>User updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Handle delete user
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>User deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>
<?php
$title = 'Admin Panel';
$subTitle = 'User Management';
include './partials/layouts/layoutTop.php';
?>

<div class="row">
    <div class="col-12">
        <h1>Admin Panel</h1>

        <div class="card">
            <div class="card-body">
                <h2>Add New User</h2>
                <form method="post" action="">
                    <input type="hidden" name="add_user" value="1">
                    Username: <input type="text" name="username" class="form-control" required><br>
                    Email: <input type="email" name="email" class="form-control" required><br>
                    Password: <input type="password" name="password" class="form-control" required><br>
                    Role: 
                    <select name="role" class="form-control" required>
                        <option value="user">User</option>
                        <option value="team_lead">Team Lead</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select><br>
                    <input type="submit" value="Add User" class="btn btn-primary">
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h2>Existing Users</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id, username, email, role FROM users";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['username']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['role']}</td>
                                <td>
                                    <form method='post' action='' style='display:inline-block'>
                                        <input type='hidden' name='update_user' value='1'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        Username: <input type='text' name='username' value='{$row['username']}' class='form-control' required>
                                        Email: <input type='email' name='email' value='{$row['email']}' class='form-control' required>
                                        Role: 
                                        <select name='role' class='form-control' required>
                                            <option value='user' " . ($row['role'] == 'user' ? 'selected' : '') . ">User</option>
                                            <option value='team_lead' " . ($row['role'] == 'team_lead' ? 'selected' : '') . ">Team Lead</option>
                                            <option value='manager' " . ($row['role'] == 'manager' ? 'selected' : '') . ">Manager</option>
                                            <option value='admin' " . ($row['role'] == 'admin' ? 'selected' : '') . ">Admin</option>
                                        </select>
                                        <input type='submit' value='Update' class='btn btn-success mt-2'>
                                    </form>
                                    <a href='access.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure?\");' class='btn btn-danger mt-2'>Delete</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include './partials/layouts/layoutBottom.php' ?>
