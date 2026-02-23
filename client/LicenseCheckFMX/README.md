# Lisans Kontrol - FireMonkey Örnek İstemci

Bu proje, **WebinarWebServis31** SaaS lisans sistemine bağlanan örnek bir **Delphi FireMonkey** uygulamasıdır.

## Özellikler

- `TRESTClient`, `TRESTRequest`, `TRESTResponse` bileşenlerini kullanır
- `POST /api/v1/license/check` endpoint'ine JSON isteği gönderir
- Lisans geçerliliği, bitiş tarihi, kalan gün ve paket bilgisini gösterir
- Cihaz bağlama / eşleşme kontrolünü destekler
- Hata ve uyarı mesajlarını ekranda gösterir
- Windows, macOS, iOS, Android ve Linux için derlenebilir

## Gereksinimler

- **Delphi 10.1 Berlin** veya üzeri (Embarcadero RAD Studio)
- `REST.Client`, `REST.Types`, `REST.Json` paketleri (varsayılan olarak gelir)
- `System.Hash` birimi (Delphi 10.1+)

## Proje Dosyaları

| Dosya | Açıklama |
|---|---|
| `LicenseCheckFMX.dpr` | Delphi proje dosyası |
| `LicenseCheckFMX.dproj` | MSBuild proje seçenekleri |
| `MainForm.pas` | Ana form birimi (iş mantığı) |
| `MainForm.fmx` | FireMonkey form tanımı (UI) |

## Kurulum ve Derleme

1. Delphi IDE'yi açın
2. `LicenseCheckFMX.dproj` dosyasını açın
3. `edtBaseURL` alanına sunucu URL'nizi girin (varsayılan: `http://localhost:8000`)
4. `Run > Run` (F9) ile çalıştırın

## Kullanım

1. **Sunucu URL** alanına API sunucusunun adresini girin  
   Örnek: `https://sizin-domain.com`

2. **E-posta Adresi** alanına lisansa kayıtlı e-postayı girin

3. **Seri Numarası** alanına lisans seri numarasını girin  
   Örnek: `XXXXXXXX-XXXXXXXX-XXXXXXXX`

4. **Cihaz ID** alanı otomatik doldurulur; boş bırakılabilir

5. **Lisansı Kontrol Et** butonuna tıklayın

## API İsteği

```
POST /api/v1/license/check
Content-Type: application/json
Accept: application/json

{
    "email": "kullanici@ornek.com",
    "serial_number": "XXXXXXXX-XXXXXXXX-XXXXXXXX",
    "device_id": "FMX-WIN-ABCD1234"
}
```

## Başarılı Yanıt Örneği

```
DURUM     : Lisans Geçerli ✓

TÜR       : Yıllık
PAKET     : DelphiRestService
BİTİŞ     : 2026-08-15
KALAN GÜN : 173 gün
```

## Hata Yanıtı Örneği

```
DURUM     : Lisans Geçersiz ✗

MESAJ     : Cihaz eşleşmedi. Bu lisans farklı bir cihaza kayıtlıdır.
```

## REST Bileşen Yapılandırması

```delphi
RESTClient1.BaseURL  := 'https://sizin-domain.com';

RESTRequest1.Client   := RESTClient1;
RESTRequest1.Response := RESTResponse1;
RESTRequest1.Resource := 'api/v1/license/check';
RESTRequest1.Method   := rmPOST;
RESTRequest1.Accept   := 'application/json';

RESTRequest1.AddBody(
  '{"email":"...","serial_number":"...","device_id":"..."}',
  TRESTContentType.ctAPPLICATION_JSON
);

RESTRequest1.Execute;
```
