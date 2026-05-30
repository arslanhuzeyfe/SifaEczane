-- =====================================================================
--  ECZANE OTOMASYON SISTEMI - Veritabani Tam Kurulum Scripti
--  BSM218/BSM303 Veritabani Yonetim Sistemleri - Final Ek Odev
--  MySQL 8.0+
-- =====================================================================
--  Bu dosyayi tek seferde calistirarak tum veritabanini kurabilirsin:
--      mysql -u root -p < sifa_eczane.sql
-- =====================================================================

DROP DATABASE IF EXISTS sifa_eczane;

CREATE DATABASE sifa_eczane
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_turkish_ci;

USE sifa_eczane;


-- =====================================================================
--  1. TABLOLAR
--  Her tabloda otomatik artan (AUTO_INCREMENT) sira numarasi (kayit_no)
--  bulunur. Is anahtari (musteri_id, ilac_id ...) PRIMARY KEY olarak
--  kalir; kayit_no UNIQUE bir surrogate anahtardir.
-- =====================================================================

CREATE TABLE ec_musteriler
(
    kayit_no          INT          NOT NULL AUTO_INCREMENT,
    musteri_id        VARCHAR(64)  NOT NULL,
    musteri_ad        VARCHAR(64)  NOT NULL,
    musteri_soyad     VARCHAR(64)  NOT NULL,
    musteri_tckimlik  VARCHAR(11)  NOT NULL UNIQUE,
    musteri_tel       VARCHAR(25)  NOT NULL,
    musteri_mail      VARCHAR(250) DEFAULT 'belirtilmemis@sifa.com',
    musteri_adres     VARCHAR(250) NOT NULL,
    musteri_sigorta   VARCHAR(15)  NOT NULL DEFAULT 'Yok',
    PRIMARY KEY (musteri_id),
    UNIQUE KEY uk_musteri_kayitno (kayit_no),
    CHECK (musteri_sigorta IN ('SGK', 'Ozel', 'Yok')),
    CHECK (CHAR_LENGTH(musteri_tckimlik) = 11)
);

CREATE TABLE ec_ilaclar
(
    kayit_no           INT          NOT NULL AUTO_INCREMENT,
    ilac_id            VARCHAR(64)  NOT NULL,
    ilac_ad            VARCHAR(250) NOT NULL,
    ilac_kategori      VARCHAR(100) NOT NULL,
    ilac_uretici       VARCHAR(150) NOT NULL,
    ilac_fiyat         FLOAT        NOT NULL,
    ilac_stok          INT          NOT NULL DEFAULT 0,
    ilac_son_kullanma  DATE         NOT NULL,
    ilac_recete        TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (ilac_id),
    UNIQUE KEY uk_ilac_kayitno (kayit_no),
    CHECK (ilac_fiyat >= 0),
    CHECK (ilac_stok  >= 0),
    CHECK (ilac_recete IN (0, 1))
);

