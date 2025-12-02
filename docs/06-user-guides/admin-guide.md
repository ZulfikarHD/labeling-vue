# Admin Guide - Label Generator System

Dokumen ini merupakan panduan lengkap untuk Administrator sistem Label Generator yang bertujuan untuk menjelaskan cara mengelola user, workstation, dan pengaturan sistem, yaitu: user management, workstation management, dan change password functionality yang tersedia untuk role Admin.

## Overview

Administrator memiliki akses penuh ke sistem Label Generator, yang mencakup pengelolaan user, workstation, dan konfigurasi sistem. Panduan ini menjelaskan langkah-langkah untuk melakukan tugas administratif dengan efektif.

## Akses Admin

### Login sebagai Admin

1. Buka halaman login di `/login`
2. Masukkan NP dan password admin
3. Klik tombol **Masuk**
4. Setelah berhasil, Anda akan diarahkan ke dashboard

### Menu Admin

Setelah login sebagai admin, Anda memiliki akses ke menu berikut:

| Menu | Path | Deskripsi |
|------|------|-----------|
| Dashboard | `/` | Halaman utama |
| Kelola User | `/admin/users` | Manajemen akun pengguna |
| Kelola Workstation | `/admin/workstations` | Manajemen tim/stasiun kerja |
| Ubah Password | `/admin/change-password` | Reset password user |

---

## User Management

### Melihat Daftar User

1. Akses menu **Kelola User** di `/admin/users`
2. Halaman menampilkan tabel dengan informasi:
   - NP (Nomor Pegawai)
   - Nama
   - Role (Administrator/Operator)
   - Workstation
   - Status (Aktif/Nonaktif)

### Mencari User

1. Di halaman daftar user, gunakan kolom pencarian
2. Ketik NP yang dicari (otomatis di-convert ke uppercase)
3. Hasil pencarian akan muncul secara otomatis

### Filter User

Gunakan dropdown filter untuk menyaring data:

- **Filter Role**: Tampilkan hanya Admin atau Operator
- **Filter Status**: Tampilkan hanya user Aktif atau Nonaktif
- **Reset**: Klik tombol Reset untuk menghapus semua filter

### Membuat User Baru

1. Klik tombol **Tambah User**
2. Isi form dengan data berikut:

| Field | Keterangan |
|-------|------------|
| NP | Nomor Pegawai (maks 5 karakter, akan di-uppercase) |
| Nama | Nama lengkap (opsional) |
| Password | Pilih opsi password |
| Role | Pilih Administrator atau Operator |
| Workstation | Pilih workstation/tim |
| Status | Centang untuk mengaktifkan |

3. **Opsi Password**:
   - **Password Default**: Centang "Gunakan password default" untuk menggunakan format `Peruri[NP]`
   - **Password Custom**: Hapus centang dan masukkan password minimal 6 karakter

4. Klik **Simpan** untuk menyimpan user baru

### Mengedit User

1. Di daftar user, klik ikon **Edit** (pensil) pada baris user
2. Ubah informasi yang diperlukan:
   - Nama
   - Role
   - Workstation
   - Status aktif
   - Password baru (opsional, kosongkan jika tidak ingin mengubah)
3. Klik **Simpan Perubahan**

> **Catatan**: NP tidak dapat diubah setelah user dibuat.

### Menghapus User

1. Di daftar user, klik ikon **Hapus** (tempat sampah) pada baris user
2. Konfirmasi penghapusan di modal yang muncul
3. Klik **Hapus** untuk menghapus user

> **Peringatan**: Anda tidak dapat menghapus akun sendiri.

---

## Workstation Management

### Melihat Daftar Workstation

1. Akses menu **Kelola Workstation** di `/admin/workstations`
2. Halaman menampilkan:
   - Nama workstation
   - Jumlah user yang terdaftar
   - Status (Aktif/Nonaktif)

### Membuat Workstation Baru

1. Klik tombol **Tambah Workstation**
2. Isi form:
   - **Nama**: Nama workstation (maks 50 karakter, harus unik)
   - **Status**: Centang untuk mengaktifkan
3. Klik **Simpan**

### Mengedit Workstation

1. Klik ikon **Edit** pada baris workstation
2. Ubah nama atau status sesuai kebutuhan
3. Klik **Simpan Perubahan**

### Toggle Status Aktif

1. Klik tombol status (Aktif/Nonaktif) pada baris workstation
2. Status akan langsung berubah

> **Catatan**: Workstation yang nonaktif tidak akan muncul saat memilih workstation untuk user baru.

### Menghapus Workstation

1. Klik ikon **Hapus** pada baris workstation
2. Konfirmasi penghapusan

> **Peringatan**: Workstation yang masih memiliki user tidak dapat dihapus. Pindahkan atau hapus user terlebih dahulu.

---

## Change Password (Reset Password User)

### Mengubah Password User

1. Akses menu **Ubah Password** di `/admin/change-password`
2. Pilih user dari dropdown
3. Pilih opsi password:
   - **Password Default**: Centang untuk reset ke `Peruri[NP]`
   - **Password Custom**: Masukkan password baru dan konfirmasi
4. Klik **Ubah Password**

### Kapan Menggunakan Fitur Ini

- User lupa password dan tidak bisa login
- Perlu mereset password untuk keamanan
- User baru meminta password diubah

---

## Best Practices

### Keamanan

1. **Password**: Gunakan password yang kuat untuk akun admin
2. **Logout**: Selalu logout setelah selesai menggunakan sistem
3. **Review Berkala**: Periksa daftar user secara berkala dan nonaktifkan akun yang tidak digunakan

### Organisasi

1. **Naming Convention**: Gunakan nama workstation yang konsisten (contoh: Team 1, Team 2)
2. **Dokumentasi**: Catat perubahan penting pada user atau workstation
3. **Backup Data**: Pastikan sistem backup berjalan dengan baik

### Troubleshooting

| Masalah | Solusi |
|---------|--------|
| User tidak bisa login | Cek status aktif dan reset password |
| Workstation tidak muncul | Pastikan workstation dalam status Aktif |
| Tidak bisa hapus workstation | Pindahkan/hapus user dari workstation terlebih dahulu |
| Tidak bisa hapus user sendiri | Minta admin lain untuk menghapus akun Anda |

---

## Quick Reference

### Default Password Format

```
Peruri + NP (uppercase)
Contoh: NP = "12345" → Password = "Peruri12345"
```

### Role Permissions

| Fitur | Admin | Operator |
|-------|-------|----------|
| Dashboard | ✅ | ✅ |
| User Management | ✅ | ❌ |
| Workstation Management | ✅ | ❌ |
| Change Password (Admin) | ✅ | ❌ |
| Profile Settings | ✅ | ✅ |

### Keyboard Shortcuts

| Aksi | Shortcut |
|------|----------|
| Search | Langsung ketik di kolom pencarian |
| Submit Form | Enter |
| Cancel Modal | Klik di luar modal atau tombol Batal |

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete
