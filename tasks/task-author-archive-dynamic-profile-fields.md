# Task: Dynamische Autorenprofile auf leeren Autorenarchiven serverseitig auflösen

## Status

Umgesetzt am 2026-07-27; visuelle Browserkontrolle ausstehend

## Kontext

Auf Autorenarchiven von `seosharingsystem.de` werden dynamische Profildaten aus dem
GeneratePress-/GenerateBlocks-Element `48767` nicht zuverlässig ausgegeben.

Betroffenes Beispiel:

- Frontend: `https://seosharingsystem.de/author/anja_p/`
- Element: `https://seosharingsystem.de/wp-admin/post.php?post=48767&action=edit`

Das Element verwendet unter anderem:

- `{{author_avatar_url}}`
- `{{author_meta key:display_name}}`
- `{{author_meta key:first_name}}`
- `{{author_meta key:last_name}}`

Bei Autoren ohne veröffentlichte Beiträge fehlt der von GenerateBlocks erwartete
Beitragsautor-Kontext. Das Autorenarchiv und sein Benutzerobjekt sind vorhanden,
aber das Avatar-`<img>` und die genannten Textfelder werden nicht gerendert. Die
GeneratePress-Ausgabe der Autorenbeschreibung funktioniert bereits.

In der WordPress-Multisite liegen einige Avatare auf der Hauptsite. Anjas Avatar
gehört beispielsweise zu `blog_id = 1`, während `seosharingsystem.de` auf
`blog_id = 9` läuft. Eine lokale Attachment-ID darf deshalb nicht auf der
aktuellen Subsite aufgelöst werden. Die WordPress-Avatar-API muss verwendet
werden.

## Ziel

Das CFP stellt eine serverseitige, updatefeste Auflösung des abgefragten Autors
bereit. Avatar und benötigte Profildaten müssen auch dann im initialen HTML
erscheinen, wenn der Autor keine veröffentlichten Beiträge besitzt.

Es darf keine JavaScript-Nachladung und keine sichtbare Fallback-Ausgabe im
Frontend geben.

## Technische Leitplanken

1. Nur auf echten Autorenarchiven mit `is_author()` eingreifen.
2. Die Benutzer-ID aus dem abgefragten Objekt beziehen, bevorzugt über
   `get_queried_object_id()` beziehungsweise das zugehörige `WP_User`-Objekt.
3. Avatare mit `get_avatar()` oder `get_avatar_url()` beziehen, damit
   Multisite- und Simple-Local-Avatars-Filter berücksichtigt werden.
4. URLs und Attribute kontextgerecht escapen.
5. Keine Medien-ID aus `simple_local_avatar` direkt auf der aktuellen Subsite
   auflösen.
6. Bestehende GenerateBlocks-Ausgaben nicht doppelt rendern.
7. Andere Seiten, Beitrags-Templates, Query Loops und Autorenarchive mit
   vorhandenem Beitragskontext dürfen nicht verändert werden.
8. Keine hart codierte Benutzer-ID, kein Mapping einzelner Autoren und keine
   Abhängigkeit vom URL-Slug einführen.

## Implementierungsentscheidung

Vor der Umsetzung prüfen, welcher Ansatz mit der installierten
GenerateBlocks-Version stabil unterstützt wird:

- CFP-Shortcodes für Avatar und Autorenfelder, die im Element `48767` eingesetzt
  werden; oder
- ein eng begrenzter serverseitiger Render-Filter für die betroffenen
  GenerateBlocks-Blöcke beziehungsweise Author-Dynamic-Tags.

Die gewählte Lösung und der Grund gegen die Alternative sind kurz im Code oder
in der Projektdokumentation festzuhalten. Ein Shortcode ist zu bevorzugen, wenn
GenerateBlocks keinen dokumentierten Filter für den benötigten Archivkontext
bereitstellt.

## Funktionsumfang

- Serverseitige Ausgabe des Autorenavatars
- Serverseitige Ausgabe des Anzeigenamens
- Serverseitige Ausgabe von Vor- und Nachname, sofern gepflegt
- Sinnvolles Verhalten bei fehlendem Avatar oder fehlenden Namensfeldern
- Barrierefreier Alternativtext für das Avatarbild
- Konfigurierbare beziehungsweise dokumentierte Avatargröße; für das bestehende
  Element ist eine Ausgabe um 150 × 150 Pixel vorgesehen

## Akzeptanzkriterien

- Auf `https://seosharingsystem.de/author/anja_p/` enthält das vom Server
  gelieferte HTML ein Avatar-`<img>` mit einer erreichbaren Bild-URL.
- Anjas Avatar wird über die WordPress-Avatar-API korrekt von der Hauptsite
  bezogen.
- Anzeigename sowie gepflegter Vor- und Nachname werden ohne JavaScript
  ausgegeben.
- Die Autorenbeschreibung bleibt unverändert vorhanden.
- Auf `https://seosharingsystem.de/author/locke85/` bleibt Jans bisher
  funktionierende Ausgabe korrekt und erscheint nicht doppelt.
- Mindestens die Autorenarchive `anja_p`, `christian`, `marcel`, `oliver` und
  `locke85` werden geprüft.
- Alle ausgegebenen Avatar-URLs liefern HTTP 200 und einen Bild-Content-Type.
- Im HTML erscheinen weder PHP-/JavaScript-Quelltext noch ungelöste Platzhalter.
- Seiten außerhalb von Autorenarchiven bleiben unverändert.
- PHP-Lint und vorhandene Projektprüfungen laufen erfolgreich.

