# Task: CFP – Alissa-GB-Forms vollständig in CFP überführen 02

## Status

Technische Umstellung auf CFP 2.4.0 umgesetzt. Die getrennte Live-Submit- und
Zustellabnahme bleibt offen.

## Ziel

Die Buchungsformulare von Alissa Stein verwenden ausschließlich das zentrale
webGefährte Custom Functionality Plugin. Die aktuelle formbezogene Must-use-
Erweiterung `alissa-booking-forms.php` wird erst entfernt, wenn CFP denselben
Renderweg, Schutz, dauerhafte Ablage, Administratorzugriff und Export
nachweislich übernimmt.

Die vorhandene statische Routing-Erweiterung bleibt nur für ihre fachfremde
statische Auslieferung bestehen. Jeder Formular-Endpunkt, jede Formularruntime
und jede Formularschutzlogik zieht daraus in CFP um.

## Ausgangslage

- GenerateBlocks Pro Form `200507` ist auf elf Buchungsflächen aktiv.
- CFP 2.2.0 enthält bereits den opt-in GenerateBlocks-Pro-Schutz mit
  Proof-of-Work, früher Prüfung und Mehrfeld-Mustererkennung.
- Die bestehende Alissa-Must-use-Erweiterung übernimmt zusätzlich dynamisches
  Rendern, die dauerhafte Buchungsablage, Admin-CSV und ein Rate-Limit. Sie ist
  nur eine Übergangslösung und darf nach dieser Aufgabe nicht mehr aktiv sein.
- Bestehende archivierte Buchungsanfragen liegen im nicht öffentlichen
  Post-Type `alissa_booking_req`; sie bleiben erhalten und für Administratoren
  sichtbar.

## Scope

1. **CFP-Formularvertrag erweitern**

   - Eine site-konfigurierbare GenerateBlocks-Pro-Formdefinition ergänzen:
     Formular-ID, zulässige öffentliche Renderroute, erlaubte statische
     Einbindungen, Redirect-Vertrag, Schutzkonfiguration, Archivvertrag und
     Rate-Limit.
   - Keine globale Aktivierung. Ohne explizite Alissa-Konfiguration bleibt CFP
     auf allen anderen Sites unverändert.
   - Der öffentliche CFP-Renderendpunkt gibt nur frisch gerendertes Form-Markup
     und versionierte, same-origin Assets aus. Er enthält keine Security-Tokens,
     Honeypot-Werte, Nonces oder Formulardaten.

2. **Dauerhafte Ablage und Administration in CFP übernehmen**

   - Die bereits vorhandene Post-Type-ID `alissa_booking_req` weiter
     registrieren, damit vorhandene Einträge ohne Migration lesbar bleiben.
   - Neue valide Buchungsanfragen vor Mail- und Autoantwort-Aktionen
     dauerhaft speichern. Scheitert die Speicherung, wird nicht versendet.
   - Zugriff ausschließlich für Administratoren mit `manage_options`;
     Export per nonce-geschütztem CSV-Download.
   - Die native GB-Pro-Ablage darf als kurzzeitige Zustellreserve bestehen,
     erfüllt aber nicht die unbefristete Aufbewahrung.

3. **Schutz und Validierung vereinheitlichen**

   - CFP 2.2.0 für Form `200507` aktivieren und den statischen Loader auf das
     versionierte CFP-Asset umstellen.
   - CFP prüft die Challenge im frühen GenerateBlocks-Hook und die enge
     Zufallszeichen-Signatur vor Archiv, Admin-Mail und Autoantwort.
   - Das Rate-Limit benutzt nur die serverseitig bestimmte Clientadresse,
     vertraut keinem `X-Forwarded-For` und zählt erst nach erfolgreichem
     dauerhaftem Archivieren.
   - Die Herkunftsauswahl wird serverseitig auf die sieben freigegebenen Werte
     geprüft. Die zwei Anliegenwerte bleiben `Moderationsanfrage` und
     `Wirkungscheck`.

4. **Statische Buchungsflächen umstellen**

   - Startseite, `/online-angebote/` und die neun SEO-Flächen laden Form und
     CFP-Runtime ausschließlich aus CFP.
   - Das Erscheinungsbild, die sichtbare Datenschutzverlinkung, Fehlermeldung,
     Dankeseite, Administratorhinweis und Du-Autoantwort bleiben unverändert.
   - Keine doppelte Runtime, keine konkurrierenden Challenge-Felder und keine
     Caching- oder Inlining-Fehler bei dynamischen Sicherheitswerten.

