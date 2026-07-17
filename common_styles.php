<?php
// common_styles.php — shared CSS for all pages
?>
<style>
*{box-sizing:border-box;}
@media(max-width:900px){
  .cards,.five-cards,.six-cards{grid-template-columns:repeat(2,1fr)!important;}
  .form-grid{grid-template-columns:1fr!important;}
  .hol-cards{grid-template-columns:1fr 1fr!important;}
}
@media(max-width:600px){
  .cards,.five-cards,.six-cards{grid-template-columns:1fr!important;}
  .hol-cards{grid-template-columns:1fr!important;}
  .topbar h2{font-size:15px;}
}
.notif-wrapper{position:relative;}
.notif-bell{font-size:20px;cursor:pointer;position:relative;display:inline-block;padding:4px 8px;border-radius:8px;transition:background .2s;}
.notif-bell:hover{background:rgba(59,130,246,.1);}
.notif-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;animation:pulse 1.5s infinite;}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
.notif-dropdown{display:none;position:fixed;width:340px;max-width:calc(100vw - 32px);background:#fff;border-radius:12px;box-shadow:0 12px 36px rgba(0,0,0,.22);z-index:9999;overflow:hidden;border:1px solid #e5e7eb;}
.notif-dropdown.open{display:block;animation:slideDown .2s ease;}
@keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.notif-overlay{display:none;}
.notif-header{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;font-weight:600;background:#f8fafc;}
.notif-list{max-height:340px;overflow-y:auto;}
.notif-item{display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-bottom:1px solid #f5f5f5;transition:background .2s;}
.notif-item:hover{background:#f8fafc;}
.notif-item.notif-new{background:#eff6ff;}
.notif-icon{font-size:18px;flex-shrink:0;}
.notif-text{flex:1;font-size:13px;color:#374151;line-height:1.6;}
.notif-type{background:#dbeafe;color:#1d4ed8;font-size:11px;padding:1px 7px;border-radius:20px;font-weight:600;}
.notif-dot{width:8px;height:8px;background:#3b82f6;border-radius:50%;flex-shrink:0;margin-top:6px;}
.notif-empty{text-align:center;padding:28px;color:#9ca3af;font-size:13px;}
.topbar-right{display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
.pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.pill.green{background:#dcfce7;color:#16a34a;}
.pill.red{background:#fee2e2;color:#dc2626;}
.pill.yellow{background:#fef3c7;color:#d97706;}
.pill.blue{background:#dbeafe;color:#1d4ed8;}
.hl-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.hl-badge.National{background:#dbeafe;color:#1d4ed8;}
.hl-badge.Festival{background:#fef3c7;color:#d97706;}
.hl-badge.State{background:#dcfce7;color:#16a34a;}
.hl-badge.Government{background:#f3e8ff;color:#7c3aed;}
.hol-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
.cal-outer{background:#fff;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden;}
.cal-top{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:linear-gradient(135deg,#1a3a6e,#3b82f6);color:#fff;}
.cal-top h3{font-size:16px;font-weight:700;margin:0;}
.cal-nav-btn{background:rgba(255,255,255,.2);color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px;transition:background .2s;}
.cal-nav-btn:hover{background:rgba(255,255,255,.35);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;padding:12px;}
.cal-day-name{text-align:center;font-size:11px;font-weight:700;color:#6b7280;padding:6px 0;text-transform:uppercase;}
.cal-cell{min-height:56px;border-radius:8px;padding:5px 7px;font-size:12px;border:1px solid #f0f0f0;background:#fff;transition:background .15s;}
.cal-cell.today{background:#eff6ff!important;border:2px solid #3b82f6;}
.cal-cell.holiday{background:#fef2f2!important;border-color:#fca5a5;}
.cal-cell.sunday{background:#fafafa;color:#9ca3af;}
.cal-cell.empty{border:none;background:none;}
.cal-num{font-weight:700;color:#374151;font-size:13px;}
.cal-cell.holiday .cal-num{color:#dc2626;}
.cal-cell.today .cal-num{color:#1d4ed8;}
.cal-hname{font-size:9px;color:#dc2626;line-height:1.3;margin-top:2px;word-break:break-word;}

/* ===== SCROLLBAR FIX ===== */
html, body { height: 100%; overflow: auto; }
.dashboard { overflow: visible; }
.main-content { overflow-y: auto; overflow-x: hidden; min-width: 0; }
.topbar { flex-wrap: wrap !important; gap: 10px !important; }
.topbar-right { flex-wrap: wrap !important; max-width: 100% !important; }
.user-info { white-space: nowrap !important; }

/* ===== HIDE SCROLLBAR (keep scroll working) ===== */
html, body, .main-content, .sidebar {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
html::-webkit-scrollbar,
body::-webkit-scrollbar,
.main-content::-webkit-scrollbar,
.sidebar::-webkit-scrollbar {
    display: none;
}
</style>
