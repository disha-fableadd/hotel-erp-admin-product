QZ Tray trust setup (fixes: Remember checked → Allow disabled)

Why Allow is grey?
- QZ Tray only allows "Remember this decision" when the site certificate is trusted.
- Our app uses a custom certificate. Until it is installed on the PC, Allow stays disabled when Remember is ticked.

Print right now (one time):
1. UNCHECK "Remember this decision"
2. Click Allow
(Print works; popup may return next time)

Stop the popup permanently (each billing PC, once):
1. Download: /qz/override.crt  and  /qz/install-qz-trust.bat
   (same files are in public/qz/)
2. Right-click install-qz-trust.bat → Run as administrator
   (copies override.crt to C:\Program Files\QZ Tray\override.crt)
3. Right-click green Q in tray → Exit, then start QZ Tray again
4. Print again → tick Remember → Allow (now enabled)

Manual copy (if bat fails):
  copy public\qz\override.crt  to  C:\Program Files\QZ Tray\override.crt
  Restart QZ Tray

Files in resources/qz/:
- digital-certificate.txt  = public cert (same content as override.crt)
- private-key.pem          = server signing key only (never install on client)
