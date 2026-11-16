<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'COPS - Chemotherapy Ordering & Preparation System')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/red-cross.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f4f7fb;
            color: #1e293b;
        }
        .cops-navbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #0284c7 100%);
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.12);
        }
        .card-medical {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.1);
        }
        .badge-high-alert {
            background-color: #dc2626;
            color: #fff;
            font-weight: 600;
        }
        .nav-pill-role {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.45rem 0.9rem;
        }
        .nav-pill-role.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff !important;
        }
    </style>
    @yield('css_before')
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark cops-navbar py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('home') }}">
            <img src="{{ asset('images/spr.jpg') }}" alt="SPR" width="40" height="40" class="rounded-circle border border-2 border-white">
            <div>
                <div class="fs-5 lh-1">COPS&trade; Oncology</div>
                <small class="fw-normal opacity-75" style="font-size: 0.75rem;">Chemotherapy Ordering &amp; Preparation System</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#copsNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="copsNavbar">
            @auth
                <ul class="navbar-nav me-auto ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link nav-pill-role {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-grid-1x2-fill me-1"></i> หน้าหลัก
                        </a>
                    </li>

                    @if(auth()->user()->canAccessDoctor())
                        <li class="nav-item">
                            <a class="nav-link nav-pill-role {{ request()->routeIs('doctor.*') ? 'active' : '' }}" href="{{ route('doctor.index') }}">
                                <i class="bi bi-heart-pulse-fill me-1"></i> ระบบแพทย์ (สั่งยา)
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->canAccessPharmacist())
                        <li class="nav-item">
                            <a class="nav-link nav-pill-role {{ request()->routeIs('pharmacist.*') ? 'active' : '' }}" href="{{ route('pharmacist.index') }}">
                                <i class="bi bi-capsule-pill me-1"></i> ระบบเภสัชกร (รับคำสั่ง/เตรียมยา)
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->canAccessNurse())
                        <li class="nav-item">
                            <a class="nav-link nav-pill-role {{ request()->routeIs('nurse.*') ? 'active' : '' }}" href="{{ route('nurse.index') }}">
                                <i class="bi bi-clipboard2-pulse-fill me-1"></i> ระบบพยาบาล (ให้ยา)
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link nav-pill-role {{ request()->routeIs('regimens.*') ? 'active' : '' }}" href="{{ route('regimens.index') }}">
                                <i class="bi bi-journal-medical me-1"></i> จัดการสูตรยา Regimen
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <li class="nav-item">
                            <a class="nav-link nav-pill-role {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="bi bi-people-fill me-1"></i> จัดการผู้ใช้ (onc_users)
                            </a>
                        </li>
                    @endif
                </ul>

                <div class="d-flex align-items-center gap-2 text-white">
                    <span class="badge {{ auth()->user()->roleBadgeClass() }} px-2 py-1">
                        {{ strtoupper(auth()->user()->role) }}
                    </span>
                    <div class="dropdown">
                        <a href="#" class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-1" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                                <small class="text-muted">{{ auth()->user()->roleTitle() }}</small>
                                @if(auth()->user()->license_no)
                                    <div><small class="badge bg-light text-dark border mt-1">{{ auth()->user()->license_no }}</small></div>
                                @endif
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-medium mt-1">
                                        <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>

<main class="container my-4 flex-grow-1">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> กรุณาตรวจสอบข้อมูลอีกครั้ง:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="bg-white border-top py-3 mt-auto">
    <div class="container d-flex flex-wrap justify-content-between align-items-center text-secondary small">
        <div>
            <strong>COPS&trade;</strong> &mdash; Chemotherapy Ordering &amp; Preparation System (Database: <code>oncospr</code>)
        </div>
        <div>
            Copyright &copy; {{ date('Y') }} Sterile Products Pharmacy Service, SPR Hospital.
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('js_after')
</body>
</html>
