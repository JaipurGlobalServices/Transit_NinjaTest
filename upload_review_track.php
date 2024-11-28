<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Extraction Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-container {
            margin: 20px 0;
        }
        input[type="text"], input[type="date"], input[type="submit"] {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
        }
        input[type="submit"] {
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        .loader {
            display: none;
        }
    </style>
</head>
<body>

    <h2>Amazon Review Extraction</h2>
    <p>Please fill in the details to extract reviews from Amazon.</p>

    <div class="form-container">
        <form action="upload_review_track.php" method="POST">
            <label for="awb_id">AWB ID:</label>
            <input type="text" id="awb_id" name="awb_id" required><br><br>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required><br><br>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required><br><br>

            <input type="submit" value="Submit">
        </form>
    </div>

    <div class="loader">
        <p>Processing... Please wait.</p>
    </div>

    <?php
    // Display extracted reviews after form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Collect user inputs
        $awb_id = $_POST['awb_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Prepare the command to call the Python script
        $command = escapeshellcmd("python3 feedback_collect.py '$awb_id' '$start_date' '$end_date'");

        // Execute the Python script and capture output
        $output = shell_exec($command);

        // Display the results
        if ($output) {
            echo "<h3>Extracted Reviews:</h3>";
            echo "<pre>$output</pre>";
        } else {
            echo "<p>No reviews found or an error occurred while processing.</p>";
        }
    }
    ?>

</body>
</html>
