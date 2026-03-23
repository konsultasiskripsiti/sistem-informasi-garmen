# Sistem Informasi Garmen

Aplikasi ini adalah sistem informasi garmen berbasis Laravel untuk mengelola:

- User, roles, dan permissions
- Supplier
- Raw material
- Product dan komposisi bahan baku
- Transaksi pembelian bahan baku
- Transaksi produksi product
- Transaksi penjualan product
- Dashboard admin berbasis TailAdmin

## Tech Stack

- PHP
- Laravel 13
- MySQL
- Laravel Breeze
- TailAdmin
- Tailwind CSS
- Alpine.js
- Vite
- Spatie Laravel Permission

## Requirements

Sebelum install, pastikan environment kamu minimal punya:

- PHP `8.3+`
- PHP `8.4` direkomendasikan karena project ini sudah dites di PHP 8.4
- Composer `2+`
- Node.js `20+`
- NPM `10+`
- MySQL / MariaDB
- Git

PHP extension yang umumnya perlu aktif:

- `bcmath`
- `ctype`
- `fileinfo`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- `pdo_mysql`
- `tokenizer`
- `xml`

## Instalasi

1. Clone repository

```bash
git clone https://github.com/konsultasiskripsiti/sistem-informasi-garmen.git
cd sistem-informasi-garmen
```

2. Install dependency PHP

```bash
composer install
```

3. Install dependency frontend

```bash
npm install
```

4. Copy file environment

```bash
cp .env.example .env
```

5. Atur konfigurasi database di file `.env`

Contoh:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=post_new2
DB_USERNAME=root
DB_PASSWORD=
```

6. Generate application key

```bash
php artisan key:generate
```

7. Jalankan migration

```bash
php artisan migrate
```

8. Jalankan seeder

```bash
php artisan db:seed
```

9. Jalankan Vite dev server

```bash
npm run dev
```

10. Jalankan Laravel server

```bash
php artisan serve
```

Setelah itu aplikasi bisa diakses di:

```txt
http://127.0.0.1:8000
```

## Instalasi Cepat

Kalau mau lebih cepat, kamu bisa pakai script bawaan Composer:

```bash
composer run setup
```

Lalu lanjutkan:

```bash
php artisan db:seed
npm run dev
php artisan serve
```

Catatan:

- `composer run setup` akan install dependency, copy `.env`, generate key, migrate database, install npm package, dan build asset.
- Seeder tetap dijalankan manual supaya data awal seperti roles, permissions, supplier, raw material, product, production, dan sales ikut terisi.

## Akun Login Default

Setelah menjalankan seeder, akun default yang bisa dipakai:

- Email: `test@example.com`
- Password: `password`

Seeder juga akan membuat data contoh untuk:

- Roles dan permissions
- User dummy
- Supplier
- Raw material
- Product
- Production
- Sales
- Notification

## Menjalankan Test

```bash
php artisan test
```

## Build Asset Production

```bash
npm run build
```

## Struktur Modul Utama

Modul yang saat ini sudah tersedia:

- Dashboard
- User Management
- Master Data Supplier
- Master Data Raw Material
- Master Data Product
- Pembelian Bahan Baku
- Produksi Product
- Penjualan Product

## Catatan Penting

- Pastikan database sudah dibuat terlebih dahulu sebelum menjalankan `php artisan migrate`
- Jika tampilan frontend tidak muncul dengan benar, pastikan `npm run dev` sedang berjalan
- Jika menggunakan PHP 8.3 dan menemui masalah dependency Composer, gunakan PHP 8.4
- Beberapa transaksi memengaruhi stok secara otomatis:
  - pembelian menambah stok raw material
  - produksi mengurangi stok raw material dan menambah stok product
  - penjualan mengurangi stok product

## Perintah Ringkas

Untuk development harian:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

## Repository

Repository project:

`https://github.com/konsultasiskripsiti/sistem-informasi-garmen`
