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

// ===== FORM VALIDATION =====
document.addEventListener('DOMContentLoaded', function(){
    // Add validation to all forms with class 'validate-form' OR all forms with submit buttons
    document.querySelectorAll('form').forEach(function(form){
        form.addEventListener('submit', function(e){
            let valid = true;
            let firstInvalid = null;

            // Remove old error messages
            form.querySelectorAll('.field-error').forEach(el => el.remove());
            form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

            // Check all required fields
            form.querySelectorAll('[required]').forEach(function(field){
                if(field.value.trim() === '' || field.value === '-- Select --' || field.value === ''){
                    valid = false;
                    field.classList.add('input-error');
                    // Show error message below field
                    let err = document.createElement('span');
                    err.className = 'field-error';
                    err.innerText = '⚠️ This field is required';
                    err.style.cssText = 'color:#dc2626;font-size:11px;margin-top:4px;display:block;';
                    field.parentNode.appendChild(err);
                    if(!firstInvalid) firstInvalid = field;
                }
            });

            if(!valid){
                e.preventDefault();
                // Scroll to first invalid field
                if(firstInvalid) firstInvalid.scrollIntoView({behavior:'smooth', block:'center'});
                // Show top alert
                let existing = form.querySelector('.form-alert');
                if(existing) existing.remove();
                let alert = document.createElement('div');
                alert.className = 'form-alert';
                alert.style.cssText = 'background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;padding:10px 16px;margin-bottom:14px;font-size:13px;font-weight:600;';
                alert.innerText = '❌ Please fill all required fields before submitting!';
                form.insertBefore(alert, form.firstChild);
            }
        });

        // Remove error on input
        form.querySelectorAll('[required]').forEach(function(field){
            field.addEventListener('input', function(){
                this.classList.remove('input-error');
                let err = this.parentNode.querySelector('.field-error');
                if(err) err.remove();
                let alert = form.querySelector('.form-alert');
                if(alert) alert.remove();
            });
            field.addEventListener('change', function(){
                this.classList.remove('input-error');
                let err = this.parentNode.querySelector('.field-error');
                if(err) err.remove();
            });
        });
    });
});
</script>

<style>
.input-error {
    border: 1.5px solid #dc2626 !important;
    background: #fff5f5 !important;
}
</style>
