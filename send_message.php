<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/Message.php';

// Update to use the connect method
$db = Database::getInstance()->connect();
$message = new Message($db);

$user_id = $_SESSION['user_id'];
$game_id = $_POST['game_id'];
$content = $_POST['message'];

if ($message->canSendMessage($user_id, $game_id)) {
    $message->sendMessage($user_id, $game_id, $content);
    echo json_encode(['success' => 'Message sent']);
} else {
    echo json_encode(['error' => 'Message limit reached']);
}
?>