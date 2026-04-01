<?php
session_start();
require_once '../config/auth.php';

$authUser  = requireRole('admin');
$username  = htmlspecialchars($authUser->username);
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
    <title>Race Control — <?= $username ?></title>
    <style>
        body { display:block; background:#0A0A0A; }
        .topbar { height:3px; background:linear-gradient(90deg,#0A0A0A 0%,#E8002D 20%,#E8002D 80%,#0A0A0A 100%); }
        .dash-header { display:flex; align-items:center; justify-content:space-between; padding:16px 28px; border-bottom:1px solid #1e1e1e; background:#111; }
        .logo { font-family:'Barlow Condensed',sans-serif; font-size:22px; font-weight:900; letter-spacing:3px; text-transform:uppercase; color:#F5F5F5; }
        .logo span { color:#E8002D; }
        .rc-badge { display:flex; align-items:center; gap:8px; padding:5px 14px; border:1px solid rgba(232,0,45,0.3); background:rgba(232,0,45,0.08); font-family:'Barlow Condensed',sans-serif; font-size:11px; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; color:#E8002D; clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%); }
        .pulse { width:7px; height:7px; background:#E8002D; border-radius:50%; animation:blink 1s infinite; }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.2;} }
        .driver-pill { display:flex; align-items:center; gap:10px; padding:6px 16px; border:1px solid #2a2a2a; background:#181818; clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%); }
        .driver-num { font-family:'Barlow Condensed',sans-serif; font-size:22px; font-weight:900; color:#E8002D; line-height:1; }
        .driver-info .callsign { font-family:'Barlow Condensed',sans-serif; font-size:14px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#F5F5F5; line-height:1; }
        .driver-info .drole { font-family:'Share Tech Mono',monospace; font-size:9px; color:#E8002D; letter-spacing:1px; margin-top:2px; }
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
        .stat-value.danger { color:#E8002D; }
        .stat-sub { font-family:'Share Tech Mono',monospace; font-size:9px; color:#3a3a3a; margin-top:6px; letter-spacing:1px; }
        .panel { background:#111; padding:22px 24px; }
        .panel-title { font-family:'Barlow Condensed',sans-serif; font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:#E8002D; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .panel-title::before { content:''; width:3px; height:14px; background:#E8002D; flex-shrink:0; }
        .panel-title.gold { color:#FFD700; }
        .panel-title.gold::before { background:#FFD700; }
        .trow { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #181818; }
        .trow:last-child { border-bottom:none; }
        .tlbl { font-family:'Barlow Condensed',sans-serif; font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#555; }
        .tval { font-family:'Share Tech Mono',monospace; font-size:12px; color:#C0C0C0; }
        .tval.ok { color:#00C864; }
        .tval.warn { color:#FFD700; }
        .tval.danger { color:#E8002D; }

        /* Driver list */
        .driver-item { display:flex; align-items:center; gap:10px; padding:9px 10px; border:1px solid #1e1e1e; margin-bottom:5px; position:relative; overflow:hidden; }
        .driver-item::before { content:''; position:absolute; left:0; top:0; bottom:0; width:2px; background:#E8002D; }
        .driver-pos { font-family:'Barlow Condensed',sans-serif; font-size:20px; font-weight:900; color:#333; width:22px; text-align:center; flex-shrink:0; }
        .driver-details { flex:1; }
        .driver-name { font-family:'Barlow Condensed',sans-serif; font-size:13px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#C0C0C0; line-height:1; }
        .driver-team { font-family:'Share Tech Mono',monospace; font-size:9px; color:#444; margin-top:2px; }
        .driver-pts { font-family:'Share Tech Mono',monospace; font-size:11px; color:#888; }

        /* Privilege items */
        .priv-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid #1e1e1e; margin-bottom:5px; }
        .priv-dot { width:6px; height:6px; background:#E8002D; flex-shrink:0; box-shadow:0 0 6px rgba(232,0,45,0.4); }
        .priv-text { font-family:'Barlow',sans-serif; font-size:13px; color:#888; }

        /* Flag status */
        .flag-status { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px; }
        .flag-pill { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border:1px solid #2a2a2a; font-family:'Barlow Condensed',sans-serif; font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; }
        .flag-pill.green-flag { border-color:#00C864; color:#00C864; }
        .flag-pill.safety { border-color:#FFD700; color:#FFD700; }
        .flag-pill.red-flag { border-color:#E8002D; color:#E8002D; }
        .fp { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .flag-pill.green-flag .fp { background:#00C864; animation:blink 1.5s infinite; }
        .flag-pill.safety .fp { background:#FFD700; }

        /* Radio log */
        .radio-item { display:flex; gap:10px; padding:8px 0; border-bottom:1px solid #181818; }
        .radio-item:last-child { border-bottom:none; }
        .radio-time { font-family:'Share Tech Mono',monospace; font-size:9px; color:#444; flex-shrink:0; padding-top:2px; }
        .radio-msg { font-family:'Barlow',sans-serif; font-size:12px; color:#888; line-height:1.5; }
        .radio-msg strong { font-family:'Barlow Condensed',sans-serif; font-weight:700; letter-spacing:0.5px; color:#C0C0C0; }

        .bottom-bar { grid-column:1/-1; background:#0d0d0d; border-top:1px solid #1a1a1a; padding:12px 28px; display:flex; align-items:center; justify-content:space-between; }
        .bb-item { display:flex; flex-direction:column; align-items:center; gap:3px; }
        .bb-lbl { font-family:'Barlow Condensed',sans-serif; font-size:9px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#444; }
        .bb-val { font-family:'Share Tech Mono',monospace; font-size:11px; color:#888; }
    </style>
</head>
<body>

<div class="topbar"></div>

<div class="dash-header">
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="logo">RACE<span>CTRL</span></div>
        <div class="rc-badge"><span class="pulse"></span>RACE DIRECTOR</div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
        <div class="driver-pill">
            <div class="driver-num">01</div>
            <div class="driver-info">
                <div class="callsign"><?= $username ?></div>
                <div class="drole">ADMIN &middot; RACE CONTROL</div>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">Retire</a>
    </div>
</div>

<div class="dash-grid">

    <div class="stat-row">
        <div class="stat c-red">
            <div class="stat-label">Registered Drivers</div>
            <div class="stat-value">247</div>
            <div class="stat-sub">TOTAL USERS IN SYSTEM</div>
        </div>
        <div class="stat c-gold">
            <div class="stat-label">Active Sessions</div>
            <div class="stat-value" id="sessions">14</div>
            <div class="stat-sub">JWT TOKENS LIVE</div>
        </div>
        <div class="stat c-green">
            <div class="stat-label">System Status</div>
            <div class="stat-value mono" style="font-size:16px;margin-top:4px;">NOMINAL</div>
            <div class="stat-sub">ALL SYSTEMS GREEN</div>
        </div>
        <div class="stat c-blue">
            <div class="stat-label">Token TTL</div>
            <div class="stat-value mono danger" id="ttl"><?= $expiresIn ?>s</div>
            <div class="stat-sub">EXPIRES <?= $expiresAt ?></div>
        </div>
    </div>

    <!-- LEFT: JWT + Status -->
    <div class="panel">
        <div class="panel-title">Admin Telemetry</div>
        <div class="trow"><span class="tlbl">Admin ID</span><span class="tval danger">adm_<?= str_pad($userId, 4, '0', STR_PAD_LEFT) ?></span></div>
        <div class="trow"><span class="tlbl">Callsign</span><span class="tval"><?= $username ?></span></div>
        <div class="trow"><span class="tlbl">Role</span><span class="tval danger">ADMIN</span></div>
        <div class="trow"><span class="tlbl">Token TTL</span><span class="tval warn" id="ttlmid"><?= $expiresIn ?>s</span></div>
        <div class="trow"><span class="tlbl">Expires at</span><span class="tval"><?= $expiresAt ?></span></div>
        <div class="trow"><span class="tlbl">Signature</span><span class="tval ok">&#10003; VERIFIED</span></div>
        <div class="trow"><span class="tlbl">Algorithm</span><span class="tval">HS256</span></div>

        <div style="margin-top:22px;">
            <div class="panel-title">Session Flags</div>
            <div class="flag-status">
                <div class="flag-pill green-flag"><span class="fp"></span>GREEN FLAG</div>
                <div class="flag-pill safety"><span class="fp"></span>SC READY</div>
            </div>
        </div>

        <div style="margin-top:6px;">
            <div class="panel-title">Race Control Privileges</div>
            <div class="priv-item"><span class="priv-dot"></span><span class="priv-text">View all registered drivers</span></div>
            <div class="priv-item"><span class="priv-dot"></span><span class="priv-text">Manage driver roles</span></div>
            <div class="priv-item"><span class="priv-dot"></span><span class="priv-text">Access system settings</span></div>
            <div class="priv-item"><span class="priv-dot"></span><span class="priv-text">Invalidate user sessions</span></div>
        </div>
    </div>

    <!-- MIDDLE: Driver standings -->
    <div class="panel">
        <div class="panel-title gold">Championship Standings</div>
        <div class="driver-item">
            <div class="driver-pos">1</div>
            <div class="driver-details">
                <div class="driver-name">VERSTAPPEN</div>
                <div class="driver-team">RED BULL RACING</div>
            </div>
            <div class="driver-pts">374 PTS</div>
        </div>
        <div class="driver-item">
            <div class="driver-pos">2</div>
            <div class="driver-details">
                <div class="driver-name">LECLERC</div>
                <div class="driver-team">SCUDERIA FERRARI</div>
            </div>
            <div class="driver-pts">299 PTS</div>
        </div>
        <div class="driver-item" style="border-color:#E8002D;">
            <div class="driver-pos" style="color:#E8002D;">3</div>
            <div class="driver-details">
                <div class="driver-name" style="color:#F5F5F5;"><?= strtoupper($username) ?></div>
                <div class="driver-team">ADMIN TEAM</div>
            </div>
            <div class="driver-pts" style="color:#E8002D;">215 PTS</div>
        </div>
        <div class="driver-item">
            <div class="driver-pos">4</div>
            <div class="driver-details">
                <div class="driver-name">NORRIS</div>
                <div class="driver-team">MCLAREN</div>
            </div>
            <div class="driver-pts">189 PTS</div>
        </div>
        <div class="driver-item">
            <div class="driver-pos">5</div>
            <div class="driver-details">
                <div class="driver-name">SAINZ</div>
                <div class="driver-team">SCUDERIA FERRARI</div>
            </div>
            <div class="driver-pts">175 PTS</div>
        </div>
        <div class="driver-item">
            <div class="driver-pos">6</div>
            <div class="driver-details">
                <div class="driver-name">HAMILTON</div>
                <div class="driver-team">MERCEDES AMG</div>
            </div>
            <div class="driver-pts">158 PTS</div>
        </div>
    </div>

    <!-- RIGHT: Radio log + system -->
    <div class="panel">
        <div class="panel-title">Team Radio Log</div>
        <div class="radio-item">
            <div class="radio-time" id="t1">38:22</div>
            <div class="radio-msg"><strong>RACE CTRL:</strong> DRS enabled on main straight, gap confirmed.</div>
        </div>
        <div class="radio-item">
            <div class="radio-time">36:14</div>
            <div class="radio-msg"><strong>VERSTAPPEN:</strong> Box this lap, box this lap — tyres are gone.</div>
        </div>
        <div class="radio-item">
            <div class="radio-time">34:51</div>
            <div class="radio-msg"><strong>LECLERC:</strong> Can we push? I have pace here.</div>
        </div>
        <div class="radio-item">
            <div class="radio-time">31:09</div>
            <div class="radio-msg"><strong>RACE CTRL:</strong> Safety car in this lap, green flag next lap.</div>
        </div>
        <div class="radio-item">
            <div class="radio-time">28:33</div>
            <div class="radio-msg"><strong><?= strtoupper($username) ?>:</strong> System check nominal, all admins accounted for.</div>
        </div>

        <div style="margin-top:22px;">
            <div class="panel-title">System Monitor</div>
            <div class="trow"><span class="tlbl">DB Connection</span><span class="tval ok">&#10003; LIVE</span></div>
            <div class="trow"><span class="tlbl">JWT Issuer</span><span class="tval">loginform_app</span></div>
            <div class="trow"><span class="tlbl">Active Sessions</span><span class="tval warn" id="sess2">14</span></div>
            <div class="trow"><span class="tlbl">Last Login</span><span class="tval" id="logtime">--:--:--</span></div>
        </div>
    </div>

    <div class="bottom-bar">
        <div class="bb-item"><div class="bb-lbl">Circuit</div><div class="bb-val">MONACO GP</div></div>
        <div class="bb-item"><div class="bb-lbl">Weather</div><div class="bb-val">27&deg;C DRY</div></div>
        <div class="bb-item"><div class="bb-lbl">Total Laps</div><div class="bb-val">57</div></div>
        <div class="bb-item"><div class="bb-lbl">SC Deployments</div><div class="bb-val">1</div></div>
        <div class="bb-item"><div class="bb-lbl">Incidents</div><div class="bb-val">0</div></div>
        <div class="bb-item"><div class="bb-lbl">Pit Stops</div><div class="bb-val">34</div></div>
        <div class="bb-item"><div class="bb-lbl">Session Time</div><div class="bb-val" id="clock">00:00</div></div>
    </div>

</div>

<script>
    let ttl = <?= $expiresIn ?>;
    setInterval(() => {
        ttl = Math.max(0, ttl - 1);
        const v = ttl + 's';
        document.getElementById('ttl').textContent = v;
        document.getElementById('ttlmid').textContent = v;
    }, 1000);

    let secs = 0;
    setInterval(() => {
        secs++;
        document.getElementById('clock').textContent = Math.floor(secs/60).toString().padStart(2,'0') + ':' + (secs%60).toString().padStart(2,'0');
    }, 1000);

    document.getElementById('logtime').textContent = '<?= $expiresAt ?>';

    let sess = 14;
    setInterval(() => {
        sess = Math.max(10, Math.min(20, sess + Math.round((Math.random() - 0.5) * 2)));
        document.getElementById('sessions').textContent = sess;
        document.getElementById('sess2').textContent = sess;
    }, 3000);
</script>
</body>
</html>