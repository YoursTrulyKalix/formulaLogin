<?php
session_start();
require_once '../config/jwt.php';

$authUser = getAuthenticatedUser();
$username = $authUser ? htmlspecialchars($authUser->username) : 'Unknown';
$role     = $authUser ? htmlspecialchars($authUser->role)     : 'none';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Exclusion Zone — 403</title>
    <style>
        .container { max-width: 500px; min-height: auto; }
        .form-box  { text-align: center; }

        .flag-stripe {
            display: flex;
            gap: 4px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .flag-stripe span {
            width: 22px; height: 22px;
            background: var(--f1-white);
        }
        .flag-stripe span:nth-child(even) { background: var(--f1-black); border: 1px solid #333; }
    </style>
</head>
<body>
<div class="container" style="padding:0;">
    <div class="form-content" style="padding:50px 44px;">
        <div class="form-box active">

            <div class="flag-stripe">
                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>
            </div>

            <p class="error-code">403</p>
            <p class="error-title">Exclusion Zone</p>
            <p class="error-detail">
                You do not have clearance for this sector.<br>
                This area is restricted to a different role.
            </p>

            <div class="role-info">
                <div class="row">
                    <span class="label">Driver</span>
                    <span class="value"><?= $username ?></span>
                </div>
                <div class="row">
                    <span class="label">Role</span>
                    <span class="value"><?= $role ?></span>
                </div>
                <div class="row">
                    <span class="label">Access</span>
                    <span class="value" style="color:var(--f1-red);">&#9632; DENIED</span>
                </div>
            </div>

            <div class="btn-group">
                <?php if ($authUser): ?>
                    <?php if ($authUser->role === 'admin'): ?>
                        <a href="admin_dashboard.php" style="flex:1;">
                            <button type="button">Admin Panel</button>
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" style="flex:1;">
                            <button type="button">My Dashboard</button>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="../logout.php" style="flex:1;">
                    <button type="button" class="btn-secondary">Logout</button>
                </a>
            </div>

        </div>
    </div>
</div>
</body>
</html>