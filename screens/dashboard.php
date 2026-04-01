<?php
session_start();
require_once '../config/auth.php';

$authUser  = requireRole('user');
$username  = htmlspecialchars($authUser->username);
$role      = htmlspecialchars($authUser->role);
$expiresIn = $authUser->exp - time();
$expiresAt = date('H:i:s', $authUser->exp);
$userId    = $authUser->user_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;900&family=Barlow:wght@300;400;500&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <title>Pitlane — <?= $username ?></title>
    <style>
        body { display:block; background:#0A0A0A; }
        .topbar { height:3px; background:linear-gradient(90deg,#0A0A0A 0%,#E8002D 20%,#E8002D 80%,#0A0A0A 100%); }
        .dash-header { display:flex; align-items:center; justify-content:space-between; padding:16px 28px; border-bottom:1px solid #1e1e1e; background:#111; }
        .logo { font-family:'Barlow Condensed',sans-serif; font-size:22px; font-weight:900; letter-spacing:3px; text-transform:uppercase; color:#F5F5F5; }
        .logo span { color:#E8002D; }
        .live-badge { display:flex; align-items:center; gap:6px; font-family:'Share Tech Mono',monospace; font-size:10px; color:#444; letter-spacing:2px; }
        .live-dot { width:7px; height:7px; background:#E8002D; border-radius:50%; animation:blink 1.2s infinite; }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.2;} }
        .driver-pill { display:flex; align-items:center; gap:10px; padding:6px 16px; border:1px solid #2a2a2a; background:#181818; clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%); }
        .driver-num { font-family:'Barlow Condensed',sans-serif; font-size:22px; font-weight:900; color:#E8002D; line-height:1; }
        .driver-info .callsign { font-family:'Barlow Condensed',sans-serif; font-size:14px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#F5F5F5; line-height:1; }
        .driver-info .drole { font-family:'Share Tech Mono',monospace; font-size:9px; color:#555; letter-spacing:1px; margin-top:2px; }
        .logout-btn { font-family:'Barlow Condensed',sans-serif; font-size:11px; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; padding:8px 20px; background:transparent; border:1px solid #2a2a2a; color:#555; cursor:pointer; text-decoration:none; display:inline-block; clip-path:polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%); transition:all 0.2s; }
        .logout-btn:hover { border-color:#E8002D; color:#E8002D; }
        .dash-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1px; background:#1a1a1a; }
        .stat-row { grid-column:1/-1; display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:#1a1a1a; }
        .stat { background:#111; padding:20px 22px; position:relative; }
        .stat::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; }
        .stat.c-red::before   { background:#E8002D; }
        .stat.c-gold::before  { background:#FFD700; }
        .stat.c-green::before { background:#00C864; }
        .stat.c-blue::before  { background:#00AEEF; }
        .stat-label { font-family:'Barlow Condensed',sans-serif; font-size:10px; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; color:#555; margin-bottom:8px; }
        .stat-value { font-family:'Barlow Condensed',sans-serif; font-size:32px; font-weight:900; line-height:1; color:#F5F5F5; }
        .stat-value.mono { font-family:'Share Tech Mono',monospace; font-size:20px; }
        .stat-sub { font-family:'Share Tech Mono',monospace; font-size:9px; color:#3a3a3a; margin-top:6px; letter-spacing:1px; }
        .panel { background:#111; padding:22px 24px; }
        .panel-title { font-family:'Barlow Condensed',sans-serif; font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:#E8002D; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .panel-title::before { content:''; width:3px; height:14px; background:#E8002D; flex-shrink:0; }
        .trow { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #181818; }
        .trow:last-child { border-bottom:none; }
        .tlbl { font-family:'Barlow Condensed',sans-serif; font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#555; }
        .tval { font-family:'Share Tech Mono',monospace; font-size:12px; color:#C0C0C0; }
        .tval.ok { color:#00C864; }
        .tval.warn { color:#FFD700; }
        .sector-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; }
        .sector { padding:10px; border:1px solid #1e1e1e; text-align:center; }
        .sector-lbl { font-family:'Barlow Condensed',sans-serif; font-size:9px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#444; margin-bottom:4px; }
        .sector-t { font-family:'Share Tech Mono',monospace; font-size:15px; color:#F5F5F5; }
        .sector-t.purple { color:#c084fc; }
        .sector-t.green  { color:#00C864; }
        .sector-t.yellow { color:#FFD700; }
        .rpm-bar { display:flex; gap:2px; align-items:flex-end; height:40px; margin:10px 0; }
        .rpm-seg { flex:1; background:#E8002D; min-height:3px; }
        .gap-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; }
        .gap-name { font-family:'Barlow Condensed',sans-serif; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#888; }
        .gap-name.me { color:#FFD700; }
        .gap-time-val { font-family:'Share Tech Mono',monospace; font-size:11px; color:#555; }
        .gap-time-val.me { color:#FFD700; }
        .gap-bar { height:3px; background:#1a1a1a; margin-bottom:10px; overflow:hidden; }
        .gap-fill { height:100%; background:#E8002D; }
        .tyre { display:flex; align-items:center; gap:10px; padding:8px 12px; border:1px solid #1e1e1e; margin-bottom:6px; }
        .tyre-circle { width:30px; height:30px; border-radius:50%; border:3px solid #555; display:flex; align-items:center; justify-content:center; font-family:'Barlow Condensed',sans-serif; font-size:11px; font-weight:900; flex-shrink:0; }
        .tyre-circle.soft   { border-color:#E8002D; color:#E8002D; }
        .tyre-circle.medium { border-color:#FFD700; color:#FFD700; }
        .tyre-name { font-family:'Barlow Condensed',sans-serif; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#F5F5F5; }
        .tyre-laps { font-family:'Share Tech Mono',monospace; font-size:9px; color:#555; margin-top:2px; }
        .drs-pill { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border:1px solid #2a2a2a; font-family:'Barlow Condensed',sans-serif; font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-right:8px; margin-top:6px; }
        .drs-pill.on  { border-color:#00C864; color:#00C864; }
        .drs-pill.off { color:#444; }
        .drs-dot { width:6px; height:6px; border-radius:50%; }
        .drs-pill.on .drs-dot  { background:#00C864; animation:blink 0.8s infinite; }
        .drs-pill.off .drs-dot { background:#333; }
        .flag-row { display:flex; gap:2px; margin-bottom:14px; }
        .flag-seg { height:3px; flex:1; }
        .bottom-bar { grid-column:1/-1; background:#0d0d0d; border-top:1px solid #1a1a1a; padding:12px 28px; display:flex; align-items:center; justify-content:space-between; }
        .bb-item { display:flex; flex-direction:column; align-items:center; gap:3px; }
        .bb-lbl { font-family:'Barlow Condensed',sans-serif; font-size:9px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#444; }
        .bb-val { font-family:'Share Tech Mono',monospace; font-size:11px; color:#888; }
    </style>
</head>
<body>

<div class="topbar"></div>

<div class="dash-header">
    <div style="display:flex;align-items:center;gap:16px;">
        <div class="logo">PITT<span>LANE</span></div>
        <div class="live-badge"><span class="live-dot"></span>LIVE SESSION</div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
        <div class="driver-pill">
            <div class="driver-num">44</div>
            <div class="driver-info">
                <div class="callsign"><?= $username ?></div>
                <div class="drole"><?= strtoupper($role) ?> &middot; DRIVER</div>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">Box Box</a>
    </div>
</div>

<div class="dash-grid">

    <div class="stat-row">
        <div class="stat c-red">
            <div class="stat-label">Current Lap</div>
            <div class="stat-value" id="lapnum">38</div>
            <div class="stat-sub">OF 57 TOTAL</div>
        </div>
        <div class="stat c-gold">
            <div class="stat-label">Best Lap</div>
            <div class="stat-value mono">1:27.432</div>
            <div class="stat-sub">LAP 24 &mdash; PERSONAL BEST</div>
        </div>
        <div class="stat c-green">
            <div class="stat-label">Gap to Leader</div>
            <div class="stat-value mono" id="gapdisplay">+4.821</div>
            <div class="stat-sub">P3 &mdash; GAINING</div>
        </div>
        <div class="stat c-blue">
            <div class="stat-label">Token TTL</div>
            <div class="stat-value mono" id="ttl"><?= $expiresIn ?>s</div>
            <div class="stat-sub">EXPIRES <?= $expiresAt ?></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-title">Sector Times</div>
        <div class="flag-row">
            <div class="flag-seg" style="background:#E8002D;"></div>
            <div class="flag-seg" style="background:#FFD700;"></div>
            <div class="flag-seg" style="background:#00C864;"></div>
        </div>
        <div class="sector-grid">
            <div class="sector"><div class="sector-lbl">S1</div><div class="sector-t purple">28.143</div></div>
            <div class="sector"><div class="sector-lbl">S2</div><div class="sector-t green">31.876</div></div>
            <div class="sector"><div class="sector-lbl">S3</div><div class="sector-t yellow">27.413</div></div>
        </div>
        <div style="margin-top:22px;">
            <div class="panel-title">RPM Trace</div>
            <div class="rpm-bar" id="rpmbar"></div>
            <div style="display:flex;justify-content:space-between;">
                <span style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#444;">0</span>
                <span style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#C0C0C0;" id="rpmval">11,200 RPM</span>
                <span style="font-family:'Share Tech Mono',monospace;font-size:9px;color:#444;">15k</span>
            </div>
        </div>
        <div style="margin-top:22px;">
            <div class="panel-title">Status</div>
            <div class="drs-pill on"><span class="drs-dot"></span>DRS OPEN</div>
            <div class="drs-pill off"><span class="drs-dot"></span>ERS DEPLOY</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-title">JWT Telemetry</div>
        <div class="trow"><span class="tlbl">Driver ID</span><span class="tval">usr_<?= str_pad($userId, 4, '0', STR_PAD_LEFT) ?></span></div>
        <div class="trow"><span class="tlbl">Callsign</span><span class="tval"><?= $username ?></span></div>
        <div class="trow"><span class="tlbl">Role</span><span class="tval ok"><?= strtoupper($role) ?></span></div>
        <div class="trow"><span class="tlbl">Token TTL</span><span class="tval warn" id="ttlmid"><?= $expiresIn ?>s</span></div>
        <div class="trow"><span class="tlbl">Expires at</span><span class="tval"><?= $expiresAt ?></span></div>
        <div class="trow"><span class="tlbl">Signature</span><span class="tval ok">&#10003; VERIFIED</span></div>
        <div class="trow"><span class="tlbl">Algorithm</span><span class="tval">HS256</span></div>
        <div style="margin-top:22px;">
            <div class="panel-title">Tyre Strategy</div>
            <div class="tyre">
                <div class="tyre-circle soft">S</div>
                <div><div class="tyre-name">Soft</div><div class="tyre-laps">CURRENT &mdash; 12 LAPS</div></div>
            </div>
            <div class="tyre">
                <div class="tyre-circle medium">M</div>
                <div><div class="tyre-name">Medium</div><div class="tyre-laps">STINT 1 &mdash; 26 LAPS</div></div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-title">Race Standings</div>
        <div class="gap-row"><span class="gap-name">P1 &nbsp; VERSTAPPEN</span><span class="gap-time-val">LEADER</span></div>
        <div class="gap-bar"><div class="gap-fill" style="width:100%;background:#C0C0C0;"></div></div>
        <div class="gap-row"><span class="gap-name">P2 &nbsp; LECLERC</span><span class="gap-time-val">+1.243</span></div>
        <div class="gap-bar"><div class="gap-fill" style="width:80%;background:#888;"></div></div>
        <div class="gap-row"><span class="gap-name me">P3 &nbsp; YOU</span><span class="gap-time-val me" id="mypos">+4.821</span></div>
        <div class="gap-bar"><div class="gap-fill" style="width:60%;"></div></div>
        <div class="gap-row"><span class="gap-name">P4 &nbsp; NORRIS</span><span class="gap-time-val">+6.102</span></div>
        <div class="gap-bar"><div class="gap-fill" style="width:42%;background:#555;"></div></div>
        <div class="gap-row"><span class="gap-name">P5 &nbsp; SAINZ</span><span class="gap-time-val">+8.887</span></div>
        <div class="gap-bar"><div class="gap-fill" style="width:28%;background:#444;"></div></div>
    </div>

    <div class="bottom-bar">
        <div class="bb-item"><div class="bb-lbl">Circuit</div><div class="bb-val">MONACO GP</div></div>
        <div class="bb-item"><div class="bb-lbl">Weather</div><div class="bb-val">27&deg;C DRY</div></div>
        <div class="bb-item"><div class="bb-lbl">Fuel Load</div><div class="bb-val">34.2 KG</div></div>
        <div class="bb-item"><div class="bb-lbl">Brake Temp</div><div class="bb-val">612&deg;C</div></div>
        <div class="bb-item"><div class="bb-lbl">Tyre Wear FL</div><div class="bb-val">68%</div></div>
        <div class="bb-item"><div class="bb-lbl">ERS Store</div><div class="bb-val">3.8 MJ</div></div>
        <div class="bb-item"><div class="bb-lbl">Session Time</div><div class="bb-val" id="clock">00:00</div></div>
    </div>

</div>

<script>
    const rpmH = [30,55,70,85,90,95,100,98,88,75,60,80,95,100,92,78,65,85,100,98,82,70,88,95,100,90,72,60,80,95];
    const bar = document.getElementById('rpmbar');
    rpmH.forEach(h => {
        const s = document.createElement('div'); s.className = 'rpm-seg';
        s.style.height = (h*0.4)+'px'; s.style.opacity = (h/100*0.7+0.3).toFixed(2);
        bar.appendChild(s);
    });
    let ttl = <?= $expiresIn ?>;
    setInterval(() => { ttl = Math.max(0,ttl-1); const v=ttl+'s'; document.getElementById('ttl').textContent=v; document.getElementById('ttlmid').textContent=v; }, 1000);
    let lap=38; setInterval(() => { lap=lap>=57?1:lap+1; document.getElementById('lapnum').textContent=lap; }, 4000);
    let gapVal=4.821; setInterval(() => { gapVal=Math.max(0.1,gapVal+(Math.random()-0.5)*0.15); const g='+'+gapVal.toFixed(3); document.getElementById('gapdisplay').textContent=g; document.getElementById('mypos').textContent=g; }, 2000);
    let secs=0; setInterval(() => { secs++; document.getElementById('clock').textContent=Math.floor(secs/60).toString().padStart(2,'0')+':'+(secs%60).toString().padStart(2,'0'); }, 1000);
    let ri=0; setInterval(() => { ri=(ri+1)%rpmH.length; document.getElementById('rpmval').textContent=Math.round(8000+rpmH[ri]*70).toLocaleString()+' RPM'; }, 400);
</script>
</body>
</html>