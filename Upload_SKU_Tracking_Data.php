<?php
$title = 'Upload SKU Tracking Data';
$subTitle = 'Admin Panel';
include './partials/layouts/layoutTop.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card mt-4">
            <div class="card-body">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileToUpload" class="form-label">Select Excel file to upload:</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Excel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include './partials/layouts/layoutBottom.php'; ?>
