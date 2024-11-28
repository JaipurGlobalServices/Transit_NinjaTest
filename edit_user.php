<?php
// Start session only if not already started

$title = 'User Management';
$subTitle = 'Admin Panel';
include './partials/layouts/layoutTop.php';

// Ensure the user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Redirect non-admin roles to index.php
if ($_SESSION['role'] == 'team_lead' || $_SESSION['role'] == 'user') {
    header("Location: index.php");
    exit();
}

function fetchUsers($conn, $role) {
    if ($role == 'admin') {
        $sql = "SELECT id, username, email, role FROM users";
    } elseif ($role == 'manager') {
        $sql = "SELECT id, username, email, role FROM users WHERE role NOT IN ('admin', 'manager')";
    } elseif ($role == 'team_lead') {
        $sql = "SELECT id, username, email, role FROM users WHERE role = 'user'";
    } else {
        $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($role == 'user') {
        $stmt->bind_param("i", $_SESSION['id']);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Handle form submission for updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Check permissions for manager role
    if ($_SESSION['role'] == 'manager' && ($role == 'admin' || $role == 'manager')) {
        echo "<div class='alert alert-danger'>You cannot edit admin or manager roles.</div>";
    } else {
        $sql = "UPDATE users SET username=?, email=?, role=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $email, $role, $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>User details updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
}

// Handle form submission for updating password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $id = $_POST['id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $password, $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Password updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Handle delete user
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Check if user is attempting to delete their own account
    if ($_SESSION['id'] == $id) {
        echo "<div class='alert alert-danger'>You cannot delete your own account.</div>";
    } else {
        $sql = "DELETE FROM users WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>User deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
}

?>

<div class="row">
    <div class="col-12">
        <div class="card mt-4">
            <div class="card-body">
                <h4>Existing Users</h4>
                <div class="table-responsive">
                    <table class="table table-bordered" id="userTable" style="border-spacing: 0 10px;">
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
                            $result = fetchUsers($conn, $_SESSION['role']);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr style='margin-bottom: 10px;'>
                                    <td>{$row['username']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['role']}</td>
                                    <td>";

                                // Show edit forms only if the user has permission
                                if ($_SESSION['role'] == 'admin' || ($_SESSION['role'] == 'manager' && $row['role'] != 'admin' && $row['role'] != 'manager')) {
                                    echo "
                                        <!-- User Details Update Form -->
                                        <form method='post' action='' style='display:inline-block'>
                                            <input type='hidden' name='update_user' value='1'>
                                            <input type='hidden' name='id' value='{$row['id']}'>
                                            <input type='text' name='username' value='{$row['username']}' class='form-control' required>
                                            <input type='email' name='email' value='{$row['email']}' class='form-control' required>
                                            <select name='role' class='form-control' required>";

                                    // Exclude 'admin' and 'manager' for managers
                                    if ($_SESSION['role'] == 'manager') {
                                        echo "
                                            <option value='user' " . ($row['role'] == 'user' ? 'selected' : '') . ">User</option>
                                            <option value='team_lead' " . ($row['role'] == 'team_lead' ? 'selected' : '') . ">Team Lead</option>
                                        ";
                                    } else {
                                        echo "
                                            <option value='user' " . ($row['role'] == 'user' ? 'selected' : '') . ">User</option>
                                            <option value='team_lead' " . ($row['role'] == 'team_lead' ? 'selected' : '') . ">Team Lead</option>
                                            <option value='manager' " . ($row['role'] == 'manager' ? 'selected' : '') . ">Manager</option>
                                            <option value='admin' " . ($row['role'] == 'admin' ? 'selected' : '') . ">Admin</option>
                                        ";
                                    }

                                    echo "
                                            </select>
                                            <input type='submit' value='Update Details' class='btn btn-success mt-2'>
                                        </form>";
                                }

                                // Show password update form
                                if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') {
                                    echo "
                                        <!-- Password Update Form -->
                                        <form method='post' action='' style='display:inline-block'>
                                            <input type='hidden' name='update_password' value='1'>
                                            <input type='hidden' name='id' value='{$row['id']}'>
                                            <input type='password' name='password' placeholder='New Password' class='form-control' required>
                                            <input type='submit' value='Update Password' class='btn btn-warning mt-2'>
                                        </form>";
                                }

                                // Show delete user button only for admin and managers who are not trying to delete their own account
                                if ($_SESSION['role'] == 'admin' || ($_SESSION['role'] == 'manager' && $row['role'] != 'admin')) {
                                    echo "
                                        <!-- Delete User -->
                                        <a href='admin.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure?\");' class='btn btn-danger mt-2'>Delete</a>";
                                }

                                echo "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script>
    $(document).ready(function() {
        $('#userTable').DataTable({
            "responsive": true, // Enable responsive extension
            "searching": true,  // Enable search functionality
            "paging": true,     // Enable pagination
            "ordering": true,   // Enable column ordering
        });
    });
</script>

<?php include './partials/layouts/layoutBottom.php' ?>
