<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QzTrayController extends Controller
{
    private function certPath(): string
    {
        return resource_path('qz/digital-certificate.txt');
    }

    private function keyPath(): string
    {
        return resource_path('qz/private-key.pem');
    }

    /**
     * Public certificate for QZ Tray trust / websocket auth.
     */
    public function certificate(): Response
    {
        $path = $this->certPath();
        if (!is_readable($path)) {
            return response('QZ certificate missing', 500)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        return response(file_get_contents($path), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Same public cert as override.crt for installing into QZ Tray on each PC.
     */
    public function overrideCrt(): Response
    {
        $path = $this->certPath();
        if (!is_readable($path)) {
            return response('QZ certificate missing', 500)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        return response(file_get_contents($path), 200)
            ->header('Content-Type', 'application/x-x509-ca-cert')
            ->header('Content-Disposition', 'attachment; filename="override.crt"')
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Windows helper script: copies override.crt into QZ Tray folder.
     */
    public function installBat(): Response
    {
        $certUrl = url('/qz/override.crt');
        $bat = <<<BAT
@echo off
:: Run as Administrator — installs ERP cert so QZ "Remember" + Allow works
set "DEST=C:\\Program Files\\QZ Tray\\override.crt"
set "SRC=%TEMP%\\fablead-qz-override.crt"

echo Downloading certificate...
powershell -NoProfile -Command "Invoke-WebRequest -UseBasicParsing -Uri '$certUrl' -OutFile '%SRC%'"
if not exist "%SRC%" (
  echo ERROR: Could not download override.crt
  pause
  exit /b 1
)

if not exist "C:\\Program Files\\QZ Tray\\" (
  echo ERROR: QZ Tray not installed. Get it from https://qz.io/download/
  pause
  exit /b 1
)

copy /Y "%SRC%" "%DEST%"
if errorlevel 1 (
  echo ERROR: Copy failed. Right-click this file and Run as administrator.
  pause
  exit /b 1
)

echo.
echo OK - Installed: %DEST%
echo Exit QZ Tray from the system tray, then start it again.
echo Print again, tick Remember, click Allow.
echo.
pause
BAT;

        return response($bat, 200)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="install-qz-trust.bat"')
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Sign a QZ Tray request with the site private key (SHA512).
     */
    public function sign(Request $request): Response
    {
        $toSign = (string) ($request->input('request') ?? $request->query('request', ''));
        if ($toSign === '') {
            return response('Missing request', 400)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $keyPath = $this->keyPath();
        if (!is_readable($keyPath)) {
            return response('QZ private key missing', 500)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $privateKey = openssl_pkey_get_private(file_get_contents($keyPath));
        if ($privateKey === false) {
            return response('Invalid QZ private key', 500)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $signature = '';
        $ok = openssl_sign($toSign, $signature, $privateKey, OPENSSL_ALGO_SHA512);
        if (!$ok) {
            return response('Signing failed', 500)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        return response(base64_encode($signature), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'no-store');
    }
}
