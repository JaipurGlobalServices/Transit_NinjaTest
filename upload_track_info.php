<?php
include 'config.php'; // Your database configuration
require 'vendor/autoload.php'; // Include the necessary library for Excel processing

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function columnLetter($columnNumber)
{
    $dividend = $columnNumber + 1;  // Adjust for 1-based indexing
    $columnName = '';
    while ($dividend > 0) {
        $modulo = ($dividend - 1) % 26;
        $columnName = chr(65 + $modulo) . $columnName;
        $dividend = (int)(($dividend - $modulo) / 26);
    }
    return $columnName;
}

function formatDate($cell)
{
    if (Date::isDateTime($cell)) {
        $phpDate = Date::excelToDateTimeObject($cell->getValue());
        return $phpDate->format('Y-m-d');
    }
    return null;
}

$message = ""; // Initialize the message variable
$showViewDataButton = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check file extension
    if ($fileType != "xls" && $fileType != "xlsx") {
        $message = "<div class='alert alert-danger'>Sorry, only XLS and XLSX files are allowed.</div>";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message = "<div class='alert alert-success'>The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.</div>";
            $showViewDataButton = true;

            // Process the uploaded file and insert data into the database
            $spreadsheet = IOFactory::load($target_file);
            $worksheet = $spreadsheet->getActiveSheet();

            // Get the header row
            $headerRow = $worksheet->toArray()[0];
            $headerIndex = array_flip(array_filter($headerRow, 'is_scalar'));

            // Check for required headers
            $requiredHeaders = ['tracking_id', 'shipment_partner', 'status', 'seller_name', 'contact_number', 'seller_mail', 'buyer_mail', 'purchase_date', 'delivery_date', 'order_id'];
            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $headerIndex)) {
                    $message = "<div class='alert alert-danger'>Please check the header of the Excel sheet. Missing column: $header</div>";
                    unlink($target_file); // Remove the uploaded file
                    $title = 'Upload Tracking Info';
                    $subTitle = 'Upload Tracking Info';
                    include './partials/layouts/layoutTop.php';
                    echo '<div class="row">
                              <div class="col-12">
                                  <div class="card">
                                      <div class="card-body">
                                          <h2>Upload Tracking Info</h2> 
                                          ' . $message . '
                                          <form action="upload_track_info.php" method="post" enctype="multipart/form-data">
                                              <div class="form-group">
                                                  <label for="fileToUpload">Select file to upload:</label>
                                                  <input type="file" name="fileToUpload" id="fileToUpload" class="form-control-file">
                                              </div>
                                              <button type="submit" class="btn btn-primary">Upload</button>
                                          </form>
                                      </div>
                                  </div>
                              </div>
                          </div>';
                    include './partials/layouts/layoutBottom.php';
                    exit;
                }
            }

            $highestRow = $worksheet->getHighestRow(); // Get the highest row number

            for ($row = 2; $row <= $highestRow; $row++) {
                $trackingId = $worksheet->getCell(columnLetter($headerIndex['tracking_id']) . $row)->getValue();
                $shipmentPartner = $worksheet->getCell(columnLetter($headerIndex['shipment_partner']) . $row)->getValue();
                $status = $worksheet->getCell(columnLetter($headerIndex['status']) . $row)->getValue();
                $sellerName = $worksheet->getCell(columnLetter($headerIndex['seller_name']) . $row)->getValue();
                $contactNumber = $worksheet->getCell(columnLetter($headerIndex['contact_number']) . $row)->getValue();
                $sellerMail = $worksheet->getCell(columnLetter($headerIndex['seller_mail']) . $row)->getValue();
                $buyerMail = $worksheet->getCell(columnLetter($headerIndex['buyer_mail']) . $row)->getValue();
                $purchaseDateCell = $worksheet->getCell(columnLetter($headerIndex['purchase_date']) . $row);
                $deliveryDateCell = $worksheet->getCell(columnLetter($headerIndex['delivery_date']) . $row);
                $purchaseDate = formatDate($purchaseDateCell); // Correctly format the date
                $deliveryDate = formatDate($deliveryDateCell); // Correctly format the date
                $orderId = $worksheet->getCell(columnLetter($headerIndex['order_id']) . $row)->getValue();

                // Validate required fields 
                if (empty($trackingId) || empty($shipmentPartner) || empty($sellerName) || empty($contactNumber) || empty($purchaseDate) || empty($orderId)) {
                    $message = "<div class='alert alert-danger'>One or more required fields are empty in row $row. Please check the Excel file and try again.</div>";
                    unlink($target_file); // Remove the uploaded file 
                    $title = 'Upload Tracking Info';
                    $subTitle = 'Upload Tracking Info';
                    include './partials/layouts/layoutTop.php';
                    echo '<div class="row"> <div class="col-12"> <div class="card"> <div class="card-body">' . $message . ' <form action="upload_track_info.php" method="post" enctype="multipart/form-data"> <div class="form-group"> <label for="fileToUpload">Select file to upload:</label> <input type="file" name="fileToUpload" id="fileToUpload" class="form-control-file"> </div> <button type="submit" class="btn btn-primary">Upload</button> </form> </div> </div> </div> </div>';
                    include './partials/layouts/layoutBottom.php';
                    exit;
                }

                // Handle blank status
                if (empty($status)) {
                    $status = 'No Status';
                }

                // Insert data into the database
                $sql = "INSERT INTO shipment_tracking_info (tracking_id, shipment_partner, status, seller_name, contact_number, seller_mail, buyer_mail, purchase_date, delivery_date, order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssss", $trackingId, $shipmentPartner, $status, $sellerName, $contactNumber, $sellerMail, $buyerMail, $purchaseDate, $deliveryDate, $orderId);
                $stmt->execute();
            }

            // Trigger the Python script
            $command = escapeshellcmd("python3 tracking.py");
            $output = shell_exec($command);
            echo "<pre>$output</pre>";
        } else {
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
        }
    }
}

$title = 'Upload Tracking Info';
$subTitle = 'Upload Tracking Info';
include './partials/layouts/layoutTop.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php
                if (!empty($message)) {
                    echo $message; // Display the message
                }
                ?>
                <form action="upload_track_info.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="fileToUpload">Select file to upload (Format: XLS, XLSX):</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <?php
                        if (isset($showViewDataButton) && $showViewDataButton === true) {
                            echo '<a href="track_info.php" class="btn btn-success">View Data</a>';
                        }
                        ?>
                        <a href="upload_tracking_info_sample_data.xlsx" class="btn btn-info">Sample File Format</a> <!-- Button to download sample file -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include './partials/layouts/layoutBottom.php';
?>