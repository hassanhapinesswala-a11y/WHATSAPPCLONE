<?php
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$me = (int)$_SESSION['user_id'];
 
$sql = "SELECT u.id, u.username,
  (SELECT message FROM messages m WHERE (m.sender_id = u.id AND m.receiver_id = :me) OR (m.sender_id = :me AND m.receiver_id = u.id) ORDER BY m.timestamp DESC LIMIT 1) as last_msg
  FROM users u WHERE u.id != :me";
if ($q!=='') { $sql .= " AND u.username LIKE :q"; }
$sql .= " ORDER BY last_msg IS NULL, last_msg DESC, u.username LIMIT 100";
$stmt = $pdo->prepare($sql);
if ($q!=='') $stmt->execute([':me'=>$me, ':q'=>"%$q%"]);
else $stmt->execute([':me'=>$me]);
$rows = $stmt->fetchAll();
$out = [];
foreach($rows as $r){
  $out[] = [
    'id'=> (int)$r['id'],
    'username'=> $r['username'],
    'initial'=> strtoupper($r['username'][0]),
    'last_msg'=> $r['last_msg'] ? (strlen($r['last_msg'])>34?substr($r['last_msg'],0,34).'...':$r['last_msg']) : null
  ];
}
echo json_encode($out);
