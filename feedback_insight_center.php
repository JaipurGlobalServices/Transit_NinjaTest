<?php
include 'config.php';

$sql = "SELECT * FROM reviews"; // Adjust query based on your database schema
$result = $conn->query($sql);

$reviews = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

?>

<h2>Feedback Insight Center</h2>
<div id="review-chart" style="width: 100%; height: 400px;"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('review-chart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [/* labels based on your data */],
            datasets: [{
                label: 'Reviews',
                data: [/* data points based on your data */],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
