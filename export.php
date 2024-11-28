<?php
include 'config.php'; // Your database configuration

// Fetch filter values from GET request, sanitize inputs
$statusFilter = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$shipmentPartnerFilter = isset($_GET['shipment_partner']) ? htmlspecialchars($_GET['shipment_partner']) : '';
$sellerNameFilter = isset($_GET['seller_name']) ? htmlspecialchars($_GET['seller_name']) : '';
$searchTerm = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Load the necessary library for creating Excel files
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add header row
$sheet->setCellValue('A1', 'Order ID');
$sheet->setCellValue('B1', 'Tracking ID');
$sheet->setCellValue('C1', 'Shipment Partner');
$sheet->setCellValue('D1', 'Status');
$sheet->setCellValue('E1', 'Seller Name');
$sheet->setCellValue('F1', 'Seller Mail');
$sheet->setCellValue('G1', 'Buyer Mail');
$sheet->setCellValue('H1', 'Contact Number');
$sheet->setCellValue('I1', 'Purchase Date');
$sheet->setCellValue('J1', 'Delivery Date');

// Function to fetch data with filters
function fetchTrackingData($conn, $statusFilter, $shipmentPartnerFilter, $sellerNameFilter, $searchTerm) {
    // Start building SQL query with filters
    $sql = "SELECT tracking_id, shipment_partner, status, seller_name, contact_number, seller_mail, buyer_mail, purchase_date, delivery_date, order_id FROM shipment_tracking_info WHERE 1=1";
    $conditions = [];
    $parameters = [];

    // Apply filters
    if ($statusFilter) {
        $conditions[] = "status LIKE ?";
        $parameters[] = '%' . $statusFilter . '%';
    }

    if ($shipmentPartnerFilter) {
        $conditions[] = "shipment_partner LIKE ?";
        $parameters[] = '%' . $shipmentPartnerFilter . '%';
    }

    if ($sellerNameFilter) {
        $conditions[] = "seller_name LIKE ?";
        $parameters[] = '%' . $sellerNameFilter . '%';
    }

    if ($searchTerm) {
        $conditions[] = "(tracking_id LIKE ? OR shipment_partner LIKE ? OR status LIKE ? OR seller_name LIKE ?)";
        $parameters[] = '%' . $searchTerm . '%';
        $parameters[] = '%' . $searchTerm . '%';
        $parameters[] = '%' . $searchTerm . '%';
        $parameters[] = '%' . $searchTerm . '%';
    }

    // Append conditions to SQL query
    if (count($conditions) > 0) {
        $sql .= ' AND ' . implode(' AND ', $conditions);
    }

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        if (!empty($parameters)) {
            $types = str_repeat('s', count($parameters)); // All parameters are strings
            $stmt->bind_param($types, ...$parameters);
        }

        $stmt->execute();
        return $stmt->get_result(); // Return the result
    } else {
        throw new Exception("Database query failed: " . $conn->error);
    }
}

// Fetch data with filters
try {
    $result = fetchTrackingData($conn, $statusFilter, $shipmentPartnerFilter, $sellerNameFilter, $searchTerm);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Add data rows to the spreadsheet
$rowIndex = 2;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowIndex, $row['order_id']);
        $sheet->setCellValue('B' . $rowIndex, $row['tracking_id']);
        $sheet->setCellValue('C' . $rowIndex, $row['shipment_partner']);
        $sheet->setCellValue('D' . $rowIndex, $row['status']);
        $sheet->setCellValue('E' . $rowIndex, $row['seller_name']);
        $sheet->setCellValue('F' . $rowIndex, $row['seller_mail']);
        $sheet->setCellValue('G' . $rowIndex, $row['buyer_mail']);
        $sheet->setCellValue('H' . $rowIndex, $row['contact_number']);
        $sheet->setCellValue('I' . $rowIndex, $row['purchase_date']);
        $sheet->setCellValue('J' . $rowIndex, $row['delivery_date']);
        $rowIndex++;
    }
}

// Set Excel sheet formatting
$sheet->getStyle('A1:J1')->getFont()->setBold(true);
$sheet->getStyle('A1:J1')->getAlignment()->setHorizontal('center');
foreach (range('A', 'J') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Generate a custom file name (e.g., with the current date)
$fileName = "shipment_tracking_info_" . date('Y-m-d_H-i-s') . ".xlsx"; // Dynamic name with current date-time

// Set headers to inform the browser to download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1'); // If you're serving to IE over SSL

// Clean the output buffer before sending the file
ob_clean();

// Write file to the browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Close the database connection and end the script
$conn->close();
exit;
?>