CREATE TABLE ec_satislar
(
    kayit_no          INT          NOT NULL AUTO_INCREMENT,
    satis_id          VARCHAR(64)  NOT NULL,
    musteri_id        VARCHAR(64)  NOT NULL,
    ilac_id           VARCHAR(64)  NOT NULL,
    satis_adet        INT          NOT NULL DEFAULT 1,
    satis_tarih       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    satis_fiyat       FLOAT        NOT NULL,
    satis_odeme_tur   VARCHAR(25)  NOT NULL DEFAULT 'Nakit',
    PRIMARY KEY (satis_id),
    UNIQUE KEY uk_satis_kayitno (kayit_no),
    FOREIGN KEY (musteri_id) REFERENCES ec_musteriler(musteri_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ilac_id) REFERENCES ec_ilaclar(ilac_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CHECK (satis_adet > 0),
    CHECK (satis_fiyat >= 0),
    CHECK (satis_odeme_tur IN ('Nakit','Kredi Karti','Havale','Veresiye'))
);

CREATE TABLE ec_odemeler
(
    kayit_no        INT          NOT NULL AUTO_INCREMENT,
    odeme_id        VARCHAR(64)  NOT NULL,
    musteri_id      VARCHAR(64)  NOT NULL,
    odeme_tarih     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    odeme_tutar     FLOAT        NOT NULL,
    odeme_tur       VARCHAR(25)  NOT NULL,
    odeme_aciklama  VARCHAR(250) DEFAULT '',
    PRIMARY KEY (odeme_id),
    UNIQUE KEY uk_odeme_kayitno (kayit_no),
    FOREIGN KEY (musteri_id) REFERENCES ec_musteriler(musteri_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CHECK (odeme_tutar > 0),
    CHECK (odeme_tur IN ('Nakit','Kredi Karti','Havale'))
);


-- =====================================================================
--  2. STORED PROCEDURE'LER
-- =====================================================================

-- ---------- MUSTERI ----------
DELIMITER $$
CREATE PROCEDURE sp_MusterilerHepsi()
BEGIN
    SELECT musteri_id AS ID, musteri_ad AS Adi, musteri_soyad AS Soyadi,
           musteri_tckimlik AS TC_Kimlik, musteri_tel AS Telefon,
           musteri_mail AS Mail, musteri_adres AS Adres,
           musteri_sigorta AS Sigorta
    FROM ec_musteriler ORDER BY musteri_ad, musteri_soyad;
END $$

CREATE PROCEDURE sp_MusteriEkle(
    IN p_id VARCHAR(64), IN p_ad VARCHAR(64), IN p_soyad VARCHAR(64),
    IN p_tc VARCHAR(11), IN p_tel VARCHAR(25), IN p_mail VARCHAR(250),
    IN p_adres VARCHAR(250), IN p_sigorta VARCHAR(15))
BEGIN
    INSERT INTO ec_musteriler
        (musteri_id, musteri_ad, musteri_soyad, musteri_tckimlik,
         musteri_tel, musteri_mail, musteri_adres, musteri_sigorta)
    VALUES (p_id, p_ad, p_soyad, p_tc, p_tel, p_mail, p_adres, p_sigorta);
END $$

CREATE PROCEDURE sp_MusteriGuncelle(
    IN p_id VARCHAR(64), IN p_ad VARCHAR(64), IN p_soyad VARCHAR(64),
    IN p_tc VARCHAR(11), IN p_tel VARCHAR(25), IN p_mail VARCHAR(250),
    IN p_adres VARCHAR(250), IN p_sigorta VARCHAR(15))
BEGIN
    UPDATE ec_musteriler
    SET musteri_ad = p_ad, musteri_soyad = p_soyad, musteri_tckimlik = p_tc,
        musteri_tel = p_tel, musteri_mail = p_mail, musteri_adres = p_adres,
        musteri_sigorta = p_sigorta
    WHERE musteri_id = p_id;
END $$

CREATE PROCEDURE sp_MusteriSil(IN p_id VARCHAR(64))
BEGIN
    DELETE FROM ec_musteriler WHERE musteri_id = p_id;
END $$

CREATE PROCEDURE sp_MusteriBul(IN p_filtre VARCHAR(64))
BEGIN
    SELECT * FROM ec_musteriler
    WHERE musteri_id LIKE CONCAT('%', p_filtre, '%')
       OR musteri_ad LIKE CONCAT('%', p_filtre, '%')
       OR musteri_soyad LIKE CONCAT('%', p_filtre, '%')
       OR musteri_tckimlik LIKE CONCAT('%', p_filtre, '%')
       OR musteri_tel LIKE CONCAT('%', p_filtre, '%');
END $$
DELIMITER ;


-- ---------- ILAC ----------
DELIMITER $$
CREATE PROCEDURE sp_IlaclarHepsi()
BEGIN
    SELECT ilac_id AS ID, ilac_ad AS Ad, ilac_kategori AS Kategori,
           ilac_uretici AS Uretici, ilac_fiyat AS Fiyat, ilac_stok AS Stok,
           ilac_son_kullanma AS SonKullanma,
           CASE WHEN ilac_recete = 1 THEN 'Evet' ELSE 'Hayir' END AS ReceteGerekli
    FROM ec_ilaclar ORDER BY ilac_ad;
END $$

CREATE PROCEDURE sp_IlacEkle(
    IN p_id VARCHAR(64), IN p_ad VARCHAR(250), IN p_kategori VARCHAR(100),
    IN p_uretici VARCHAR(150), IN p_fiyat FLOAT, IN p_stok INT,
    IN p_skt DATE, IN p_recete TINYINT)
BEGIN
    INSERT INTO ec_ilaclar
        (ilac_id, ilac_ad, ilac_kategori, ilac_uretici, ilac_fiyat,
         ilac_stok, ilac_son_kullanma, ilac_recete)
    VALUES (p_id, p_ad, p_kategori, p_uretici, p_fiyat, p_stok, p_skt, p_recete);
END $$

CREATE PROCEDURE sp_IlacGuncelle(
    IN p_id VARCHAR(64), IN p_ad VARCHAR(250), IN p_kategori VARCHAR(100),
    IN p_uretici VARCHAR(150), IN p_fiyat FLOAT, IN p_stok INT,
    IN p_skt DATE, IN p_recete TINYINT)
BEGIN
    UPDATE ec_ilaclar
    SET ilac_ad = p_ad, ilac_kategori = p_kategori, ilac_uretici = p_uretici,
        ilac_fiyat = p_fiyat, ilac_stok = p_stok,
        ilac_son_kullanma = p_skt, ilac_recete = p_recete
    WHERE ilac_id = p_id;
END $$

CREATE PROCEDURE sp_IlacSil(IN p_id VARCHAR(64))
BEGIN
    DELETE FROM ec_ilaclar WHERE ilac_id = p_id;
END $$

CREATE PROCEDURE sp_IlacBul(IN p_filtre VARCHAR(64))
BEGIN
    SELECT * FROM ec_ilaclar
    WHERE ilac_id LIKE CONCAT('%', p_filtre, '%')
       OR ilac_ad LIKE CONCAT('%', p_filtre, '%')
       OR ilac_kategori LIKE CONCAT('%', p_filtre, '%')
       OR ilac_uretici LIKE CONCAT('%', p_filtre, '%');
END $$

CREATE PROCEDURE sp_StoguAzalanIlaclar(IN p_esik INT)
BEGIN
    SELECT * FROM ec_ilaclar WHERE ilac_stok < p_esik ORDER BY ilac_stok ASC;
END $$
DELIMITER ;


-- ---------- SATIS ----------
DELIMITER $$
CREATE PROCEDURE sp_SatislarHepsi()
BEGIN
    SELECT * FROM ec_satislar ORDER BY satis_tarih DESC;
END $$

CREATE PROCEDURE sp_SatisEkle(
    IN p_satis_id VARCHAR(64), IN p_musteri_id VARCHAR(64),
    IN p_ilac_id VARCHAR(64), IN p_adet INT, IN p_tarih DATETIME,
    IN p_fiyat FLOAT, IN p_odeme VARCHAR(25))
BEGIN
    INSERT INTO ec_satislar
        (satis_id, musteri_id, ilac_id, satis_adet,
         satis_tarih, satis_fiyat, satis_odeme_tur)
    VALUES (p_satis_id, p_musteri_id, p_ilac_id, p_adet,
            p_tarih, p_fiyat, p_odeme);
END $$

CREATE PROCEDURE sp_SatisGuncelle(
    IN p_satis_id VARCHAR(64), IN p_musteri_id VARCHAR(64),
    IN p_ilac_id VARCHAR(64), IN p_adet INT, IN p_tarih DATETIME,
    IN p_fiyat FLOAT, IN p_odeme VARCHAR(25))
BEGIN
    UPDATE ec_satislar
    SET musteri_id = p_musteri_id, ilac_id = p_ilac_id,
        satis_adet = p_adet, satis_tarih = p_tarih,
        satis_fiyat = p_fiyat, satis_odeme_tur = p_odeme
    WHERE satis_id = p_satis_id;
END $$

CREATE PROCEDURE sp_SatisSil(IN p_id VARCHAR(64))
BEGIN
    DELETE FROM ec_satislar WHERE satis_id = p_id;
END $$

CREATE PROCEDURE sp_SatisDetay()
BEGIN
    SELECT s.satis_id AS SatisID,
           CONCAT(m.musteri_ad,' ',m.musteri_soyad) AS Musteri,
           m.musteri_sigorta AS Sigorta, i.ilac_ad AS Ilac,
           i.ilac_kategori AS Kategori, s.satis_adet AS Adet,
           i.ilac_fiyat AS BirimFiyat, s.satis_fiyat AS ToplamFiyat,
           s.satis_odeme_tur AS OdemeTuru, s.satis_tarih AS SatisTarihi
    FROM ec_satislar s
        INNER JOIN ec_musteriler m ON m.musteri_id = s.musteri_id
        INNER JOIN ec_ilaclar i ON i.ilac_id = s.ilac_id
    ORDER BY s.satis_tarih DESC;
END $$

CREATE PROCEDURE sp_MusteriSatislari(IN p_musteri_id VARCHAR(64))
BEGIN
    SELECT s.*, i.ilac_ad
    FROM ec_satislar s
    INNER JOIN ec_ilaclar i ON i.ilac_id = s.ilac_id
    WHERE s.musteri_id = p_musteri_id
    ORDER BY s.satis_tarih DESC;
END $$
DELIMITER ;


-- ---------- ODEME ----------
DELIMITER $$
CREATE PROCEDURE sp_OdemelerHepsi()
BEGIN
    SELECT * FROM ec_odemeler ORDER BY odeme_tarih DESC;
END $$

CREATE PROCEDURE sp_OdemeEkle(
    IN p_id VARCHAR(64), IN p_musteri_id VARCHAR(64),
    IN p_tarih DATETIME, IN p_tutar FLOAT,
    IN p_tur VARCHAR(25), IN p_aciklama VARCHAR(250))
BEGIN
    INSERT INTO ec_odemeler
        (odeme_id, musteri_id, odeme_tarih, odeme_tutar, odeme_tur, odeme_aciklama)
    VALUES (p_id, p_musteri_id, p_tarih, p_tutar, p_tur, p_aciklama);
END $$

CREATE PROCEDURE sp_OdemeGuncelle(
    IN p_id VARCHAR(64), IN p_musteri_id VARCHAR(64),
    IN p_tarih DATETIME, IN p_tutar FLOAT,
    IN p_tur VARCHAR(25), IN p_aciklama VARCHAR(250))
BEGIN
    UPDATE ec_odemeler
    SET musteri_id = p_musteri_id, odeme_tarih = p_tarih,
        odeme_tutar = p_tutar, odeme_tur = p_tur, odeme_aciklama = p_aciklama
    WHERE odeme_id = p_id;
END $$

CREATE PROCEDURE sp_OdemeSil(IN p_id VARCHAR(64))
BEGIN
    DELETE FROM ec_odemeler WHERE odeme_id = p_id;
END $$

CREATE PROCEDURE sp_OdemeDetay()
BEGIN
    SELECT o.odeme_id AS OdemeID,
           CONCAT(m.musteri_ad,' ',m.musteri_soyad) AS Musteri,
           o.odeme_tarih AS Tarih, o.odeme_tutar AS Tutar,
           o.odeme_tur AS Tur, o.odeme_aciklama AS Aciklama
    FROM ec_odemeler o
    INNER JOIN ec_musteriler m ON m.musteri_id = o.musteri_id
    ORDER BY o.odeme_tarih DESC;
END $$
DELIMITER ;


-- =====================================================================
--  3. KULLANICI TANIMLI FONKSIYONLAR
-- =====================================================================

DELIMITER $$
CREATE FUNCTION fn_MusteriBakiye(p_musteri_id VARCHAR(64))
RETURNS FLOAT DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_borc  FLOAT DEFAULT 0;
    DECLARE v_odeme FLOAT DEFAULT 0;
    SELECT IFNULL(SUM(satis_fiyat),0) INTO v_borc
        FROM ec_satislar WHERE musteri_id = p_musteri_id;
    SELECT IFNULL(SUM(odeme_tutar),0) INTO v_odeme
        FROM ec_odemeler WHERE musteri_id = p_musteri_id;
    RETURN (v_odeme - v_borc);
END $$

CREATE FUNCTION fn_AylikCiro(p_yil INT, p_ay INT)
RETURNS FLOAT DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_ciro FLOAT DEFAULT 0;
    SELECT IFNULL(SUM(satis_fiyat),0) INTO v_ciro
        FROM ec_satislar
        WHERE YEAR(satis_tarih) = p_yil AND MONTH(satis_tarih) = p_ay;
    RETURN v_ciro;
END $$

CREATE FUNCTION fn_SKTKalanGun(p_ilac_id VARCHAR(64))
RETURNS INT DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_skt DATE;
    SELECT ilac_son_kullanma INTO v_skt
        FROM ec_ilaclar WHERE ilac_id = p_ilac_id;
    RETURN DATEDIFF(v_skt, CURDATE());
END $$
DELIMITER ;


-- =====================================================================
--  4. TRIGGER'LAR
-- =====================================================================

DELIMITER //
CREATE TRIGGER tg_satis_kontrol
BEFORE INSERT ON ec_satislar FOR EACH ROW
BEGIN
    DECLARE v_stok INT;
    DECLARE v_skt  DATE;
    DECLARE v_ad   VARCHAR(250);
    DECLARE v_msg  VARCHAR(250);
    SELECT ilac_stok, ilac_son_kullanma, ilac_ad
        INTO v_stok, v_skt, v_ad FROM ec_ilaclar WHERE ilac_id = NEW.ilac_id;
    IF NEW.satis_adet > v_stok THEN
        SET v_msg = CONCAT('HATA: ', v_ad, ' icin stok yetersiz! ',
                           'Istenen: ', NEW.satis_adet, ' / Mevcut: ', v_stok);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
    END IF;
    IF v_skt < CURDATE() THEN
        SET v_msg = CONCAT('HATA: ', v_ad, ' son kullanma tarihi gecmis! SKT: ', v_skt);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
    END IF;
END; //

CREATE TRIGGER tg_stok_azalt
AFTER INSERT ON ec_satislar FOR EACH ROW
BEGIN
    UPDATE ec_ilaclar SET ilac_stok = ilac_stok - NEW.satis_adet
    WHERE ilac_id = NEW.ilac_id;
END; //

CREATE TRIGGER tg_ilac_skt_kontrol
BEFORE INSERT ON ec_ilaclar FOR EACH ROW
BEGIN
    IF NEW.ilac_son_kullanma <= CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'HATA: Son kullanma tarihi bugunden ileri olmalidir!';
    END IF;
END; //
DELIMITER ;


-- =====================================================================
--  5. ORNEK VERI
-- =====================================================================

CALL sp_MusteriEkle('M001','Ahmet','Yilmaz','12345678901','05551112233','ahmet@mail.com','Bartin Merkez','SGK');
CALL sp_MusteriEkle('M002','Ayse','Demir','12345678902','05552223344','ayse@mail.com','Bartin Merkez','Ozel');
CALL sp_MusteriEkle('M003','Mehmet','Kara','12345678903','05553334455','mehmet@mail.com','Bartin Merkez','Yok');

CALL sp_IlacEkle('I001','Parol 500mg','Agri Kesici','Atabay',12.50,100,'2027-12-31',0);
CALL sp_IlacEkle('I002','Augmentin BID','Antibiyotik','GSK',85.00,30,'2026-09-15',1);
CALL sp_IlacEkle('I003','C Vitamini 1000','Vitamin','Roche',65.00,50,'2027-06-20',0);
CALL sp_IlacEkle('I004','Aspirin 100mg','Agri Kesici','Bayer',18.75,200,'2028-03-10',0);

CALL sp_SatisEkle('S001','M001','I001',2,NOW(),25.00,'Nakit');
CALL sp_SatisEkle('S002','M002','I003',1,NOW(),65.00,'Kredi Karti');
CALL sp_SatisEkle('S003','M001','I004',3,NOW(),56.25,'Veresiye');

CALL sp_OdemeEkle('O001','M001',NOW(),50.00,'Nakit','Kismi odeme');


-- =====================================================================
--  6. TEST SORGULARI
-- =====================================================================

CALL sp_SatisDetay();
SELECT fn_MusteriBakiye('M001') AS M001_Bakiye;
SELECT fn_AylikCiro(2026, 5) AS MayisCirosu;
CALL sp_StoguAzalanIlaclar(50);
SELECT ilac_ad, fn_SKTKalanGun(ilac_id) AS KalanGun FROM ec_ilaclar ORDER BY KalanGun ASC;
