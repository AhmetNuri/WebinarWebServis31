  @echo off
setlocal enabledelayedexpansion

REM ===================================================================
REM SaaS Lisans Sistemi - Windows/XAMPP Kurulum
REM XAMPP dizini: D:\XAMPP
REM ===================================================================

echo ============================================================
echo  SaaS Lisans Sistemi - Windows/XAMPP Kurulum
echo  XAMPP dizini: D:\XAMPP
echo ============================================================
echo.

REM PHP ve uygulama dizini ayarlari
SET "PHP_EXE=D:\XAMPP\php\php.exe"
SET "APP_DIR=%~dp0"
SET "MYSQL_PATH=D:\XAMPP\mysql\bin"

REM Uygulama dizinine gec
cd /d "%APP_DIR%"

echo [Kontrol] Sistem gereksinimleri kontrol ediliyor...
echo ----------------------------------------------------------------
if not exist "%PHP_EXE%" (
    echo [HATA] PHP bulunamadi: %PHP_EXE%
    pause
    exit /b 1
)
echo [OK] PHP bulundu: %PHP_EXE%

REM PHP versiyonu kontrolu
echo.
echo PHP versiyonu kontrol ediliyor...
"%PHP_EXE%" -v 2>nul | findstr /C:"PHP" > "%TEMP%\php_version.txt"
if errorlevel 1 (
    echo [UYARI] PHP versiyonu alinamadi
    set "PHP_VERSION=Unknown"
) else (
    set /p PHP_VERSION=<"%TEMP%\php_version.txt"
    echo   !PHP_VERSION!
    echo [OK] PHP versiyonu uygun
)
del "%TEMP%\php_version.txt" 2>nul

REM PHP 8.2+ kontrolu
"%PHP_EXE%" -r "if (version_compare(PHP_VERSION, '8.2.0', '<')) { echo '[HATA] PHP 8.2+ gerekli! Mevcut: ' . PHP_VERSION . PHP_EOL; exit(1); } else { echo '[OK] PHP versiyonu uygun: ' . PHP_VERSION . PHP_EOL; }"
if errorlevel 1 (
    echo.
    echo XAMPP'in guncel versiyonunu yukleyin.
    pause
    exit /b 1
)

REM Composer yolunu otomatik tespit et - DUZELTILDI
echo.
echo [Kontrol] Composer araniyor...
SET "COMPOSER_FOUND=0"
SET "COMPOSER_CMD="

REM 1. PATH'te composer var mi?
where composer >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
    SET "COMPOSER_CMD=composer"
    SET "COMPOSER_FOUND=1"
    echo [OK] Composer PATH'te bulundu
    goto :composer_found
)

REM 2. D:\composer\composer.bat
IF EXIST "D:\composer\composer.bat" (
    SET "COMPOSER_CMD=D:\composer\composer.bat"
    SET "COMPOSER_FOUND=1"
    echo [OK] Composer bulundu: D:\composer\composer.bat
    goto :composer_found
)

REM 3. D:\composer\composer.phar
IF EXIST "D:\composer\composer.phar" (
    SET "COMPOSER_CMD=%PHP_EXE% D:\composer\composer.phar"
    SET "COMPOSER_FOUND=1"
    echo [OK] Composer bulundu: D:\composer\composer.phar
    goto :composer_found
)

REM 4. AppData Composer
IF EXIST "%APPDATA%\Composer\vendor\bin\composer.bat" (
    SET "COMPOSER_CMD=%APPDATA%\Composer\vendor\bin\composer.bat"
    SET "COMPOSER_FOUND=1"
    echo [OK] Composer bulundu
    goto :composer_found
)

REM 5. Proje dizininde composer.phar
IF EXIST "%APP_DIR%composer.phar" (
    SET "COMPOSER_CMD=%PHP_EXE% %APP_DIR%composer.phar"
    SET "COMPOSER_FOUND=1"
    echo [OK] Composer bulundu: composer.phar
    goto :composer_found
)

REM Composer bulunamadi
echo [HATA] Composer bulunamadi!
echo https://getcomposer.org/download/ adresinden yukleyin
pause
exit /b 1

