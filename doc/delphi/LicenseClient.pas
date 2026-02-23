unit LicenseClient;

{
  SaaS Lisans Sistemi - Delphi REST İstemcisi
  ============================================
  Bu ünite, web servisine lisans doğrulama isteği göndermek için
  kullanılan TLicenseClient sınıfını içerir.

  Kullanım:
    1. TLicenseClient.Create(ABaseURL) ile nesne oluşturun.
    2. CheckLicense(AEmail, ASerialNumber) ile lisansı doğrulayın.
    3. Sonucu TLicenseCheckResult kaydı üzerinden okuyun.
    4. İşlem bitince nesneyi Free edin.

  Gerekli Delphi bileşen kütüphaneleri:
    REST.Client, REST.Types, REST.Json, System.JSON
}

interface

uses
  System.SysUtils,
  System.Classes,
  System.JSON,
  REST.Client,
  REST.Types,
  REST.Json,
  Registry,
  WinAPI.Windows;

type
  /// <summary>
  /// Lisans doğrulama sonucunu tutan kayıt.
  /// </summary>
  TLicenseCheckResult = record
    /// <summary>Lisans geçerliyse True.</summary>
    Valid: Boolean;
    /// <summary>Geçersiz veya askıya alınmış lisanslarda hata mesajı.</summary>
    ErrorMessage: string;
    /// <summary>Lisansın bitiş tarihi (YYYY-AA-GG). Ömür boyu lisanslarda boş.</summary>
    ExpiresAt: string;
    /// <summary>Lisansın bitmesine kalan gün sayısı. Ömür boyu lisanslarda -1.</summary>
    DaysLeft: Integer;
    /// <summary>Ürün paketi adı.</summary>
    Package: string;
    /// <summary>Lisans türü: 'yearly' | 'lifetime'</summary>
    LicenseType: string;
    /// <summary>Acil durum bayrağı.</summary>
    Emergency: Boolean;
    /// <summary>Bitiş tarihi 10 günden az kaldığında uyarı mesajı.</summary>
    Warning: string;
    /// <summary>HTTP veya ağ hatası oluştuğunda hata metni.</summary>
    NetworkError: string;
  end;

  /// <summary>
  /// Web servisine lisans doğrulama isteği gönderen istemci sınıfı.
  /// </summary>
  TLicenseClient = class
  private
    FBaseURL: string;
    FTimeoutSec: Integer;
    function ParseResponse(const AJSON: string; out AResult: TLicenseCheckResult): Boolean;
  public
    /// <param name="ABaseURL">
    ///   Servis kök URL'i. Örnek: 'https://sizin-domain.com'
    /// </param>
    /// <param name="ATimeoutSec">
    ///   HTTP zaman aşımı (saniye). Varsayılan: 15
    /// </param>
    constructor Create(const ABaseURL: string; ATimeoutSec: Integer = 15);

    /// <summary>
    ///   Lisans doğrulama isteği gönderir.
    /// </summary>
    /// <param name="AEmail">Kullanıcının e-posta adresi.</param>
    /// <param name="ASerialNumber">Seri numarası (ör. XXXXXXXX-XXXXXXXX-XXXXXXXX).</param>
    /// <param name="ADeviceID">
    ///   Cihaz kimliği. Boş bırakılırsa GenerateDeviceID ile otomatik üretilir.
    /// </param>
    function CheckLicense(const AEmail, ASerialNumber: string;
      const ADeviceID: string = ''): TLicenseCheckResult;

    /// <summary>
    ///   Mevcut makinenin donanım kimliğini (HWID) döndürür.
    ///   Değer, Windows kayıt defterindeki MachineGuid'e dayanır.
    /// </summary>
    function GenerateDeviceID: string;

    /// <summary>Zaman aşımı süresi (saniye).</summary>
    property TimeoutSec: Integer read FTimeoutSec write FTimeoutSec;
    /// <summary>Servis kök URL'i.</summary>
    property BaseURL: string read FBaseURL write FBaseURL;
  end;

implementation

{ TLicenseClient }

constructor TLicenseClient.Create(const ABaseURL: string; ATimeoutSec: Integer);
begin
  inherited Create;
  FBaseURL    := ABaseURL.TrimRight(['/']);
  FTimeoutSec := ATimeoutSec;
end;

function TLicenseClient.GenerateDeviceID: string;
var
  Reg: TRegistry;
