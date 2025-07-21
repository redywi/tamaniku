# Tamaniku - Sistem Manajemen Toko Tanaman Hias

Tamaniku adalah sistem manajemen toko tanaman hias yang responsif dengan fitur lengkap untuk admin dan customer.

## 🚀 Fitur Utama

### Untuk Customer (Frontend)
- **Katalog Produk**: Browse tanaman hias dengan filter kategori, harga, dan pencarian
- **Filter & Sort**: Filter berdasarkan kategori, rentang harga, dan sorting
- **Detail Produk**: Informasi lengkap produk dengan gambar
- **Pemesanan**: Sistem pemesanan terintegrasi dengan WhatsApp
- **Responsive Design**: Tampilan optimal di desktop, tablet, dan mobile

### Untuk Admin (Backend)
- **Dashboard**: Statistik ringkasan (total produk, kategori, pesanan)
- **Manajemen Kategori**: CRUD lengkap dengan validasi foreign key
- **Manajemen Produk**: CRUD dengan upload gambar dan validasi stok
- **Manajemen Pesanan**: Filter, sort, dan update status pesanan
- **Filter Advanced**: Pencarian dan filter di semua halaman admin
- **Validation**: Validasi lengkap untuk mencegah hapus data yang masih digunakan

## 📋 Persyaratan Sistem

- **Web Server**: Apache/Nginx dengan PHP 7.4+
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **Browser**: Chrome, Firefox, Safari, Edge (versi terbaru)

## 🛠️ Instalasi

### 1. Setup Database
```sql
-- Buat database
CREATE DATABASE db_tamaniku;

-- Import struktur dan data dummy
SOURCE config/db_tamaniku.sql;
```

### 2. Konfigurasi Database
Edit file `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // sesuaikan username
define('DB_PASS', '');            // sesuaikan password  
define('DB_NAME', 'db_tamaniku');
```

### 3. Setup Folder
```bash
# Pastikan folder uploads dapat ditulis
chmod 755 uploads/

# Atau buat folder jika belum ada
mkdir uploads
chmod 755 uploads
```

### 4. Test Instalasi
Akses: `http://localhost/tamaniku/test_connection.php`

## 🔐 Login Admin

**URL:** `admin/login.php`
- **Username:** `admin`
- **Password:** `admin`

## 📁 Struktur Folder

```
tamaniku/
├── admin/                  # Panel admin
│   ├── index.php          # Dashboard
│   ├── login.php          # Login admin
│   ├── logout.php         # Logout dengan konfirmasi
│   ├── kategori.php       # Manajemen kategori
│   ├── kategori_tambah.php
│   ├── kategori_edit.php
│   ├── kategori_hapus.php
│   ├── produk.php         # Manajemen produk
│   ├── produk_tambah.php
│   ├── produk_edit.php
│   ├── produk_hapus.php
│   └── pesanan.php        # Manajemen pesanan
├── api/                   # REST API
│   ├── kategori.php
│   ├── produk.php
│   └── pesanan.php
├── assets/
│   ├── css/style.css      # Styling responsive
│   └── js/main.js         # JavaScript untuk interaktivitas
├── config/
│   ├── database.php       # Konfigurasi database
│   └── db_tamaniku.sql    # Struktur database dan data dummy
├── templates/             # Template HTML
│   ├── header.php
│   ├── footer.php
│   ├── header_admin.php
│   └── footer_admin.php
├── uploads/               # Upload gambar produk
├── index.php             # Halaman utama
├── detail_produk.php     # Detail produk
└── test_connection.php   # Test koneksi database
```

## 🎨 Fitur Responsif

### CSS Responsive
- **Mobile First**: Desain yang mengutamakan tampilan mobile
- **Breakpoints**: 768px (tablet), 480px (mobile), 360px (small mobile)
- **Grid System**: Flexbox dan CSS Grid untuk layout yang fleksibel
- **Typography**: Font yang skalabel dan mudah dibaca

### JavaScript Enhanced
- **Real-time Validation**: Validasi form secara real-time
- **Loading States**: Indikator loading untuk UX yang lebih baik
- **Auto-submit Filters**: Filter otomatis tanpa reload halaman
- **Debounced Search**: Pencarian yang efisien dengan delay
- **Responsive Tables**: Tabel yang dapat di-scroll horizontal di mobile

## 🔍 Fitur Filter

### Filter Frontend (index.php)
- **Pencarian**: Cari berdasarkan nama produk
- **Kategori**: Filter berdasarkan kategori tanaman
- **Harga**: Filter berdasarkan rentang harga
- **Sort**: Urutkan berdasarkan nama, harga, atau tanggal

### Filter Admin
- **Kategori**: Pencarian dan sort berdasarkan nama atau jumlah produk
- **Produk**: Filter kategori, stok, harga, dengan pencarian
- **Pesanan**: Filter status, tanggal, pelanggan dengan pencarian

## 🛡️ Keamanan & Validasi

### Validasi Database
- **Foreign Key Protection**: Tidak bisa hapus kategori/produk yang masih digunakan
- **Data Integrity**: Validasi input untuk menjaga konsistensi data
- **SQL Injection Prevention**: Prepared statements untuk semua query

### Validasi Form
- **Client-side**: JavaScript validation untuk UX yang responsif
- **Server-side**: PHP validation untuk keamanan
- **File Upload**: Validasi tipe dan ukuran file gambar

## 📱 Mobile Optimization

### Touch-Friendly
- **Button Size**: Minimal 44px untuk mudah di-tap
- **Spacing**: Margin yang cukup antar elemen
- **Gestures**: Support untuk touch dan swipe

### Performance
- **Lazy Loading**: Gambar dimuat saat diperlukan
- **Compressed CSS/JS**: Asset yang dioptimasi
- **Caching**: Header cache untuk asset statis

## 🎯 Data Dummy

Sistem sudah include data dummy:
- **8 Kategori**: Tanaman Hias Daun, Bunga, Sukulen, Herbal, Indoor, Outdoor, Gantung, Air
- **40+ Produk**: Berbagai jenis tanaman dengan harga bervariasi
- **1 Admin**: Username/password: admin/admin

## 🚨 Troubleshooting

### 1. Error Koneksi Database
```
Fatal error: Uncaught mysqli_sql_exception
```
**Solusi**: Periksa konfigurasi database di `config/database.php`

### 2. Folder Uploads Error
```
Warning: move_uploaded_file(): failed to open stream
```
**Solusi**: 
```bash
chmod 755 uploads/
chown www-data:www-data uploads/  # Linux/Mac
```

### 3. Login Admin Tidak Bisa
**Solusi**: Import ulang file `config/db_tamaniku.sql`

### 4. API Tidak Berfungsi
**Solusi**: Pastikan URL rewrite aktif atau akses langsung `api/produk.php`

## 🔧 Customization

### Menambah Kategori Baru
1. Login admin → Kategori → Tambah Kategori
2. Sistem akan otomatis update filter di frontend

### Mengganti Logo/Branding
1. Edit file `templates/header.php` dan `templates/header_admin.php`
2. Ganti text "Tamaniku" dengan brand Anda

### Mengubah WhatsApp Admin
Edit file `assets/js/main.js` pada bagian:
```javascript
const nomorAdmin = '6281234567890'; // Ganti dengan nomor Anda
```

## 📞 Support

Untuk bantuan teknis atau pertanyaan, silakan buat issue di repository atau hubungi developer.

## 📝 License

Project ini menggunakan MIT License - bebas digunakan untuk keperluan komersial dan non-komersial.

---

**Tamaniku** - Bringing Nature to Your Digital Doorstep 🌱
