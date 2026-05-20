<?php
/**
 * BUSINESS LAYER (BL)
 * ====================
 * İş kuralları ve doğrulamalar burada yapılır.
 * Veritabanı işlemleri için DAL katmanı çağrılır.
 * ❗ Bu katmanda hiçbir SQL komutu bulunmaz.
 */

require_once __DIR__ . '/../dal/EczaneDAL.php';

class EczaneBL
{
    private $dal;

    public function __construct()
    {
        $this->dal = new EczaneDAL();
    }

    // =============================================
    //  MÜŞTERİ
    // =============================================

    public function musterileriGetir()
    {
        return $this->dal->musterilerHepsi();
    }

    public function musteriAra($filtre)
    {
        if (empty(trim($filtre))) {
            return $this->dal->musterilerHepsi();
        }
        return $this->dal->musteriBul(trim($filtre));
    }

    public function musteriEkle($id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta)
    {
        // Doğrulama
        if (empty($id) || empty($ad) || empty($soyad) || empty($tc)) {
            throw new Exception("ID, Ad, Soyad ve TC Kimlik zorunludur.");
        }
        if (strlen($tc) != 11 || !ctype_digit($tc)) {
            throw new Exception("TC Kimlik 11 haneli rakamlardan oluşmalıdır.");
        }
        if (!in_array($sigorta, ['SGK', 'Ozel', 'Yok'])) {
            throw new Exception("Sigorta türü SGK, Ozel veya Yok olmalıdır.");
        }

        return $this->dal->musteriEkle($id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta);
    }

    public function musteriGuncelle($id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta)
    {
        if (empty($id)) {
            throw new Exception("Güncellenecek müşteri ID gereklidir.");
        }
        if (strlen($tc) != 11 || !ctype_digit($tc)) {
            throw new Exception("TC Kimlik 11 haneli rakamlardan oluşmalıdır.");
        }

        return $this->dal->musteriGuncelle($id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta);
    }

    public function musteriSil($id)
    {
        if (empty($id)) {
            throw new Exception("Silinecek müşteri ID gereklidir.");
        }
        return $this->dal->musteriSil($id);
    }

    // =============================================
    //  İLAÇ
    // =============================================

    public function ilaclariGetir()
    {
        return $this->dal->ilaclarHepsi();
    }

    public function ilacAra($filtre)
    {
        if (empty(trim($filtre))) {
            return $this->dal->ilaclarHepsi();
        }
        return $this->dal->ilacBul(trim($filtre));
    }

    public function ilacEkle($id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete)
    {
        if (empty($id) || empty($ad) || empty($kategori)) {
            throw new Exception("ID, Ad ve Kategori zorunludur.");
        }
        if ($fiyat < 0) {
            throw new Exception("Fiyat negatif olamaz.");
        }
        if ($stok < 0) {
            throw new Exception("Stok negatif olamaz.");
        }

        return $this->dal->ilacEkle($id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete);
    }

    public function ilacGuncelle($id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete)
    {
        if (empty($id)) {
            throw new Exception("Güncellenecek ilaç ID gereklidir.");
        }
        return $this->dal->ilacGuncelle($id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete);
    }

    public function ilacSil($id)
    {
        if (empty($id)) {
            throw new Exception("Silinecek ilaç ID gereklidir.");
        }
        return $this->dal->ilacSil($id);
    }

    public function stoguAzalanlar($esik = 20)
    {
        return $this->dal->stoguAzalanIlaclar($esik);
    }

    // =============================================
    //  SATIŞ
    // =============================================

    public function satisDetayGetir()
    {
        return $this->dal->satisDetay();
    }

    public function satislarGetir()
    {
        return $this->dal->satislarHepsi();
    }

    public function satisEkle($satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme)
    {
        if (empty($satisId) || empty($musteriId) || empty($ilacId)) {
            throw new Exception("Satış ID, Müşteri ID ve İlaç ID zorunludur.");
        }
        if ($adet <= 0) {
            throw new Exception("Adet 0'dan büyük olmalıdır.");
        }
        if (!in_array($odeme, ['Nakit', 'Kredi Karti', 'Havale', 'Veresiye'])) {
            throw new Exception("Geçersiz ödeme türü.");
        }

        // ❗ Trigger (tg_satis_kontrol) stok ve SKT kontrolünü veritabanında yapar.
        // Hata durumunda PDOException fırlatılır.
        return $this->dal->satisEkle($satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme);
    }

    public function satisGuncelle($satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme)
    {
        if (empty($satisId)) {
            throw new Exception("Güncellenecek satış ID gereklidir.");
        }
        return $this->dal->satisGuncelle($satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme);
    }

    public function satisSil($id)
    {
        if (empty($id)) {
            throw new Exception("Silinecek satış ID gereklidir.");
        }
        return $this->dal->satisSil($id);
    }

    public function musteriSatislari($musteriId)
    {
        if (empty($musteriId)) {
            throw new Exception("Müşteri ID gereklidir.");
        }
        return $this->dal->musteriSatislari($musteriId);
    }

    // =============================================
    //  ÖDEME
    // =============================================

    public function odemeDetayGetir()
    {
        return $this->dal->odemeDetay();
    }

    public function odemeEkle($id, $musteriId, $tarih, $tutar, $tur, $aciklama)
    {
        if (empty($id) || empty($musteriId)) {
            throw new Exception("Ödeme ID ve Müşteri ID zorunludur.");
        }
        if ($tutar <= 0) {
            throw new Exception("Ödeme tutarı 0'dan büyük olmalıdır.");
        }
        if (!in_array($tur, ['Nakit', 'Kredi Karti', 'Havale'])) {
            throw new Exception("Geçersiz ödeme türü.");
        }

        return $this->dal->odemeEkle($id, $musteriId, $tarih, $tutar, $tur, $aciklama);
    }

    public function odemeGuncelle($id, $musteriId, $tarih, $tutar, $tur, $aciklama)
    {
        if (empty($id)) {
            throw new Exception("Güncellenecek ödeme ID gereklidir.");
        }
        return $this->dal->odemeGuncelle($id, $musteriId, $tarih, $tutar, $tur, $aciklama);
    }

    public function odemeSil($id)
    {
        if (empty($id)) {
            throw new Exception("Silinecek ödeme ID gereklidir.");
        }
        return $this->dal->odemeSil($id);
    }

    // =============================================
    //  RAPORLAR (FUNCTION çağrıları)
    // =============================================

    public function musteriBakiye($musteriId)
    {
        if (empty($musteriId)) {
            throw new Exception("Müşteri ID gereklidir.");
        }
        return $this->dal->musteriBakiye($musteriId);
    }

    public function aylikCiro($yil, $ay)
    {
        if ($yil < 2020 || $ay < 1 || $ay > 12) {
            throw new Exception("Geçerli bir yıl ve ay giriniz.");
        }
        return $this->dal->aylikCiro($yil, $ay);
    }

    public function sktKalanGun($ilacId)
    {
        return $this->dal->sktKalanGun($ilacId);
    }
}
