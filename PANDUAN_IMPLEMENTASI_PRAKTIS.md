# 📚 PANDUAN IMPLEMENTASI PRAKTIS - STEP BY STEP

**Tanggal: 18 Desember 2025**
**Durasi: ~1-2 jam untuk implementasi lengkap**

---

## ✅ STEP 1: DATABASE SETUP (10 menit)

### 1.1 Buka phpMyAdmin

```
1. Buka browser
2. Ketik: http://localhost/phpmyadmin
3. Login jika diminta
4. Klik database "mobilenest_db" di kiri
```

### 1.2 Jalankan SQL Query

Klik tab **"SQL"** dan copy-paste queries berikut satu per satu:

#### Query 1: Buat tabel keranjang (jika belum ada)
```sql
CREATE TABLE IF NOT EXISTS keranjang (
  id_keranjang INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_produk INT NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  tanggal_ditambahkan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE,
  UNIQUE KEY unique_user_product (id_user, id_produk)
);
```

#### Query 2: Update tabel transaksi
```sql
ALTER TABLE transaksi ADD COLUMN IF NOT EXISTS (
  kode_transaksi VARCHAR(50) UNIQUE,
  catatan_user TEXT,
  bukti_pembayaran VARCHAR(255),
  ekspedisi VARCHAR(100),
  no_resi_awal VARCHAR(100)
);
```

#### Query 3: Buat tabel detail_transaksi (jika belum ada)
```sql
CREATE TABLE IF NOT EXISTS detail_transaksi (
  id_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT NOT NULL,
  id_produk INT NOT NULL,
  jumlah INT NOT NULL,
  harga_satuan DECIMAL(10, 2) NOT NULL,
  subtotal DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk)
);
```

#### Query 4: Buat tabel ulasan (jika belum ada)
```sql
CREATE TABLE IF NOT EXISTS ulasan (
  id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_produk INT NOT NULL,
  rating INT CHECK (rating >= 1 AND rating <= 5),
  komentar TEXT,
  tanggal_ulasan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE,
  UNIQUE KEY unique_user_review (id_user, id_produk)
);
```

---

## ✅ STEP 2: PULL CODE DARI GITHUB (5 menit)

### 2.1 Terminal
```bash
cd C:\xampp\htdocs\Project-MobileNest\MobileNest
git pull origin main
```

---

## ✅ STEP 3: UPDATE FILE EXISTING (45 menit)

### File yang perlu di-update:
1. `produk/list-produk.php` - Tambah script & button
2. `produk/detail-produk.php` - Tambah script & quantity input
3. `transaksi/keranjang.php` - Update seluruh file
4. `transaksi/checkout.php` - Update seluruh file
5. `transaksi/proses-pembayaran.php` - Update seluruh file
6. `user/pesanan.php` - Update seluruh file

Lihat file `IMPLEMENTASI_STEP_BY_STEP.md` untuk kode lengkap setiap file.

---

## ✅ STEP 4: UPDATE HEADER

Edit `includes/header.php`:
- Tambah cart badge di navbar
- Tambah script untuk update cart count

---

## ✅ STEP 5: TESTING

1. Test add to cart
2. Test view cart
3. Test checkout
4. Test riwayat pesanan

---

**Lihat file dokumentasi lainnya untuk detail lengkap!**