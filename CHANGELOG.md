# Changelog

Alle wesentlichen Änderungen am webGefaehrte Custom Functionality Plugin werden
in dieser Datei dokumentiert.

## 2.0.2 – 2026-07-27

- Serverseitige GenerateBlocks-Fallbacks für Autorenarchive ohne regulären
  Beitragskontext ergänzt.
- Avatar-Auflösung über die WordPress-Avatar-API umgesetzt, damit
  Multisite- und Simple-Local-Avatars-Filter berücksichtigt werden.
- Anzeigename, Vorname und Nachname auf eine feste Meta-Whitelist begrenzt und
  kontextgerecht escaped.
- Fehlende Alternativtexte für Autorenavataren serverseitig ergänzt.
- Bereits funktionierende GenerateBlocks-Profildaten bleiben erhalten und
  werden nicht dupliziert.
- Avatargröße mit einem dokumentierten Standard von 150 Pixeln konfigurierbar
  gemacht.

## 2.0.1 – 2026-05-02

- Hauptseitengebundene CPT-Module für `wg_seo_chat` und `wg_sharing_news`
  ergänzt.
- Chat- und News-Metaboxen im CFP gehärtet.
- Offenen Chat-AJAX-Flow durch eine nonce-geschützte, nur für Redakteure
  verfügbare Frontend-Erfassung über `admin-post` ersetzt.
- Modulgrenzen dokumentiert und alte Debug-/Upload-Funktionen ausgeschlossen.

## 2.0.0 – 2026-05-01

- Modulares CFP-Laden mit hauptseitengebundener Initialisierung eingeführt.
- Podcast-Show-Notes und kompatiblen `wg_guest`-Shortcode ergänzt.
- Chat-Archiv-Helfer und abgesicherte Taxonomie-Registrierung ergänzt.
- GeneratePress-Helfer konsolidiert.

## 1.7.0 – 2026-05-01

- Membership-spezifischen `wg_angebot`-Author-Support entfernt.
- Page-Excerpt-Support dedupliziert und Metabox-Speicherung gehärtet.
- Contact-Form-7-Redirects eingegrenzt und Font Awesome vom Child Theme
  entkoppelt.
- Ratgeber-Permalinks mit sicheren Fallbacks ergänzt.

## 1.6.9

- Fehlerhaften `save_post`-Callback korrigiert.
- Bedingte Zuweisung korrigiert.
- PHP-8-Kompatibilität verbessert.
