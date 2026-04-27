# Inventory Management App

Aplikasi ini adalah sistem manajemen inventaris yang dibangun menggunakan teknologi modern untuk memastikan performa, skalabilitas, dan kemudahan pengembangan.

---

## 🚀 Tech Stack

* **Backend**: Laravel 12
* **Frontend**: Alpine.js
* **Database**: PostgreSQL

---

## 📦 Fitur Utama

* Manajemen Produk
* Stock In & Stock Out
* Tracking Stock Movement
* Dashboard Statistik
* User Management (Admin / Staff / User)

---

## ⚙️ Persyaratan Sistem

Pastikan environment kamu sudah terinstall:

* PHP >= 8.2
* Composer
* Node.js & NPM
* PostgreSQL
* Git

---

## 📥 Cara Menjalankan Project dari GitHub

### 1. Clone Repository

```bash
git clone https://github.com/username/inventory-management.git
cd inventory-management
```

---

### 2. Install Dependency Backend

```bash
composer install
```

---

### 3. Install Dependency Frontend

```bash
npm install
```

---

### 4. Setup Environment

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

---

### 5. Generate App Key

```bash
php artisan key:generate
```

---

### 6. Konfigurasi Database PostgreSQL

Edit file `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventory_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

### 7. Migrasi Database

```bash
php artisan migrate
```

```bash
php artisan db:seed
```

---

### 8. Build Asset Frontend

```bash
npm run build
```

Atau untuk development:

```bash
npm run dev
```

---

### 9. Jalankan Server

```bash
php artisan serve
```

Akses aplikasi di:

```
http://127.0.0.1:8000
```

---

## 🧪 Testing (Optional)

```bash
php artisan test
```

---

## 📁 Struktur Penting

```
app/
resources/
routes/
database/
public/
```

---

## 🤝 Kontribusi

Pull request dan kontribusi sangat terbuka. Silakan fork repository ini dan buat perubahan sesuai kebutuhan.

---

## 📄 License

Project ini menggunakan lisensi MIT.

---

## ✨ Catatan

* Pastikan PostgreSQL sudah berjalan sebelum menjalankan migrasi
* Gunakan `.env` sesuai environment masing-masing
* Disarankan menggunakan tools seperti pgAdmin atau TablePlus untuk monitoring database

---

Happy coding 🚀
