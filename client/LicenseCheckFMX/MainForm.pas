unit MainForm;

interface

uses
  System.SysUtils, System.Types, System.UITypes, System.Classes,
  System.Variants, System.JSON, System.Hash,
  FMX.Types, FMX.Controls, FMX.Forms, FMX.Graphics, FMX.Dialogs,
  FMX.StdCtrls, FMX.Edit, FMX.Memo, FMX.Controls.Presentation,
  FMX.ScrollBox, FMX.Layouts,
  REST.Client, REST.Types, REST.Json;

type
  TfrmMain = class(TForm)
    // REST bileşenleri
    RESTClient1: TRESTClient;
    RESTRequest1: TRESTRequest;
    RESTResponse1: TRESTResponse;

    // Layout bileşenleri
    LayoutMain: TLayout;
    LayoutFields: TLayout;
    LayoutButtons: TLayout;
    LayoutResult: TLayout;

    // Başlık
    lblTitle: TLabel;

    // Sunucu URL
    lblBaseURL: TLabel;
    edtBaseURL: TEdit;

    // E-posta alanı
    lblEmail: TLabel;
    edtEmail: TEdit;

    // Seri numarası alanı
    lblSerialNumber: TLabel;
    edtSerialNumber: TEdit;

    // Cihaz ID alanı
    lblDeviceID: TLabel;
    edtDeviceID: TEdit;

    // Kontrol butonu
    btnCheck: TButton;
    btnClear: TButton;

    // Durum göstergesi
    lblStatus: TLabel;

    // Sonuç alanı
    lblResultTitle: TLabel;
    memoResult: TMemo;

    procedure FormCreate(Sender: TObject);
    procedure btnCheckClick(Sender: TObject);
    procedure btnClearClick(Sender: TObject);

  private
    procedure SetStatus(const AText: string; AColor: TAlphaColor);
    procedure ShowLicenseResult(const AJson: TJSONObject);
    procedure ShowError(const AMessage: string);
    function GetDeviceID: string;
  end;

var
  frmMain: TfrmMain;

implementation

{$R *.fmx}

{ TfrmMain }

procedure TfrmMain.FormCreate(Sender: TObject);
begin
  // REST bileşenlerini yapılandır
  RESTClient1.BaseURL := edtBaseURL.Text;
  RESTRequest1.Client := RESTClient1;
  RESTRequest1.Response := RESTResponse1;
  RESTRequest1.Resource := 'api/v1/license/check';
  RESTRequest1.Method := rmPOST;
  RESTRequest1.Accept := 'application/json';
  RESTRequest1.AcceptCharset := 'UTF-8';

  // Cihaz ID alanını otomatik doldur (isteğe bağlı)
  edtDeviceID.Text := GetDeviceID;

  SetStatus('Hazır', TAlphaColorRec.Gray);
end;

procedure TfrmMain.btnCheckClick(Sender: TObject);
var
  LBodyJSON: TJSONObject;
  LResponseJSON: TJSONObject;
begin
  // Giriş doğrulama
  if Trim(edtEmail.Text) = '' then
  begin
    ShowError('E-posta adresi boş bırakılamaz.');
    edtEmail.SetFocus;
    Exit;
  end;

  if Trim(edtSerialNumber.Text) = '' then
  begin
    ShowError('Seri numarası boş bırakılamaz.');
    edtSerialNumber.SetFocus;
    Exit;
  end;

  if Trim(edtBaseURL.Text) = '' then
  begin
    ShowError('Sunucu URL adresi boş bırakılamaz.');
    edtBaseURL.SetFocus;
    Exit;
  end;

  SetStatus('Kontrol ediliyor...', TAlphaColorRec.Blue);
  btnCheck.Enabled := False;
  memoResult.Text := '';

  try
    // REST istemcisini güncelle
    RESTClient1.BaseURL := Trim(edtBaseURL.Text);

    // Önceki parametreleri temizle
    RESTRequest1.Params.Clear;

    // JSON istek gövdesini oluştur
    LBodyJSON := TJSONObject.Create;
    try
      LBodyJSON.AddPair('email', Trim(edtEmail.Text));
      LBodyJSON.AddPair('serial_number', Trim(edtSerialNumber.Text));
      if Trim(edtDeviceID.Text) <> '' then
        LBodyJSON.AddPair('device_id', Trim(edtDeviceID.Text));

      // İstek gövdesini ayarla
      RESTRequest1.AddBody(LBodyJSON.ToJSON, TRESTContentType.ctAPPLICATION_JSON);
    finally
      LBodyJSON.Free;
    end;

    // İsteği çalıştır
    RESTRequest1.Execute;

    // Yanıtı işle
    if RESTResponse1.StatusCode = 200 then
    begin
      LResponseJSON := TJSONObject.ParseJSONValue(RESTResponse1.Content) as TJSONObject;
      if LResponseJSON <> nil then
      try
        ShowLicenseResult(LResponseJSON);
      finally
        LResponseJSON.Free;
      end
      else
        ShowError('Geçersiz JSON yanıtı alındı.');
    end
    else if RESTResponse1.StatusCode = 429 then
    begin
      SetStatus('Hata', TAlphaColorRec.Red);
      ShowError('Çok fazla istek gönderildi. Lütfen bir süre bekleyip tekrar deneyin.' +
                ' (429 Too Many Requests)');
    end
    else if RESTResponse1.StatusCode = 422 then
    begin
      SetStatus('Hata', TAlphaColorRec.Red);
      ShowError('Geçersiz istek parametreleri. (422 Unprocessable Entity)' + sLineBreak +
                RESTResponse1.Content);
    end
    else
    begin
      SetStatus('Hata', TAlphaColorRec.Red);
      ShowError(Format('Sunucu hatası: HTTP %d', [RESTResponse1.StatusCode]) + sLineBreak +
                RESTResponse1.Content);
    end;

  except
    on E: Exception do
    begin
      SetStatus('Bağlantı hatası', TAlphaColorRec.Red);
      ShowError('Bağlantı hatası: ' + E.Message);
    end;
  end;

  btnCheck.Enabled := True;
