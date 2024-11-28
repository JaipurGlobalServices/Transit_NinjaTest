<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<?php include './partials/head.php' ?>

<body>
    <?php
    session_start();
    include 'config.php';

    if (!isset($_SESSION['email'])) {
        header("Location: sign-in.php");
        exit();
    }

    // Fetch user data from the database
    $email = $_SESSION['email'];
    $sql = "SELECT username, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
    } else {
        // Redirect to logout.php if user data is not found 
        header("Location: logout.php");
        exit();
    }

    ?>
    <?php include './partials/sidebar.php' ?>

    <main class="dashboard-main">
        <?php include './partials/navbar.php' ?>

        <div class="dashboard-main-body">

            <?php include './partials/breadcrumb.php' ?>