# 💊 Şifa Eczanesi Otomasyon Sistemi

BSM218 Veritabanı Yönetim Sistemleri — Final Ek Ödevi

## 📐 N-Katmanlı Mimari (N-Tier Architecture)

```
┌──────────────────────────────────────────┐
│   Presentation Layer (UI)                │
│   index.php, musteriler.php, ilaclar.php │
│   satislar.php, odemeler.php, raporlar   │
├──────────────────────────────────────────┤
│   Business Layer (BL)                    │
│   bl/EczaneBL.php                        │
├──────────────────────────────────────────┤
│   Data Access Layer (DAL)                │
│   dal/EczaneDAL.php                      │
│   ❗ SADECE Stored Procedure çağrıları   │
├──────────────────────────────────────────┤
│   MySQL Stored Procedures / Functions    │
│   / Triggers                             │
└──────────────────────────────────────────┘
```

**❗ Hiçbir katmanda doğrudan SELECT/INSERT/UPDATE/DELETE komutu kullanılmaz.**

## 🛠️ Kurulum (CachyOS / Arch Linux)

### 1. Gerekli Paketleri Kur
```bash
sudo pacman -S php mariadb
```

PHP'de PDO MySQL desteğini aç:
```bash
sudo sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/php.ini
```

### 2. MariaDB Kurulumu ve Başlatma
```bash
sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mysql_secure_installation
```

### 3. Veritabanını Oluştur
```bash
mysql -u root -p < sifa_eczane.sql
```
Bu komut tabloları, stored procedure'leri, function'ları ve trigger'ları tek seferde oluşturur.

### 4. Uygulamayı Çalıştır
```bash
cd SifaEczane
php -S localhost:8080
```
Tarayıcıda `http://localhost:8080` adresini aç.

### Alternatif: Windows (XAMPP) Kurulumu
1. `SifaEczane` klasörünü `C:\xampp\htdocs\` altına kopyala
2. phpMyAdmin'den `sifa_eczane.sql` dosyasını içe aktar
3. `http://localhost/SifaEczane/` adresini aç

## 📋 Dosya Yapısı

```
SifaEczane/
├── db.php                 # PDO veritabanı bağlantısı
├── header.php             # Ortak sayfa başlığı (navbar)
├── footer.php             # Ortak sayfa sonu
├── index.php              # Ana sayfa (Dashboard)
├── musteriler.php         # Müşteri CRUD işlemleri
├── ilaclar.php            # İlaç CRUD + stok yönetimi
├── satislar.php           # Satış kayıtları (Trigger demo)
├── odemeler.php           # Ödeme kayıtları
├── raporlar.php           # Bakiye/Ciro/SKT raporları (Function demo)
├── dal/
│   └── EczaneDAL.php      # Data Access Layer (SADECE SP çağrıları)
├── bl/
│   └── EczaneBL.php       # Business Layer (doğrulama + iş kuralları)
├── assets/
│   └── style.css          # Arayüz stilleri
└── sifa_eczane.sql        # Veritabanı kurulum scripti
```

## 🔧 Veritabanı Bileşenleri

| Tür | Adet | Örnekler |
|-----|------|----------|
| Tablo | 4 | ec_musteriler, ec_ilaclar, ec_satislar, ec_odemeler |
| Stored Procedure | 24+ | sp_MusteriEkle, sp_SatisDetay, sp_OdemeDetay ... |
| Function | 3 | fn_MusteriBakiye, fn_AylikCiro, fn_SKTKalanGun |
| Trigger | 3 | tg_satis_kontrol, tg_stok_azalt, tg_ilac_skt_kontrol |

## ⚡ Trigger Davranışları

- **tg_satis_kontrol** (BEFORE INSERT on ec_satislar): Stok yetersizse veya SKT geçmişse satışı engeller
- **tg_stok_azalt** (AFTER INSERT on ec_satislar): Satış sonrası ilaç stoğunu otomatik azaltır
- **tg_ilac_skt_kontrol** (BEFORE INSERT on ec_ilaclar): SKT bugünden önceyse ilaç eklemeyi engeller

## 📊 Function Kullanımı (Raporlar Sayfası)

- **fn_MusteriBakiye(id)** → Müşterinin borç/alacak bakiyesini döndürür
- **fn_AylikCiro(yıl, ay)** → İlgili ayın toplam satış cirosunu döndürür
- **fn_SKTKalanGun(id)** → İlacın son kullanma tarihine kalan gün sayısı
