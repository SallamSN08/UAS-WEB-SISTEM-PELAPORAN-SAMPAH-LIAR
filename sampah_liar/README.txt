================================================================================
SiPAL - Sistem Pelaporan Sampah Liar
================================================================================

CARA INSTALASI:
---------------
1. Extract file ZIP ke folder htdocs/sampah_liar
2. Buka phpMyAdmin (http://localhost/phpmyadmin)
3. Import file sql/sampah_liar.sql ke database
4. Pastikan XAMPP Apache dan MySQL sudah running
5. Akses website di http://localhost/sampah_liar

AKUN DEFAULT:
-------------
Admin:
  Username: admin
  Password: password
  Role: superadmin

User (Warga):
  Username: budi
  Password: password

  Username: ani
  Password: password

  Username: dedi
  Password: password

  Username: siti
  Password: password

  Username: rudi
  Password: password

Petugas:
  Username: petugas1
  Password: password

  Username: petugas2
  Password: password

  Username: petugas3
  Password: password

FITUR:
------
- GPS Otomatis dengan Leaflet.js
- Upload Foto Kondisi
- Tracking Progress Real-time
- Notifikasi Status Update
- Dashboard Admin dengan Chart.js
- Peta Interaktif Semua Titik Laporan
- Grafik & Statistik Lengkap
- Export Data ke CSV
- Sistem Login Terpisah (User & Admin)
- Responsive Mobile & Desktop

STRUKTUR FOLDER:
----------------
sampah_liar/
├── sql/                  # Database SQL
├── includes/             # Config, Auth, Functions
├── assets/               # CSS, JS, Uploads
├── user/                 # Area Warga
├── admin/                # Area Admin
├── index.php             # Landing Page
├── login.php             # Login Warga
├── register.php          # Registrasi Warga
└── logout.php            # Logout Warga

KEAMANAN:
---------
- Password di-hash dengan password_hash()
- Prepared statement untuk semua query
- Validasi file upload (tipe & ukuran)
- Folder uploads dilindungi .htaccess
- Session prefix terpisah user/admin

================================================================================
Dibuat dengan ❤️ untuk kota yang lebih bersih
SiPAL 2026
================================================================================
