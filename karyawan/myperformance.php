<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Statistics - Kaon Employee Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #c41e3a 0%, #a01729 100%);
            color: white;
            position: fixed;
            top: 0; 
            left: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        .sidebar.collapsed {
            transform: translateX(-280px);
        }

        .main-content.collapsed {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .sidebar-header {
            padding: 2rem 1rem;
            border-bottom: 1px solid #CB001E;
            text-align: center;
            background: rgba(0,0,0,0.1);
        }

        .sidebar-logo {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }

        .sidebar-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
            font-weight: 300;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(255,255,255,0.1);
            transition: width 0.3s ease;
        }

        .nav-link:hover::before {
            width: 100%;
        }

        .nav-link:hover {
            background: #CB001E;
            border-left-color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-left-color: white;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            opacity: 0.8;
            z-index: 1;
            position: relative;
        }

        .nav-text {
            font-weight: 500;
            z-index: 1;
            position: relative;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            width: calc(100% - 280px);
        }

        .main-content.collapsed {
            margin-left: 0;
            width: 100%;
        }

        /* Segitiga caret dropdown */
.header .dropdown-toggle::after {
    margin-left: 0.5rem;
    vertical-align: middle;
    color: black;
}

/* Dropdown menu agar muncul di atas semua */
.dropdown-menu {
    z-index: 9999; 
    position: absolute !important;
}
  .dropdown-item:hover {
    background-color: #c41e3a;
    color: white;
  }

  .dropdown-item.text-danger:hover {
    background-color: #ffe5e5; /* latar belakang merah muda */
    color: #c41e3a; /* teks merah */
}

/* Sembunyikan tombol saat sidebar terbuka di desktop */
@media (min-width: 769px) {
  .sidebar:not(.collapsed) ~ .toggle-burger {
    display: none;
  }
}

        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            z-index: 1000;
            position: fixed;
        }

        .main-content.collapsed .header {
            padding-left: 5rem;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2c5aa0 0%, #c41e3a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-left: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #c41e3a 0%, #a01729 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.3);
            transition: transform 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .content {
            flex: 1;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            overflow-y: auto;
            transition: all 0.3s ease;
            margin-top: 80px;
        }

        .main-content.collapsed .content {
            padding-left: 5rem;
        }

        .toggle-burger {
            position: fixed;
            top: 1.6rem;
            left: 1.5rem;
            width: 45px;
            height: 45px;
            background: #CB001E;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            z-index: 1100;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .toggle-burger::before {
            content: '☰';
            color: white;
            font-size: 18px;
        }

        body.sidebar-collapsed .toggle-burger {
            background: #c41e3a;
            box-shadow: 0 4px 12px rgba(196, 30, 58, 0.5);
        }

        .toggle-burger:hover {
            background: #e08a8a;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Statistics Page Specific Styles */
        .stats-header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 2rem;
        }

        .stats-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2c5aa0 0%, #c41e3a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .stats-subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .stats-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .filter-select {
            padding: 0.6rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #c41e3a;
        }

        .download-btn {
            padding: 0.6rem 1.5rem;
            background: linear-gradient(135deg, #c41e3a 0%, #a01729 100%);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #c41e3a, #2c5aa0);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .stat-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c5aa0;
        }

        .stat-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-achieve {
            background: #d4edda;
            color: #155724;
        }

        .badge-nonachieve {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-progress {
            background: #fff3cd;
            color: #856404;
        }

        .stat-metrics {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .metric {
            text-align: center;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2c5aa0 0%, #c41e3a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .metric-label {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .progress-bar-container {
            margin-top: 1rem;
        }

        .progress-bar-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #c41e3a, #2c5aa0);
            transition: width 0.3s ease;
        }

        .chart-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 2rem;
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c5aa0;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 530px;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .summary-number {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2c5aa0 0%, #c41e3a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            z-index: 9999; 
            position: absolute !important;
        }

        .dropdown-item:hover {
            background-color: #c41e3a;
            color: white;
        }

        .dropdown-item.text-danger:hover {
            background-color: #ffe5e5;
            color: #c41e3a;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-content,
            .main-content.collapsed {
                margin-left: 0;
                width: 100%;
            }

            .sidebar {
                width: 100%;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .stats-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .summary-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .summary-stats {
                grid-template-columns: 1fr;
            }
        }

        @media print {
    body * {
        visibility: hidden;
    }

    #reportContent, #reportContent * {
        visibility: visible;
    }

    #reportContent {
        position: absolute;
        left: 0;
        top: 0;
    }
}

/* Opsi khusus saat digunakan oleh html2pdf */
.pdf-export {
    background: white;
    color: black;
    padding: 20px;
    font-size: 12px;
    max-width: 800px;
    margin: auto;
}

.pdf-export h2 {
    font-size: 18px;
    margin-bottom: 10px;
}

.pdf-export img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 0 auto 20px;
}

