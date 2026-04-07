<?php
// header.php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#667eea">
    <title>Şantiye Yönetimi</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            color: #fff;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .navbar-brand i {
            margin-right: 8px;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            transition: 0.3s;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
            color: #fff;
        }
        .dropdown-menu {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .dropdown-item {
            color: rgba(255,255,255,0.9);
        }
        .dropdown-item:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .user-info {
            color: #fff;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 15px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">

        <a class="navbar-brand" href="index.php">
            <i class="bi bi-box-seam"></i> Şantiye Yönetimi
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <!-- Ana Sayfa -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house-door"></i> Ana Sayfa
                    </a>
                </li>

                <!-- Malzeme -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam"></i> Malzeme
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="malzemeler.php">Malzemeler</a></li>
                        <li><a class="dropdown-item" href="ekle.php">Malzeme Ekle</a></li>
                    </ul>
                </li>

                <!-- Araç -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-truck"></i> Araç
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="araclar.php">Araçlar</a></li>
                        <li><a class="dropdown-item" href="arac_ekle.php">Araç Ekle</a></li>
                    </ul>
                </li>

                <!-- Ayarlar -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Ayarlar
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="kategoriler.php">Kategoriler</a></li>
                        <li><a class="dropdown-item" href="lokasyon.php">Lokasyonlar</a></li>
                        <li><a class="dropdown-item" href="kullanici_ekle.php">Kullanıcılar</a></li>
                    </ul>
                </li>

                <!-- Rapor -->
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-file-earmark-text"></i> Raporlar
                    </a>
                </li>

                <!-- Çıkış -->
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Çıkış
                    </a>
                </li>

            </ul>

            <!-- Kullanıcı -->
            <?php if (isset($_SESSION['kullanici'])): ?>
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($_SESSION['kullanici']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>