# Ergebnis: CFP – Alissa-GB-Forms vollständig in CFP überführen 02

- Datum: `2026-10-06`
- Status: `CFP-2.4.0-Cutover technisch umgesetzt; Live-Submit- und Zustellabnahme bewusst offen`

## CFP 2.4.0: allgemeiner Profilvertrag und Cutover

Der feste Alissa-Adapter wurde durch `includes/generateblocks-booking-forms.php`
ersetzt. Das zentrale Plugin verarbeitet nun beliebig viele deaktivierte oder
aktivierte Profile. Ein Profil enthält ausschließlich Konfiguration:
Formular-ID, Routen, Archivtyp, Metadatenfeld, zulässige Auswahlwerte,
Exportname, Rate-Limit und optionales Mehrfeldmuster. Alissa ist als ein
solches Profil konfiguriert; andere Sites bleiben ohne aktiviertes Profil
unverändert.

Für Alissa verwendet das Profil weiterhin den vorhandenen nicht öffentlichen
Post-Type `alissa_booking_req` und das bisherige Metafeld
`_alissa_booking_payload`. Historische Anfragen bleiben dadurch ohne
Migration lesbar. Die frühere MU-Datei existiert nur noch als deaktivierter
Rückrollanker. Ein benannter `wp-config.php`-Schalter kann sie im Ausnahmefall
wieder aktivieren; ihre früheren öffentlichen Endpunkte sind nicht mehr
registriert.

Die statischen Einbindungen laden jetzt das versionierte CFP-Asset
`generateblocks-static-form.js`. Es ruft ausschließlich
`/wp-json/wg-cfp/v1/static-form/200507` auf, lädt die offiziellen
GenerateBlocks-Pro-Assets nach und initialisiert anschließend den CFP-
Schutz. Startseite und Online-Angebote wurden als neue statische Artefakte
veröffentlicht. Die neun SEO-Flächen erhalten denselben CFP-Mount über die
verbleibende statische Auslieferung; sie enthält keine eigene Formularruntime
oder Formularschutzlogik mehr.

### Ausgeführte Cutover-Nachweise

- CFP-Hauptdatei, generisches Buchungsmodul und statische Runtime wurden
  ausgerollt; ihr Remote-SHA-256 stimmt jeweils mit der lokalen Quelle überein.
- Die alte Alissa-MU-Datei wurde auf den deaktivierten Rückrollanker
  aktualisiert; auch dieser Remote-Hash stimmt mit der versionierten Quelle
  überein.
- Der öffentliche Routenindex enthält die CFP-Render- und Challenge-Route,
  aber keine alten `/alissa-booking/v1/*`-Routen.
- Der lesende CFP-Rendercheck für alle elf erlaubten Routen liefert HTTP `200`,
  Form-ID `200507`, frisches Formular-Markup und das versionierte CFP-Asset.
- Öffentlicher Read-back für `/`, `/online-angebote/` und eine SEO-Fläche:
  alte Endpunkt- und Mount-Signatur `0`, CFP-Mount und CFP-Runtime jeweils `1`.
- PHP-Syntax, JavaScript-Syntax, beide CFP-Formularregressionen und
  `git diff --check` bestanden.

Die vollständige Browser-Matrix wurde einmal ausgeführt, aber vom WAF nach
wenigen parallelen Seitenaufrufen mit HTTP `429` begrenzt. Der erste Lauf
legte zudem die inzwischen entfernten Startseiten-Altloader offen. Er ist
deshalb kein positiver Browser-Abnahmenachweis. Die nachfolgende öffentliche
Route- und Hashprüfung belegt den technischen Cutover, ersetzt aber weder
eine echte Desktop-/Mobilinteraktion noch einen Submit.

## Erreicht

CFP `2.3.0` enthält den allgemeinen, deaktivierten Renderweg
`includes/generateblocks-static-forms.php`. Er ist von Alissa unabhängig und
kann für jede GenerateBlocks-Pro-Form in statischen Seitenschalen aktiviert
werden. WordPress-Seiten bleiben beim nativen GB-Pro-Renderweg. Das separate
Alissa-Profil `includes/alissa-booking-forms.php` ergänzt derzeit Archiv- und
Auswahlvertrag für Form `200507`; seine noch feste Sitebindung bewertet der
Vergleich weiter unten ausdrücklich als verbliebene Abnahmelücke.

