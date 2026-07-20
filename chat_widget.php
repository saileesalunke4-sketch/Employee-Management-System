<?php
// chat_widget.php — floating AI assistant, included from each topbar file.
// Uses its own fixed purple-to-pink gradient (--hr-chat-1 / --hr-chat-2)
// so it stands out consistently across all three role themes.
?>
<div id="hrChatBubble" onclick="toggleHrChat()" title="Ask the HR Assistant">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <rect x="4.5" y="9" width="15" height="11" rx="3"/>
        <path d="M12 9V5.5"/>
        <circle cx="12" cy="4" r="1.3" fill="currentColor" stroke="none"/>
        <circle cx="8.7" cy="14.2" r="1.4" fill="currentColor" stroke="none"/>
        <circle cx="15.3" cy="14.2" r="1.4" fill="currentColor" stroke="none"/>
        <path d="M9 17.5h6"/>
        <path d="M2.5 12.5v3M21.5 12.5v3"/>
    </svg>
</div>

<div id="hrChatPanel">
    <div id="hrChatHeader">
        <span>HR Assistant</span>
        <button type="button" onclick="toggleHrChat()" aria-label="Close">&times;</button>
    </div>
    <div id="hrChatMessages">
        <div class="hr-chat-msg hr-chat-bot">Hi! Ask me about your leave balance, attendance, or tasks.</div>
    </div>
    <form id="hrChatForm" onsubmit="return sendHrChatMessage(event)">
        <input type="text" id="hrChatInput" placeholder="Type your question…" maxlength="500" autocomplete="off">
        <button type="submit" aria-label="Send">&#10148;</button>
    </form>
</div>

<style>
:root{
    --hr-chat-1: #7C3AED;
    --hr-chat-2: #EC4899;
}
#hrChatBubble{
    position:fixed; bottom:24px; right:24px; z-index:9999;
    width:56px; height:56px; border-radius:50%;
    background:linear-gradient(135deg, var(--hr-chat-1), var(--hr-chat-2));
    color:#fff; display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 24px rgba(124,58,237,0.45); cursor:pointer;
    transition:transform .15s ease, box-shadow .15s ease;
    animation:hrChatPulse 2.6s ease-in-out infinite;
}
#hrChatBubble:hover{ transform:scale(1.08); box-shadow:0 10px 28px rgba(236,72,153,0.5); animation-play-state:paused; }
@keyframes hrChatPulse{
    0%, 100%{ box-shadow:0 8px 24px rgba(124,58,237,0.45), 0 0 0 0 rgba(124,58,237,0.35); }
    50%{ box-shadow:0 8px 24px rgba(124,58,237,0.45), 0 0 0 9px rgba(124,58,237,0); }
}

#hrChatPanel{
    display:none; position:fixed; bottom:90px; right:24px; z-index:9999;
    width:340px; max-width:calc(100vw - 32px); height:440px; max-height:calc(100vh - 130px);
    background:var(--surface,#fff); border-radius:16px; overflow:hidden;
    box-shadow:0 16px 40px rgba(15,23,42,0.2);
    flex-direction:column;
    border:1px solid var(--border,#e5e7eb);
}
#hrChatPanel.open{ display:flex; }

#hrChatHeader{
    background:linear-gradient(135deg, var(--hr-chat-1), var(--hr-chat-2));
    color:#fff; padding:12px 16px; font-weight:600; font-size:14px;
    display:flex; align-items:center; justify-content:space-between;
}
#hrChatHeader button{ background:none; border:none; color:#fff; font-size:20px; cursor:pointer; line-height:1; }

#hrChatMessages{ flex:1; overflow-y:auto; padding:12px; display:flex; flex-direction:column; gap:8px; background:var(--surface-soft,#f3f4f7); }
.hr-chat-msg{ padding:8px 12px; border-radius:12px; font-size:13px; line-height:1.4; max-width:85%; white-space:pre-wrap; }
.hr-chat-bot{ background:#fff; border:1px solid var(--border,#e5e7eb); color:var(--text-1,#14161a); align-self:flex-start; }
.hr-chat-user{ background:linear-gradient(135deg, var(--hr-chat-1), var(--hr-chat-2)); color:#fff; align-self:flex-end; }
.hr-chat-loading{ background:#fff; border:1px solid var(--border,#e5e7eb); color:var(--text-3,#9aa1ac); align-self:flex-start; font-style:italic; }

#hrChatForm{ display:flex; gap:8px; padding:10px; border-top:1px solid var(--border,#e5e7eb); background:#fff; }
#hrChatInput{ flex:1; border:1px solid var(--border,#e5e7eb); border-radius:20px; padding:8px 14px; font-size:13px; outline:none; }
#hrChatInput:focus{ border-color:var(--hr-chat-1); }
#hrChatForm button{ background:linear-gradient(135deg, var(--hr-chat-1), var(--hr-chat-2)); color:#fff; border:none; border-radius:50%; width:36px; height:36px; cursor:pointer; font-size:14px; flex-shrink:0; }
</style>

<script>
function toggleHrChat(){
    document.getElementById('hrChatPanel').classList.toggle('open');
}

function sendHrChatMessage(e){
    e.preventDefault();
    var input = document.getElementById('hrChatInput');
    var msg = input.value.trim();
    if(!msg) return false;

    var box = document.getElementById('hrChatMessages');
    box.insertAdjacentHTML('beforeend', '<div class="hr-chat-msg hr-chat-user"></div>');
    box.lastElementChild.textContent = msg;
    input.value = '';

    var loadingId = 'hr-loading-' + Date.now();
    box.insertAdjacentHTML('beforeend', '<div class="hr-chat-msg hr-chat-loading" id="'+loadingId+'">Thinking…</div>');
    box.scrollTop = box.scrollHeight;

    fetch('hr_chatbot.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message: msg})
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        var loadingEl = document.getElementById(loadingId);
        if(loadingEl) loadingEl.remove();
        var reply = data.reply || data.error || 'Something went wrong — please try again.';
        box.insertAdjacentHTML('beforeend', '<div class="hr-chat-msg hr-chat-bot"></div>');
        box.lastElementChild.textContent = reply;
        box.scrollTop = box.scrollHeight;
    })
    .catch(function(){
        var loadingEl = document.getElementById(loadingId);
        if(loadingEl) loadingEl.remove();
        box.insertAdjacentHTML('beforeend', '<div class="hr-chat-msg hr-chat-bot">Couldn\'t reach the server — please try again.</div>');
        box.scrollTop = box.scrollHeight;
    });

    return false;
}
</script>
