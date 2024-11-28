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
            $requiredHeaders = ['review_id', 'customer_name', 'product_name', 'review_text', 'rating', 'review_date'];
            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $headerIndex)) {
                    $message = "<div class='alert alert-danger'>Please check the header of the Excel sheet. Missing column: $header</div>";
                    unlink($target_file); // Remove the uploaded file
                    $title = 'Upload Review Track Sheet';
                    $subTitle = 'Upload Review Track Sheet';
                    include './partials/layouts/layoutTop.php';
                    echo '<div class="row">
                              <div class="col-12">
                                  <div class="card">
                                      <div class="card-body">
                                          <h2>Upload Review Track Sheet</h2> 
                                          ' . $message . '
                                          <form action="upload_review_track_sheet.php" method="post" enctype="multipart/form-data">
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
                $reviewId = $worksheet->getCell(columnLetter($headerIndex['review_id']) . $row)->getValue();
                $customerName = $worksheet->getCell(columnLetter($headerIndex['customer_name']) . $row)->getValue();
                $productName = $worksheet->getCell(columnLetter($headerIndex['product_name']) . $row)->getValue();
                $reviewText = $worksheet->getCell(columnLetter($headerIndex['review_text']) . $row)->getValue();
                $rating = $worksheet->getCell(columnLetter($headerIndex['rating']) . $row)->getValue();
                $reviewDateCell = $worksheet->getCell(columnLetter($headerIndex['review_date']) . $row);
                $reviewDate = formatDate($reviewDateCell); // Correctly format the date

                // Validate required fields 
                if (empty($reviewId) || empty($customerName) || empty($productName) || empty($reviewText) || empty($rating) || empty($reviewDate)) {
                    $message = "<div class='alert alert-danger'>One or more required fields are empty in row $row. Please check the Excel file and try again.</div>";
                    unlink($target_file); // Remove the uploaded file 
                    $title = 'Upload Review Track Sheet';
                    $subTitle = 'Upload Review Track Sheet';
                    include './partials/layouts/layoutTop.php';
                    echo '<div class="row"> <div class="col-12"> <div class="card"> <div class="card-body">' . $message . ' <form action="upload_review_track_sheet.php" method="post" enctype="multipart/form-data"> <div class="form-group"> <label for="fileToUpload">Select file to upload:</label> <input type="file" name="fileToUpload" id="fileToUpload" class="form-control-file"> </div> <button type="submit" class="btn btn-primary">Upload</button> </form> </div> </div> </div> </div>';
                    include './partials/layouts/layoutBottom.php';
                    exit;
                }

                // Insert data into the database
                $sql = "INSERT INTO reviews (review_id, customer_name, product_name, review_text, rating, review_date) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $reviewId, $customerName, $productName, $reviewText, $rating, $reviewDate);
                $stmt->execute();
            }

            // Trigger the Python script (if applicable)
            $command = escapeshellcmd("python3 review_processing.py");
            $output = shell_exec($command);
            echo "<pre>$output</pre>";
        } else {
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
        }
    }
}

$title = 'Upload Review Track Sheet';
$subTitle = 'Upload Review Track Sheet';
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
                <form action="upload_review_track_sheet.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="fileToUpload">Select file to upload (Format: XLS, XLSX):</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <?php
                        if (isset($showViewDataButton) && $showViewDataButton === true) {
                            echo '<a href="feedback_insight_center.php" class="btn btn-success">View Data</a>';
                        }
                        ?>
                        <a href="upload_review_sample.xlsx" class="btn btn-info">Sample File Format</a> <!-- Button to download sample file -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include './partials/layouts/layoutBottom.php';
?>