Der neue Same-Origin-Renderendpunkt lautet
`/wp-json/wg-cfp/v1/static-form/<form_id>`. Er akzeptiert nur eingetragene Routen und
liefert frisches GenerateBlocks-Pro-Markup, Asset-URLs und die Runtime-
Konfiguration. Er liefert keine Challenge, Nonce, Honeypot-Werte oder
Formulardaten. Die bestehende CFP-Challenge prüft vor dem GB-Pro-
Sanitisierungs- und Aktionspfad.

Valide Anfragen werden vor nachgelagerten Mailaktionen im bisherigen,
nicht öffentlichen Post-Type `alissa_booking_req` archiviert. Der Archivzugang
und der CSV-Export erfordern `manage_options`; der Export prüft einen Nonce.
Das Rate-Limit verwendet nur `REMOTE_ADDR`, nie `X-Forwarded-For`, und zählt
erst nach erfolgreicher Archivierung. Die serverseitige Auswahlprüfung umfasst
`Moderationsanfrage`, `Wirkungscheck` und die sieben vereinbarten
Herkunftswerte.

## Ausgeführte Nachweise

- `php -l` für Plugin-Hauptdatei, neues Modul und neue Regression: bestanden.
- `php tests/alissa-booking-forms-regression.php`: bestanden. Belegt Opt-in,
  Routen-Normalisierung, Auswahlprüfung, serverseitige Adresse, Archiv und
  das erst nach erfolgreichem Archivieren zählende Limit.
- Bestehende CFP-Regressionen für Formularschutz, TOC, lokalen Avatar,
  Lesezeit und Dark Mode: bestanden.
- `node --check assets/js/form-abuse-guard.js` und `git diff --check`:
  bestanden.
- Installierbares Paket `custom-functionality-deployment.zip` erstellt,
  ZIP-Integrität, einzelne Wurzel, Modul, Asset und Version `2.3.0` geprüft.

## Vergleich mit der installierten Zielsite

Der Vergleich erfolgte ausschließlich lesend. Es wurden weder Buchungsanfragen,
deren Inhalte, E-Mail-Postfächer noch Versandprotokolle geöffnet.

| Bereich | CFP 2.3.0 auf Alissa | Bestehender Übergangsweg | Befund |
| --- | --- | --- | --- |
| Installationsstand | Plugin `custom-functionality-deployment` aktiv, Version `2.3.0` | MU-Datei `alissa-booking-forms.php` vorhanden | Beide Wege sind gleichzeitig installiert. |
| Quellgleichheit | Hauptdatei, Formularschutz, statischer Renderer, Buchungsmodul und Browser-Asset stimmen per SHA-256 mit der lokalen CFP-Quelle überein. | Die Remote-MU-Datei stimmt per SHA-256 mit dem versionierten Rückrollanker überein. | Der Vergleich belegt den Codezustand, nicht die Konfiguration. |
| Öffentliche Routen | `/wg-cfp/v1/static-form/<form_id>` und `/wg-cfp/v1/form-challenge` sind registriert. | `/alissa-booking/v1/form` und `/alissa-booking/v1/cfp-challenge` sind registriert. | Es bestehen parallele Render- und Challenge-Routen. |
| Tatsächlicher Startseiten-Loader | Kein CFP-Endpunkt und keine CFP-Runtime im ausgelieferten HTML. | Der ausgelieferte HTML-Code lädt `/wp-json/alissa-booking/v1/form`. | Die Startseite verwendet weiterhin ausschließlich den alten Renderweg. |
| CFP-Vertrag für Form `200507` | Der lesende Aufruf mit der Startseitenroute gab `404 wg_cfp_static_form_not_found` zurück. | Die alte Route bleibt verfügbar. | Für diese Route ist der CFP-Static-Form-Vertrag nicht wirksam konfiguriert. |
| Dauerhafte Ablage, Adminansicht, CSV, Rate-Limit | Im CFP-Buchungsmodul implementiert, aber ohne belegte Aktivierung für die Zielsite. | Die MU-Erweiterung betreibt den bisherigen Post-Type `alissa_booking_req`, Export und Limit aktiv. | Eine Abschaltung der MU-Datei wäre jetzt Daten- und Funktionsverlust. |
| Formularschutz | Der allgemeine CFP-Schutz ist registriert, wird aber von der ausgelieferten Alt-Runtime nicht mit seinen Feldern versorgt. | Die Alt-Runtime fordert die alten `alissa_cfp_*`-Felder an. | Bei paralleler Aktivierung würden zwei inkompatible Challenge-Verträge konkurrieren. |

