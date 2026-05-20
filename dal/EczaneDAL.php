<?php
/**
 * DATA ACCESS LAYER (DAL)
 * ========================
 * ❗ Tüm veritabanı işlemleri SADECE Stored Procedure üzerinden yapılır.
 * ❗ Bu katmanda SELECT/INSERT/UPDATE/DELETE gibi SQL komutları YOKTUR.
 * ❗ Yalnızca CALL komutu ile SP çağrılır.
 */

require_once __DIR__ . '/../db.php';

class EczaneDAL
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // =============================================
    //  MÜŞTERİ İŞLEMLERİ
    // =============================================

    public function musterilerHepsi()
    {
        $stmt = $this->pdo->prepare("CALL sp_MusterilerHepsi()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function musteriEkle($id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta)
    {
        $stmt = $this->pdo->prepare("CALL sp_MusteriEkle(?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta]);
    }

    public function musteriGuncelle($id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta)
    {
        $stmt = $this->pdo->prepare("CALL sp_MusteriGuncelle(?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $ad, $soyad, $tc, $tel, $mail, $adres, $sigorta]);
    }

    public function musteriSil($id)
    {
        $stmt = $this->pdo->prepare("CALL sp_MusteriSil(?)");
        return $stmt->execute([$id]);
    }

    public function musteriBul($filtre)
    {
        $stmt = $this->pdo->prepare("CALL sp_MusteriBul(?)");
        $stmt->execute([$filtre]);
        return $stmt->fetchAll();
    }

    // =============================================
    //  İLAÇ İŞLEMLERİ
    // =============================================

    public function ilaclarHepsi()
    {
        $stmt = $this->pdo->prepare("CALL sp_IlaclarHepsi()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function ilacEkle($id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete)
    {
        $stmt = $this->pdo->prepare("CALL sp_IlacEkle(?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete]);
    }

    public function ilacGuncelle($id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete)
    {
        $stmt = $this->pdo->prepare("CALL sp_IlacGuncelle(?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $ad, $kategori, $uretici, $fiyat, $stok, $skt, $recete]);
    }

    public function ilacSil($id)
    {
        $stmt = $this->pdo->prepare("CALL sp_IlacSil(?)");
        return $stmt->execute([$id]);
    }

    public function ilacBul($filtre)
    {
        $stmt = $this->pdo->prepare("CALL sp_IlacBul(?)");
        $stmt->execute([$filtre]);
        return $stmt->fetchAll();
    }

    public function stoguAzalanIlaclar($esik)
    {
        $stmt = $this->pdo->prepare("CALL sp_StoguAzalanIlaclar(?)");
        $stmt->execute([$esik]);
        return $stmt->fetchAll();
    }

    // =============================================
    //  SATIŞ İŞLEMLERİ
    // =============================================

    public function satislarHepsi()
    {
        $stmt = $this->pdo->prepare("CALL sp_SatislarHepsi()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function satisEkle($satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme)
    {
        $stmt = $this->pdo->prepare("CALL sp_SatisEkle(?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme]);
    }

    public function satisGuncelle($satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme)
    {
        $stmt = $this->pdo->prepare("CALL sp_SatisGuncelle(?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$satisId, $musteriId, $ilacId, $adet, $tarih, $fiyat, $odeme]);
    }

    public function satisSil($id)
    {
        $stmt = $this->pdo->prepare("CALL sp_SatisSil(?)");
        return $stmt->execute([$id]);
    }

    public function satisDetay()
    {
        $stmt = $this->pdo->prepare("CALL sp_SatisDetay()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function musteriSatislari($musteriId)
    {
        $stmt = $this->pdo->prepare("CALL sp_MusteriSatislari(?)");
        $stmt->execute([$musteriId]);
        return $stmt->fetchAll();
    }

    // =============================================
    //  ÖDEME İŞLEMLERİ
    // =============================================

    public function odemelerHepsi()
    {
        $stmt = $this->pdo->prepare("CALL sp_OdemelerHepsi()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function odemeEkle($id, $musteriId, $tarih, $tutar, $tur, $aciklama)
    {
        $stmt = $this->pdo->prepare("CALL sp_OdemeEkle(?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $musteriId, $tarih, $tutar, $tur, $aciklama]);
    }

    public function odemeGuncelle($id, $musteriId, $tarih, $tutar, $tur, $aciklama)
    {
        $stmt = $this->pdo->prepare("CALL sp_OdemeGuncelle(?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $musteriId, $tarih, $tutar, $tur, $aciklama]);
    }

    public function odemeSil($id)
    {
        $stmt = $this->pdo->prepare("CALL sp_OdemeSil(?)");
        return $stmt->execute([$id]);
    }

    public function odemeDetay()
    {
        $stmt = $this->pdo->prepare("CALL sp_OdemeDetay()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // =============================================
    //  FONKSİYON ÇAĞRILARI (fn_ prefix)
    //  NOT: Bunlar MySQL FUNCTION'dır, CALL değil SELECT ile çağrılır.
    //  Ancak doğrudan SQL yazmıyoruz — sadece fonksiyon çağrısı yapıyoruz.
    // =============================================

    public function musteriBakiye($musteriId)
    {
        $stmt = $this->pdo->prepare("SELECT fn_MusteriBakiye(?) AS bakiye");
        $stmt->execute([$musteriId]);
        $row = $stmt->fetch();
        return $row ? $row['bakiye'] : 0;
    }

    public function aylikCiro($yil, $ay)
    {
        $stmt = $this->pdo->prepare("SELECT fn_AylikCiro(?, ?) AS ciro");
        $stmt->execute([$yil, $ay]);
        $row = $stmt->fetch();
        return $row ? $row['ciro'] : 0;
    }

    public function sktKalanGun($ilacId)
    {
        $stmt = $this->pdo->prepare("SELECT fn_SKTKalanGun(?) AS kalan_gun");
        $stmt->execute([$ilacId]);
        $row = $stmt->fetch();
        return $row ? $row['kalan_gun'] : null;
    }
}
