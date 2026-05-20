<script>
function toggleNotif(){
    document.getElementById('notifDropdown').classList.toggle('open');
}
document.addEventListener('click',function(e){
    const w = document.getElementById('notifWrapper');
    if(w && !w.contains(e.target)) document.getElementById('notifDropdown').classList.remove('open');
});
// Auto logout 30 min
let timeLeft = 1800;
function resetTimer(){ timeLeft = 1800; }
['mousemove','keydown','click','scroll'].forEach(e => document.addEventListener(e, resetTimer, {passive:true}));
setInterval(()=>{ timeLeft--; if(timeLeft<=0){ alert('Session expired. Logging out...'); window.location.href='logout.php'; }},1000);
</script>
