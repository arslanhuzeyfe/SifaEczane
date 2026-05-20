<?php
/**
 * PRESENTATION LAYER (UI) — Ödeme İşlemleri
 * ❗ Doğrudan SQL YOKTUR. BL → DAL → Stored Procedure.
 */
$pageTitle = 'Ödemeler';
require_once 'bl/EczaneBL.php';

$bl    = new EczaneBL();
$mesaj = '';
$hata  = '';
$islem = $_POST['islem'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($islem === 'ekle') {
            $bl->odemeEkle(
                $_POST['odeme_id'], $_POST['musteri_id'],
                $_POST['tarih'], floatval($_POST['tutar']),
                $_POST['tur'], $_POST['aciklama']
            );
            $mesaj = "✅ Ödeme kaydedildi. (sp_OdemeEkle çağrıldı)";

        } elseif ($islem === 'guncelle') {
            $bl->odemeGuncelle(
                $_POST['odeme_id'], $_POST['musteri_id'],
                $_POST['tarih'], floatval($_POST['tutar']),
                $_POST['tur'], $_POST['aciklama']
            );
            $mesaj = "✅ Ödeme güncellendi. (sp_OdemeGuncelle çağrıldı)";

        } elseif ($islem === 'sil') {
            $bl->odemeSil($_POST['odeme_id']);
            $mesaj = "✅ Ödeme silindi. (sp_OdemeSil çağrıldı)";
        }
    } catch (Exception $e) {
        $hata = "❌ Hata: " . $e->getMessage();
    }
}

try {
    $musteriler = $bl->musterileriGetir();
    $odemeler   = $bl->odemeDetayGetir();
} catch (Exception $e) {
    $musteriler = [];
    $odemeler   = [];
    $hata = "❌ Veriler yüklenemedi: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="page-header">
    <h1>💰 Ödeme İşlemleri</h1>
</div>

<?php if ($mesaj): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mesaj) ?></div>
<?php endif; ?>
<?php if ($hata): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
<?php endif; ?>

<div class="card">
    <h2>➕ Ödeme Ekle / Güncelle</h2>
    <form method="POST" id="odemeForm">
        <div class="form-grid">
            <div class="form-group">
                <label>Ödeme ID *</label>
                <input type="text" name="odeme_id" id="odeme_id" required placeholder="O001" value="O<?= date('His') ?>">
            </div>
            <div class="form-group">
                <label>Müşteri *</label>
                <select name="musteri_id" id="musteri_id" required>
                    <option value="">-- Seçiniz --</option>
                    <?php foreach ($musteriler as $m): ?>
                        <option value="<?= htmlspecialchars($m['ID'] ?? $m['musteri_id']) ?>">
                            <?= htmlspecialchars(($m['ID'] ?? $m['musteri_id']) . ' - ' . ($m['Adi'] ?? $m['musteri_ad']) . ' ' . ($m['Soyadi'] ?? $m['musteri_soyad'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tutar (₺) *</label>
                <input type="number" name="tutar" id="tutar" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label>Tarih</label>
                <input type="datetime-local" name="tarih" value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="form-group">
                <label>Ödeme Türü</label>
                <select name="tur" id="tur">
                    <option value="Nakit">Nakit</option>
                    <option value="Kredi Karti">Kredi Kartı</option>
                    <option value="Havale">Havale</option>
                </select>
            </div>
            <div class="form-group">
                <label>Açıklama</label>
                <input type="text" name="aciklama" id="aciklama" placeholder="Ek not...">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" name="islem" value="ekle" class="btn btn-success">➕ Ekle</button>
            <button type="submit" name="islem" value="guncelle" class="btn btn-warning">✏️ Güncelle</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('odemeForm').reset()">🔄 Temizle</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>📋 Ödeme Listesi (sp_OdemeDetay — JOIN sorgusu)</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Ödeme ID</th>
                    <th>Müşteri</th>
                    <th>Tarih</th>
                    <th>Tutar (₺)</th>
                    <th>Tür</th>
                    <th>Açıklama</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($odemeler)): ?>
                    <tr><td colspan="7" style="text-align:center;color:#999;">Kayıt bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($odemeler as $o): ?>
                    <tr>
                        <td><?= htmlspecialchars($o['OdemeID']) ?></td>
                        <td><?= htmlspecialchars($o['Musteri']) ?></td>
                        <td><?= htmlspecialchars($o['Tarih']) ?></td>
                        <td><strong><?= number_format(floatval($o['Tutar']), 2, ',', '.') ?></strong></td>
                        <td><?= htmlspecialchars($o['Tur']) ?></td>
                        <td><?= htmlspecialchars($o['Aciklama']) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="doldurOdeme(<?= htmlspecialchars(json_encode($o)) ?>)">✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Bu ödemeyi silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="odeme_id" value="<?= htmlspecialchars($o['OdemeID']) ?>">
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
        Toplam: <?= count($odemeler) ?> ödeme | Stored Procedure: sp_OdemeDetay (2 tablo JOIN)
    </p>
</div>

<script>
function doldurOdeme(o) {
    document.getElementById('odeme_id').value   = o.OdemeID || '';
    document.getElementById('tutar').value       = o.Tutar || 0;
    document.getElementById('tur').value         = o.Tur || 'Nakit';
    document.getElementById('aciklama').value    = o.Aciklama || '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php require_once 'footer.php'; ?>
