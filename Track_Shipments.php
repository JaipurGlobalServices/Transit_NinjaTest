<?php
// session_start();
include 'config.php'; // Your database configuration

// Check if the user is logged in
// if (!isset($_SESSION['email'])) {
//     header("Location: sign-in.php");
//     exit();
// }

// Pagination setup
$limit = 30; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for the SQL query

// Fetch total records count
$totalQuery = "SELECT COUNT(*) FROM shipment_tracking";
$totalResult = $conn->query($totalQuery);
$totalData = $totalResult->fetch_row()[0];
$totalPages = ceil($totalData / $limit); // Total pages

// Fetch records for current page
$sql = "SELECT * FROM shipment_tracking LIMIT $limit OFFSET $offset";
$query = $conn->query($sql);
if (!$query) {
    die("Database query failed: " . $conn->error);
}

// Prepare data for the table
$shipments = [];
while ($row = $query->fetch_assoc()) {
    $shipments[] = $row;
}

// HTML part for the tracking page
$title = 'Track Shipments';
$subTitle = 'Admin Panel';
include './partials/layouts/layoutTop.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="shipmentTable" style="border-spacing: 0 10px;">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Order Item ID</th>
                                <th>Purchase Date</th>
                                <th>Payments Date</th>
                                <th>Buyer Email</th>
                                <th>Buyer Name</th>
                                <th>Buyer Phone Number</th>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Quantity Purchased</th>
                                <th>Currency</th>
                                <th>Item Price</th>
                                <th>Item Tax</th>
                                <th>Shipping Price</th>
                                <th>Shipping Tax</th>
                                <th>Ship Service Level</th>
                                <th>Recipient Name</th>
                                <th>Ship Address 1</th>
                                <th>Ship Address 2</th>
                                <th>Ship Address 3</th>
                                <th>Ship City</th>
                                <th>Ship State</th>
                                <th>Ship Postal Code</th>
                                <th>Ship Country</th>
                                <th>Ship Phone Number</th>
                                <th>Delivery Start Date</th>
                                <th>Delivery End Date</th>
                                <th>Delivery Time Zone</th>
                                <th>Delivery Instructions</th>
                                <th>Payment Method</th>
                                <th>COD Collectible Amount</th>
                                <th>Already Paid</th>
                                <th>Payment Method Fee</th>
                                <th>Is Business Order</th>
                                <th>Purchase Order Number</th>
                                <th>Price Designation</th>
                                <th>Is Prime</th>
                                <th>Fulfilled By</th>
                                <th>Is IBA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shipments as $shipment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($shipment['order_id']) ?></td>
                                    <td><?= htmlspecialchars($shipment['order_item_id']) ?></td>
                                    <td><?= htmlspecialchars($shipment['purchase_date']) ?></td>
                                    <td><?= htmlspecialchars($shipment['payments_date']) ?></td>
                                    <td><?= htmlspecialchars($shipment['buyer_email']) ?></td>
                                    <td><?= htmlspecialchars($shipment['buyer_name']) ?></td>
                                    <td><?= htmlspecialchars($shipment['buyer_phone_number']) ?></td>
                                    <td><?= htmlspecialchars($shipment['sku']) ?></td>
                                    <td><?= htmlspecialchars($shipment['product_name']) ?></td>
                                    <td><?= htmlspecialchars($shipment['quantity_purchased']) ?></td>
                                    <td><?= htmlspecialchars($shipment['currency']) ?></td>
                                    <td><?= htmlspecialchars($shipment['item_price']) ?></td>
                                    <td><?= htmlspecialchars($shipment['item_tax']) ?></td>
                                    <td><?= htmlspecialchars($shipment['shipping_price']) ?></td>
                                    <td><?= htmlspecialchars($shipment['shipping_tax']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_service_level']) ?></td>
                                    <td><?= htmlspecialchars($shipment['recipient_name']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_address_1']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_address_2']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_address_3']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_city']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_state']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_postal_code']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_country']) ?></td>
                                    <td><?= htmlspecialchars($shipment['ship_phone_number']) ?></td>
                                    <td><?= htmlspecialchars($shipment['delivery_start_date']) ?></td>
                                    <td><?= htmlspecialchars($shipment['delivery_end_date']) ?></td>
                                    <td><?= htmlspecialchars($shipment['delivery_time_zone']) ?></td>
                                    <td><?= htmlspecialchars($shipment['delivery_instructions']) ?></td>
                                    <td><?= htmlspecialchars($shipment['payment_method']) ?></td>
                                    <td><?= htmlspecialchars($shipment['cod_collectible_amount']) ?></td>
                                    <td><?= htmlspecialchars($shipment['already_paid']) ?></td>
                                    <td><?= htmlspecialchars($shipment['payment_method_fee']) ?></td>
                                    <td><?= htmlspecialchars($shipment['is_business_order']) ?></td>
                                    <td><?= htmlspecialchars($shipment['purchase_order_number']) ?></td>
                                    <td><?= htmlspecialchars($shipment['price_designation']) ?></td>
                                    <td><?= htmlspecialchars($shipment['is_prime']) ?></td>
                                    <td><?= htmlspecialchars($shipment['fulfilled_by']) ?></td>
                                    <td><?= htmlspecialchars($shipment['is_iba']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Improved Pagination Links -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=1">First</a>
                        </li>
                        <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>
                        
                        <?php if ($page > 2): ?>
                            <li class="page-item"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages - 1): ?>
                            <li class="page-item"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        
                        <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                        <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $totalPages ?>">Last</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#shipmentTable').DataTable({
            "responsive": true,
            "searching": true,
            "paging": false, // Disable DataTables paging
            "ordering": true,
        });
    });
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
