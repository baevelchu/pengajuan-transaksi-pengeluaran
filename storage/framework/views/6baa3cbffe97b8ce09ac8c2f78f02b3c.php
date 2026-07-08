<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> · Sistem Pengajuan Transaksi Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --brand-1:#4f46e5;
            --brand-2:#7c3aed;
            --brand-3:#06b6d4;
            --ink-900:#0f172a;
            --ink-700:#334155;
            --ink-500:#64748b;
            --surface:#f6f7fb;
            --card:#ffffff;
            --border:#e9ecf3;
            --shadow-sm:0 1px 2px rgba(15,23,42,.04),0 1px 3px rgba(15,23,42,.06);
            --shadow-md:0 4px 6px rgba(15,23,42,.04),0 10px 20px -6px rgba(79,70,229,.10);
            --radius:14px;
        }
        *{box-sizing:border-box;}
        html,body{height:100%;}
        body{
            font-family:'Inter',system-ui,-apple-system,sans-serif;
            background:var(--surface);
            color:var(--ink-900);
            -webkit-font-smoothing:antialiased;
        }
        .app-shell{display:flex; min-height:100vh;}

        /* Sidebar */
        .sidebar{
            width:264px; min-width:264px;
            background:linear-gradient(195deg, #150f30 0%, #1a1140 45%, #241354 100%);
            color:#e6e4f5;
            display:flex; flex-direction:column;
            position:sticky; top:0; height:100vh;
            padding:24px 18px;
            z-index:20;
        }
        .brand{
            display:flex; align-items:center; gap:12px;
            padding:6px 8px 26px 8px;
            border-bottom:1px solid rgba(255,255,255,.08);
            margin-bottom:22px;
        }
        .brand-icon{
            width:42px;height:42px;border-radius:12px;
            background:linear-gradient(135deg,var(--brand-3),var(--brand-1));
            display:flex;align-items:center;justify-content:center;
            font-size:20px; color:#fff; box-shadow:0 6px 16px rgba(79,70,229,.45);
            flex-shrink:0;
        }
        .brand-text{font-family:'Poppins',sans-serif; font-weight:700; font-size:.98rem; line-height:1.25; color:#fff;}
        .brand-text small{display:block; font-weight:400; font-size:.72rem; color:#a5a0d1; font-family:'Inter',sans-serif;}

        .nav-section-title{
            font-size:.68rem; text-transform:uppercase; letter-spacing:.08em;
            color:#8b85bf; padding:0 10px; margin:18px 0 8px;
            font-weight:600;
        }
        .side-link{
            display:flex; align-items:center; gap:12px;
            padding:11px 12px; border-radius:10px;
            color:#cfcbee; text-decoration:none; font-size:.92rem; font-weight:500;
            margin-bottom:4px; transition:all .15s ease;
        }
        .side-link i{font-size:1.05rem; width:20px; text-align:center; opacity:.85;}
        .side-link:hover{background:rgba(255,255,255,.07); color:#fff;}
        .side-link.active{
            background:linear-gradient(135deg, rgba(124,58,237,.55), rgba(6,182,212,.35));
            color:#fff; box-shadow:var(--shadow-sm);
        }

        .sidebar-footer{margin-top:auto; padding-top:16px; border-top:1px solid rgba(255,255,255,.08);}
        .user-chip{
            display:flex; align-items:center; gap:10px; padding:10px; border-radius:12px;
            background:rgba(255,255,255,.06);
        }
        .avatar-badge{
            width:38px;height:38px;border-radius:50%;
            background:linear-gradient(135deg,var(--brand-1),var(--brand-2));
            display:flex;align-items:center;justify-content:center;
            color:#fff; font-weight:700; font-size:.85rem; flex-shrink:0;
        }
        .user-chip .name{font-size:.85rem; font-weight:600; color:#fff; line-height:1.15;}
        .user-chip .role{font-size:.7rem; color:#a5a0d1;}
        .logout-btn{
            width:100%; margin-top:10px; border:1px solid rgba(255,255,255,.15);
            background:transparent; color:#cfcbee; border-radius:10px; padding:8px;
            font-size:.82rem; transition:.15s;
        }
        .logout-btn:hover{background:rgba(255,255,255,.08); color:#fff;}

        /* Main area */
        .main-area{flex:1; min-width:0; display:flex; flex-direction:column;}
        .topbar{
            background:rgba(255,255,255,.85); backdrop-filter:blur(10px);
            border-bottom:1px solid var(--border);
            padding:16px 32px; position:sticky; top:0; z-index:10;
            display:flex; align-items:center; justify-content:space-between;
        }
        .topbar h1{font-family:'Poppins',sans-serif; font-size:1.15rem; font-weight:700; margin:0; color:var(--ink-900);}
        .topbar .subtitle{font-size:.8rem; color:var(--ink-500); margin:0;}
        .content-wrap{padding:28px 32px 48px; max-width:1400px; width:100%; margin:0 auto;}

        /* Cards */
        .card{
            border:1px solid var(--border); border-radius:var(--radius);
            box-shadow:var(--shadow-sm); background:var(--card);
        }
        .card-header{background:transparent; border-bottom:1px solid var(--border); font-weight:600; padding:16px 20px;}
        .card-body{padding:22px;}

        /* Buttons */
        .btn{border-radius:10px; font-weight:600; font-size:.88rem; padding:9px 18px; transition:.15s;}
        .btn-primary{background:linear-gradient(135deg,var(--brand-1),var(--brand-2)); border:none; box-shadow:0 4px 12px rgba(79,70,229,.35);}
        .btn-primary:hover{filter:brightness(1.08); transform:translateY(-1px); box-shadow:0 6px 16px rgba(79,70,229,.45);}
        .btn-success{background:linear-gradient(135deg,#10b981,#059669); border:none;}
        .btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626); border:none;}
        .btn-outline-secondary{border-color:var(--border); color:var(--ink-700);}
        .btn-outline-primary{border-color:var(--brand-1); color:var(--brand-1);}
        .btn-sm{padding:6px 12px; font-size:.8rem;}

        /* Tables */
        .table{margin-bottom:0;}
        .table thead th{
            font-size:.72rem; text-transform:uppercase; letter-spacing:.05em;
            color:var(--ink-500); font-weight:700; border-bottom:2px solid var(--border);
            padding:12px 14px; background:#fafbfd;
        }
        .table tbody td{padding:14px; vertical-align:middle; font-size:.9rem; border-color:var(--border);}
        .table tbody tr{transition:.12s;}
        .table-hover tbody tr:hover{background:#f8f7ff;}

        /* Badges */
        .badge{font-weight:600; font-size:.72rem; padding:6px 11px; border-radius:8px; letter-spacing:.01em;}
        .badge.bg-secondary{background:#eef0f5!important; color:var(--ink-700)!important;}
        .badge.bg-info{background:#e0f2fe!important; color:#0369a1!important;}
        .badge.bg-warning{background:#fef3c7!important; color:#b45309!important;}
        .badge.bg-success{background:#d1fae5!important; color:#047857!important;}
        .badge.bg-danger{background:#fee2e2!important; color:#b91c1c!important;}

        /* Alerts */
        .alert{border:none; border-radius:12px; font-size:.9rem;}
        .alert-success{background:#ecfdf5; color:#065f46;}
        .alert-danger{background:#fef2f2; color:#991b1b;}

        /* Forms */
        .form-control, .form-select{
            border-radius:10px; border-color:var(--border); font-size:.9rem;
            padding:10px 14px;
        }
        .form-control:focus, .form-select:focus{border-color:var(--brand-1); box-shadow:0 0 0 3px rgba(79,70,229,.12);}
        .form-label{font-weight:600; font-size:.85rem; color:var(--ink-700); margin-bottom:6px;}
        .form-text{font-size:.78rem;}

        /* Stat cards (dashboard) */
        .stat-card{
            border-radius:var(--radius); padding:20px 22px; color:#fff; position:relative; overflow:hidden;
            box-shadow:var(--shadow-md);
        }
        .stat-card .stat-icon{
            width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.18);
            display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:14px;
        }
        .stat-card .stat-value{font-family:'Poppins',sans-serif; font-size:1.6rem; font-weight:700; margin:0;}
        .stat-card .stat-label{font-size:.78rem; opacity:.85; margin:0;}

        ::-webkit-scrollbar{width:8px; height:8px;}
        ::-webkit-scrollbar-thumb{background:#c7c9d9; border-radius:8px;}

        @media (max-width: 992px){
            .sidebar{position:fixed; left:-280px; transition:.25s; box-shadow:var(--shadow-md);}
            .sidebar.open{left:0;}
            .content-wrap{padding:20px 16px 40px;}
            .topbar{padding:14px 16px;}
        }
    </style>
</head>
<body>
<?php if(auth()->guard()->check()): ?>
<?php
    $role = auth()->user()->role;
    $roleLabels = ['staff'=>'Staff','spv'=>'Supervisor','manager'=>'Manager','direktur'=>'Direktur','finance'=>'Finance'];
    $current = request()->route()?->getName();
?>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="brand-text">Pengajuan Transaksi<small>Sistem Persetujuan Digital</small></div>
        </div>

        <div class="nav-section-title">Menu</div>
        <?php if($role === 'staff'): ?>
            <a href="<?php echo e(route('pengajuan.index')); ?>" class="side-link <?php echo e($current==='pengajuan.index' ? 'active':''); ?>"><i class="bi bi-clock-history"></i> Riwayat Pengajuan</a>
            <a href="<?php echo e(route('pengajuan.create')); ?>" class="side-link <?php echo e($current==='pengajuan.create' ? 'active':''); ?>"><i class="bi bi-file-earmark-plus"></i> Buat Pengajuan</a>
        <?php elseif(in_array($role, ['spv','manager','direktur'])): ?>
            <a href="<?php echo e(route('approval.index', $role)); ?>" class="side-link <?php echo e($current==='approval.index' ? 'active':''); ?>"><i class="bi bi-check2-square"></i> Antrian Approval</a>
        <?php elseif($role === 'finance'): ?>
            <a href="<?php echo e(route('finance.index')); ?>" class="side-link <?php echo e($current==='finance.index' ? 'active':''); ?>"><i class="bi bi-wallet2"></i> Antrian Finance</a>
        <?php endif; ?>

        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="avatar-badge"><?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?></div>
                <div>
                    <div class="name"><?php echo e(auth()->user()->name); ?></div>
                    <div class="role"><?php echo e($roleLabels[$role] ?? $role); ?></div>
                </div>
            </div>
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </aside>

    <div class="main-area">
        <div class="topbar">
            <div>
                <h1><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
                <p class="subtitle">Sistem Pengajuan Transaksi Pengeluaran</p>
            </div>
            <button class="btn btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="content-wrap">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>
<?php else: ?>
    <?php echo $__env->yieldContent('content'); ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\arlia\Downloads\Compressed\pengajuan-transaksi-pengeluaran\resources\views/layouts/app.blade.php ENDPATH**/ ?>