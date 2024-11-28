<?php
$title = 'Track Info';
$subTitle = 'Track Info';
include 'config.php'; // Your database configuration
include './partials/layouts/layoutTop.php'; // Fetch unique filter values from the database

// Fetch filter values from GET request
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$shipmentPartnerFilter = isset($_GET['shipment_partner']) ? $_GET['shipment_partner'] : '';
$sellerNameFilter = isset($_GET['seller_name']) ? $_GET['seller_name'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$startDateFilter = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDateFilter = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Fetch sorting options
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'order_id';
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'desc' : 'asc';

// Function to fetch unique filter values from the database
function getUniqueValues($conn, $column)
{
    $sql = "SELECT DISTINCT $column FROM shipment_tracking_info";
    $result = $conn->query($sql);
    $values = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
    }
    return $values;
}

$statusOptions = getUniqueValues($conn, 'status');
$shipmentPartners = getUniqueValues($conn, 'shipment_partner');
$sellerNames = getUniqueValues($conn, 'seller_name');

// Pagination variables
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$startEntry = ($currentPage - 1) * $entriesPerPage;

$totalQuery = "SELECT COUNT(*) FROM shipment_tracking_info";
$totalResult = $conn->query($totalQuery);
$totalData = $totalResult->fetch_row()[0];
$totalPages = ceil($totalData / 10); // Total pages

// Start building SQL query with filters and search term
$sql = "SELECT order_id, tracking_id, shipment_partner, status, seller_name, contact_number, seller_mail, purchase_date, delivery_date, buyer_mail FROM shipment_tracking_info WHERE 1=1";

// Apply filters
if ($statusFilter) {
    $sql .= " AND status LIKE '%" . $conn->real_escape_string($statusFilter) . "%'";
}
if ($shipmentPartnerFilter) {
    $sql .= " AND shipment_partner LIKE '%" . $conn->real_escape_string($shipmentPartnerFilter) . "%'";
}
if ($sellerNameFilter) {
    $sql .= " AND seller_name LIKE '%" . $conn->real_escape_string($sellerNameFilter) . "%'";
}
if ($searchTerm) {
    $sql .= " AND (tracking_id LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
                OR shipment_partner LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
                OR status LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
                OR seller_name LIKE '%" . $conn->real_escape_string($searchTerm) . "%')";
}
if ($startDateFilter && $endDateFilter) {
    $sql .= " AND purchase_date BETWEEN '" . $conn->real_escape_string($startDateFilter) . "' AND '" . $conn->real_escape_string($endDateFilter) . "'";
} elseif ($startDateFilter) {
    $sql .= " AND purchase_date >= '" . $conn->real_escape_string($startDateFilter) . "'";
} elseif ($endDateFilter) {
    $sql .= " AND purchase_date <= '" . $conn->real_escape_string($endDateFilter) . "'";
}

// Apply sorting
$sql .= " ORDER BY $sort $order";

// Add pagination to the query
$sql .= " LIMIT $startEntry, $entriesPerPage";

$result = $conn->query($sql);

// Fetch the total number of rows for pagination calculation
$totalRowsSql = "SELECT COUNT(*) as total FROM shipment_tracking_info WHERE 1=1";
if ($statusFilter) {
    $totalRowsSql .= " AND status LIKE '%" . $conn->real_escape_string($statusFilter) . "%'";
}
if ($shipmentPartnerFilter) {
    $totalRowsSql .= " AND shipment_partner LIKE '%" . $conn->real_escape_string($shipmentPartnerFilter) . "%'";
}
if ($sellerNameFilter) {
    $totalRowsSql .= " AND seller_name LIKE '%" . $conn->real_escape_string($sellerNameFilter) . "%'";
}
if ($searchTerm) {
    $totalRowsSql .= " AND (tracking_id LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
                         OR shipment_partner LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
                         OR status LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
                         OR seller_name LIKE '%" . $conn->real_escape_string($searchTerm) . "%')";
}
if ($startDateFilter && $endDateFilter) {
    $totalRowsSql .= " AND purchase_date BETWEEN '" . $conn->real_escape_string($startDateFilter) . "' AND '" . $conn->real_escape_string($endDateFilter) . "'";
} elseif ($startDateFilter) {
    $totalRowsSql .= " AND purchase_date >= '" . $conn->real_escape_string($startDateFilter) . "'";
} elseif ($endDateFilter) {
    $totalRowsSql .= " AND purchase_date <= '" . $conn->real_escape_string($endDateFilter) . "'";
}

$totalRowsResult = $conn->query($totalRowsSql);
$totalRows = $totalRowsResult->fetch_assoc()['total'];