begin
  Result := '';
  Reg := TRegistry.Create(KEY_READ);
  try
    Reg.RootKey := HKEY_LOCAL_MACHINE;
    if Reg.OpenKey('SOFTWARE\Microsoft\Cryptography', False) then
    begin
      Result := Reg.ReadString('MachineGuid');
      Reg.CloseKey;
    end;
  finally
    Reg.Free;
  end;

  // Fallback: bilgisayar adını kullan
  if Result = '' then
  begin
    SetLength(Result, MAX_COMPUTERNAME_LENGTH + 1);
    var Len: DWORD := MAX_COMPUTERNAME_LENGTH + 1;
    if GetComputerName(PChar(Result), Len) then
      SetLength(Result, Len)
    else
      Result := 'UNKNOWN-DEVICE';
  end;
end;

function TLicenseClient.ParseResponse(const AJSON: string;
  out AResult: TLicenseCheckResult): Boolean;
var
  JSONValue: TJSONValue;
  JSONObj: TJSONObject;
  DaysLeftVal: TJSONValue;
begin
  Result := False;
  AResult := Default(TLicenseCheckResult);
  AResult.DaysLeft := -1;

  JSONValue := TJSONObject.ParseJSONValue(AJSON);
  if JSONValue = nil then
    Exit;

  try
    if not (JSONValue is TJSONObject) then
      Exit;

    JSONObj := JSONValue as TJSONObject;

    AResult.Valid := JSONObj.GetValue<Boolean>('valid', False);

    if not AResult.Valid then
    begin
      AResult.ErrorMessage := JSONObj.GetValue<string>('message', '');
      Result := True;
      Exit;
    end;

    AResult.ExpiresAt   := JSONObj.GetValue<string>('expires_at', '');
    AResult.Package     := JSONObj.GetValue<string>('package', '');
    AResult.LicenseType := JSONObj.GetValue<string>('type', '');
    AResult.Emergency   := JSONObj.GetValue<Boolean>('emergency', False);
    AResult.Warning     := JSONObj.GetValue<string>('warning', '');

    DaysLeftVal := JSONObj.Values['days_left'];
    if (DaysLeftVal <> nil) and not (DaysLeftVal is TJSONNull) then
      AResult.DaysLeft := DaysLeftVal.GetValue<Integer>
    else
      AResult.DaysLeft := -1; // ömür boyu lisans

    Result := True;
  finally
    JSONValue.Free;
  end;
end;

function TLicenseClient.CheckLicense(const AEmail, ASerialNumber: string;
  const ADeviceID: string): TLicenseCheckResult;
var
  RESTClient:   TRESTClient;
  RESTRequest:  TRESTRequest;
  RESTResponse: TRESTResponse;
  DeviceID:     string;
  Body:         TJSONObject;
begin
  Result := Default(TLicenseCheckResult);
  Result.DaysLeft := -1;

  DeviceID := ADeviceID;
  if DeviceID = '' then
    DeviceID := GenerateDeviceID;

  RESTClient   := TRESTClient.Create(FBaseURL);
  RESTRequest  := TRESTRequest.Create(nil);
  RESTResponse := TRESTResponse.Create(nil);
  try
    RESTRequest.Client   := RESTClient;
    RESTRequest.Response := RESTResponse;
    RESTRequest.Resource := 'api/v1/license/check';
    RESTRequest.Method   := rmPOST;
    RESTRequest.Timeout  := FTimeoutSec * 1000;

    RESTClient.ContentType := 'application/json';
    RESTRequest.Params.AddHeader('Accept', 'application/json');

    Body := TJSONObject.Create;
    try
      Body.AddPair('email',         AEmail);
      Body.AddPair('serial_number', ASerialNumber);
      Body.AddPair('device_id',     DeviceID);

      RESTRequest.AddBody(Body.ToJSON, TRESTContentType.ctAPPLICATION_JSON);
    finally
      Body.Free;
    end;

    try
      RESTRequest.Execute;
    except
      on E: Exception do
      begin
        Result.Valid        := False;
        Result.NetworkError := E.Message;
        Exit;
      end;
    end;

    if RESTResponse.StatusCode = 429 then
    begin
      Result.Valid        := False;
      Result.NetworkError := 'İstek limiti aşıldı. Lütfen bir dakika bekleyip tekrar deneyin.';
      Exit;
    end;

    if RESTResponse.StatusCode <> 200 then
    begin
      Result.Valid        := False;
      Result.NetworkError := Format('Sunucu hatası: HTTP %d', [RESTResponse.StatusCode]);
      Exit;
    end;

    if not ParseResponse(RESTResponse.Content, Result) then
    begin
      Result.Valid        := False;
      Result.NetworkError := 'Sunucu yanıtı ayrıştırılamadı.';
    end;
  finally
    RESTResponse.Free;
    RESTRequest.Free;
    RESTClient.Free;
  end;
end;

end.
