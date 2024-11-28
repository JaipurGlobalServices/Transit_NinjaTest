<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page (e.g., 'track_info.php')
// Check if 'QUERY_STRING' exists and is not empty
$current_page_query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''; // Get the query string (e.g., 'page=2&entries=10')

function isActive($page)
{
    global $current_page, $current_page_query;

    // Check if the page matches the current page or if the query string contains the page name
    if ($current_page == $page || strpos($current_page_query, $page) !== false) {
        return true;
    }
    return false;
}
?>
<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="index.php" class="sidebar-logo">
            <img src="assets/images/logo.png" alt="site logo" class="light-logo">
            <img src="assets/images/logo-light.png" alt="site logo" class="dark-logo">
            <img src="assets/images/logo-icon.png" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <!-- Dashboard Menu -->
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li class="<?= isActive('index.php') ? 'active active-page' : '' ?>">
                        <a href="index.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Transit Ninja</a>
                    </li>
                </ul>
            </li>

            <!-- Shipping Management Menu -->
            <li class="dropdown <?= (isActive('upload_track_info.php') || isActive('track_info.php') || isActive('delivered_track_info.php')) ? 'open' : '' ?>"> <!-- Keep open if any child is active -->
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                    <span>Shipping Management</span>
                </a>
                <ul class="sidebar-submenu <?= (isActive('upload_track_info.php') || isActive('track_info.php') || isActive('delivered_track_info.php')) ? 'show' : '' ?>"> <!-- Show submenu if any child is active -->
                    <li class="<?= isActive('upload_track_info.php') ? 'active' : '' ?>">
                        <a href="upload_track_info.php"><i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Upload Track Shipments Info</a>
                    </li>
                    <li class="<?= isActive('track_info.php') ? 'active active-page' : '' ?>"> <!-- Apply 'active-page' when on 'track_info.php' -->
                        <a href="track_info.php"><i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Track Shipments Info</a>
                    </li>
                    <li class="<?= isActive('delivered_track_info.php') ? 'active active-page' : '' ?>">
                        <a href="delivered_track_info.php"><i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Delivered Track Shipments Info</a>
                    </li>
                </ul>
            </li>

            <!-- User Management Menu (only for admin and manager roles) -->
            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager') : ?>
                <li class="dropdown <?= (isActive('edit_user.php') || isActive('add_user.php')) ? 'open' : '' ?>" >
                    <a href="javascript:void(0)">
                        <iconify-icon icon="simple-line-icons:vector" class="menu-icon"></iconify-icon>
                        <span>User Management</span>
                    </a>
                    <ul class="sidebar-submenu <?= (isActive('edit_user.php') || isActive('add_user.php')) ? 'open' : '' ?>">
                        <li class="<?= isActive('edit_user.php') ? 'active active-page' : '' ?>">
                            <a href="edit_user.php"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Edit User</a>
                        </li>
                        <li class="<?= isActive('add_user.php') ? 'active active-page' : '' ?>">
                            <a href="add_user.php"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Add User</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</aside>
