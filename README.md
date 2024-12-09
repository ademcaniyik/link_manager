# Bağlantı Yönetim Sistemi

Modern ve kullanıcı dostu bir bağlantı (link) yönetim sistemi. Bu uygulama ile bağlantılarınızı kategorilere göre düzenleyebilir, açıklamalar ekleyebilir ve kolayca yönetebilirsiniz.

## Özellikler

- 🔗 Bağlantı ekleme ve yönetme
- 📝 Her bağlantı için açıklama ekleme
- 📁 Kategori bazlı organizasyon
- ✏️ Bağlantı düzenleme
- 🗑️ Bağlantı silme
- 📱 Mobil uyumlu tasarım
- 🎨 Modern ve kullanıcı dostu arayüz

## Kurulum

1. Projeyi XAMPP'ın htdocs klasörüne klonlayın:

```bash
cd c:/xampp/htdocs
git clone [https://github.com/ademcaniyik/link_manager]
```

2. MySQL veritabanını oluşturun:

```sql
CREATE DATABASE link_db;
USE link_db;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url TEXT NOT NULL,
    description TEXT,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

3. `config.php` dosyasındaki veritabanı bağlantı bilgilerini kontrol edin.

4. Tarayıcınızda aşağıdaki URL'i açın:

```
http://localhost/linkler
```

## Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- XAMPP veya benzeri bir local server

## Kullanılan Teknolojiler

- PHP
- MySQL
- Bootstrap 5
- Font Awesome
- JavaScript (AJAX)
- HTML5/CSS3

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına bakın.
