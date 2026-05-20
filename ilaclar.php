<?php
/**
 * PRESENTATION LAYER (UI) — İlaç İşlemleri
 * ❗ Bu sayfada doğrudan SQL YOKTUR. Sadece BL katmanı çağrılır.
 */
$pageTitle = 'İlaçlar';
require_once 'bl/EczaneBL.php';

$bl     = new EczaneBL();
$mesaj  = '';
$hata   = '';
$islem  = $_POST['islem'] ?? '';
$filtre = $_GET['filtre'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($islem === 'ekle') {
            $bl->ilacEkle(
                $_POST['ilac_id'], $_POST['ad'], $_POST['kategori'],
                $_POST['uretici'], floatval($_POST['fiyat']),
                intval($_POST['stok']), $_POST['skt'],
                intval($_POST['recete'])
            );
            $mesaj = "✅ İlaç başarıyla eklendi. (sp_IlacEkle çağrıldı — tg_ilac_skt_kontrol trigger'ı çalıştı)";

        } elseif ($islem === 'guncelle') {
            $bl->ilacGuncelle(
                $_POST['ilac_id'], $_POST['ad'], $_POST['kategori'],
                $_POST['uretici'], floatval($_POST['fiyat']),
                intval($_POST['stok']), $_POST['skt'],
                intval($_POST['recete'])
            );
            $mesaj = "✅ İlaç güncellendi. (sp_IlacGuncelle çağrıldı)";

        } elseif ($islem === 'sil') {
            $bl->ilacSil($_POST['ilac_id']);
            $mesaj = "✅ İlaç silindi. (sp_IlacSil çağrıldı)";
        }
    } catch (PDOException $e) {
        // Trigger hatası (tg_ilac_skt_kontrol)
        $hata = "❌ Trigger Hatası: " . $e->getMessage();
    } catch (Exception $e) {
        $hata = "❌ Hata: " . $e->getMessage();
    }
}

try {
    $ilaclar = !empty($filtre) ? $bl->ilacAra($filtre) : $bl->ilaclariGetir();
} catch (Exception $e) {
    $ilaclar = [];
    $hata = "❌ Liste yüklenemedi: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="page-header">
    <h1>💊 İlaç İşlemleri</h1>
</div>

<?php if ($mesaj): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mesaj) ?></div>
<?php endif; ?>
<?php if ($hata): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
<?php endif; ?>

<div class="alert alert-info">
    💡 <strong>Trigger:</strong> İlaç eklerken son kullanma tarihi bugünden önceyse
    <code>tg_ilac_skt_kontrol</code> trigger'ı hata fırlatır ve eklemeyi engeller.
</div>

