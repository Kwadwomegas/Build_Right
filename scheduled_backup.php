<?php
include 'config/db.php';

// Directory to store backups
$backup_dir = __DIR__ . '/backups/';

// Load the backup schedule
$schedule_file = __DIR__ . '/backup_schedule.txt';
$schedule = file_exists($schedule_file) ? file_get_contents($schedule_file) : 'none';

if ($schedule === 'none') {
    exit(); // No schedule, exit
}

// Check the last backup time
$last_backup_file = __DIR__ . '/last_backup.txt';
$last_backup = file_exists($last_backup_file) ? (int) file_get_contents($last_backup_file) : 0;
$now = time();

$should_backup = false;
if ($schedule === 'daily') {
    // Backup if 24 hours have passed
    if (($now - $last_backup) >= (24 * 60 * 60)) {
        $should_backup = true;
    }
} elseif ($schedule === 'weekly') {
    // Backup if 7 days have passed
    if (($now - $last_backup) >= (7 * 24 * 60 * 60)) {
        $should_backup = true;
    }
}

if ($should_backup) {
    // Perform the backup
    $backup_file = performBackup($db_config, $backup_dir);
    if ($backup_file) {
        // Update the last backup time
        file_put_contents($last_backup_file, $now);

        // Optionally, send an email to the admin (similar to manual backup)
        // Add email logic here if desired
    }
}

// Function to perform a backup (same as in settings.php)
function performBackup($db_config, $backup_dir) {
    $db_host = $db_config['host'];
    $db_user = $db_config['user'];
    $db_pass = $db_config['pass'];
    $db_name = $db_config['name'];

    if (is_dir($backup_dir)) {
        $files = glob($backup_dir . '*.sql');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > (7 * 24 * 60 * 60)) {
                unlink($file);
            }
        }
    }

    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    $backup_file = 'backup_' . $db_name . '_' . date('Ymd_His') . '.sql';
    $backup_path = $backup_dir . $backup_file;

    $mysqldump_path = '"C:\xampp\mysql\bin\mysqldump.exe"';
    $command = "$mysqldump_path --host=$db_host --user=$db_user --password=$db_pass $db_name > \"$backup_path\" 2>&1";
    exec($command, $output, $return_var);

    if ($return_var === 0 && file_exists($backup_path)) {
        return $backup_file;
    } else {
        error_log("Scheduled backup failed: " . implode("\n", $output));
        return false;
    }
}
?>