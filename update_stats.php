<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/UserStats.php';

$userId = $_SESSION['user_id'];
$result = $_POST['result'];
$gameMode = $_POST['gameMode'];

// Debugging: Log received data
error_log("Received data - userId: $userId, result: $result, gameMode: $gameMode");

// Update to use the new connect method
$db = Database::getInstance()->connect();
$userStats = new UserStats($db);

if ($userStats->updateStats($userId, $result, $gameMode)) {
    http_response_code(200);
    echo 'Stats updated successfully';
} else {
    // Debugging: Log failure
    error_log("Failed to update stats for userId: $userId");
    http_response_code(500);
    echo 'Failed to update stats';
}
?>