<div class="card">
    <h2>➕ İlaç Ekle / Güncelle</h2>
    <form method="POST" id="ilacForm">
        <div class="form-grid">
            <div class="form-group">
                <label>İlaç ID *</label>
                <input type="text" name="ilac_id" id="ilac_id" required placeholder="I001">
            </div>
            <div class="form-group">
                <label>İlaç Adı *</label>
                <input type="text" name="ad" id="ad" required>
            </div>
            <div class="form-group">
                <label>Kategori *</label>
                <select name="kategori" id="kategori">
                    <option value="Agri Kesici">Ağrı Kesici</option>
                    <option value="Antibiyotik">Antibiyotik</option>
                    <option value="Vitamin">Vitamin</option>
                    <option value="Tansiyon">Tansiyon</option>
                    <option value="Diyabet">Diyabet</option>
                    <option value="Antidepresan">Antidepresan</option>
                    <option value="Mide">Mide</option>
                    <option value="Diger">Diğer</option>
                </select>
            </div>
            <div class="form-group">
                <label>Üretici Firma</label>
                <input type="text" name="uretici" id="uretici">
            </div>
            <div class="form-group">
                <label>Birim Fiyat (₺)</label>
                <input type="number" name="fiyat" id="fiyat" step="0.01" min="0" value="0">
            </div>
            <div class="form-group">
                <label>Stok Miktarı</label>
                <input type="number" name="stok" id="stok" min="0" value="0">
            </div>
            <div class="form-group">
                <label>Son Kullanma Tarihi</label>
                <input type="date" name="skt" id="skt" required>
            </div>
            <div class="form-group">
                <label>Reçete Gerekli</label>
                <select name="recete" id="recete">
                    <option value="0">Hayır</option>
                    <option value="1">Evet</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" name="islem" value="ekle" class="btn btn-success">➕ Ekle</button>
            <button type="submit" name="islem" value="guncelle" class="btn btn-warning">✏️ Güncelle</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('ilacForm').reset()">🔄 Temizle</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>📋 İlaç Listesi (sp_IlaclarHepsi / sp_IlacBul)</h2>
    <form method="GET" class="search-bar">
        <input type="text" name="filtre" value="<?= htmlspecialchars($filtre) ?>" placeholder="İlaç adı, kategori, üretici ile ara...">
        <button type="submit" class="btn btn-primary">🔍 Ara</button>
        <a href="ilaclar.php" class="btn btn-secondary">Tümü</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Kategori</th>
                    <th>Üretici</th>
                    <th>Fiyat (₺)</th>
                    <th>Stok</th>
                    <th>SKT</th>
                    <th>Reçete</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ilaclar)): ?>
                    <tr><td colspan="9" style="text-align:center;color:#999;">Kayıt bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($ilaclar as $i): ?>
                    <?php
                        $stok = $i['Stok'] ?? $i['ilac_stok'] ?? 0;
                        $skt  = $i['SonKullanma'] ?? $i['ilac_son_kullanma'] ?? '';
                        $sktYakin = (!empty($skt) && strtotime($skt) < strtotime('+90 days'));
                    ?>
                    <tr style="<?= $stok < 10 ? 'background:#fdebd0;' : ($sktYakin ? 'background:#fadbd8;' : '') ?>">
                        <td><?= htmlspecialchars($i['ID'] ?? $i['ilac_id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($i['Ad'] ?? $i['ilac_ad'] ?? '') ?></td>
                        <td><?= htmlspecialchars($i['Kategori'] ?? $i['ilac_kategori'] ?? '') ?></td>
                        <td><?= htmlspecialchars($i['Uretici'] ?? $i['ilac_uretici'] ?? '') ?></td>
                        <td><?= number_format(floatval($i['Fiyat'] ?? $i['ilac_fiyat'] ?? 0), 2, ',', '.') ?></td>
                        <td><strong><?= intval($stok) ?></strong></td>
                        <td><?= htmlspecialchars($skt) ?></td>
                        <td>
                            <?php $rec = $i['ReceteGerekli'] ?? ($i['ilac_recete'] ?? 0); ?>
                            <span class="badge <?= ($rec === 'Evet' || $rec == 1) ? 'badge-evet' : 'badge-hayir' ?>">
                                <?= ($rec === 'Evet' || $rec == 1) ? 'Evet' : 'Hayır' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="doldurIlac(<?= htmlspecialchars(json_encode($i)) ?>)">✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Bu ilacı silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="ilac_id" value="<?= htmlspecialchars($i['ID'] ?? $i['ilac_id'] ?? '') ?>">
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
        Toplam: <?= count($ilaclar) ?> kayıt |
        🟠 Sarı = Stok < 10 | 🔴 Kırmızı = SKT 90 günden az
    </p>
</div>

<script>
function doldurIlac(i) {
    document.getElementById('ilac_id').value  = i.ID || i.ilac_id || '';
    document.getElementById('ad').value       = i.Ad || i.ilac_ad || '';
    document.getElementById('kategori').value = i.Kategori || i.ilac_kategori || '';
    document.getElementById('uretici').value  = i.Uretici || i.ilac_uretici || '';
    document.getElementById('fiyat').value    = i.Fiyat || i.ilac_fiyat || 0;
    document.getElementById('stok').value     = i.Stok || i.ilac_stok || 0;
    document.getElementById('skt').value      = i.SonKullanma || i.ilac_son_kullanma || '';
    var rec = i.ReceteGerekli || i.ilac_recete || 0;
    document.getElementById('recete').value   = (rec === 'Evet' || rec == 1) ? '1' : '0';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php require_once 'footer.php'; ?>
