<?php
/**
 * Veritabanı Bağlantısı (PDO)
 * Şifa Eczanesi Otomasyon Sistemi
 *
 * CachyOS / Arch Linux: MariaDB unix socket kullanır.
 * Windows / XAMPP: TCP 127.0.0.1 kullanır.
 */

$host     = '127.0.0.1';       // localhost yerine IP → TCP bağlantısı zorlar
$dbname   = 'sifa_eczane';
$username = 'eczane';
$password = 'eczane123';                 // mysql_secure_installation'da belirlediğin şifre
$charset  = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