end;

procedure TfrmMain.btnClearClick(Sender: TObject);
begin
  edtEmail.Text := '';
  edtSerialNumber.Text := '';
  edtDeviceID.Text := GetDeviceID;
  memoResult.Text := '';
  SetStatus('Hazır', TAlphaColorRec.Gray);
end;

procedure TfrmMain.SetStatus(const AText: string; AColor: TAlphaColor);
begin
  lblStatus.Text := AText;
  lblStatus.FontColor := AColor;
end;

procedure TfrmMain.ShowLicenseResult(const AJson: TJSONObject);
var
  LValid: Boolean;
  LMessage, LExpiresAt, LPackage, LType: string;
  LDaysLeft: Integer;
  LEmergency: Boolean;
  LWarning: string;
  LSB: TStringBuilder;
begin
  LSB := TStringBuilder.Create;
  try
    LValid := AJson.GetValue<Boolean>('valid', False);

    if LValid then
    begin
      SetStatus('✓ Lisans GEÇERLİ', TAlphaColorRec.Green);

      LExpiresAt := AJson.GetValue<string>('expires_at', '');
      LDaysLeft  := AJson.GetValue<Integer>('days_left', -1);
      LPackage   := AJson.GetValue<string>('package', '');
      LType      := AJson.GetValue<string>('type', '');
      LEmergency := AJson.GetValue<Boolean>('emergency', False);
      LWarning   := AJson.GetValue<string>('warning', '');

      LSB.AppendLine('DURUM     : Lisans Geçerli ✓');
      LSB.AppendLine('');

      if LType = 'lifetime' then
        LSB.AppendLine('TÜR       : Ömür Boyu')
      else if LType = 'yearly' then
        LSB.AppendLine('TÜR       : Yıllık')
      else if LType = 'monthly' then
        LSB.AppendLine('TÜR       : Aylık')
      else
        LSB.AppendLine('TÜR       : ' + LType);

      if LPackage <> '' then
        LSB.AppendLine('PAKET     : ' + LPackage);

      if LExpiresAt <> '' then
        LSB.AppendLine('BİTİŞ     : ' + LExpiresAt)
      else if LType = 'lifetime' then
        LSB.AppendLine('BİTİŞ     : Süresiz');

      if LDaysLeft >= 0 then
        LSB.AppendLine(Format('KALAN GÜN : %d gün', [LDaysLeft]))
      else if LType = 'lifetime' then
        LSB.AppendLine('KALAN GÜN : Süresiz');

      if LEmergency then
        LSB.AppendLine('ACİL MOD  : Evet ⚠');

      if LWarning <> '' then
      begin
        LSB.AppendLine('');
        LSB.AppendLine('⚠ UYARI   : ' + LWarning);
      end;
    end
    else
    begin
      SetStatus('✗ Lisans GEÇERSİZ', TAlphaColorRec.Red);

      LMessage := AJson.GetValue<string>('message', 'Bilinmeyen hata.');
      LSB.AppendLine('DURUM     : Lisans Geçersiz ✗');
      LSB.AppendLine('');
      LSB.AppendLine('MESAJ     : ' + LMessage);
    end;

    memoResult.Text := LSB.ToString;
  finally
    LSB.Free;
  end;
end;

procedure TfrmMain.ShowError(const AMessage: string);
begin
  memoResult.Text := 'HATA: ' + AMessage;
  SetStatus('Hata', TAlphaColorRec.Red);
end;

function TfrmMain.GetDeviceID: string;
var
  LHostname: string;
begin
  // Basit bir cihaz kimliği oluştur (gerçek uygulamada
  // donanım bilgilerinden türetilebilir)
  LHostname := GetEnvironmentVariable({$IFDEF MSWINDOWS}'COMPUTERNAME'{$ELSE}'HOSTNAME'{$ENDIF});
  if LHostname = '' then
    LHostname := 'UNKNOWN-DEVICE';

  Result := 'FMX-' + {$IFDEF MSWINDOWS}'WIN'{$ENDIF}
                      {$IFDEF MACOS}'MAC'{$ENDIF}
                      {$IFDEF IOS}'IOS'{$ENDIF}
                      {$IFDEF ANDROID}'AND'{$ENDIF}
                      {$IFDEF LINUX}'LNX'{$ENDIF}
            + '-' + Copy(THashMD5.GetHashString(LHostname), 1, 8).ToUpper;
end;

end.