## Verifikation

1. CFP lokal mit `php -l` prüfen.
2. Vor dem Deployment die bestehenden Ausgaben der genannten Autorenseiten
   sichern.
3. CFP über den bestehenden Deployment-Prozess ausrollen.
4. Element `48767` nur dann anpassen, wenn die gewählte CFP-Schnittstelle dies
   erfordert.
5. Frontend-HTML ohne eingeloggte Sitzung und mit umgangenem Seitencache prüfen.
6. Darstellung auf Desktop und Mobilgerät visuell kontrollieren.
7. Jans Autorenarchiv als Regressionstest prüfen.

## Rollback

- Vorherige CFP-Version über den bestehenden Deployment-Prozess wiederherstellen.
- Falls Element `48767` geändert wird, vorher eine WordPress-Revision erzeugen
  und bei einem Rollback diese Revision wiederherstellen.

## Nicht Bestandteil

- Migration aller Multisite-Avatare in die Mediathek von Blog 9
- Neuzuordnung einzelner Avatar-Attachments
- Änderungen an Autorenbeschreibungen oder redaktionellen Profildaten
- Clientseitige REST-API-Fallbacks

## Umsetzung

- CFP-Version `2.0.2` ergänzt das global geladene Modul
  `includes/author-archive-dynamic-profile.php`.
- Verwendet werden die in GenerateBlocks 2.2.0 vorhandenen serverseitigen
  Filter `generateblocks_dynamic_tag_replacement` und
  `generateblocks_before_dynamic_tag_replace`.
- Der Fallback greift nur bei `is_author()`, einem gültigen abgefragten
  `WP_User` und leerer ursprünglicher GenerateBlocks-Ausgabe.
- Unterstützt werden `author_avatar_url` sowie die Whitelist
  `display_name`, `first_name` und `last_name`.
- Avatar-URLs werden ausschließlich über `get_avatar_url()` bezogen. Die
  Standardgröße beträgt 150 Pixel und ist über
  `WG_CFP_AUTHOR_ARCHIVE_AVATAR_SIZE` beziehungsweise
  `wg_cfp_author_archive_avatar_size` konfigurierbar.
- Fehlende Avatar-Alt-Texte werden serverseitig aus dem Anzeigenamen ergänzt.
- Shortcodes wurden nicht eingesetzt, da die installierte GenerateBlocks-Version
  einen engeren PHP-Filterpfad bereitstellt und Element `48767` dadurch
  unverändert bleiben konnte.

## Verifikation

- PHP-Lint für Hauptdatei und alle Include-Dateien: erfolgreich.
- Isolierte Funktionstests für Scope, Escaping, Whitelist, Avatar-API,
  Nicht-Duplizierung und Alt-Text: erfolgreich.
- Deployment per bestehendem Raidboxes-SFTP-Zugang: erfolgreich; die drei
  ausgelieferten Dateien sind SHA-256-identisch mit dem lokalen Stand.
- Live-Version nach dem Metadatenabgleich: `2.0.2`.
- Release-Version `2.0.2` ist konsistent in `package.json`, `plugin.json`,
  Plugin-Header und `readme.txt` hinterlegt.
- Die Änderungshistorie wurde in `CHANGELOG.md`, `plugin.json` und
  `readme.txt` ergänzt; der vorherige Release-Stand ist als `2.0.1`
  eingeordnet.
- Geprüftes Release-Paket:
  `custom-functionality-deployment_v2.0.2.zip`, SHA-256
  `fb83618aabf8c88321470790d505da901b538bfc1bd7be3cab9d6532d8f851c5`.
- Anonyme, cache-umgehende HTML-Prüfung für `anja_p`, `christian`, `marcel`,
  `oliver` und `locke85`: erfolgreich.
- Je Archiv genau ein Profilavatar, Anzeigename, Vorname, Nachname und
  barrierefreier Alt-Text; keine Platzhalter oder Quelltext-Leaks.
- Alle fünf Avatar-URLs: HTTP 200 mit `image/png` oder `image/jpeg`.
- Anjas Avatar-URL wird über die WordPress-Avatar-API von
  `webgefaehrte.de/wp-content/uploads/` bezogen.
- Autorenbeschreibungen sind gegenüber dem Vorher-Stand unverändert.
- Element `48767` ist einschließlich Inhalt bytegleich geblieben.
- Vorher-, Nachher- und Rollback-Artefakte liegen unter
  `tasks/artifacts/author-archive-dynamic-profile-fields-20260727/`.
- Eine visuelle Desktop-/Mobilkontrolle konnte nicht automatisiert werden, weil
  in der Browser-Runtime keine Browser-Sitzung verfügbar war.

Hinweis: Für `oliver` liefert die bestehende WordPress-Avatar-API aktuell eine
erreichbare JPEG-Datei mit dem Dateinamen
`2025-08_Jutta-Kirsch_SEO-Share-Basisabo_2025_57_110-pdf.jpg`. Das CFP bildet
genau die API-Ausgabe ab; eine redaktionelle Korrektur der Avatar-Zuordnung ist
gemäß Nicht-Bestandteil dieses Tasks nicht erfolgt.

## Rollback-Stand

- Gesicherte CFP-Hauptdatei und Readme der vorherigen Live-Version `2.1.0`
  befinden sich im Unterordner `rollback`.
- Für ein Rollback sind diese beiden Dateien zurückzuspielen und das neue
  Include `includes/author-archive-dynamic-profile.php` zu entfernen.
- Für Element `48767` ist kein Rollback nötig, da es nicht geändert wurde.
