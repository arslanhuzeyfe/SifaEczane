<?php
/**
 * PRESENTATION LAYER (UI) — Müşteri İşlemleri
 * ❗ Bu sayfada doğrudan SQL YOKTUR. Sadece BL katmanı çağrılır.
 */
$pageTitle = 'Müşteriler';
require_once 'bl/EczaneBL.php';

$bl      = new EczaneBL();
$mesaj   = '';
$hata    = '';
$islem   = $_POST['islem'] ?? $_GET['islem'] ?? '';
$filtre  = $_GET['filtre'] ?? '';

// --- POST İşlemleri ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($islem === 'ekle') {
            $bl->musteriEkle(
                $_POST['musteri_id'], $_POST['ad'], $_POST['soyad'],
                $_POST['tc'], $_POST['tel'], $_POST['mail'],
                $_POST['adres'], $_POST['sigorta']
            );
            $mesaj = "✅ Müşteri başarıyla eklendi. (sp_MusteriEkle çağrıldı)";

        } elseif ($islem === 'guncelle') {
            $bl->musteriGuncelle(
                $_POST['musteri_id'], $_POST['ad'], $_POST['soyad'],
                $_POST['tc'], $_POST['tel'], $_POST['mail'],
                $_POST['adres'], $_POST['sigorta']
            );
            $mesaj = "✅ Müşteri başarıyla güncellendi. (sp_MusteriGuncelle çağrıldı)";

        } elseif ($islem === 'sil') {
            $bl->musteriSil($_POST['musteri_id']);
            $mesaj = "✅ Müşteri silindi. (sp_MusteriSil çağrıldı)";
        }
    } catch (Exception $e) {
        $hata = "❌ Hata: " . $e->getMessage();
    }
}

// --- Listeyi Getir ---
try {
    if (!empty($filtre)) {
        $musteriler = $bl->musteriAra($filtre);
    } else {
        $musteriler = $bl->musterileriGetir();
    }
} catch (Exception $e) {
    $musteriler = [];
    $hata = "❌ Liste yüklenemedi: " . $e->getMessage();
}

require_once 'header.php';
?>

<div class="page-header">
    <h1>👤 Müşteri İşlemleri</h1>
</div>

<?php if ($mesaj): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mesaj) ?></div>
<?php endif; ?>
<?php if ($hata): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
<?php endif; ?>

<!-- EKLEME / GÜNCELLEME FORMU -->
<div class="card">
    <h2>➕ Müşteri Ekle / Güncelle</h2>
    <form method="POST" id="musteriForm">
        <div class="form-grid">
            <div class="form-group">
                <label>Müşteri ID *</label>
                <input type="text" name="musteri_id" id="musteri_id" required placeholder="M001">
            </div>
            <div class="form-group">
                <label>Ad *</label>
                <input type="text" name="ad" id="ad" required>
            </div>
            <div class="form-group">
                <label>Soyad *</label>
                <input type="text" name="soyad" id="soyad" required>
            </div>
            <div class="form-group">
                <label>TC Kimlik *</label>
                <input type="text" name="tc" id="tc" required maxlength="11" pattern="\d{11}" title="11 haneli TC kimlik">
            </div>
            <div class="form-group">
                <label>Telefon</label>
                <input type="text" name="tel" id="tel" placeholder="05XX XXX XX XX">
            </div>
            <div class="form-group">
                <label>E-posta</label>
                <input type="email" name="mail" id="mail">
            </div>
            <div class="form-group">
                <label>Adres</label>
                <input type="text" name="adres" id="adres">
            </div>
            <div class="form-group">
                <label>Sigorta Türü</label>
                <select name="sigorta" id="sigorta">
                    <option value="SGK">SGK</option>
                    <option value="Ozel">Özel</option>
                    <option value="Yok" selected>Yok</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" name="islem" value="ekle" class="btn btn-success">➕ Ekle</button>
            <button type="submit" name="islem" value="guncelle" class="btn btn-warning">✏️ Güncelle</button>
            <button type="button" class="btn btn-secondary" onclick="temizle()">🔄 Temizle</button>
        </div>
    </form>
</div>

<!-- ARAMA -->
<div class="card">
    <h2>📋 Müşteri Listesi (sp_MusterilerHepsi / sp_MusteriBul)</h2>
    <form method="GET" class="search-bar">
        <input type="text" name="filtre" value="<?= htmlspecialchars($filtre) ?>" placeholder="İsim, TC, telefon ile ara...">
        <button type="submit" class="btn btn-primary">🔍 Ara</button>
        <a href="musteriler.php" class="btn btn-secondary">Tümü</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Soyad</th>
                    <th>TC Kimlik</th>
                    <th>Telefon</th>
                    <th>E-posta</th>
                    <th>Adres</th>
                    <th>Sigorta</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($musteriler)): ?>
                    <tr><td colspan="9" style="text-align:center;color:#999;">Kayıt bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($musteriler as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['ID'] ?? $m['musteri_id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['Adi'] ?? $m['musteri_ad'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['Soyadi'] ?? $m['musteri_soyad'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['TC_Kimlik'] ?? $m['musteri_tckimlik'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['Telefon'] ?? $m['musteri_tel'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['Mail'] ?? $m['musteri_mail'] ?? '') ?></td>
                        <td><?= htmlspecialchars($m['Adres'] ?? $m['musteri_adres'] ?? '') ?></td>
                        <td>
                            <?php
                            $sig = $m['Sigorta'] ?? $m['musteri_sigorta'] ?? 'Yok';
                            $cls = $sig === 'SGK' ? 'badge-sgk' : ($sig === 'Ozel' ? 'badge-ozel' : 'badge-yok');
                            ?>
                            <span class="badge <?= $cls ?>"><?= htmlspecialchars($sig) ?></span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="doldur(<?= htmlspecialchars(json_encode($m)) ?>)">✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?')">
                                <input type="hidden" name="musteri_id" value="<?= htmlspecialchars($m['ID'] ?? $m['musteri_id'] ?? '') ?>">
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
        Toplam: <?= count($musteriler) ?> kayıt |
        <?= !empty($filtre) ? 'Stored Procedure: sp_MusteriBul' : 'Stored Procedure: sp_MusterilerHepsi' ?>
    </p>
</div>

<script>
function doldur(m) {
    document.getElementById('musteri_id').value = m.ID || m.musteri_id || '';
    document.getElementById('ad').value         = m.Adi || m.musteri_ad || '';
    document.getElementById('soyad').value      = m.Soyadi || m.musteri_soyad || '';
    document.getElementById('tc').value         = m.TC_Kimlik || m.musteri_tckimlik || '';
    document.getElementById('tel').value        = m.Telefon || m.musteri_tel || '';
    document.getElementById('mail').value       = m.Mail || m.musteri_mail || '';
    document.getElementById('adres').value      = m.Adres || m.musteri_adres || '';
    document.getElementById('sigorta').value    = m.Sigorta || m.musteri_sigorta || 'Yok';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function temizle() {
    document.getElementById('musteriForm').reset();
}
</script>

<?php require_once 'footer.php'; ?>
