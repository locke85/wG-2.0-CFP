# Ergebnis: CFP – Selbst gehosteter Formularschutz v2

- Datum: `2026-10-05`
- Referenzintegration: Alissa Stein, GenerateBlocks Pro Form `200507`
- Zugehörige Aufgabe: `tasks/task-cfp-self-hosted-form-abuse-guard-01.md`

## Ergebnis

Die Referenzimplementierung „Alissa CFP“ Version `2.0.0` ist als Must-use-
Erweiterung produktiv. Sie kombiniert drei Schichten:

1. Eine eigene Proof-of-Work-Challenge wird vom gleichen WordPress-Host
   ausgegeben. Sie ist drei Minuten gültig, mit HMAC signiert und nach der
   ersten Prüfverwendung ungültig.
2. Der GenerateBlocks-Hook `generateblocks_form_verify_submission` prüft die
   Lösung vor Sanitizing, dauerhafter Archivierung, Benachrichtigung und
   Autoantwort.
3. Der Hook `generateblocks_form_validate_submission` verwirft das beobachtete
   Mehrfeld-Zufallszeichenmuster sowie ungültige Auswahlwerte.

Der Browser zeigt während der Berechnung einen Status an und aktiviert den
Submit erst nach gültiger Lösung. Die Challenge überträgt keine
Formulareingaben und verwendet weder Drittanbieter-Skripte noch externe APIs.

## Zusätzliche Korrektur

Die sieben Werte der Herkunftsauswahl wurden in der gemeinsamen
GenerateBlocks-Formdefinition feldgenau wiederhergestellt. Die beiden
fehlerhaften Werte für Instagram und LinkedIn waren ein Konfigurationsfehler,
nicht selbst ein Spam-Schutzproblem.

## Nachweise

- PHP-Syntax für die Must-use-Erweiterung und das statische Routing: bestanden.
- JavaScript-Syntax für die statische Formular-Runtime: bestanden.
- Authentifizierter Form-Read-back: sieben erwartete Auswahlwerte vorhanden.
- Öffentlicher Read-back: CFP-Runtime und Statusbereich vorhanden.
- Same-origin-Challenge: gültige Aufgabe der Schwierigkeit vier ausgeliefert;
  eine lokale Lösung wurde berechnet, ohne einen Formular-Submit auszulösen.
- Upload der Must-use-Erweiterung: Remote-Dateihash entspricht der Quelle.

## CFP-Übernahme am 2026-10-06

Die Pluginquelle wurde auf Version `2.2.0` erweitert. Sie enthält nun:

- das Modul `includes/form-abuse-guard.php` mit der same-origin REST-Challenge,
  der Einmalverwendung und dem GenerateBlocks-Pro-Adapter;
- das versionierte Asset `assets/js/form-abuse-guard.js` mit zugänglichem
  Status und gesperrtem Submit bis zur gelösten Challenge;
- **Einstellungen → CFP Formularschutz** zur expliziten Aktivierung einzelner
  GenerateBlocks-Pro-Formular-IDs;
- die Regression `tests/form-abuse-guard-regression.php` für gültige,
  manipulierte und wiederverwendete Challenges sowie für die enge
  Mehrfeld-Musterprüfung;
- die Betriebsdokumentation in `README.md` und `readme.txt`.

Das Modul bleibt ohne eingetragene Formular-ID inaktiv. Es ist damit nach dem
Update auf einer bestehenden CFP-Installation nicht stillschweigend für alle
Formulare aktiv.

Die lokale Syntaxprüfung, die neue CFP-v2-Regression und alle vier bestehenden
CFP-Regressionen bestehen. Das installierbare Paket und ein Deployment von
Version `2.2.0` stehen noch aus, weil für die Installation keine Zielsite
benannt wurde.

## Grenze

Der breite Browserlauf der Referenzsite wurde durch HTTP `429` der bestehenden
öffentlichen Rate-/WAF-Schicht begrenzt. Eine vollständige negative
Produktivübermittlung wurde nicht ausgeführt, weil sie eine gesonderte,
unmittelbar geltende Freigabe für den Submitpfad benötigt.
