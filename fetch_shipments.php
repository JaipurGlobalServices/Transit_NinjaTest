<?php
session_start();
include 'config.php'; // Your database configuration

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch the request data
$request = $_REQUEST;
$columns = [
    'order_id', 'order_item_id', 'purchase_date', 'payments_date', 'buyer_email',
    'buyer_name', 'buyer_phone_number', 'sku', 'product_name', 'quantity_purchased',
    'currency', 'item_price', 'item_tax', 'shipping_price', 'shipping_tax',
    'ship_service_level', 'recipient_name', 'ship_address_1', 'ship_address_2',
    'ship_address_3', 'ship_city', 'ship_state', 'ship_postal_code',
    'ship_country', 'ship_phone_number', 'delivery_start_date', 'delivery_end_date',
    'delivery_time_zone', 'delivery_instructions', 'payment_method',
    'cod_collectible_amount', 'already_paid', 'payment_method_fee',
    'is_business_order', 'purchase_order_number', 'price_designation',
    'is_prime', 'fulfilled_by', 'is_iba'
];

// Base query to get total records
$totalQuery = "SELECT COUNT(*) FROM shipment_tracking";
$totalResult = $conn->query($totalQuery);
$totalData = $totalResult->fetch_row()[0];

// Search functionality
$searchValue = $conn->real_escape_string($request['search']['value']);
$searchQuery = "SELECT * FROM shipment_tracking WHERE 1=1";

if (!empty($searchValue)) {
    $searchQuery .= " AND (order_id LIKE '%$searchValue%' OR order_item_id LIKE '%$searchValue%' OR buyer_email LIKE '%$searchValue%' OR buyer_name LIKE '%$searchValue%' OR sku LIKE '%$searchValue%' OR product_name LIKE '%$searchValue%')";
}

$filteredResult = $conn->query($searchQuery);
$totalFiltered = $filteredResult->num_rows;

// Ordering functionality
$orderColumn = $columns[$request['order'][0]['column']] ?? 'order_id';
$orderDir = $request['order'][0]['dir'] ?? 'DESC';
$searchQuery .= " ORDER BY $orderColumn $orderDir";

// Pagination functionality
$limit = (int)$request['length'];
$offset = (int)$request['start'];
$searchQuery .= " LIMIT $limit OFFSET $offset";

$query = $conn->query($searchQuery);
if (!$query) {
    echo json_encode(["error" => $conn->error]);
    exit;
}

$data = [];
while ($row = $query->fetch_assoc()) {
    $nestedData = [];
    foreach ($columns as $column) {
        $nestedData[] = $row[$column];
    }
    $data[] = $nestedData;
}

// Prepare the response
$response = [
    "draw" => intval($request['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
];

// Validate JSON
$jsonResponse = json_encode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["json_error" => json_last_error_msg()]);
    exit;
}

// Output JSON response
header('Content-Type: application/json');
echo $jsonResponse;
exit;
