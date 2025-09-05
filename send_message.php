?php
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['ok'=>false]); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$to = isset($input['to']) ? (int)$input['to'] : 0;
$msg = trim($input['message'] ?? '');
$from = (int)$_SESSION['user_id'];
if (!$to || $msg==='') { echo json_encode(['ok'=>false]); exit;}
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, status) VALUES (?, ?, ?, 'sent')");
$stmt->execute([$from, $to, $msg]);
echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
 
