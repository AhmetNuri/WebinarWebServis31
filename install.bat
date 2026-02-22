@echo off
echo ============================================================
echo  SaaS Lisans Sistemi - Windows/XAMPP Kurulum
echo  XAMPP dizini: D:\XAMPP
echo ============================================================
echo.

SET PHP=D:\XAMPP\php\php.exe
SET COMPOSER=D:\composer\composer
SET APP_DIR=%~dp0

echo [1/6] .env dosyasi olusturuluyor...
IF NOT EXIST "%APP_DIR%.env" (
    copy "%APP_DIR%.env.example" "%APP_DIR%.env"
    echo .env dosyasi olusturuldu.
) ELSE (
    echo .env zaten mevcut, atlaniyor.
)

echo.
echo [2/6] Composer bagimliliklar yukleniyor...
cd /d "%APP_DIR%"
"%COMPOSER%" install --no-interaction --prefer-dist
IF %ERRORLEVEL% NEQ 0 (
    echo HATA: Composer yukleme basarisiz!
    pause
    exit /b 1
)

echo.
echo [3/6] Uygulama anahtari olusturuluyor...
"%PHP%" artisan key:generate

echo.
echo [4/6] Storage ve cache linkleri olusturuluyor...
"%PHP%" artisan storage:link

echo.
echo [5/6] Veritabani kurulumu...
echo NOT: MySQL calisiyor mu kontrol edin (XAMPP Control Panel)
echo NOT: .env dosyasindaki DB_DATABASE, DB_USERNAME, DB_PASSWORD degerlerini doldurun
echo.
set /p CONTINUE="Devam etmek istiyor musunuz? (E/H): "
IF /I "%CONTINUE%" NEQ "E" (
    echo Kurulum iptal edildi.
    pause
    exit /b 0
)

"%PHP%" artisan migrate --seed
IF %ERRORLEVEL% NEQ 0 (
    echo HATA: Veritabani migrasyonu basarisiz!
    echo .env dosyasindaki DB ayarlarini kontrol edin.
    pause
    exit /b 1
)

echo.
echo [6/6] NPM ile frontend derleniyor...
where npm >nul 2>&1
IF %ERRORLEVEL% EQU 0 (
    npm install
    npm run build
) ELSE (
    echo UYARI: npm bulunamadi. Frontend derleme atlandi.
    echo Node.js yuklemek icin: https://nodejs.org
)

echo.
echo ============================================================
echo  KURULUM TAMAMLANDI!
echo ============================================================
echo.
echo  Varsayilan Admin Giris Bilgileri:
echo    E-posta : admin@example.com
echo    Sifre   : password
echo.
echo  Uygulama URL: http://localhost/[proje-klasoru]/public
echo  veya XAMPP VirtualHost ayarlayin.
echo.
echo  ONEMLI: Ilk giriste admin sifrenizi degistirin!
echo ============================================================
pause
