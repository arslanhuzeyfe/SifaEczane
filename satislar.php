<?php
/**
 * PRESENTATION LAYER (UI) — Satış İşlemleri
 * ❗ Doğrudan SQL YOKTUR. BL → DAL → Stored Procedure.
 * ❗ Satış eklenince MySQL Trigger'ları otomatik tetiklenir:
 *    - tg_satis_kontrol: Stok ve SKT kontrolü (BEFORE INSERT)
 *    - tg_stok_azalt:    Stok düşürme (AFTER INSERT)
 */
$pageTitle = 'Satışlar';
require_once 'bl/EczaneBL.php';

$bl    = new EczaneBL();
$mesaj = '';
$hata  = '';
$islem = $_POST['islem'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($islem === 'ekle') {
            $adet  = intval($_POST['adet']);
            $fiyat = floatval($_POST['fiyat']);

            // ❗ sp_SatisEkle çağrılır → tg_satis_kontrol BEFORE INSERT trigger'ı
            // stok yetersizse veya SKT geçmişse SIGNAL SQLSTATE '45000' fırlatır.
            // Başarılıysa → tg_stok_azalt AFTER INSERT trigger'ı stoğu azaltır.
            $bl->satisEkle(
                $_POST['satis_id'], $_POST['musteri_id'], $_POST['ilac_id'],
                $adet, $_POST['tarih'], $fiyat, $_POST['odeme']
            );
            $mesaj = "✅ Satış kaydedildi! (sp_SatisEkle → tg_satis_kontrol ✓ → tg_stok_azalt ✓)";

        } elseif ($islem === 'sil') {
            $bl->satisSil($_POST['satis_id']);
            $mesaj = "✅ Satış silindi. (sp_SatisSil çağrıldı)";
        }
    } catch (PDOException $e) {
        // Trigger hatası yakalanır (stok yetersiz / SKT geçmiş)
        $hata = "❌ TRIGGER HATASI: " . $e->getMessage();
    } catch (Exception $e) {
        $hata = "❌ Hata: " . $e->getMessage();
    }
}

// Müşteri ve İlaç listeleri (dropdown için)
try {
    $musteriler = $bl->musterileriGetir();
    $ilaclar    = $bl->ilaclariGetir();
    $satislar   = $bl->satisDetayGetir();
} catch (Exception $e) {
    $musteriler = [];
    $ilaclar    = [];
    $satislar   = [];
    $hata = "❌ Veriler yüklenemedi: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="page-header">
    <h1>🛒 Satış İşlemleri</h1>
</div>

<?php if ($mesaj): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mesaj) ?></div>
<?php endif; ?>
<?php if ($hata): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
<?php endif; ?>

<div class="alert alert-warning">
    ⚡ <strong>Aktif Trigger'lar:</strong><br>
    1️⃣ <code>tg_satis_kontrol</code> (BEFORE INSERT) — Stok yetersizse veya son kullanma tarihi geçmişse satış ENGELLENİR.<br>
    2️⃣ <code>tg_stok_azalt</code> (AFTER INSERT) — Satış başarılıysa ilaç stoğu otomatik azaltılır.
</div>