:composer_found
REM Composer versiyonunu goster
echo.
echo Composer versiyon bilgisi:
call %COMPOSER_CMD% --version
if errorlevel 1 (
    echo [HATA] Composer calistirilamadi!
    pause
    exit /b 1
)

REM Laravel proje dosyalari kontrolu
echo.
echo [Kontrol] Laravel proje dosyalari kontrol ediliyor...
echo   Script dizini: %APP_DIR%
echo   Calisma dizini: %CD%
echo.

if not exist "%APP_DIR%artisan" (
    echo [HATA] artisan dosyasi bulunamadi!
    echo Bu bir Laravel projesi degil veya yanlis dizindesiniz.
    echo.
    pause
    exit /b 1
)
echo [OK] artisan bulundu

if not exist "%APP_DIR%composer.json" (
    echo [HATA] composer.json bulunamadi!
    echo.
    pause
    exit /b 1
)
echo [OK] composer.json bulundu
echo.

REM Devam onayı al
echo ============================================================
echo  KURULUMA DEVAM EDILECEK
echo ============================================================
echo.
set /p START="Kuruluma baslamak istiyor musunuz? (E/H): "
IF /I "%START%" NEQ "E" (
    echo Kurulum iptal edildi.
    pause
    exit /b 0
)

REM ===================================================================
REM KURULUM ADIMLARI
REM ===================================================================

echo.
echo [1/8] .env dosyasi olusturuluyor...
echo ----------------------------------------------------------------
IF NOT EXIST "%APP_DIR%.env" (
    IF EXIST "%APP_DIR%.env.example" (
        copy /Y "%APP_DIR%.env.example" "%APP_DIR%.env" >nul 2>&1
        if errorlevel 1 (
            echo [HATA] .env kopyalanamadi!
            pause
            exit /b 1
        )
        echo [OK] .env olusturuldu
        
        REM XAMPP MySQL ayarlarini uygula
        powershell -Command "(Get-Content .env) -replace 'DB_HOST=127.0.0.1', 'DB_HOST=localhost' | Set-Content .env" 2>nul
        powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content .env" 2>nul
        powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env" 2>nul
        echo [OK] XAMPP MySQL ayarlari uygulandi
    ) ELSE (
        echo [HATA] .env.example bulunamadi!
        pause
        exit /b 1
    )
) ELSE (
    echo [BILGI] .env zaten mevcut
)

echo.
echo [2/8] Composer bagimliliklar yukleniyor...
echo ----------------------------------------------------------------
echo LUTFEN BEKLEYIN - Bu islem 2-5 dakika surebilir...
echo.

cd /d "%APP_DIR%"
call %COMPOSER_CMD% install --no-interaction --prefer-dist --optimize-autoloader
IF %ERRORLEVEL% NEQ 0 (
    echo.
    echo [HATA] Composer install basarisiz!
    echo.
    echo Cozum onerileri:
    echo   1. Internet baglantisini kontrol edin
    echo   2. composer.lock dosyasini silin
    echo   3. Manuel: composer install
    echo.
    pause
    exit /b 1
)

if not exist "%APP_DIR%vendor\autoload.php" (
    echo [HATA] vendor/autoload.php olusturulamadi!
    pause
    exit /b 1
)
echo [OK] Composer bagimliliklar yuklendi

echo.
echo [3/8] Uygulama anahtari olusturuluyor...
echo ----------------------------------------------------------------
call "%PHP_EXE%" artisan key:generate --ansi
IF %ERRORLEVEL% NEQ 0 (
    echo [HATA] Anahtar olusturulamadi!
    pause
    exit /b 1
)
echo [OK] Uygulama anahtari olusturuldu

echo.
echo [4/8] Storage dizinleri hazirlaniyor...
echo ----------------------------------------------------------------
if not exist "%APP_DIR%storage\app\public" mkdir "%APP_DIR%storage\app\public" 2>nul
if not exist "%APP_DIR%storage\framework\cache\data" mkdir "%APP_DIR%storage\framework\cache\data" 2>nul
if not exist "%APP_DIR%storage\framework\sessions" mkdir "%APP_DIR%storage\framework\sessions" 2>nul
if not exist "%APP_DIR%storage\framework\views" mkdir "%APP_DIR%storage\framework\views" 2>nul
if not exist "%APP_DIR%storage\framework\testing" mkdir "%APP_DIR%storage\framework\testing" 2>nul
if not exist "%APP_DIR%storage\logs" mkdir "%APP_DIR%storage\logs" 2>nul
if not exist "%APP_DIR%bootstrap\cache" mkdir "%APP_DIR%bootstrap\cache" 2>nul

