# WattWise Beta Landing Page - Formspree Integration

## Übersicht

Die WattWise Beta-Landing Page verwendet jetzt Formspree für die Verarbeitung von Beta-Anmeldungen und Newsletter-Anmeldungen. Alle Formulareingaben werden direkt an das konfigurierte Formspree-Formular gesendet.

## Formspree-Konfiguration

### Formspree Einstellungen
- **Formspree Endpoint**: https://formspree.io/f/xqaypoal
- **Formular-ID**: xqaypoal
- **Methode**: POST

### Implementierung
- **Beta-Anmeldung**: Formular in `index.html` sendet direkt an Formspree
- **Newsletter**: Footer-Formular sendet ebenfalls an Formspree
- **Admin Dashboard**: `admin.html` zeigt Formspree-Link zum Dashboard

## Features

### Formspree-Integration
- ✅ Automatische Verarbeitung von Beta-Anmeldungen
- ✅ Newsletter-Anmeldungen über separates Feld
- ✅ Spam-Schutz durch Formspree
- ✅ E-Mail-Benachrichtigungen an konfigurierte Adresse
- ✅ Keine Server-side PHP-Logik erforderlich

### Entfernte Features
- ❌ PHP E-Mail-Verarbeitung (submit-beta.php)
- ❌ Gmail SMTP-Konfiguration
- ❌ Lokale CSV-Datei-Speicherung
- ❌ E-Mail-Konfigurationsdateien

## Datensicherheit

### Formspree-Sicherheit
- Automatischer Spam-Schutz
- HTTPS-Verschlüsselung
- DSGVO-konforme Datenverarbeitung
- Keine lokale Datenspeicherung erforderlich

## Deployment-Anweisungen

### 1. Server-Anforderungen
- Webserver (Apache/Nginx)
- Keine PHP erforderlich
- Statische Dateien können auf jedem Webserver gehostet werden

### 2. Formspree Setup
1. Formspree-Konto erstellen auf https://formspree.io
2. Neues Formular erstellen
3. Formular-ID (xqaypoal) in HTML-Formularen verwenden
4. E-Mail-Benachrichtigungen in Formspree konfigurieren

### 3. Deployment
```bash
# Dateien hochladen
scp *.html your-server:/var/www/html/

# Berechtigungen setzen
chmod 644 *.html
chmod 755 .
```

### 4. Testen
```bash
# Formular testen durch Ausfüllen auf der Website
# oder mit cURL:
curl -X POST \
  -F "email=test@example.com" \
  -F "firstName=Test" \
  -F "lastName=User" \
  https://formspree.io/f/xqaypoal
```

## Überwachung

### Formspree Dashboard
- Prüfe https://formspree.io/forms/xqaypoal für Einreichungen
- Überwache E-Mail-Benachrichtigungen 
- Kontrolliere Spam-Filter in Formspree

### Analytics
- Google Analytics für Formular-Tracking
- Formspree-interne Statistiken verfügbar

## Troubleshooting

### Häufige Probleme

1. **Formular wird nicht abgesendet**
   - Prüfe Formspree-Endpoint-URL
   - Kontrolliere Netzwerkverbindung
   - Überprüfe Browser-Konsole auf Fehler

2. **Keine E-Mail-Benachrichtigungen**
   - Prüfe Formspree-Dashboard auf Einreichungen
   - Kontrolliere Spam-Ordner
   - Verifiziere E-Mail-Adresse in Formspree

3. **Formular-Validierung schlägt fehl**
   - Überprüfe required-Attribute in HTML
   - Teste mit verschiedenen Browsern

## Sicherheitshinweise

### Formspree-Sicherheit
- HTTPS für alle Formulare verwenden
- Formspree bietet integrierte Spam-Filterung
- Keine sensiblen Daten in Formularen übertragen

### Website-Sicherheit
- Regelmäßige Updates des Webservers
- HTTPS-Zertifikat verwenden
- Content Security Policy implementieren

---

## Änderungshistorie

**[Aktuelles Datum]**: 
- ✅ PHP E-Mail-System entfernt
- ✅ Formspree-Integration implementiert
- ✅ Alle E-Mail-bezogenen PHP-Dateien gelöscht
- ✅ Admin-Dashboard auf Formspree umgestellt
- ✅ Dokumentation aktualisiert