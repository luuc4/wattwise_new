# WattWise Beta Landing Page - Email Integration

## Übersicht

Die WattWise Beta-Landing Page wurde umgestellt von CSV-Speicherung auf E-Mail-Benachrichtigungen. Jede Beta-Anmeldung wird automatisch als formatierte HTML-E-Mail an wattwisevorarlberg@gmail.com gesendet.

## Email-Konfiguration

### Gmail SMTP Einstellungen
- **E-Mail**: wattwisevorarlberg@gmail.com
- **App-Passwort**: xave ohaw rmqp pwpk
- **SMTP Server**: smtp.gmail.com
- **Port**: 587 (TLS)
- **Verschlüsselung**: STARTTLS

### Implementierung
- **Haupt-Handler**: `submit-beta.php` - verarbeitet Formulareingaben und sendet E-Mails
- **Admin Dashboard**: `admin.html` - zeigt jetzt E-Mail-Status statt CSV-Daten
- **Produktions-Config**: `email-config-production.php` - vollständige SMTP-Konfiguration

## Features

### E-Mail-Benachrichtigungen
- ✅ Automatische E-Mail bei jeder Beta-Anmeldung
- ✅ Formatierte HTML-E-Mails mit allen Benutzerdaten
- ✅ Fallback auf PHP mail() wenn SMTP nicht verfügbar
- ✅ Umfangreiche Fehlerbehandlung und Logging

### Entfernte Features
- ❌ CSV-Datei-Speicherung
- ❌ Lokale Datenbank
- ❌ CSV-Export im Admin-Dashboard

## Datensicherheit

### Eingebaute Sicherheit
- Input-Validierung und -Sanitization
- E-Mail-Format-Validierung
- CSRF-Schutz durch Origin-Validierung
- Keine lokale Datenspeicherung (DSGVO-konform)

## Deployment-Anweisungen

### 1. Server-Anforderungen
- PHP 7.4+ mit mail() Funktion
- Ausgehende Internetverbindung auf Port 587
- OpenSSL-Erweiterung für TLS

### 2. Gmail App-Passwort Setup
1. Google-Konto öffnen → Sicherheit
2. 2-Faktor-Authentifizierung aktivieren
3. App-Passwörter → Neues App-Passwort für "Mail"
4. 16-stelliges Passwort verwenden: `xave ohaw rmqp pwpk`

### 3. Produktions-Deployment
```bash
# Dateien hochladen
scp *.php *.html your-server:/var/www/html/

# Berechtigungen setzen
chmod 644 *.php *.html
chmod 755 .

# PHP-Konfiguration prüfen
php -m | grep openssl  # Muss vorhanden sein
```

### 4. Testen
```bash
# SMTP-Verbindung testen
telnet smtp.gmail.com 587

# Formular testen
curl -X POST -H "Content-Type: application/json" \
  -d '{"firstName":"Test","lastName":"User","email":"test@example.com"}' \
  https://your-domain.com/submit-beta.php
```

## Überwachung

### Log-Überwachung
- Erfolgreiche E-Mails: `Beta signup email sent: [email] -> wattwisevorarlberg@gmail.com`
- Fehlgeschlagene E-Mails: `Email failed for beta signup: [email] -> wattwisevorarlberg@gmail.com`
- SMTP-Fehler: `SMTP Error: [details]`

### E-Mail-Überwachung
- Prüfe Posteingang von wattwisevorarlberg@gmail.com
- Überwache Spam-Ordner
- Kontrolliere Gmail-Sendelimits (500 E-Mails/Tag)

## Troubleshooting

### Häufige Probleme
1. **E-Mails kommen nicht an**
   - Prüfe App-Passwort
   - Kontrolliere Server-Firewall (Port 587)
   - Überprüfe PHP error_log

2. **SMTP-Authentifizierung fehlgeschlagen**
   - 2-Faktor-Auth muss aktiviert sein
   - Verwende App-Passwort, nicht reguläres Passwort
   - Prüfe Gmail-Sicherheitseinstellungen

3. **TLS/SSL-Fehler**
   - OpenSSL-Extension installieren
   - PHP-Version überprüfen
   - Server-Zeit synchronisieren

### Debug-Modus
Für Debugging in `submit-beta.php` ändern:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Nur für Debug!
```

## Sicherheitshinweise

### Produktions-Sicherheit
- App-Passwort sicher speichern
- HTTPS verwenden
- PHP error_log überwachen
- Regelmäßige Sicherheitsupdates

### Gmail-Limits
- **Sende-Limit**: 500 E-Mails/Tag
- **Rate-Limit**: ~100 E-Mails/Stunde
- **Überwachung**: Google Admin Console

---

## Änderungshistorie

**29.09.2025**: 
- ✅ CSV-Speicherung entfernt
- ✅ Gmail SMTP Integration implementiert
- ✅ E-Mail-Templates optimiert
- ✅ Admin-Dashboard angepasst
- ✅ Produktions-Konfiguration erstellt