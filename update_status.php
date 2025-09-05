<?php
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['ok'=>false]); exit; }
$me = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$contact = isset($input['contact']) ? (int)$input['contact'] : 0;
$action = $input['action'] ?? '';
 
if ($contact && $action === 'mark_delivered_or_read') {
    // mark messages sent to me from contact as delivered (done in fetch) and mark delivered -> read
    $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE sender_id = ? AND receiver_id = ? AND status = 'delivered'");
    $stmt->execute([$contact, $me]);
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false]);