// Handling deletion
if (isset($_POST['delete_delivered'])) {
    // Delete records with status 'delivered' 
    $deleteQuery = "DELETE FROM shipment_tracking_info WHERE status = 'delivered'";
    if ($conn->query($deleteQuery) === TRUE) {
        $message = "Delivered records deleted successfully. Please Refresh Page.";
    } else {
        $message = "Error deleting delivered records: " . $conn->error;
    }
}
?>
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="icon-field position-relative">
                <form method="GET" action="" class="d-inline">
                    <input type="text" id="searchInput" name="search" class="form-control form-control-sm w-auto" placeholder="Search" value="<?= htmlspecialchars($searchTerm) ?>" onkeyup="applyFilters()">
                    <span class="icon position-absolute end-0 top-50 translate-middle-y">
                        <iconify-icon icon="ion:search-outline"></iconify-icon>
                    </span>
                </form>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="statusFilter" class="me-2">Status</label>
                <select class="form-select form-select-sm w-auto" id="statusFilter" name="status" onchange="applyFilters()">
                    <option value="">All</option>
                    <?php foreach ($statusOptions as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>" <?= $status == $statusFilter ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="shipmentPartnerFilter" class="me-2">Shipment Partner</label>
                <select class="form-select form-select-sm w-auto" id="shipmentPartnerFilter" name="shipment_partner" onchange="applyFilters()">
                    <option value="">All</option>
                    <?php foreach ($shipmentPartners as $partner): ?>
                        <option value="<?= htmlspecialchars($partner) ?>" <?= $partner == $shipmentPartnerFilter ? 'selected' : '' ?>><?= htmlspecialchars($partner) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="sellerNameFilter" class="me-2">Seller Name</label>
                <select class="form-select form-select-sm w-auto" id="sellerNameFilter" name="seller_name" onchange="applyFilters()">
                    <option value="">All</option>
                    <?php foreach ($sellerNames as $seller): ?>
                        <option value="<?= htmlspecialchars($seller) ?>" <?= $seller == $sellerNameFilter ? 'selected' : '' ?>><?= htmlspecialchars($seller) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <form method="get" action="export.php" class="d-inline">
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                <input type="hidden" name="shipment_partner" value="<?= htmlspecialchars($shipmentPartnerFilter) ?>">
                <input type="hidden" name="seller_name" value="<?= htmlspecialchars($sellerNameFilter) ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" name="export" class="btn btn-sm btn-success">Export to Excel</button>
            </form>
            <style>
                /* Spinner CSS */
                .spinner {
                    border: 4px solid rgba(0, 0, 0, 0.1);
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    border-left-color: #09f;
                    animation: spin 1s ease infinite;
                    display: none;
                    margin: 20px auto;
                }

                @keyframes spin {
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
                }
            </style>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script>
                function runScript() {
                    // Hide the text and show the spinner
                    document.getElementById('runText').style.display = 'none';
                    document.getElementById('spinner').style.display = 'inline-block';

                    // Make the AJAX request
                    $.ajax({
                        type: "POST",
                        url: "run_script.php",
                        success: function(data) {
                            // Hide the spinner and show the text again
                            document.getElementById('spinner').style.display = 'none';
                            document.getElementById('runText').style.display = 'inline';
                            // Display the script output
                            document.getElementById('output').innerHTML = data;
                        },
                        error: function(xhr, status, error) {
                            // Hide the spinner and show the text again
                            document.getElementById('spinner').style.display = 'none';
                            document.getElementById('runText').style.display = 'inline';
                            // Display an error message
                            alert("Error: " + error);
                        }
                    });
                }
            </script>

            <!-- Button with text and Spinner -->
            <button onclick="runScript()" class="btn btn-sm btn-success" style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 200px; height: 40px; text-align: center; white-space: nowrap; border: 1px solid #28a745; border-radius: 0.25rem; color: #fff; cursor: pointer; transition: background-color 0.3s, border-color 0.3s;"> <span id="runText" style="display: inline;">Run Python Script</span>
                <div id="spinner" class="spinner" style="display: none; border: solid #f3f3f3; border-top: 2px solid #28a745; border-radius: 50%; width: 1rem; height: 1rem; animation: spin 1s linear infinite;"></div>
            </button>
            <pre id="output"></pre> <!-- Keyframes for spinner animation -->
            <style>
                @keyframes spin {
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
                }
            </style>
        </div>
    </div>
    <div class="card-body overflow-auto">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col"><a href="?sort=order_id&order=<?= $sort == 'order_id' && $order == 'asc' ? 'desc' : 'asc' ?>">Order ID</a></th>
                        <th scope="col"><a href="?sort=tracking_id&order=<?= $sort == 'tracking_id' && $order == 'asc' ? 'desc' : 'asc' ?>">Tracking ID</a></th>
                        <th scope="col"><a href="?sort=shipment_partner&order=<?= $sort == 'shipment_partner' && $order == 'asc' ? 'desc' : 'asc' ?>">Shipment Partner</a></th>
                        <th scope="col"><a href="?sort=status&order=<?= $sort == 'status' && $order == 'asc' ? 'desc' : 'asc' ?>">Status</a></th>
                        <th scope="col"><a href="?sort=seller_name&order=<?= $sort == 'seller_name' && $order == 'asc' ? 'desc' : 'asc' ?>">Seller Name</a></th>
                        <th scope="col">Seller Mail</th>
                        <th scope="col">Buyer Mail</th>
                        <th scope="col">Contact Number</th>
                        <th scope="col"><a href="?sort=purchase_date&order=<?= $sort == 'purchase_date' && $order == 'asc' ? 'desc' : 'asc' ?>">Purchase Date</a></th>
                        <th scope="col"><a href="?sort=delivery_date&order=<?= $sort == 'delivery_date' && $order == 'asc' ? 'desc' : 'asc' ?>">Delivery Date</a></th>
                    </tr>
                </thead>
                <tbody id="trackingInfoTable">
                    <?php if ($result->num_rows > 0) {
                        $i = $startEntry + 1;
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= $row['order_id'] ?></td>
                                <td><?= $row['tracking_id'] ?></td>
                                <td><?= $row['shipment_partner'] ?></td>
                                <td><?= $row['status'] ?></td>
                                <td><?= $row['seller_name'] ?></td>
                                <td><?= $row['seller_mail'] ?></td>
                                <td><?= $row['buyer_mail'] ?></td>
                                <td><?= $row['contact_number'] ?></td>
                                <td><?= $row['purchase_date'] ?></td>
                                <td><?= $row['delivery_date'] ?></td>
                            </tr>
                        <?php $i++;
                        }
                    } else { ?>
                        <tr>
                            <td colspan="11">No tracking information available</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-4">
            <span>Showing <?php
                            $startEntryDisplay = $startEntry + 1;
                            $endEntry = min($startEntry + $entriesPerPage, $totalRows);
                            echo "$startEntryDisplay to $endEntry of $totalRows"; ?> entries</span>
            <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                <!-- First Page Link -->
                <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                    <a class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px" href="?page=1&entries=<?= $entriesPerPage ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $shipmentPartnerFilter ? '&shipment_partner=' . urlencode($shipmentPartnerFilter) : '' ?><?= $sellerNameFilter ? '&seller_name=' . urlencode($sellerNameFilter) : '' ?>">First</a>
                </li>
                <!-- Previous Page Link -->
                <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                    <a class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px" href="?page=<?= $currentPage - 1 ?>&entries=<?= $entriesPerPage ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $shipmentPartnerFilter ? '&shipment_partner=' . urlencode($shipmentPartnerFilter) : '' ?><?= $sellerNameFilter ? '&seller_name=' . urlencode($sellerNameFilter) : '' ?>">Previous</a>
                </li>
                <?php if ($currentPage > 2): ?>
                    <li class="page-item"><span class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px">...</span></li>
                <?php endif; ?>
                <!-- Page Number Links -->
                <?php for ($i = max(1, $currentPage - 1); $i <= min($totalPages, $currentPage + 1); $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px" href="?page=<?= $i ?>&entries=<?= $entriesPerPage ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $shipmentPartnerFilter ? '&shipment_partner=' . urlencode($shipmentPartnerFilter) : '' ?><?= $sellerNameFilter ? '&seller_name=' . urlencode($sellerNameFilter) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages - 1): ?>
                    <li class="page-item"><span class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px">...</span></li>
                <?php endif; ?>
                <!-- Next Page Link -->
                <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px" href="?page=<?= $currentPage + 1 ?>&entries=<?= $entriesPerPage ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $shipmentPartnerFilter ? '&shipment_partner=' . urlencode($shipmentPartnerFilter) : '' ?><?= $sellerNameFilter ? '&seller_name=' . urlencode($sellerNameFilter) : '' ?>">Next</a>
                </li>
                <!-- Last Page Link -->
                <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link bg-primary-50 text-secondary-light fw-medium radius-8 border-0 px-20 py-10 d-flex align-items-center justify-content-center h-48-px" href="?page=<?= $totalPages ?>&entries=<?= $entriesPerPage ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $shipmentPartnerFilter ? '&shipment_partner=' . urlencode($shipmentPartnerFilter) : '' ?><?= $sellerNameFilter ? '&seller_name=' . urlencode($sellerNameFilter) : '' ?>">Last</a>
                </li>
            </ul>
        </div>

        <!-- Display message if set -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <button onclick="refreshPage()" class="btn btn-sm btn-info">Refresh Page</button>
        <?php endif; ?>
        <script>
            function refreshPage() {
                location.reload();
            }
        </script>
        <form method="post" action="">
            <button type="submit" name="delete_delivered" class="btn btn-sm btn-danger">Delete Delivered Shipments</button>
        </form>
    </div>

</div>
<script>
    function applyFilters() {
        let searchValue = document.getElementById('searchInput').value;
        let statusFilter = document.getElementById('statusFilter').value;
        let shipmentPartnerFilter = document.getElementById('shipmentPartnerFilter').value;
        let sellerNameFilter = document.getElementById('sellerNameFilter').value;
        let url = window.location.href.split('?')[0] + '?page=1&entries=10'; // Reset to page 1
        if (searchValue) url += '&search=' + encodeURIComponent(searchValue);
        if (statusFilter) url += '&status=' + statusFilter;
        if (shipmentPartnerFilter) url += '&shipment_partner=' + shipmentPartnerFilter;
        if (sellerNameFilter) url += '&seller_name=' + sellerNameFilter;
        window.location.href = url;
    }
</script>
<?php include './partials/layouts/layoutBottom.php'; ?>