<div class="card">
    <h2>➕ Yeni Satış Kaydı</h2>
    <form method="POST" id="satisForm">
        <div class="form-grid">
            <div class="form-group">
                <label>Satış ID *</label>
                <input type="text" name="satis_id" required placeholder="S001" value="S<?= date('His') ?>">
            </div>
            <div class="form-group">
                <label>Müşteri *</label>
                <select name="musteri_id" required>
                    <option value="">-- Seçiniz --</option>
                    <?php foreach ($musteriler as $m): ?>
                        <option value="<?= htmlspecialchars($m['ID'] ?? $m['musteri_id']) ?>">
                            <?= htmlspecialchars(($m['ID'] ?? $m['musteri_id']) . ' - ' . ($m['Adi'] ?? $m['musteri_ad']) . ' ' . ($m['Soyadi'] ?? $m['musteri_soyad'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>İlaç *</label>
                <select name="ilac_id" id="ilac_select" required onchange="fiyatHesapla()">
                    <option value="" data-fiyat="0">-- Seçiniz --</option>
                    <?php foreach ($ilaclar as $i): ?>
                        <option value="<?= htmlspecialchars($i['ID'] ?? $i['ilac_id']) ?>"
                                data-fiyat="<?= floatval($i['Fiyat'] ?? $i['ilac_fiyat'] ?? 0) ?>"
                                data-stok="<?= intval($i['Stok'] ?? $i['ilac_stok'] ?? 0) ?>">
                            <?= htmlspecialchars(($i['ID'] ?? $i['ilac_id']) . ' - ' . ($i['Ad'] ?? $i['ilac_ad']) . ' (Stok: ' . ($i['Stok'] ?? $i['ilac_stok']) . ' | ₺' . number_format(floatval($i['Fiyat'] ?? $i['ilac_fiyat'] ?? 0), 2) . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Adet *</label>
                <input type="number" name="adet" id="adet" min="1" value="1" required onchange="fiyatHesapla()" oninput="fiyatHesapla()">
            </div>
            <div class="form-group">
                <label>Toplam Fiyat (₺)</label>
                <input type="number" name="fiyat" id="fiyat" step="0.01" min="0" value="0" required>
            </div>
            <div class="form-group">
                <label>Tarih</label>
                <input type="datetime-local" name="tarih" value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="form-group">
                <label>Ödeme Türü</label>
                <select name="odeme">
                    <option value="Nakit">Nakit</option>
                    <option value="Kredi Karti">Kredi Kartı</option>
                    <option value="Havale">Havale</option>
                    <option value="Veresiye">Veresiye</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" name="islem" value="ekle" class="btn btn-success">🛒 Satış Yap</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>📋 Satış Detayları (sp_SatisDetay — JOIN sorgusu)</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Satış ID</th>
                    <th>Müşteri</th>
                    <th>Sigorta</th>
                    <th>İlaç</th>
                    <th>Kategori</th>
                    <th>Adet</th>
                    <th>Birim ₺</th>
                    <th>Toplam ₺</th>
                    <th>Ödeme</th>
                    <th>Tarih</th>
                    <th>Sil</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($satislar)): ?>
                    <tr><td colspan="11" style="text-align:center;color:#999;">Kayıt bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($satislar as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['SatisID']) ?></td>
                        <td><?= htmlspecialchars($s['Musteri']) ?></td>
                        <td><span class="badge badge-<?= strtolower($s['Sigorta'] ?? 'yok') ?>"><?= htmlspecialchars($s['Sigorta']) ?></span></td>
                        <td><?= htmlspecialchars($s['Ilac']) ?></td>
                        <td><?= htmlspecialchars($s['Kategori']) ?></td>
                        <td><?= $s['Adet'] ?></td>
                        <td><?= number_format($s['BirimFiyat'], 2, ',', '.') ?></td>
                        <td><strong><?= number_format($s['ToplamFiyat'], 2, ',', '.') ?></strong></td>
                        <td><?= htmlspecialchars($s['OdemeTuru']) ?></td>
                        <td><?= htmlspecialchars($s['SatisTarihi']) ?></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Bu satışı silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="satis_id" value="<?= htmlspecialchars($s['SatisID']) ?>">
                                <button type="submit" name="islem" value="sil" class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top:0.75rem;color:#999;font-size:0.82rem;">
        Toplam: <?= count($satislar) ?> satış | Stored Procedure: sp_SatisDetay (3 tablo JOIN)
    </p>
</div>

<script>
function fiyatHesapla() {
    var sel  = document.getElementById('ilac_select');
    var opt  = sel.options[sel.selectedIndex];
    var birim = parseFloat(opt.getAttribute('data-fiyat')) || 0;
    var adet  = parseInt(document.getElementById('adet').value) || 1;
    document.getElementById('fiyat').value = (birim * adet).toFixed(2);
}
</script>

<?php require_once 'footer.php'; ?>
