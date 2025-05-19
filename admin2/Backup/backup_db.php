<?php

// Database credentials
$host = 'localhost';
$database = 'vote'; // Corrected variable name
$username = 'root';
$password = '';

// Project directory (where the backup will be stored)
$projectDir = __DIR__; // Current directory (where this script is located)

// Create the backup directory if it doesn't exist
$backupDir = $projectDir.'/Backup';  // Changed to "Backup"
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true); // Creates directory with full permissions
}

// Generate the backup filename (including timestamp)
$date = date("Y-m-d_H-i-s");
$backupFile = $backupDir . "/backup_" . $database . "_" . $date . ".sql";

// Construct the mysqldump command
$command = "\"C:/xampp/mysql/bin/mysqldump.exe\" --host={$host} --user={$username} --password='{$password}' {$database} > {$backupFile}";
// Execute the command
exec($command, $output, $returnCode);

// Check for errors
if ($returnCode === 0) {
    $message = "Database backup created successfully: " . $backupFile;
    $success = true;
} else {
    $message = "Error creating database backup:" . PHP_EOL;
    $message .= "Command: " . $command . PHP_EOL;
    $message .= "Return code: " . $returnCode . PHP_EOL;
    $success = false;
    // You might want to log the error to a file for further investigation
}

// Retention policy (number of days to keep backups)
$retentionDays = 3;

// Calculate the timestamp for backups older than the retention period
$cutoffTimestamp = time() - ($retentionDays * 24 * 60 * 60);

// Get a list of all backup files in the backup directory
$backupFiles = glob($backupDir . "/backup_" . $database . "_*.sql");

// Iterate through the backup files and delete any that are older than the cutoff
foreach ($backupFiles as $backupFile) {
    if (filemtime($backupFile) < $cutoffTimestamp) {
        unlink($backupFile); // Delete the file
        $message = "Deleted old backup: " . $backupFile;
        $success = true;
    }
}

// Return the result as an array
return array('success' => $success, 'message' => $message);

?>