5. **Altcode erst nach erfolgreicher CFP-Abnahme entfernen**

   - Vor der Umstellung Hash, Dateipfad und Rückrollanleitung der aktuellen
     Must-use-Erweiterung sichern, ohne personenbezogene Formulardaten zu
     lesen oder zu kopieren.
   - Nach bestandenem CFP-Read-back und freigegebenem Live-Test wird nur der
     formularbezogene Alissa-MU-Code entfernt. Unabhängige statische Routing-
     Funktionen bleiben unangetastet.

## Grenzen

- Keine zusätzliche Must-use-Erweiterung, kein site-spezifisches Plugin und
  kein dritter Formularschutzdienst.
- Keine Änderung an MailPoet, Contact Form 7, Flamingo, WP Armour oder deren
  historischen Daten. Ihre spätere Ablösung ist ein eigener Auftrag.
- Kein Mailboxzugriff und keine Übernahme von Anfragetexten, E-Mail-Adressen,
  IP-Adressen oder Secrets in Code, Tests oder Ergebnisartefakte.
- Kein produktiver Submit, keine Testmail und keine Löschung der
  Übergangserweiterung ohne unmittelbar geltende Freigabe für Zielsite,
  Testdaten, Empfänger und Entfernung.

## Umsetzungsreihenfolge

1. CFP-Quellstand, bestehende Alissa-Konfiguration und Rückrollanker
   read-only inventarisieren.
2. CFP-Module, Settings, versionierte Assets und Regressionstests bauen.
3. Installierbares CFP-Paket erzeugen und dessen Wurzel, Version, Syntax und
   Inhalt prüfen.
4. Nach benannter Zielsite-Freigabe CFP installieren beziehungsweise auf die
   neue Version aktualisieren und Form `200507` explizit konfigurieren.
5. Statische Einbindungen auf CFP-Renderweg und CFP-Asset umstellen.
6. Zuerst synthetisch testen; danach nur mit separater Live-Freigabe einen
   klar markierten End-to-End-Test ausführen.
7. CFP-Archiv, CSV, Redirect, Admin- und Du-Mail, Schutzablehnungen und
   Fehlermeldungen read-back prüfen.
8. Erst jetzt formularbezogenen Alissa-MU-Code entfernen, Remote-Dateihash
   sowie alle elf Flächen erneut prüfen und den Rückrollweg beibehalten.

## Abnahme

1. Nur CFP stellt Renderweg, Browser-Asset, Challenge, Validierung,
   dauerhafte Ablage, Rate-Limit und CSV für Form `200507` bereit.
2. Der Alissa-MU-Code für Buchungsformulare ist entfernt; es existiert keine
   parallele Formularschutz- oder Archivlogik.
3. Alle vorhandenen `alissa_booking_req`-Einträge bleiben für Administratoren
   sichtbar, ohne dass ihre Inhalte für die Abnahme gelesen werden müssen.
4. Die elf Flächen liefern dieselbe Formdefinition ohne eingefrorene
   Sicherheitswerte und ohne CF7-Signatur.
5. Gültige synthetische Daten, fehlende/manipulierte/verbrauchte CFP-Challenge,
   Zufallszeichenmuster, Auswahlwerte, Rate-Limit, Speicherfehler und
   Fehlermeldungen sind in getrennten Tests belegt.
6. Authentifizierter Editor: null Blockwarnungen und null
   Wiederherstellungsaktionen. Öffentlich: Desktop und echtes Mobilformat ohne
   Überlauf, zugänglicher Status, Tastaturbedienung und fehlerfreie Runtime.
7. Ein Live-Test gilt nur bei getrennter Evidenz für Annahme, dauerhafte
   Speicherung, Transport und vom Nutzer bestätigte Zustellung.

## Ergebnisartefakte

- `results/worker-result-cfp-alissa-gb-forms-consolidation-02.md`
- versionierte CFP-Tests und ein bereinigter Paketinhalt;
- sanitierter Read-back für Konfiguration, Routen und elf Buchungsflächen;
- Rückrollanleitung mit exakten Dateipfaden und Hashes, ohne Secrets oder
  personenbezogene Daten.
