<?php
$title = 'Dashboard';
$version = 'v1.1'; // Version number here
$subTitle = 'Transit Ninja ' . $version;
include './partials/layouts/layoutTop.php';

// Fetch data from the database
function fetchData($conn, $query)
{
    $result = $conn->query($query);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : "No data found.";
}

// Fetch multiple rows data
function fetchAllData($conn, $query)
{
    $result = $conn->query($query);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Total Sellers
$totalSellersData = fetchData($conn, "SELECT COUNT(DISTINCT seller_name) as total_sellers FROM shipment_tracking_info");
$totalSellers = is_array($totalSellersData) ? $totalSellersData['total_sellers'] : $totalSellersData;

// Total In-Transit Status
$totalInTransitData = fetchData($conn, "SELECT COUNT(status) as total_in_transit FROM shipment_tracking_info WHERE status = 'IN-TRANSIT'");
$totalInTransit = is_array($totalInTransitData) ? $totalInTransitData['total_in_transit'] : $totalInTransitData;

// Total In-Transit for Return Status
$totalInTransitReturnData = fetchData($conn, "SELECT COUNT(status) as total_in_transit_return FROM shipment_tracking_info WHERE status = 'IN TRANSIT FOR RETURN'");
$totalInTransitReturn = is_array($totalInTransitReturnData) ? $totalInTransitReturnData['total_in_transit_return'] : $totalInTransitReturnData;

// Total Delivered Status
$totalDeliveredData = fetchData($conn, "SELECT COUNT(status) as total_delivered FROM delivered_info");
$totalDelivered = is_array($totalDeliveredData) ? $totalDeliveredData['total_delivered'] : $totalDeliveredData;

// Total Tracking IDs
$totalTrackingIdsData = fetchData($conn, "SELECT COUNT(tracking_id) as total_tracking_ids FROM shipment_tracking_info");
$totalTrackingIds = is_array($totalTrackingIdsData) ? $totalTrackingIdsData['total_tracking_ids'] : $totalTrackingIdsData;

// Fetch data for all statuses
$statusCounts = [];
$statusData = fetchAllData($conn, "SELECT status, COUNT(*) as count FROM shipment_tracking_info GROUP BY status");
foreach ($statusData as $status) {
    $statusCounts[$status['status']] = $status['count'];
}

// Fetch top sellers
$topSellers = fetchAllData($conn, "SELECT seller_name, COUNT(*) as total_orders FROM shipment_tracking_info GROUP BY seller_name ORDER BY total_orders DESC LIMIT 5");

// Fetch top shipment partners
$topShipmentPartners = fetchAllData($conn, "SELECT shipment_partner, COUNT(*) as total_orders FROM shipment_tracking_info GROUP BY shipment_partner ORDER BY total_orders DESC LIMIT 5");
?>

<h1>Welcome, <?php echo ucwords(strtolower($_SESSION['username'])); ?>!</h1>

<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <!-- Total Sellers Card -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-33">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Sellers</p>
                        <h6 class="mb-0"><?php echo $totalSellers; ?></h6>
                    </div>
                    <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="gridicons:multiple-users" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div><!-- card end -->
    </div>

    <!-- Total In-Transit Status Card -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-33">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total In-Transit Status</p>
                        <h6 class="mb-0"><?php echo $totalInTransit; ?></h6>
                    </div>
                    <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="fa-solid:award" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div><!-- card end -->
    </div>

    <!-- Total Tracking IDs Card -->
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-33">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Tracking IDs</p>
                        <h6 class="mb-0"><?php echo $totalTrackingIds; ?></h6>
                    </div>
                    <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="fluent:people-20-filled" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
            </div>
        </div><!-- card end -->
    </div>
</div>

<div class="row gy-4 mt-1">
    <div class="col-12 col-md-12">
        <div class="card h-100 radius-8 border-0 overflow-hidden">
            <div class="card-body p-24">
                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                    <h6 class="mb-2 fw-bold text-lg">Status Overview</h6>
                </div>
                <div id="userOverviewDonutChart"></div>
                <ul class="d-flex flex-wrap align-items-center justify-content-between mt-3 gap-3">
                    <li class="d-flex align-items-center gap-2">
                        <span class="w-12-px h-12-px radius-2 bg-purple"></span>
                        <span class="text-secondary-light text-sm fw-normal">Delivered:
                            <span class="text-primary-light fw-semibold"><?php echo $totalDelivered; ?></span>
                        </span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <span class="w-12-px h-12-px radius-2 bg-red"></span>
                        <span class="text-secondary-light text-sm fw-normal">Returned:
                            <span class="text-primary-light fw-semibold"><?php echo $totalInTransitReturn; ?></span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Top Seller Card -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                    <h6 class="mb-2 fw-bold text-lg mb-0">Top 5 Seller Name</h6>
                </div>
                <div class="mt-32">
                    <?php foreach ($topSellers as $seller): ?>
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-24">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($seller['seller_name']); ?></h6>
                                    <span class="text-sm text-secondary-light fw-medium">Total Orders: <?= htmlspecialchars($seller['total_orders']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Shipment Partner Card -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
                    <h6 class="mb-2 fw-bold text-lg mb-0">Top Shipment Partners</h6>
                </div>
                <div class="mt-32">
                    <?php foreach ($topShipmentPartners as $partner): ?>
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-24">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($partner['shipment_partner']); ?></h6>
                                    <span class="text-sm text-secondary-light fw-medium">Total Orders: <?= htmlspecialchars($partner['total_orders']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$script = '<script src="assets/js/homeOneChart.js"></script>';
$chartData = json_encode(array_values($statusCounts));
$chartLabels = json_encode(array_keys($statusCounts));
echo "<script>var chartData = $chartData; var chartLabels = $chartLabels;</script>";
include './partials/layouts/layoutBottom.php';
?>