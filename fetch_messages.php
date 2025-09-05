<?php
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }
$me = (int)$_SESSION['user_id'];
$contact = isset($_GET['contact']) ? (int)$_GET['contact'] : 0;
if (!$contact) { echo json_encode([]); exit; }
 
// fetch messages between me and contact
$stmt = $pdo->prepare("SELECT id, sender_id, receiver_id, message, timestamp, status FROM messages
  WHERE (sender_id = :me AND receiver_id = :c) OR (sender_id = :c AND receiver_id = :me)
  ORDER BY timestamp ASC");
$stmt->execute([':me'=>$me, ':c'=>$contact]);
$rows = $stmt->fetchAll();
 
// mark incoming messages (where receiver = me) as delivered if currently 'sent'
$idsToDeliver = [];
foreach($rows as $r){
  if ($r['receiver_id'] == $me && $r['status'] === 'sent') $idsToDeliver[] = $r['id'];
}
if (!empty($idsToDeliver)) {
  $in = implode(',', array_map('intval',$idsToDeliver));
  $pdo->query("UPDATE messages SET status = 'delivered' WHERE id IN ($in)");
  // Reflect change in $rows by re-fetching (simpler)
  $stmt->execute([':me'=>$me, ':c'=>$contact]);
  $rows = $stmt->fetchAll();
}
 
$out = [];
foreach($rows as $r){
  $out[] = [
    'id'=> (int)$r['id'],
    'is_me'=> $r['sender_id']==$me,
    'message'=> $r['message'],
    'ts'=> date('H:i', strtotime($r['timestamp'])),
    'status'=> $r['status']
  ];
}
echo json_encode($out);
 
