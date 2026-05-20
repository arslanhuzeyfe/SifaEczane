<?php
/**
 * Şifa Eczanesi - Otomatik Test Scripti
 * =======================================
 * Tüm Stored Procedure, Function ve Trigger'ları test eder.
 * Çalıştır: php test.php
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/bl/EczaneBL.php';

$bl      = new EczaneBL();
$gecen   = 0;
$toplam  = 0;

// --- Renkli çıktı yardımcıları ---
function ok($msg)    { global $gecen, $toplam; $gecen++; $toplam++; echo "\e[32m  ✓ PASS\e[0m  $msg\n"; }
function fail($msg)  { global $toplam; $toplam++; echo "\e[31m  ✗ FAIL\e[0m  $msg\n"; }
function info($msg)  { echo "\e[36m\n▶ $msg\e[0m\n"; }
function sep()       { echo str_repeat("─", 60) . "\n"; }

echo "\n";
echo "\e[1m\e[34m╔══════════════════════════════════════════════════════════╗\e[0m\n";
echo "\e[1m\e[34m║     Şifa Eczanesi — Otomatik Test Paketi                 ║\e[0m\n";
echo "\e[1m\e[34m╚══════════════════════════════════════════════════════════╝\e[0m\n\n";

// ============================================================
//  1. MÜŞTERİ STORED PROCEDURE TESTLERİ
// ============================================================
info("1. MÜŞTERİ İŞLEMLERİ (Stored Procedure)");
sep();

// sp_MusteriEkle
try {
    $bl->musteriEkle('TEST01','Test','Kullanici','99999999999','05550000001','test@test.com','Test Adres','SGK');
    ok("sp_MusteriEkle — Yeni müşteri eklendi (TEST01)");
} catch (Exception $e) { fail("sp_MusteriEkle — " . $e->getMessage()); }

// sp_MusterilerHepsi
try {
    $liste = $bl->musterileriGetir();
    $bulunan = false;
    foreach ($liste as $m) {
        if (($m['ID'] ?? $m['musteri_id'] ?? '') === 'TEST01') { $bulunan = true; break; }
    }
    $bulunan ? ok("sp_MusterilerHepsi — TEST01 listede görünüyor") : fail("sp_MusterilerHepsi — TEST01 listede yok");
} catch (Exception $e) { fail("sp_MusterilerHepsi — " . $e->getMessage()); }

// sp_MusteriBul
try {
    $sonuc = $bl->musteriAra('TEST01');
    count($sonuc) > 0 ? ok("sp_MusteriBul — Arama sonucu döndü") : fail("sp_MusteriBul — Sonuç yok");
} catch (Exception $e) { fail("sp_MusteriBul — " . $e->getMessage()); }

// sp_MusteriGuncelle
try {
    $bl->musteriGuncelle('TEST01','Test','Guncellendi','99999999999','05550000002','test2@test.com','Yeni Adres','Ozel');
    ok("sp_MusteriGuncelle — TEST01 güncellendi");
} catch (Exception $e) { fail("sp_MusteriGuncelle — " . $e->getMessage()); }

// TC Kimlik doğrulama (BL katmanı)
try {
    $bl->musteriEkle('TEST_X','Hata','Testi','1234','05550000003','','','SGK');
    fail("BL TC Doğrulama — Kısa TC'ye izin verilmemeli!");
} catch (Exception $e) { ok("BL TC Doğrulama — Kısa TC reddedildi: " . $e->getMessage()); }


// ============================================================
//  2. İLAÇ STORED PROCEDURE TESTLERİ
// ============================================================
info("2. İLAÇ İŞLEMLERİ (Stored Procedure + Trigger)");
sep();

// sp_IlacEkle
try {
    $bl->ilacEkle('ITEST01','Test İlacı','Vitamin','Test Firması',25.00,50,'2028-12-31',0);
    ok("sp_IlacEkle — Yeni ilaç eklendi (ITEST01)");
} catch (Exception $e) { fail("sp_IlacEkle — " . $e->getMessage()); }

// sp_IlaclarHepsi
try {
    $liste = $bl->ilaclariGetir();
    $bulunan = false;
    foreach ($liste as $i) {
        if (($i['ID'] ?? $i['ilac_id'] ?? '') === 'ITEST01') { $bulunan = true; break; }
    }
    $bulunan ? ok("sp_IlaclarHepsi — ITEST01 listede görünüyor") : fail("sp_IlaclarHepsi — ITEST01 listede yok");
} catch (Exception $e) { fail("sp_IlaclarHepsi — " . $e->getMessage()); }

// sp_IlacBul
try {
    $sonuc = $bl->ilacAra('Test İlacı');
    count($sonuc) > 0 ? ok("sp_IlacBul — Arama sonucu döndü") : fail("sp_IlacBul — Sonuç yok");
} catch (Exception $e) { fail("sp_IlacBul — " . $e->getMessage()); }

// sp_IlacGuncelle
try {
    $bl->ilacGuncelle('ITEST01','Test İlacı Güncellendi','Vitamin','Test Firması',30.00,45,'2028-12-31',0);
    ok("sp_IlacGuncelle — ITEST01 güncellendi");
} catch (Exception $e) { fail("sp_IlacGuncelle — " . $e->getMessage()); }

// sp_StoguAzalanIlaclar
try {
    $sonuc = $bl->stoguAzalanlar(999);
    count($sonuc) > 0 ? ok("sp_StoguAzalanIlaclar — " . count($sonuc) . " ilaç döndü") : fail("sp_StoguAzalanIlaclar — Sonuç yok");
} catch (Exception $e) { fail("sp_StoguAzalanIlaclar — " . $e->getMessage()); }

// ── TRIGGER TEST: tg_ilac_skt_kontrol ──
echo "\n  \e[33m⚡ Trigger Testi: tg_ilac_skt_kontrol\e[0m\n";
try {
    $bl->ilacEkle('ITEST_SKT','Süresi Geçmiş','Vitamin','Firma',10.00,10,'2020-01-01',0);
    fail("tg_ilac_skt_kontrol — Geçmiş SKT'ye izin verilmemeli!");
} catch (Exception $e) {
    ok("tg_ilac_skt_kontrol — Geçmiş SKT reddedildi ✓ (" . substr($e->getMessage(), 0, 60) . "...)");
}


// ============================================================
//  3. SATIŞ STORED PROCEDURE + TRIGGER TESTLERİ
// ============================================================
info("3. SATIŞ İŞLEMLERİ (Stored Procedure + 2 Trigger)");
sep();

// Önce stoku kontrol et
$ilaclar = $bl->ilaclariGetir();
$stokOnce = 0;
foreach ($ilaclar as $i) {
    if (($i['ID'] ?? $i['ilac_id'] ?? '') === 'ITEST01') {
        $stokOnce = intval($i['Stok'] ?? $i['ilac_stok'] ?? 0);
        break;
    }
}

// sp_SatisEkle — normal satış
try {
    $bl->satisEkle('STEST01','TEST01','ITEST01',2,date('Y-m-d H:i:s'),60.00,'Nakit');
    ok("sp_SatisEkle — Normal satış eklendi (STEST01)");
} catch (Exception $e) { fail("sp_SatisEkle — " . $e->getMessage()); }

// tg_stok_azalt kontrolü
echo "\n  \e[33m⚡ Trigger Testi: tg_stok_azalt\e[0m\n";
$ilaclar = $bl->ilaclariGetir();
$stokSonra = 0;
foreach ($ilaclar as $i) {
    if (($i['ID'] ?? $i['ilac_id'] ?? '') === 'ITEST01') {
        $stokSonra = intval($i['Stok'] ?? $i['ilac_stok'] ?? 0);
        break;
    }
}
if ($stokSonra === $stokOnce - 2) {
    ok("tg_stok_azalt — Stok $stokOnce → $stokSonra (2 adet azaldı) ✓");
} else {
    fail("tg_stok_azalt — Stok değişmedi! Beklenen: " . ($stokOnce-2) . " Gelen: $stokSonra");
}

// sp_SatisDetay (JOIN testi)
try {
    $detay = $bl->satisDetayGetir();
    count($detay) > 0 ? ok("sp_SatisDetay — JOIN sorgusu çalıştı, " . count($detay) . " kayıt") : fail("sp_SatisDetay — Sonuç yok");
} catch (Exception $e) { fail("sp_SatisDetay — " . $e->getMessage()); }

// tg_satis_kontrol — stok yetersiz
echo "\n  \e[33m⚡ Trigger Testi: tg_satis_kontrol (stok yetersiz)\e[0m\n";
try {
    $bl->satisEkle('STEST_HATA','TEST01','ITEST01',99999,date('Y-m-d H:i:s'),999999,'Nakit');
    fail("tg_satis_kontrol — Yetersiz stoğa izin verilmemeli!");
} catch (Exception $e) {
    ok("tg_satis_kontrol — Yetersiz stok reddedildi ✓ (" . substr($e->getMessage(), 0, 60) . "...)");
}

// sp_MusteriSatislari
try {
    $sonuc = $bl->musteriSatislari('TEST01');
    count($sonuc) > 0 ? ok("sp_MusteriSatislari — TEST01'in satışları listelendi") : fail("sp_MusteriSatislari — Sonuç yok");
} catch (Exception $e) { fail("sp_MusteriSatislari — " . $e->getMessage()); }

// sp_SatisSil
try {
    $bl->satisSil('STEST01');
    ok("sp_SatisSil — STEST01 silindi");
} catch (Exception $e) { fail("sp_SatisSil — " . $e->getMessage()); }


// ============================================================
//  4. ÖDEME STORED PROCEDURE TESTLERİ
// ============================================================
info("4. ÖDEME İŞLEMLERİ (Stored Procedure)");
sep();

// sp_OdemeEkle
try {
    $bl->odemeEkle('OTEST01','TEST01',date('Y-m-d H:i:s'),100.00,'Nakit','Otomatik test ödemesi');
    ok("sp_OdemeEkle — Ödeme eklendi (OTEST01)");
} catch (Exception $e) { fail("sp_OdemeEkle — " . $e->getMessage()); }

// sp_OdemeDetay
try {
    $detay = $bl->odemeDetayGetir();
    count($detay) > 0 ? ok("sp_OdemeDetay — JOIN sorgusu çalıştı, " . count($detay) . " kayıt") : fail("sp_OdemeDetay — Sonuç yok");
} catch (Exception $e) { fail("sp_OdemeDetay — " . $e->getMessage()); }

// sp_OdemeGuncelle
try {
    $bl->odemeGuncelle('OTEST01','TEST01',date('Y-m-d H:i:s'),150.00,'Havale','Güncellendi');
    ok("sp_OdemeGuncelle — OTEST01 güncellendi");
} catch (Exception $e) { fail("sp_OdemeGuncelle — " . $e->getMessage()); }

// sp_OdemeSil
try {
    $bl->odemeSil('OTEST01');
    ok("sp_OdemeSil — OTEST01 silindi");
} catch (Exception $e) { fail("sp_OdemeSil — " . $e->getMessage()); }


// ============================================================
//  5. FUNCTION TESTLERİ
// ============================================================
info("5. KULLANICI TANIMLI FONKSİYONLAR (MySQL FUNCTION)");
sep();

// fn_MusteriBakiye
try {
    $bakiye = $bl->musteriBakiye('M001');
    ok("fn_MusteriBakiye — M001 bakiyesi: " . number_format($bakiye, 2) . " ₺");
} catch (Exception $e) { fail("fn_MusteriBakiye — " . $e->getMessage()); }

// fn_AylikCiro
try {
    $ciro = $bl->aylikCiro(2026, 5);
    ok("fn_AylikCiro — Mayıs 2026 cirosu: " . number_format($ciro, 2) . " ₺");
} catch (Exception $e) { fail("fn_AylikCiro — " . $e->getMessage()); }

// fn_SKTKalanGun
try {
    $kalan = $bl->sktKalanGun('I001');
    is_numeric($kalan) ? ok("fn_SKTKalanGun — I001 için son kullanmaya $kalan gün kaldı") : fail("fn_SKTKalanGun — Sonuç gelmedi");
} catch (Exception $e) { fail("fn_SKTKalanGun — " . $e->getMessage()); }


// ============================================================
//  6. TEMİZLİK (Test verilerini sil)
// ============================================================
info("6. TEMİZLİK (Test verileri siliniyor)");
sep();

try { $bl->musteriSil('TEST01');  ok("TEST01 müşterisi silindi"); } catch (Exception $e) { fail($e->getMessage()); }
try { $bl->ilacSil('ITEST01');    ok("ITEST01 ilacı silindi");    } catch (Exception $e) { fail($e->getMessage()); }


// ============================================================
//  SONUÇ
// ============================================================
echo "\n";
echo str_repeat("═", 60) . "\n";
$renk = ($gecen === $toplam) ? "\e[32m" : "\e[33m";
echo "{$renk}\e[1m  SONUÇ: $gecen / $toplam test geçti\e[0m\n";
echo str_repeat("═", 60) . "\n\n";
