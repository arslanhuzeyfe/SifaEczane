<?php
/**
 * PRESENTATION LAYER (UI) — Raporlar
 * ❗ Bu sayfa MySQL FUNCTION'larını kullanır:
 *    - fn_MusteriBakiye: Müşterinin toplam bakiyesi
 *    - fn_AylikCiro:     Belirtilen ayın cirosu
 *    - fn_SKTKalanGun:   İlacın son kullanma tarihine kalan gün
 * ❗ Doğrudan SQL YOKTUR. BL → DAL → MySQL Function çağrısı.
 */
$pageTitle = 'Raporlar';
require_once 'bl/EczaneBL.php';

$bl = new EczaneBL();

// Bakiye hesaplama
$bakiyeSonuc  = null;
$bakiyeMusteri = '';
if (isset($_GET['bakiye_musteri']) && !empty($_GET['bakiye_musteri'])) {
    try {
        $bakiyeMusteri = $_GET['bakiye_musteri'];
        $bakiyeSonuc   = $bl->musteriBakiye($bakiyeMusteri);
    } catch (Exception $e) {
        $bakiyeSonuc = 'HATA: ' . $e->getMessage();
    }
}

// Aylık ciro
$ciroSonuc = null;
$ciroYil   = $_GET['ciro_yil'] ?? date('Y');
$ciroAy    = $_GET['ciro_ay']  ?? date('n');
if (isset($_GET['ciro_hesapla'])) {
    try {
        $ciroSonuc = $bl->aylikCiro(intval($ciroYil), intval($ciroAy));
    } catch (Exception $e) {
        $ciroSonuc = 'HATA: ' . $e->getMessage();
    }
}

// Müşteri listesi
try {
    $musteriler = $bl->musterileriGetir();
    $ilaclar    = $bl->ilaclariGetir();
} catch (Exception $e) {
    $musteriler = [];
    $ilaclar    = [];
}

// SKT kalan gün hesapla (her ilaç için)
$sktVeriler = [];
foreach ($ilaclar as $i) {
    $ilacId = $i['ID'] ?? $i['ilac_id'] ?? '';
    if (!empty($ilacId)) {
        try {
            $kalan = $bl->sktKalanGun($ilacId);
            $sktVeriler[] = [
                'id'      => $ilacId,
                'ad'      => $i['Ad'] ?? $i['ilac_ad'] ?? '',
                'skt'     => $i['SonKullanma'] ?? $i['ilac_son_kullanma'] ?? '',
                'kalan'   => $kalan,
                'stok'    => $i['Stok'] ?? $i['ilac_stok'] ?? 0,
            ];
        } catch (Exception $e) {}
    }
}

// Kalan güne göre sırala
usort($sktVeriler, function($a, $b) { return $a['kalan'] - $b['kalan']; });

require_once 'header.php';
?>

<div class="page-header">
    <h1>📊 Raporlar (MySQL FUNCTION Kullanımı)</h1>
</div>

<div class="alert alert-info">
    💡 Bu sayfadaki tüm hesaplamalar MySQL <strong>kullanıcı tanımlı fonksiyonları (FUNCTION)</strong>
    tarafından yapılmaktadır. PHP tarafında hesaplama yoktur; sonuçlar doğrudan MySQL'den gelir.
</div>