REM Storage izinleri
icacls "%APP_DIR%storage" /grant Everyone:F /T >nul 2>&1
icacls "%APP_DIR%bootstrap\cache" /grant Everyone:F /T >nul 2>&1

call "%PHP_EXE%" artisan storage:link 2>nul
echo [OK] Storage dizinleri hazir

echo.
echo [5/8] MySQL kontrolu...
echo ----------------------------------------------------------------
netstat -ano | findstr ":3306" >nul 2>&1
if errorlevel 1 (
    echo [UYARI] MySQL calisiyor gibi gorunmuyor!
    echo.
    echo XAMPP Control Panel'den MySQL'i baslatin ve devam edin.
    echo.
    set /p MYSQL_WAIT="MySQL baslatildi mi? (E/H): "
    IF /I "!MYSQL_WAIT!" NEQ "E" (
        echo [BILGI] MySQL kurulumu atlandi
        goto :skip_db
    )
)

REM MySQL baglanti testi
echo MySQL baglantisi test ediliyor...
"%PHP_EXE%" -r "try { new PDO('mysql:host=localhost', 'root', ''); echo '[OK] MySQL baglantisi basarili' . PHP_EOL; } catch(Exception $e) { echo '[HATA] MySQL baglantisi basarisiz: ' . $e->getMessage() . PHP_EOL; exit(1); }"
if errorlevel 1 (
    echo.
    echo XAMPP MySQL sunucusunu baslatin!
    pause
    exit /b 1
)

echo.
echo [6/8] Veritabani olusturuluyor...
echo ----------------------------------------------------------------
"%MYSQL_PATH%\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS simple_lic_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if errorlevel 1 (
    echo [UYARI] Veritabani otomatik olusturulamadi
    echo Manuel olarak phpMyAdmin'den olusturun: simple_lic_manager
    set /p DB_MANUAL="Veritabani olusturuldu mu? (E/H): "
    IF /I "!DB_MANUAL!" NEQ "E" goto :skip_db
) else (
    echo [OK] Veritabani olusturuldu: simple_lic_manager
)

echo.
echo [7/8] Migration ve seed...
echo ----------------------------------------------------------------
call "%PHP_EXE%" artisan migrate:fresh --seed --force
IF %ERRORLEVEL% NEQ 0 (
    echo [HATA] Migration basarisiz!
    echo .env dosyasindaki DB ayarlarini kontrol edin
    pause
    exit /b 1
)
echo [OK] Veritabani kuruldu

:skip_db

echo.
echo [8/8] Cache temizleme...
echo ----------------------------------------------------------------
call "%PHP_EXE%" artisan config:clear 2>nul
call "%PHP_EXE%" artisan cache:clear 2>nul
call "%PHP_EXE%" artisan view:clear 2>nul
echo [OK] Cache temizlendi

echo.
echo ============================================================
echo  ✓✓✓ KURULUM TAMAMLANDI! ✓✓✓
echo ============================================================
echo.
echo  Varsayilan Kullanicilar:
echo  ----------------------------------------
echo  Admin:
echo    E-posta: admin@root.com
echo    Sifre: 123123
echo.
echo  Demo:
echo    E-posta: demo@example.com
echo    Sifre: password
echo  ----------------------------------------
echo.
echo  Erisim Yollari:
echo  ----------------------------------------
echo  XAMPP:
echo    http://localhost/WebinarWebServis31-main/public
echo.
echo  Gelistirme Sunucusu:
echo    php artisan serve
echo    http://localhost:8000
echo  ----------------------------------------
echo.
echo  Kayit:
echo    http://localhost:8000/register
echo.
echo  Iyi calismalar!
echo ============================================================
pause
