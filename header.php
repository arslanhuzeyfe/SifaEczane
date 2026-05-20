<?php
// Aktif sayfa kontrolü
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Şifa Eczanesi' ?> - Eczane Otomasyon</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo">
        <span>💊</span> Şifa Eczanesi
    </a>
    <nav>
        <a href="index.php"      class="<?= $current == 'index'      ? 'active' : '' ?>">🏠 Ana Sayfa</a>
        <a href="musteriler.php" class="<?= $current == 'musteriler' ? 'active' : '' ?>">👤 Müşteriler</a>
        <a href="ilaclar.php"    class="<?= $current == 'ilaclar'    ? 'active' : '' ?>">💊 İlaçlar</a>
        <a href="satislar.php"   class="<?= $current == 'satislar'   ? 'active' : '' ?>">🛒 Satışlar</a>
        <a href="odemeler.php"   class="<?= $current == 'odemeler'   ? 'active' : '' ?>">💰 Ödemeler</a>
        <a href="raporlar.php"   class="<?= $current == 'raporlar'   ? 'active' : '' ?>">📊 Raporlar</a>
    </nav>
</div>

<div class="container">
