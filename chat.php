<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='login.php';</script>"; exit;
}
$me_id = (int)$_SESSION['user_id'];
$me_name = $_SESSION['username'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Chat — ChatClone</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Internal CSS - modern chat look */
:root{--bg:#071533;--panel:#071130;--muted:rgba(255,255,255,0.65)}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,Arial;background:linear-gradient(180deg,#021024 0%, #07203a 100%);color:#fff;height:100vh;display:flex;align-items:stretch}
.app{display:grid;grid-template-columns:320px 1fr;height:100vh;width:100%;margin:auto;max-width:1200px;border-radius:12px;overflow:hidden;box-shadow:0 20px 60px rgba(2,6,23,0.6);margin-top:10px;}
.sidebar{background:linear-gradient(180deg,rgba(255,255,255,0.03),transparent);padding:18px;overflow:auto;}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.brand{font-weight:700;font-size:18px}
.search{width:100%; padding:8px 10px;border-radius:10px;border:1px solid rgba(255,255,255,0.06); background:transparent;color:#fff;margin-top:10px}
.contact{display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;cursor:pointer;margin-bottom:8px}
.contact:hover{background:rgba(255,255,255,0.02)}
.avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#06b6d4,#3b82f6);display:flex;align-items:center;justify-content:center;font-weight:700}
.main{background:#f6f8ff1a;padding:18px;display:flex;flex-direction:column;overflow:hidden}
.chatHeader{display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.03)}
.messages{flex:1;overflow:auto;padding:14px 6px;display:flex;flex-direction:column;gap:8px}
.msg{max-width:70%;padding:10px 14px;border-radius:18px;line-height:1.3;font-size:14px}
.msg.me{align-self:flex-end;background:linear-gradient(90deg,#06b6d4,#3b82f6);color:#04212b;border-bottom-right-radius:6px}
.msg.you{align-self:flex-start;background:rgba(255,255,255,0.04);color:#fff;border-bottom-left-radius:6px}
.timestamp{font-size:11px;color:var(--muted);margin-top:6px;text-align:right}
.inputBox{display:flex;gap:8px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.03)}
.inputBox input{flex:1;padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:#fff}
.inputBox button{padding:10px 14px;border-radius:12px;border:none;background:linear-gradient(90deg,#06b6d4,#3b82f6);cursor:pointer}
.topRight{display:flex;gap:8px;align-items:center}
.badge{background:#06b6d4;padding:6px 10px;border-radius:999px;font-weight:700;color:#012}
.empty{display:flex;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,0.4);font-size:18px}
@media(max-width:820px){.app{grid-template-columns:1fr} .sidebar{display:none}}
</style>
</head>
<body>
<div class="app">
  <div class="sidebar">
    <div class="header">
      <div class="brand">ChatClone</div>
      <div class="topRight">
        <div class="badge"><?=htmlspecialchars($me_name)?></div>
        <a href="logout.php" style="color:#fff;text-decoration:none;font-weight:600">Logout</a>
      </div>
    </div>
    <input id="search" class="search" placeholder="Search contacts...">
    <div id="contacts"></div>
  </div>
 
  <div class="main">
    <div id="chatArea">
      <div class="empty">Select a contact to start chatting ✨</div>
    </div>
    <div style="display:none" id="composer" class="inputBox">
      <input id="msgInput" placeholder="Type a message..." maxlength="1000"/>
      <button id="sendBtn">Send</button>
    </div>
  </div>
</div>
 
<script>
const meId = <?=json_encode($me_id)?>;
let activeContact = null;
let polling = null;
 
// load contacts
function loadContacts(q='') {
  fetch('fetch_contacts.php?q=' + encodeURIComponent(q))
    .then(r=>r.json())
    .then(data=>{
      const ct = document.getElementById('contacts');
      ct.innerHTML = '';
      if (data.length===0) { ct.innerHTML = '<div style="color:rgba(255,255,255,0.45);margin-top:20px;">No contacts found</div>'; return; }
      data.forEach(u=>{
        const div = document.createElement('div');
        div.className='contact';
        div.innerHTML = `<div class="avatar">${u.initial}</div><div style="flex:1">
          <div style="font-weight:700">${u.username}</div>
          <div style="font-size:12px;color:rgba(255,255,255,0.5)">${u.last_msg||'Say hi!'}</div>
        </div>`;
        div.onclick = ()=>{ openChat(u.id, u.username); };
        ct.appendChild(div);
      });
    });
}
 
// open chat with contactId
function openChat(contactId, contactName){
  activeContact = contactId;
  document.getElementById('chatArea').innerHTML = `
    <div class="chatHeader">
      <div class="avatar">${contactName.charAt(0).toUpperCase()}</div>
      <div style="font-weight:700">${contactName}</div>
    </div>
    <div id="messages" class="messages"></div>
  `;
  document.getElementById('composer').style.display = 'flex';
  // initial fetch
  fetchMessages();
  // mark messages as delivered/read
  fetch('update_status.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({contact:activeContact, action:'mark_delivered_or_read'})
  });
  // polling
  if (polling) clearInterval(polling);
  polling = setInterval(fetchMessages, 1500);
}
 
// send message
document.getElementById('sendBtn').addEventListener('click', sendMessage);
document.getElementById('msgInput').addEventListener('keydown', (e)=>{ if(e.key==='Enter') sendMessage(); });
 
function sendMessage(){
  const text = document.getElementById('msgInput').value.trim();
  if (!text || !activeContact) return;
  fetch('send_message.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({to: activeContact, message: text})
  }).then(r=>r.json()).then(res=>{
    document.getElementById('msgInput').value='';
    fetchMessages();
  });
}
 
function fetchMessages(){
  if (!activeContact) return;
  fetch(`fetch_messages.php?contact=${activeContact}`)
    .then(r=>r.json())
    .then(data=>{
      const mdiv = document.getElementById('messages');
      if (!mdiv) return;
      mdiv.innerHTML='';
      data.forEach(msg=>{
        const div = document.createElement('div');
        div.className = 'msg ' + (msg.is_me ? 'me' : 'you');
        div.innerHTML = `<div>${escapeHtml(msg.message)}</div>
          <div class="timestamp">${msg.ts} ${msg.is_me ? '&nbsp;&nbsp;'+statusIcon(msg.status) : ''}</div>`;
        mdiv.appendChild(div);
      });
      mdiv.scrollTop = mdiv.scrollHeight;
    });
}
 
function statusIcon(status){
  if (status==='sent') return '✓';
  if (status==='delivered') return '✓✓';
  if (status==='read') return '✓✓ (read)';
  return '';
}
 
function escapeHtml(s){ return s.replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }
 
// search contacts
document.getElementById('search').addEventListener('input', (e)=>{ loadContacts(e.target.value); });
 
// initial
loadContacts();
</script>
</body>
</html>
 
