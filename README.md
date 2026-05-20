# Şifa Eczanesi Otomasyon Sistemi

BSM218 Veritabanı Yönetim Sistemleri - Final Ek Ödevi

## N-Katmanlı Mimari (N-Tier Architecture)

Presentation Layer (UI) -> Business Layer (BL) -> Data Access Layer (DAL) -> MySQL SP

Hicbir katmanda dogrudan SELECT/INSERT/UPDATE/DELETE komutu kullanilmaz.

## Kurulum

### Gereksinimler
- PHP 8.0+
- MySQL 8.0+
- Web sunucusu veya PHP built-in server

### 1. Veritabanini Olustur
mysql -u root -p < sifa_eczane.sql

### 2. Baglantıyı Yapilandir
db.php dosyasinda su satirlari guncelle:
- $username = 'kullanici_adin';
- $password = 'sifren';

### 3. Uygulamayi Calistir
php -S localhost:8080
Tarayicida http://localhost:8080 adresini ac.

## Dosya Yapisi

SifaEczane/
- db.php
- index.php
- musteriler.php
- ilaclar.php
- satislar.php
- odemeler.php
- raporlar.php
- test.php
- dal/EczaneDAL.php
- bl/EczaneBL.php
- assets/style.css
- sifa_eczane.sql

## Veritabani Bilesenleri

Tablo: 4 adet (ec_musteriler, ec_ilaclar, ec_satislar, ec_odemeler)
Stored Procedure: 24+
Function: 3 (fn_MusteriBakiye, fn_AylikCiro, fn_SKTKalanGun)
Trigger: 3 (tg_satis_kontrol, tg_stok_azalt, tg_ilac_skt_kontrol)

## Triggerlar

- tg_satis_kontrol: Stok yetersizse veya SKT gecmisse satisi engeller
- tg_stok_azalt: Satis sonrasi stoku otomatik azaltir
- tg_ilac_skt_kontrol: Gecmis SKT ile ilac eklemeyi engeller

## Functionlar

- fn_MusteriBakiye: Musterinin bakiyesini hesaplar
- fn_AylikCiro: Aylik toplam ciroyu hesaplar
- fn_SKTKalanGun: Son kullanma tarihine kalan gun

## Otomatik Test

php test.php
Sonuc: 26 / 26 test gecti