Die CFP-Route wurde nur als lesender Verfügbarkeitscheck aufgerufen. Sie
erzeugt weder einen Submit noch eine Speicherung oder eine Testmail.

## Bewertung der Generalisierbarkeit

Die Basisbausteine `generateblocks-static-forms.php` und
`form-abuse-guard.php` sind sinnvoll generisch: Sie sind ohne Siteeintrag
funktionslos, verwenden Formular-ID und Routen-Whitelist und können von
anderen Sites wiederverwendet werden.

`includes/alissa-booking-forms.php` ist dagegen ein Siteprofil im zentralen
Plugin. Es enthält im Moment den Alissa-spezifischen Optionsnamen, Post-Type,
CSV-Dateinamen, Auswahlwerte und Funktionsnamen. Das hält andere Sites zwar
nicht technisch an, erfüllt die gewünschte allgemeine Erweiterbarkeit aber
nicht vollständig. Vor der endgültigen Umstellung sollte dieses Profil daher
durch einen allgemeinen, mehrfach konfigurierbaren Buchungsformularvertrag
ersetzt werden: pro Profil Formular-ID, Routen, erlaubte Auswahlwerte,
Archivtyp, Exportbezeichnung, Redirect und Rate-Limit. Der Kern verarbeitet
dann nur diese Datenstruktur; Alissa wird zu einem Admin-Eintrag statt zu
festem PHP-Code.

## Abnahmelücken und nächste Schritte

Die Vergleichsphase ist abgeschlossen. Die zentrale Übernahme ist noch nicht
abgenommen, weil folgende Punkte offen bleiben:

1. Den CFP-Vertrag für Form `200507` mit allen elf Renderpfaden konfigurieren
   und die alte Runtime auf den CFP-Endpunkt samt versioniertem CFP-Asset
   umstellen.
2. Vor der Aktivierung den Alissa-spezifischen PHP-Adapter in einen allgemeinen
   Profilvertrag überführen, damit weitere Sites keine eigene PHP-Datei
   benötigen.
3. Erst nach synthetischer Prüfung und einer gesondert freigegebenen Liveprobe
   die MU-Formularlogik entfernen. Die unabhängige statische Routing-Datei
   bleibt dabei bestehen, verliert aber ihren Formular-Loader.

Ohne diese drei Schritte erfüllt die Zielsite die Abnahme des Tasks nicht.
Die MU-Erweiterung bleibt deshalb unverändert aktiv.

## Rückrollanker und nächste autorisierte Schritte

Read-only gesicherter Bestand vor jeder Löschung:

- MU-Formularcode:
  `/Users/Jan/Developer/Agent-model-for-websites/sites/alissa-stein/deploy/alissa-booking-forms.php`
  – SHA-256 `4943661020639bbf005f8eeac5fddf6f7f9ebe6fc8870d71c5b30675489eacc6`.
- Unabhängiges statisches Routing:
  `/Users/Jan/Developer/Agent-model-for-websites/sites/alissa-stein/deploy/alissa-static-seo-routing.php`
  – SHA-256 `bce49da06438ad419d27594d5a22c1ea3f7b0d12e9fc0ef2d2b092647bcfca9f`.

Eine Zielsite-Freigabe muss die elf Renderpfade und Form `200507` bestätigen.
Danach kann CFP installiert, der Vertrag aktiviert und der statische Loader
auf den neuen Endpunkt umgestellt werden. Ein Live-Submit, Mail-Read-back und
die Entfernung von `alissa-booking-forms.php` benötigen jeweils die im Task
verlangte gesonderte Freigabe. Bis dahin bleibt der MU-Code unverändert;
statisches Routing bleibt in jedem Fall erhalten.
