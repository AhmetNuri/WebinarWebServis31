# SaaS Lisans Sistemi - Kurulum Kılavuzu

## Sistem Gereksinimleri

- PHP >= 8.2
- MySQL 8.0+
- Composer (local geliştirme için)
- Node.js >= 18 (Tailwind CSS derleme için)

---

## Yerel Geliştirme (Windows/XAMPP)

### 1. XAMPP Kurulumu
- [XAMPP](https://www.apachefriends.org/download.html) indirip `D:\XAMPP` dizinine kurun.
- XAMPP Control Panel'den **Apache** ve **MySQL** servislerini başlatın.

### 2. Projeyi Klonlayın / İndirin
```bash
git clone https://github.com/AhmetNuri/WebinarWebServis31.git D:\XAMPP\htdocs\saas-lisans
cd D:\XAMPP\htdocs\saas-lisans
```

### 3. Otomatik Kurulum (install.bat)
```
install.bat
```
Betik aşağıdaki adımları otomatik gerçekleştirir:
1. `.env` dosyasını oluşturur
2. Composer bağımlılıklarını yükler
3. Uygulama anahtarı oluşturur
4. Veritabanı migrasyonlarını çalıştırır
5. Admin kullanıcısını oluşturur
6. Frontend varlıklarını derler

### 4. Manuel Kurulum
```bash
# .env oluştur
copy .env.example .env

# Bağımlılıkları yükle
composer install

# Uygulama anahtarı oluştur
php artisan key:generate

# .env dosyasını düzenle - veritabanı bilgilerini girin
notepad .env

# Veritabanı oluştur (phpMyAdmin veya MySQL komut satırı)
# CREATE DATABASE saas_lisans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Migrasyonları çalıştır ve admin kullanıcısını oluştur
php artisan migrate --seed

# Frontend derle
npm install
npm run build

# Storage linkini oluştur
php artisan storage:link
```

### 5. .env Veritabanı Ayarları
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_lisans
DB_USERNAME=root
DB_PASSWORD=
```

### 6. XAMPP VirtualHost Ayarı (Önerilen)
`D:\XAMPP\apache\conf\extra\httpd-vhosts.conf` dosyasına ekleyin:
```apache
<VirtualHost *:80>
    ServerName saas-lisans.local
    DocumentRoot "D:/XAMPP/htdocs/saas-lisans/public"
    <Directory "D:/XAMPP/htdocs/saas-lisans/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

`C:\Windows\System32\drivers\etc\hosts` dosyasına ekleyin:
```
127.0.0.1  saas-lisans.local
```

---

## Varsayılan Giriş Bilgileri

| Alan    | Değer                  |
|---------|------------------------|
| E-posta | admin@example.com      |
| Şifre   | password               |

> ⚠️ **ÖNEMLİ:** İlk girişte admin şifresini değiştirin!

---

## cPanel Paylaşımlı Hosting'e Taşıma

> **Not:** Paylaşımlı hosting'de Composer çalıştırılamaz. Bu nedenle yerel makinede build yapıp dosyaları FTP ile taşıyın.

### 1. Local'de Production Build
```bash
# .env'i production ayarlarına göre düzenle
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sizin-domain.com

# Production için optimize et
composer install --no-dev --optimize-autoloader
npm run build

# Cache'leri oluştur
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. FTP ile Taşıma
Aşağıdaki klasör/dosyaları hosting'e yükleyin:
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/` → hosting'deki `public_html/` içine
- `resources/`
- `routes/`
- `storage/` (izinlere dikkat)
- `vendor/`
- `artisan`
- `composer.json`
- `composer.lock`
- `.env` (production ayarlarıyla)

### 3. public_html Ayarı
`public/index.php` dosyasındaki yolları hosting'deki gerçek dizin yapısına göre güncelleyin:
```php
// public/index.php
$app = require_once __DIR__.'/../laravel/bootstrap/app.php';
```

### 4. Hosting'de Migration
SSH erişiminiz varsa:
```bash
php artisan migrate --seed --force
```

SSH yoksa, yerel ortamda SQL dump alıp phpMyAdmin ile import edin:
```bash
php artisan migrate --seed
mysqldump -u root saas_lisans > saas_lisans_dump.sql
```

---

## API Kullanımı (Delphi VCL Rest Client)

### Endpoint
```
POST /api/v1/license/check
Content-Type: application/json
Accept: application/json
```

### İstek Body
```json
{
    "email": "kullanici@ornek.com",
    "serial_number": "XXXXXXXX-XXXXXXXX-XXXXXXXX",
    "device_id": "HWID-ABC123"
}
```

### Başarılı Yanıt
```json
{
    "valid": true,
    "expires_at": "2026-08-15",
    "days_left": 175,
    "package": "DelphiRestService",
    "type": "yearly",
    "emergency": false,
    "warning": "Lisansınızın bitmesine 10 günden az kaldı!"
}
```

### Başarısız Yanıt
```json
{
    "valid": false,
    "message": "Cihaz eşleşmedi."
}
```

### Rate Limiting
- Endpoint başına dakikada 30 istek limiti uygulanmaktadır.
- Limit aşıldığında `429 Too Many Requests` yanıtı döner.

### Delphi Örnek Kod
```delphi
procedure TForm1.CheckLicense;
var
  RESTClient: TRESTClient;
  RESTRequest: TRESTRequest;
  RESTResponse: TRESTResponse;
  JSONObj: TJSONObject;
begin
  RESTClient := TRESTClient.Create('https://sizin-domain.com');
  RESTRequest := TRESTRequest.Create(nil);
  RESTResponse := TRESTResponse.Create(nil);
  try
    RESTClient.BaseURL := 'https://sizin-domain.com';
    RESTRequest.Client := RESTClient;
    RESTRequest.Response := RESTResponse;
    RESTRequest.Resource := 'api/v1/license/check';
    RESTRequest.Method := rmPOST;
    RESTRequest.AddBody(
      '{"email":"user@example.com","serial_number":"XXXX-XXXX-XXXX","device_id":"HW123"}',
      TRESTContentType.ctAPPLICATION_JSON
    );
    RESTRequest.Execute;

    if RESTResponse.StatusCode = 200 then
    begin
      JSONObj := TJSONObject.ParseJSONValue(RESTResponse.Content) as TJSONObject;
      try
        if JSONObj.GetValue<Boolean>('valid') then
          ShowMessage('Lisans geçerli! Bitiş: ' + JSONObj.GetValue<string>('expires_at'))
        else
          ShowMessage('Lisans geçersiz: ' + JSONObj.GetValue<string>('message'));
      finally
        JSONObj.Free;
      end;
    end;
  finally
    RESTClient.Free;
    RESTRequest.Free;
    RESTResponse.Free;
  end;
end;
```
