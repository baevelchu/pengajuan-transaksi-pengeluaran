@extends('layouts.app')
@section('title', 'Login')
@section('content')
<style>
    body{ background:radial-gradient(1200px 600px at 10% 0%, #1a1140 0%, #0f0b28 45%, #0a0720 100%); min-height:100vh; }
    .login-shell{
        min-height:100vh; display:flex; align-items:stretch; font-family:'Inter',sans-serif;
    }
    .login-visual{
        flex:1.1; position:relative; overflow:hidden;
        background:linear-gradient(160deg,#4f46e5 0%, #7c3aed 55%, #06b6d4 120%);
        display:flex; flex-direction:column; justify-content:center; padding:60px 64px; color:#fff;
    }
    .login-visual::before{
        content:''; position:absolute; width:520px; height:520px; border-radius:50%;
        background:rgba(255,255,255,.08); top:-160px; right:-160px;
    }
    .login-visual::after{
        content:''; position:absolute; width:360px; height:360px; border-radius:50%;
        background:rgba(255,255,255,.06); bottom:-120px; left:-100px;
    }
    .login-visual .badge-glow{
        display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.15);
        padding:8px 16px; border-radius:999px; font-size:.78rem; font-weight:600; width:fit-content;
        backdrop-filter:blur(6px); margin-bottom:28px; position:relative; z-index:1;
    }
    .login-visual h2{font-family:'Poppins',sans-serif; font-weight:800; font-size:2.4rem; line-height:1.15; position:relative; z-index:1; max-width:520px;}
    .login-visual p{font-size:1rem; opacity:.9; max-width:460px; margin-top:14px; position:relative; z-index:1;}
    .flow-steps{margin-top:44px; position:relative; z-index:1;}
    .flow-steps .step{display:flex; align-items:center; gap:14px; margin-bottom:16px;}
    .flow-steps .dot{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.18); display:flex;align-items:center;justify-content:center; font-size:.95rem; flex-shrink:0;}
    .flow-steps span.label{font-size:.88rem; font-weight:500; opacity:.95;}

    .login-panel{
        flex:1; background:#ffffff; display:flex; align-items:center; justify-content:center; padding:40px 20px;
    }
    .login-card{width:100%; max-width:420px;}
    .login-card .icon-top{
        width:52px;height:52px;border-radius:14px; background:linear-gradient(135deg,#4f46e5,#7c3aed);
        display:flex;align-items:center;justify-content:center; color:#fff; font-size:1.4rem; margin-bottom:20px;
        box-shadow:0 8px 20px rgba(79,70,229,.35);
    }
    .login-card h3{font-family:'Poppins',sans-serif; font-weight:700; color:#0f172a; margin-bottom:4px;}
    .login-card p.desc{color:#64748b; font-size:.9rem; margin-bottom:26px;}
    .login-card .form-control{padding:12px 14px; border-radius:10px; border-color:#e2e5ee; font-size:.92rem;}
    .login-card .form-control:focus{border-color:#4f46e5; box-shadow:0 0 0 4px rgba(79,70,229,.1);}
    .login-card .btn-primary{
        width:100%; padding:12px; border-radius:10px; font-weight:600; border:none;
        background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 8px 20px rgba(79,70,229,.3);
    }
    .login-card .btn-primary:hover{filter:brightness(1.07);}
    .demo-box{
        margin-top:26px; background:#f8f7ff; border:1px solid #ece9ff; border-radius:12px; padding:14px 16px;
    }
    .demo-box .title{font-size:.78rem; font-weight:700; color:#4f46e5; text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;}
    .demo-box .chip{
        display:inline-block; background:#fff; border:1px solid #ece9ff; color:#4338ca; font-size:.76rem;
        padding:4px 10px; border-radius:999px; margin:2px 3px 0 0; font-family:monospace;
    }
    @media (max-width:992px){ .login-visual{display:none;} }
</style>

<div class="login-shell">
    <div class="login-visual">
        <span class="badge-glow"><i class="bi bi-shield-check"></i> Approval Workflow Otomatis</span>
        <h2>Kelola Pengajuan Transaksi Pengeluaran Lebih Cepat & Transparan</h2>
        <p>Satu platform untuk Staff, SPV, Manager, Direktur, dan Finance — dari pengajuan hingga pembayaran, semua tercatat rapi.</p>

        <div class="flow-steps">
            <div class="step"><div class="dot"><i class="bi bi-person"></i></div><span class="label">Staff mengajukan transaksi & upload dokumen</span></div>
            <div class="step"><div class="dot"><i class="bi bi-diagram-3"></i></div><span class="label">Approval berjenjang sesuai kategori & nilai</span></div>
            <div class="step"><div class="dot"><i class="bi bi-cash-stack"></i></div><span class="label">Finance memvalidasi saldo & memproses pembayaran</span></div>
        </div>
    </div>

    <div class="login-panel">
        <div class="login-card">
            <div class="icon-top"><i class="bi bi-cash-coin"></i></div>
            <h3>Selamat Datang Kembali</h3>
            <p class="desc">Masuk ke akun Anda untuk melanjutkan.</p>

            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:10px;">
                    <i class="bi bi-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem; font-weight:600; color:#334155;">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@perusahaan.com" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.85rem; font-weight:600; color:#334155;">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" style="font-size:.85rem; color:#64748b;" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
            </form>

            <div class="demo-box">
                <div class="title"><i class="bi bi-info-circle"></i> Akun Demo (password: password)</div>
                <span class="chip">staff@test.com</span>
                <span class="chip">spv@test.com</span>
                <span class="chip">manager@test.com</span>
                <span class="chip">direktur@test.com</span>
                <span class="chip">finance@test.com</span>
            </div>
        </div>
    </div>
</div>
@endsection