.page-break {
    page-break-after: always;
  }

  .modal-custom {
      display: none;
      position: fixed;
      z-index: 1050;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      backdrop-filter: blur(6px);
      overflow-y: auto;
      padding: 2rem 1rem;
    }

    .task-info {
      background: #f1f3f5;
      padding: 1rem;
      border-left: 4px solid #2c5aa0;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }

    .form-label {
      font-weight: 600;
      
    }

    .submit-btn {
      background: linear-gradient(135deg, #c41e3a, #a01729);
      color: white;
      border: none;
      width: 100%;
      padding: 0.75rem;
      border-radius: 0.75rem;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .submit-btn:hover {
      background: #a01729;
    }

    .help-text {
      font-size: 0.6rem;
      color: #666;
      font-style: italic;
    }

    @media (max-width: 576px) {
      .input-group {
        flex-direction: column;
      }
    }

    /* Modal icon */
.modal-icon {
    width: 50px;
    height: 50px;
    background: #dc3545;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.modal-icon i {
    font-size: 1.2rem;
    color: white;
}

/* Tombol logout */
.btn-logout {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-logout:hover {
    background: #bb2d3b;
    border-color: #bb2d3b;
    transform: translateY(-1px);
}

/* Tombol cancel */
.btn-cancel {
    border-color: #dc3545;
    color: #dc3545;
}

.btn-cancel:hover {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}

/* Modal tampilan dan animasi */
.modal-dialog {
    max-width: 400px;
}

.modal-content {
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-body {
    padding: 2rem;
}

.modal-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.modal-message {
    color: #666;
    margin-bottom: 1.5rem;
}

/* Efek backdrop modal */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Transisi tombol */
.btn {
    transition: all 0.2s ease;
}

    </style>
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()"></button>
    
    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-container">
                    <div class="sidebar-logo">Kaon</div>
                </div>
            </div>
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="mytasks.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">My Tasks</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="myperformance.php" class="nav-link active">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Statistics</span>
                    </a>
                </div>
            </div>
        </nav>

        <main class="main-content" id="mainContent">
            <header class="header">
                <div>
                    <h1 class="header-title">Task Statistics</h1>
                </div>   
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2 bg-primary">FR</div>
                            <span class="fw-semibold text-dark">Fajar</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="profile.php">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="content">
                 <!-- Summary Statistics -->
                <div class="summary-stats">
                    <div class="summary-card">
                        <div class="summary-number">18</div>
                        <div class="summary-label">Total Tasks</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">12</div>
                        <div class="summary-label">Tasks Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">1</div>
                        <div class="summary-label">Tasks Not Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">5</div>
                        <div class="summary-label">In Progress</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">85%</div>
                        <div class="summary-label">Success Rate</div>
                    </div>
                </div>
                
                <div id="reportContent" class="pdf-export" style="display:none;">
                <!-- Summary Statistics -->
                <div class="summary-stats">
                    <div class="summary-card">
                        <div class="summary-number">18</div>
                        <div class="summary-label">Total Tasks</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">12</div>
                        <div class="summary-label">Tasks Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">1</div>
                        <div class="summary-label">Tasks Not Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">5</div>
                        <div class="summary-label">In Progress</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">85%</div>
                        <div class="summary-label">Success Rate</div>
                    </div>
                </div>
                </div>

                <!-- Controls -->
                <div class="stats-header">
                    <h2 class="stats-title">Task Performance Analytics</h2>
                    <p class="stats-subtitle">Comprehensive statistics of your task performance and achievements</p>
                    
                    <div class="stats-controls">
                        <select class="filter-select" id="taskFilter" onchange="filterByTask()">
                            <option value="all">All Tasks</option>
                            <option value="pelurusan_kpi">Pelurusan KPI</option>
                            <option value="fallout_cons">Fallout CONS/EBIS</option>
                            <option value="up_odp">UP ODP</option>
                            <option value="cek_port">Cek Port BT</option>
                            <option value="val_tiang">Val Tiang</option>
                            <option value="odp_kendala">ODP Kendala</option>
                            <option value="validasi_ftm">Validasi FTM</option>
                            <option value="pelurusan_gdoc">Pelurusan GDOC HS</option>
                            <option value="fallout_uim">Fallout UIM DAMAN</option>
                            <option value="pelurusan_ebis">Pelurusan EBIS</option>
                            <option value="pelurusan_aso">Pelurusan GDOC ASO</option>
                            <option value="e2e">E2E</option>
                        </select>
                        
                        <select class="filter-select" id="statusFilter" onchange="filterByStatus()">
                            <option value="all">All Status</option>
                            <option value="achieve">Achieved</option>
                            <option value="nonachieve">Not Achieved</option>
                            <option value="progress">In Progress</option>
                        </select>
                        
                        <button class="download-btn" onclick="downloadStatistics()">
                            <i class="bi bi-download"></i>
                            Download Report
                        </button>
                    </div>
                </div>

                <!-- Individual Task Statistics -->
                <div class="stats-grid" id="statsGrid">
                    <!-- Task cards will be populated by JavaScript -->
                </div>

                <!-- Task Performance Chart -->
                <div class="chart-container">
                    <h3 class="chart-title">Task Performance Overview</h3>
                    <div class="chart-wrapper">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Target vs Achieved Chart -->
                <div class="chart-container">
                    <h3 class="chart-title">Target vs Completed Tasks</h3>
                    <div class="chart-wrapper">
                        <canvas id="targetVsCompletedChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="modal-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <p class="modal-message">Are you sure you want to sign out?</p>
                    
                    <div class="d-flex gap-2 justify-content-center flex-column flex-sm-row">
                        <button type="button" class="btn btn-danger btn-logout" onclick="confirmLogout()">
                            Yes, Logout
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-cancel" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Task data with targets and achievements
        const taskData = [
            {
                id: 'pelurusan_kpi',
                name: 'Pelurusan KPI',
                type: 'Daily Target',
                target: 50,
                unit: 'WO/Hari',
                achieved: 57,
                status: 'achieve',
                totalTasks: 3,
                completedTasks: 57,
                description: 'KPI alignment and verification'
            },
            {
                id: 'fallout_cons',
                name: 'Fallout CONS/EBIS',
                type: 'Solution Based',
                target: 'All FO solved',
                unit: 'FO',
                achieved: 51,
                status: 'achieve',
                totalTasks: 2,
                completedTasks: 51,
                description: 'CONS/EBIS fallout resolution'
            },
            {
                id: 'up_odp',
                name: 'UP ODP',
                type: 'Solution Based',
                target: 'All FO solved',
                unit: 'FO',
                achieved: 45,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 51,
                description: 'ODP upgrade and maintenance'
            },
            {
                id: 'cek_port',
                name: 'Cek Port BT',
                type: 'Solution Based',
                target: 'All FO solved',
                unit: 'FO',
                achieved: 38,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 38,
                description: 'BT port checking and validation'
            },
            {
                id: 'val_tiang',
                name: 'Val Tiang',
                type: 'Validation',
                target: 'No fixed target',
                unit: 'Validations',
                achieved: 32,
                status: 'progress',
                totalTasks: 4,
                completedTasks: 32,
                description: 'Pole validation and rack verification'
            },
            {
                id: 'odp_kendala',
                name: 'ODP Kendala',
                type: 'Problem Solving',
                target: 'No fixed target',
                unit: 'Issues',
                achieved: 28,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 28,
                description: 'ODP problem resolution'
            },
            {
                id: 'validasi_ftm',
                name: 'Validasi FTM',
                type: 'Equipment Check',
                target: '1 RACK EA, 1 RACK OA',
                unit: 'Racks',
                achieved: 27,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 27,
                description: 'FTM equipment validation'
            },
                        {
                id: 'pelurusan_gdoc',
                name: 'Pelurusan GDOC HS',
                type: 'Documentation',
                target: '40 FO/Day',
                unit: 'Docs',
                achieved: 5,
                status: 'progress',
                totalTasks: 2,
                completedTasks: 1,
                description: 'GDOC HS document validation'
            },
            {
                id: 'fallout_uim',
                name: 'Fallout UIM DAMAN',
                type: 'Solution Based',
                target: 'All Issues Resolved',
                unit: 'FO',
                achieved: 27,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 27,
                description: 'Resolving fallout on UIM DAMAN'
            },
            {
                id: 'pelurusan_ebis',
                name: 'Pelurusan EBIS',
                type: 'Documentation',
                target: 'Completed EBIS Update',
                unit: 'Docs',
                achieved: 39,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 39,
                description: 'EBIS data alignment and update'
            },
            {
                id: 'pelurusan_aso',
                name: 'Pelurusan GDOC ASO',
                type: 'Documentation',
                target: 'Completed ASO Update',
                unit: 'Docs',
                achieved: 20,
                status: 'nonachieve',
                totalTasks: 1,
                completedTasks: 20,
                description: 'ASO documentation alignment'
            },
            {
                id: 'e2e',
                name: 'E2E',
                type: 'End-to-End Process',
                target: 'Monitor All Processes',
                unit: 'Process',
                achieved: 0,
                status: 'progress',
                totalTasks: 2,
                completedTasks: 0,
                description: 'End-to-End monitoring and verification'
            }
        ];

        // Tambahkan variabel global untuk menyimpan referensi chart
let performanceChart = null;
let targetVsCompletedChart = null;

// Perbaiki fungsi toggleSidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);

    if (isCollapsed) {
        body.classList.add('sidebar-collapsed');
    } else {
        body.classList.remove('sidebar-collapsed');
    }

    // Tambahkan delay untuk resize chart setelah transisi selesai
    setTimeout(() => {
        resizeCharts();
    }, 400); // 400ms sesuai dengan durasi transisi CSS
}

// Perbaiki fungsi closeSidebar
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');

    // Tambahkan delay untuk resize chart setelah transisi selesai
    setTimeout(() => {
        resizeCharts();
    }, 400);
}

// Fungsi untuk resize semua chart
function resizeCharts() {
    if (performanceChart) {
        performanceChart.resize();
    }
    if (targetVsCompletedChart) {
        targetVsCompletedChart.resize();
    }
}

// Perbaiki inisialisasi chart performance
function initPerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Destroy chart yang ada jika ada
    if (performanceChart) {
        performanceChart.destroy();
    }
    
    performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: taskData.map(task => task.name),
            datasets: [{
                label: 'Achieved',
                data: taskData.map(task => task.achieved),
                backgroundColor: '#2c5aa0'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Tambahkan ini
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Perbaiki fungsi renderTargetVsCompletedChart
function renderTargetVsCompletedChart() {
    const ctx = document.getElementById('targetVsCompletedChart').getContext('2d');

    // Destroy chart yang ada jika ada
    if (targetVsCompletedChart) {
        targetVsCompletedChart.destroy();
    }

    const labels = taskData.map(task => task.name);
    const targets = taskData.map(task => {
        const t = parseInt(task.target);
        return isNaN(t) ? 0 : t;
    });

    const completed = taskData.map(task => task.completedTasks);

    targetVsCompletedChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target',
                    data: targets,
                    backgroundColor: 'rgba(44, 90, 160, 0.6)',
                    borderColor: 'rgba(44, 90, 160, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(196, 30, 58, 0.6)',
                    borderColor: 'rgba(196, 30, 58, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Tambahkan ini
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

// Perbaiki window.onload
window.onload = () => {
    renderStatsGrid('all', 'all');
    initPerformanceChart(); // Ganti dari kode inline
    renderTargetVsCompletedChart();
};

// Tambahkan event listener untuk window resize
window.addEventListener('resize', function() {
    // Delay untuk memastikan transisi selesai
    setTimeout(() => {
        resizeCharts();
    }, 100);
    
    // Handle mobile/desktop switch
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

        // Function to close sidebar
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;

            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        }

        // Function to navigate with sidebar close
        function navigateWithCloseSidebar(url, event) {
            event.preventDefault();
            
            const currentPage = window.location.pathname.split('/').pop();
            
            // Jika bukan halaman yang sama
            if (url !== currentPage) {
                // Tutup sidebar langsung
                closeSidebar();
                
                // Navigasi langsung tanpa delay
                window.location.href = url;
            }
        }

        // Close sidebar when clicking outside of it (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const burgerBtn = document.getElementById('burgerBtn');
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile && !sidebar.classList.contains('collapsed')) {
                if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
                    closeSidebar();
                }
            }
        });

        // Close sidebar on window resize if switching to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Initialize sidebar as closed on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;
            
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        });

        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('logoutModal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            }
        });

        function filterByTask() {
            const taskFilter = document.getElementById('taskFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            renderStatsGrid(taskFilter, statusFilter);
        }

        function filterByStatus() {
            const taskFilter = document.getElementById('taskFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            renderStatsGrid(taskFilter, statusFilter);
        }

        function renderStatsGrid(taskFilter, statusFilter) {
            const grid = document.getElementById('statsGrid');
            grid.innerHTML = '';

            const filtered = taskData.filter(task => {
                const taskMatch = taskFilter === 'all' || task.id === taskFilter;
                const statusMatch = statusFilter === 'all' || task.status === statusFilter;
                return taskMatch && statusMatch;
            });

            filtered.forEach(task => {
                const card = document.createElement('div');
                card.className = 'stat-card';

                const statusBadge = {
                    'achieve': 'badge-achieve',
                    'nonachieve': 'badge-nonachieve',
                    'progress': 'badge-progress'
                }[task.status];

                card.innerHTML = `
                    <div class="stat-card-header">
                        <div class="stat-card-title">${task.name}</div>
                        <span class="stat-badge ${statusBadge}">${task.status.toUpperCase()}</span>
                    </div>
                    <div class="stat-metrics">
                        <div class="metric">
                            <div class="metric-value">${task.achieved}</div>
                            <div class="metric-label">Achieved</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${task.totalTasks}</div>
                            <div class="metric-label">Total Tasks</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${task.completedTasks}</div>
                            <div class="metric-label">Completed</div>
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-label">
                            <span>Progress</span>
                            <span>${Math.round((task.completedTasks / task.totalTasks) * 100)}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${Math.round((task.completedTasks / task.totalTasks) * 100)}%;"></div>
                        </div>
                    </div>
                `;

                grid.appendChild(card);
            });
        }

        function downloadStatistics() {
    const content = document.getElementById('reportContent');
    if (!content) {
        alert('Elemen #reportContent tidak ditemukan!');
        return;
    }

    content.innerHTML = ''; // Kosongkan konten sebelumnya

    const title = `<h2 style="text-align:center; margin-bottom:10px;">Task Performance Report</h2>`;
    const date = `<p style="text-align:center; margin-bottom:20px;">Generated on: ${new Date().toLocaleDateString()}</p>`;

    const summaryHTML = `
        <table border="1" cellspacing="0" cellpadding="6" style="width:100%; font-size:12px; border-collapse: collapse; margin-bottom:20px;">
            <thead style="background:#f0f0f0;">
                <tr>
                    <th>Task Name</th>
                    <th>Target</th>
                    <th>Achieved</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ${taskData.map(task => `
                    <tr>
                        <td>${task.name}</td>
                        <td>${task.target}</td>
                        <td>${task.achieved}</td>
                        <td>${task.totalTasks}</td>
                        <td>${task.completedTasks}</td>
                        <td>${task.status.toUpperCase()}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    // Tangkap grafik sebagai gambar
    const performanceCanvas = document.getElementById('performanceChart');
    const achievementCanvas = document.getElementById('targetVsCompletedChart');

    const performanceImg = performanceCanvas ? performanceCanvas.toDataURL("image/png") : '';
    const achievementImg = achievementCanvas ? achievementCanvas.toDataURL("image/png") : '';

    const chartsHTML = `
  <div class="page-break">
    <h3>Performance Chart</h3>
    <img src="${performanceImg}" style="width:100%; max-width:700px; margin-bottom:30px;">
  </div>
  <div>
    <h3>Achievement Chart</h3>
    <img src="${achievementImg}" style="width:100%; max-width:700px;">
  </div>
`;


    content.innerHTML = title + date + summaryHTML + chartsHTML;
    content.style.display = 'block';

    html2pdf().set({
        margin: 0.5,
        filename: 'task-statistics.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 3, useCORS: true, scrollY: 0, scrollX: 0 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(content).save().then(() => {
        content.style.display = 'none';
    }).catch(err => {
        console.error('Gagal mengunduh PDF:', err);
        alert('Terjadi kesalahan saat mengunduh PDF.');
    });
}



        window.onload = () => {
            renderStatsGrid('all', 'all');
            renderTargetVsCompletedChart();
        };

        // Tambahkan setelah window.onload
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: taskData.map(task => task.name),
        datasets: [{
            label: 'Achieved',
            data: taskData.map(task => task.achieved),
            backgroundColor: '#2c5aa0'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function renderTargetVsCompletedChart() {
    const ctx = document.getElementById('targetVsCompletedChart').getContext('2d');

    const labels = taskData.map(task => task.name);
    const targets = taskData.map(task => {
    const t = parseInt(task.target);
    return isNaN(t) ? 0 : t;
});

    const completed = taskData.map(task => task.completedTasks);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target',
                    data: targets,
                    backgroundColor: 'rgba(44, 90, 160, 0.6)',
                    borderColor: 'rgba(44, 90, 160, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(196, 30, 58, 0.6)',
                    borderColor: 'rgba(196, 30, 58, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmLogout() {
            // Redirect logic here
            window.location.href = '../logout.php';
            hideLogoutModal();
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });
    </script>
</body>
</html>
