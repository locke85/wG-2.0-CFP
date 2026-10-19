# Task: CFP – Selbst gehosteter Formularschutz v2

## Status

In CFP `2.2.0` implementiert und lokal geprüft; ein installierbares Paket und
die Installation auf einer benannten Zielsite stehen noch aus.

## Ziel

CFP erhält eine wiederverwendbare, selbst gehostete Schutzschicht für
WordPress-Formulare. Sie erhöht die Kosten automatisierter Übermittlungen und
stoppt definierte maschinelle Eingabemuster vor Speicherung, E-Mail und
nachgelagerten Aktionen. Der Schutz benötigt keinen externen CAPTCHA-,
Tracking- oder Prüfservice.

## Referenzfall

Eine GenerateBlocks-Pro-Buchungsform erhielt zwei vollständig ausgefüllte
Anfragen mit langen, zusammenhängenden Zufallszeichen in mehreren Freitexten
und in der Nachricht. Honeypot, Token-Freshness und Origin-Prüfung waren
aktiv. Browserfähige Bots konnten diese Basisschutzmechanismen dennoch
erfüllen.

## Scope

- Einen same-origin REST-Endpunkt bereitstellen, der pro Abruf eine zufällige,
  kurz gültige Challenge mit Server-Signatur ausliefert.
- Browserseitig eine Proof-of-Work-Lösung berechnen und die Challenge-Daten als
  versteckte Formularfelder mitsenden.
- Serverseitig Signatur, Ablauf, Schwierigkeit, Lösung und Einmalverwendung
  prüfen, bevor die jeweilige Formularintegration Felder bereinigt oder
  Aktionen ausführt.
- Eine anpassbare Musterprüfung für mehrere lange alphanumerische Freitexte
  plus wortlose Nachricht ergänzen. Sie darf einzelne kurze Fachbegriffe oder
  normale Freitexte nicht sperren.
- Eine zugängliche Status- und Fehlerrückmeldung bieten und den Submit erst
  nach gelöster Challenge freigeben.
- Adapter für GenerateBlocks Pro vorsehen. Der passende Hook ist
  `generateblocks_form_verify_submission`; feldbezogene Musterprüfungen laufen
  anschließend in `generateblocks_form_validate_submission`.

## Sicherheitsvertrag

- Challenge-ID, Salt, Ablauf, Schwierigkeit und HMAC-Signatur sind zufällig
  beziehungsweise serverseitig signiert. Es werden keine Formularinhalte,
  IP-Adressen oder Browserkennungen in der Challenge gespeichert.
- Eine gelöste Challenge ist nach dem ersten Prüfversuch ungültig.
- Fehlende, veränderte, abgelaufene und wiederverwendete Daten werden mit einer
  allgemein gehaltenen Nutzerhinweis abgelehnt.
- Proof of Work erschwert automatisierte Einsendungen, ersetzt aber weder
  Rate-Limits noch inhaltliche Plausibilitätsregeln gegen vollwertige Browser-
  Bots.
- Das Rate-Limit zählt erst nach erfolgreicher, dauerhafter Speicherung oder
  dokumentiert ausdrücklich eine abweichende Entscheidung.

## Grenzen

- Keine Übernahme von Formularwerten, E-Mail-Adressen, IP-Adressen, Secrets
  oder Kundenbezügen in CFP-Quellen, Tests oder Ergebnisdateien.
- Keine globale Aktivierung für alle Formulare. Jede Integration benennt Form,
  Hook-Reihenfolge, Failure-Verhalten und Rückbau separat.
- Keine Abhängigkeit von SaaS, Cloudflare, Google reCAPTCHA oder hCaptcha.
- Kein automatisierter Produktiv-Submit im Test. Ein Live-Test benötigt die
  dafür geltende ausdrückliche Freigabe.

## Abnahme

1. Eine Challenge ist nur kurz gültig, kryptografisch signiert und genau einmal
   verwendbar.
2. Manipulierte, fehlende, abgelaufene und wiederverwendete Lösungen stoppen
   den Submit vor allen Speicher- und Mailaktionen.
3. Eine gültige Lösung lässt die fachliche Formularvalidierung passieren.
4. Die enge Mehrfeld-Musterregel blockiert die Referenzsignatur und akzeptiert
   repräsentative echte Freitexte.
5. Die Browser-Integration bleibt bei deaktiviertem JavaScript oder fehlender
   Web-Crypto zugänglich: Sie erklärt den Fehler, sendet aber nicht ungeschützt
   ab.
6. PHP-, JavaScript- und Integrationsprüfungen sind grün; ein produktiver
   Negativtest bleibt als eigene Evidenz ausgewiesen.
