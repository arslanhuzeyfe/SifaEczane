<?php
/**
 * PRESENTATION LAYER (UI) — Ana Sayfa / Dashboard
 * ❗ Doğrudan SQL kullanılmaz. İş katmanı (BL) çağrılır.
 */
$pageTitle = 'Ana Sayfa';
require_once 'header.php';
?>

<div class="page-header">
    <h1>🏠 Şifa Eczanesi Otomasyon Sistemi</h1>
</div>

<div class="tier-info">
    <strong>📐 N-Katmanlı Mimari:</strong>
    Presentation Layer (Bu Arayüz) → Business Layer (bl/EczaneBL.php) →
    Data Access Layer (dal/EczaneDAL.php) → MySQL Stored Procedures
    <br>
    <strong>❗ Hiçbir katmanda doğrudan SELECT/INSERT/UPDATE/DELETE komutu kullanılmaz.</strong>
</div>

<div class="dashboard-grid">
    <a href="musteriler.php" class="dash-card">
        <div class="icon blue">👤</div>
        <div class="info">
            <h3>Müşteriler</h3>
            <p>Müşteri kayıtlarını yönet, ekle, güncelle, sil</p>
        </div>
    </a>

    <a href="ilaclar.php" class="dash-card">
        <div class="icon green">💊</div>
        <div class="info">
            <h3>İlaçlar</h3>
            <p>İlaç stok yönetimi, son kullanma tarihi takibi</p>
        </div>
    </a>

    <a href="satislar.php" class="dash-card">
        <div class="icon orange">🛒</div>
        <div class="info">
            <h3>Satışlar</h3>
            <p>Satış kaydı oluştur (Trigger ile stok kontrolü)</p>
        </div>
    </a>

    <a href="odemeler.php" class="dash-card">
        <div class="icon red">💰</div>
        <div class="info">
            <h3>Ödemeler</h3>
            <p>Müşteri ödemelerini kaydet ve takip et</p>
        </div>
    </a>

    <a href="raporlar.php" class="dash-card">
        <div class="icon purple">📊</div>
        <div class="info">
            <h3>Raporlar</h3>
            <p>Bakiye, ciro, SKT raporları (Function ile)</p>
        </div>
    </a>
</div>

<div class="card">
    <h2>📋 Sistem Özellikleri</h2>
    <table>
        <thead>
            <tr>
                <th>Özellik</th>
                <th>Açıklama</th>
                <th>Konum</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Stored Procedure</strong></td>
                <td>Tüm CRUD işlemleri (Insert/Update/Delete/Select) her tablo için</td>
                <td>DAL → sp_MusteriEkle, sp_IlacEkle, sp_SatisEkle ...</td>
            </tr>
            <tr>
                <td><strong>Function (3 adet)</strong></td>
                <td>fn_MusteriBakiye, fn_AylikCiro, fn_SKTKalanGun</td>
                <td>Raporlar sayfasından çağrılır</td>
            </tr>
            <tr>
                <td><strong>Trigger (3 adet)</strong></td>
                <td>tg_satis_kontrol (stok+SKT), tg_stok_azalt, tg_ilac_skt_kontrol</td>
                <td>Satış/İlaç ekleme sırasında otomatik tetiklenir</td>
            </tr>
            <tr>
                <td><strong>N-Tier Mimari</strong></td>
                <td>Presentation → Business → Data Access Layer</td>
                <td>UI (*.php) → BL (bl/) → DAL (dal/) → MySQL SP</td>
            </tr>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