<div class="rapor-grid">

    <!-- FUNCTION 1: fn_MusteriBakiye -->
    <div class="rapor-card">
        <h3>👤 Müşteri Bakiye Sorgulama</h3>
        <p style="font-size:0.85rem;color:#666;margin-bottom:0.75rem;">
            MySQL Function: <code>fn_MusteriBakiye(musteri_id)</code><br>
            Toplam satış borcu ile toplam ödemelerin farkını hesaplar.
        </p>
        <form method="GET">
            <div class="form-group" style="margin-bottom:0.5rem;">
                <label>Müşteri Seçin</label>
                <select name="bakiye_musteri" required>
                    <option value="">-- Seçiniz --</option>
                    <?php foreach ($musteriler as $m): ?>
                        <option value="<?= htmlspecialchars($m['ID'] ?? $m['musteri_id']) ?>"
                            <?= $bakiyeMusteri === ($m['ID'] ?? $m['musteri_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(($m['ID'] ?? $m['musteri_id']) . ' - ' . ($m['Adi'] ?? $m['musteri_ad']) . ' ' . ($m['Soyadi'] ?? $m['musteri_soyad'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Bakiye Sorgula</button>
        </form>

        <?php if ($bakiyeSonuc !== null && !is_string($bakiyeSonuc)): ?>
            <div class="rapor-result <?= $bakiyeSonuc >= 0 ? 'positive' : 'negative' ?>">
                <?= number_format($bakiyeSonuc, 2, ',', '.') ?> ₺
            </div>
            <p style="font-size:0.82rem;color:#666;margin-top:0.3rem;">
                <?php if ($bakiyeSonuc > 0): ?>
                    Müşterinin <?= number_format($bakiyeSonuc, 2, ',', '.') ?> ₺ alacağı var (fazla ödeme).
                <?php elseif ($bakiyeSonuc < 0): ?>
                    Müşterinin <?= number_format(abs($bakiyeSonuc), 2, ',', '.') ?> ₺ borcu var.
                <?php else: ?>
                    Müşterinin hesabı kapalıdır (borç = ödeme).
                <?php endif; ?>
            </p>
        <?php elseif (is_string($bakiyeSonuc)): ?>
            <div class="alert alert-danger" style="margin-top:0.5rem;"><?= htmlspecialchars($bakiyeSonuc) ?></div>
        <?php endif; ?>
    </div>

    <!-- FUNCTION 2: fn_AylikCiro -->
    <div class="rapor-card">
        <h3>📈 Aylık Ciro Raporu</h3>
        <p style="font-size:0.85rem;color:#666;margin-bottom:0.75rem;">
            MySQL Function: <code>fn_AylikCiro(yil, ay)</code><br>
            Belirtilen ayın toplam satış cirosunu hesaplar.
        </p>
        <form method="GET">
            <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                <div class="form-group" style="flex:1;">
                    <label>Yıl</label>
                    <input type="number" name="ciro_yil" value="<?= intval($ciroYil) ?>" min="2020" max="2030">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Ay</label>
                    <select name="ciro_ay">
                        <?php
                        $aylar = ['','Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
                        for ($a = 1; $a <= 12; $a++):
                        ?>
                            <option value="<?= $a ?>" <?= intval($ciroAy) === $a ? 'selected' : '' ?>><?= $aylar[$a] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <input type="hidden" name="ciro_hesapla" value="1">
            <button type="submit" class="btn btn-primary btn-sm">Ciro Hesapla</button>
        </form>

        <?php if ($ciroSonuc !== null && !is_string($ciroSonuc)): ?>
            <div class="rapor-result"><?= number_format($ciroSonuc, 2, ',', '.') ?> ₺</div>
            <p style="font-size:0.82rem;color:#666;margin-top:0.3rem;">
                <?= $aylar[intval($ciroAy)] ?> <?= intval($ciroYil) ?> toplam ciro
            </p>
        <?php endif; ?>
    </div>

</div>

<!-- FUNCTION 3: fn_SKTKalanGun -->
<div class="card" style="margin-top:1.5rem;">
    <h2>⏰ Son Kullanma Tarihi Takibi (fn_SKTKalanGun)</h2>
    <p style="font-size:0.85rem;color:#666;margin-bottom:1rem;">
        MySQL Function: <code>fn_SKTKalanGun(ilac_id)</code> — Her ilacın son kullanma tarihine kaç gün kaldığını hesaplar.
        <br>🔴 Kırmızı: SKT geçmiş veya 30 günden az | 🟠 Sarı: 90 günden az | 🟢 Yeşil: 90+ gün
    </p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>İlaç ID</th>
                    <th>İlaç Adı</th>
                    <th>Son Kullanma</th>
                    <th>Kalan Gün</th>
                    <th>Stok</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sktVeriler)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#999;">İlaç verisi bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($sktVeriler as $s): ?>
                    <?php
                        $kalan = intval($s['kalan']);
                        if ($kalan <= 30) {
                            $renk = 'background:#fadbd8;';
                            $durum = '🔴 KRİTİK';
                        } elseif ($kalan <= 90) {
                            $renk = 'background:#fdebd0;';
                            $durum = '🟠 DİKKAT';
                        } else {
                            $renk = '';
                            $durum = '🟢 Normal';
                        }
                        if ($kalan <= 0) {
                            $durum = '⛔ GEÇMİŞ';
                        }
                    ?>
                    <tr style="<?= $renk ?>">
                        <td><?= htmlspecialchars($s['id']) ?></td>
                        <td><?= htmlspecialchars($s['ad']) ?></td>
                        <td><?= htmlspecialchars($s['skt']) ?></td>
                        <td><strong><?= $kalan ?> gün</strong></td>
                        <td><?= intval($s['stok']) ?></td>
                        <td><?= $durum ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Stoğu Azalan İlaçlar -->
<?php
try {
    $azalanlar = $bl->stoguAzalanlar(20);
} catch (Exception $e) {
    $azalanlar = [];
}
?>
<?php if (!empty($azalanlar)): ?>
<div class="card">
    <h2>⚠️ Stoğu Azalan İlaçlar (sp_StoguAzalanIlaclar — eşik: 20)</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>İlaç ID</th>
                    <th>Ad</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Fiyat (₺)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($azalanlar as $a): ?>
                <tr style="background:#fdebd0;">
                    <td><?= htmlspecialchars($a['ilac_id']) ?></td>
                    <td><?= htmlspecialchars($a['ilac_ad']) ?></td>
                    <td><?= htmlspecialchars($a['ilac_kategori']) ?></td>
                    <td><strong><?= intval($a['ilac_stok']) ?></strong></td>
                    <td><?= number_format(floatval($a['ilac_fiyat']), 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
