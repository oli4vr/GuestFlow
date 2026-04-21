<?php
/**
 * GuestFlow – reception.csv configuration
 * Select the correct path based on server environment
 */

// Server IP (or client IP on LAN)
$serverIp   = $_SERVER['SERVER_ADDR'] ?? '';
$serverName = $_SERVER['SERVER_NAME'] ?? '';

$csvFile = '/data/reception.csv';

// Check file existence and permissions
if (!file_exists($csvFile)) {
    die("Error: File $csvFile does not exist at the specified location.");
}

if (!is_writable($csvFile)) {
    die("Error: File $csvFile exists but is not writable.");
}

// Language detection and loading
$lang = $_GET['lang'] ?? 'en';
$lang = preg_replace('/[^a-z]/', '', $lang);  // sanitize to lowercase letters only

if ($lang === 'nl') {
    include 'lang-nl.php';
} elseif ($lang === 'fr') {
    include 'lang-fr.php';
} else {
    $lang = 'en';
    include 'lang-en.php';
}
