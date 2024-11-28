<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(0);  // Set unlimited execution time

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Python script ka relative path
    $pythonScriptPath = 'tracking.py';

    // Full path to virtual environment's Python executable
    $pythonExecutable = 'D:\\xampp\\htdocs\\Transit_Ninja\\venv\\Scripts\\python.exe';
    $command = escapeshellcmd($pythonExecutable . " " . $pythonScriptPath);

    // Execute the command and store the output
    $output = shell_exec($command . " 2>&1");  // Capturing stderr as well

    // Print the output and log file content
    echo "<pre>$output</pre>";

    // Display the log file content
    $logContent = file_get_contents('tracking.log');
    // echo "<h2>Log File:</h2><pre>$logContent</pre>";
}
?>
