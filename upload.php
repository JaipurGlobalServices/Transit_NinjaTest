
<?php
include 'config.php';

$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$title = 'Upload SKU Tracking Data Status Page';
$subTitle = 'Admin Panel';
include './partials/layouts/layoutTop.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card mt-4">
            <div class="card-body">
                    <div class="mb-3">
                        <?php 
                            if ($fileType != "xls" && $fileType != "xlsx") {
                                echo "Sorry, only XLS and XLSX files are allowed.";
                                $uploadOk = 0;
                            }

                            if ($uploadOk == 1) {
                                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                                    echo "<br>The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";
                                    include 'process_excel.php';
                                } else {
                                    echo "Sorry, there was an error uploading your file.";
                                }
                            }
                            ?>
                    </div>
                    <a href='Track_Shipments.php' class="btn btn-primary">View Data</a>
            </div>
        </div>
    </div>
</div>
<?php include './partials/layouts/layoutBottom.php'; ?>