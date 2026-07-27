# webGefaehrte Custom Functionality Plugin

Aktuelle Release-Version: `2.0.2`. Die vollständige Änderungshistorie steht in
[`CHANGELOG.md`](CHANGELOG.md).

## Zweck

Das CFP ist das gruppenweite Standard-Plugin fuer allgemeine Website-Funktionalitaet, die nicht zum WordPress-Core gehoert und nicht in die `functions.php` einzelner Websites verlagert werden soll.

## Abgrenzung

- `CFP`: allgemeine Website-Funktionalitaet wie Page Excerpts, TOC, H2-Anker, Lesezeit, Header-/CTA-Metaboxen, SMTP per Konstanten, GeneratePress-/GenerateBlocks-nahe Helfer, Yoast-/MailPoet-/SSP-Helfer, Tags fuer Pages, Glossar-Shortcodes, FontAwesome-Bereitstellung, scoped CF7-Redirects.
- `wg-membership-suite`: Membership, Checkout, LMS, Login, 2FA, Bestellungen, Membership-CPTs und zugehoerige Rollen-/Kompatibilitaetslogik.
- `functions.php`: website-spezifische Einzelfaelle.

`wg_angebot` Author-Support liegt nicht im CFP.

## Bereitgestellte Funktionen

- Page Excerpts fuer `page`.
- TOC-Shortcode `toc`.
- H2-Anker im Content.
- Lesezeit-Shortcode `lesezeit`.
- Header-/CTA-Metaboxen mit den bestehenden Meta Keys:
  `wg_h1_title`, `wg_div_tagline`, `wg_button_cta_text`, `wg_button_cta_url`, `wg_button_cta_tagline`.
- Podcast-Show-Notes-Metabox fuer `podcast` sowie der kompatible Gast-Shortcode `wg_guest`.
- Hauptseitengebundene CPT-Module fuer `wg_seo_chat` und `wg_sharing_news`, inklusive gehärteter Admin-Metaboxen.
- Chat-/Archiv-Helfer fuer `wg_seo_chat`, inklusive `display_post_type` und `chat_category_grid`.
- Der kompatible Shortcode `wg_seo_chat_form` ist als schlanke Frontend-Erfassung nur fuer eingeloggte Redakteure verfuegbar und speichert Chats als `pending`.
- SMTP-Konfiguration nur ueber `wp-config.php`-Konstanten.
- Tags fuer Pages inklusive Tag-Archiv-Erweiterung.
- Shortcodes `list_terms` und `show_tag_descriptions`.
- GeneratePress-Helfer fuer Modal-Script, Embedded-Pages und sicheren Smooth Scroll.
- Yoast-Breadcrumb-Anpassung fuer Tag-Archive.
- SSP-Helfer fuer Revisions-Support beim `podcast`-Post-Type.
- Optionales HTML in Benutzerprofil-Beschreibungen ueber `wg_cfp_allow_user_description_html` als Opt-in.
- Serverseitige GenerateBlocks-Fallbacks fuer Avatar, Anzeigename, Vorname und Nachname auf leeren Autorenarchiven.

## Autorenarchive

GenerateBlocks 2.x bezieht `author_meta` und `author_avatar_url` aus dem Autor des aktuellen Beitrags. Auf einem Autorenarchiv ohne Beitraege fehlt dieser Beitragskontext. Das CFP nutzt deshalb die serverseitigen GenerateBlocks-Filter `generateblocks_dynamic_tag_replacement` und `generateblocks_before_dynamic_tag_replace`.

- Der Fallback greift ausschliesslich bei `is_author()` und einem gueltigen abgefragten `WP_User`.
- Bereits vorhandene GenerateBlocks-Profildaten bleiben unveraendert; es wird nichts zusaetzlich ausgegeben. Ein fehlender Avatar-Alt-Text wird aus Gruenden der Barrierefreiheit auch bei bereits aufgeloesten Avatar-URLs ergaenzt.
- Unterstuetzt werden `display_name`, `first_name`, `last_name` und `author_avatar_url`.
- Der Avatar wird ueber `get_avatar_url()` aufgeloest. Dadurch bleiben Simple Local Avatars und Multisite-Avatare kompatibel.
- Die Standardgroesse ist 150 Pixel. Sie kann per Konstante `WG_CFP_AUTHOR_ARCHIVE_AVATAR_SIZE` oder Filter `wg_cfp_author_archive_avatar_size` angepasst werden.
- Der bestehende GenerateBlocks-Media-Block erhaelt beim Fallback einen Alt-Text mit dem Anzeigenamen.

Shortcodes wurden bewusst nicht verwendet: Der vorhandene Element-Aufbau bleibt damit unveraendert, und die in GenerateBlocks 2.2.0 bereitgestellten PHP-Filter erlauben einen engeren, rein serverseitigen Eingriff. Element 48767 muss nicht geaendert werden.

## Hauptseiten-Module

Die folgenden Module werden nur im Hauptseiten-Kontext der Multisite geladen. Auf Single-Site-Installationen greift der vorhandene Fallback und laedt sie ebenfalls.

- `wg_seo_chat` inklusive `wg_chat_category`, eigener Permalink-Struktur `/hilfe-chat/{kategorie}/{beitrag}/` und gehärteter Chat-Metabox.
- `wg_sharing_news` inklusive gehärteter News-Metabox.
- Podcast-Show-Notes, Chat-Archive und User-Profile-HTML.
- Die Frontend-Erfassung `wg_seo_chat_form` nutzt einen nonce-geschuetzten `admin-post`-Workflow nur fuer eingeloggte Nutzer mit `edit_posts`.

Bewusst nicht aus den Alt-Plugins uebernommen wurden:

- das oeffentliche Chat-Formular samt AJAX-/Upload-Workflow,
- Gast-Einreichungen,
- Debug-Logs,
- Theme-gebundene Admin-Assets,
- die globale Tag-Rewrite-Umschreibung aus `wg-news-cpt`.

## CF7-Redirect

Der Redirect fuer Contact Form 7 ist scoped und filterbar.

- Filter `wg_cfp_cf7_redirect_form_ids`: Liste erlaubter Formular-IDs. Default ist `[]`.
- Filter `wg_cfp_cf7_thank_you_url`: Ziel-URL. Default ist `home_url('/kontakt/danke')`.

Verhalten:

- Wenn Formular-IDs gesetzt sind, redirecten nur diese Formulare.
- Wenn keine Formular-IDs gesetzt sind, redirectet nur das Formular auf `/kontakt` bzw. `/kontakt/`.
- Ohne Formular-ID-Whitelist greift der Fallback nur auf `/kontakt` bzw. `/kontakt/`.
- Fuer abweichende Kontaktseiten sollen `wg_cfp_cf7_redirect_form_ids` oder `wg_cfp_cf7_thank_you_url` genutzt werden.
- Checkout- und Multi-Step-Formulare werden dadurch nicht global umgeleitet.

## FontAwesome

FontAwesome ist vom Theme entkoppelt und filterbar.

- Default-Pfad: `assets/fontawesome/css/all.css` innerhalb des Plugins.
- Filter `wg_cfp_fontawesome_url`: ueberschreibt die Asset-URL.
- Filter `wg_cfp_load_fontawesome`: aktiviert/deaktiviert das Laden.

Wenn keine Datei vorhanden ist und kein Filter eine URL liefert, wird FontAwesome sauber nicht geladen.

## Ratgeber-Permalinks

Das CFP kann fuer Beitraege die URL-Struktur `/ratgeber/{kategorie}/{post-name}/` bereitstellen.

- Empfohlen ist eine Permalinkstruktur mit `%category%`, z. B. `/%category%/%postname%/`.
- Das CFP erweitert diese Struktur zu `/ratgeber/%category%/%postname%/` und WordPress ersetzt `%category%` anschliessend durch die Beitragskategorie.
- Wenn keine `%category%`-Struktur verwendet wird, werden Permalinks standardmaessig nicht ueberschrieben.
- Filter `wg_cfp_ratgeber_permalink_base`: aendert den Basis-Slug. Default ist `ratgeber`.
- Filter `wg_cfp_enable_ratgeber_permalinks`: aktiviert/deaktiviert das Feature. Default ist `true`.
- Filter `wg_cfp_ratgeber_force_permalink_without_category_placeholder`: optionales Erzwingen ohne `%category%`. Default ist `false`.

Nach Aktivierung oder nach Aenderung des Basis-Slugs muessen die Permalinks einmal in WordPress gespeichert werden, damit die Rewrite Rule aktiv ist.

## Manuelle Tests

- CFP aktiv ohne `wg-membership-suite`.
- CFP aktiv mit `wg-membership-suite`.
- Page Excerpts.
- TOC.
- H2-Anker.
- Lesezeit-Shortcode.
- Header-/CTA-Metabox speichern.
- Podcast-Show-Notes-Metabox erscheint bei `podcast` und speichert Metadaten.
- `[wg_guest field="display_name"]` funktioniert auf Podcast-Beitrag mit `wg_interviewgast`.
- `[wg_guest field="wg_linkedin" link="1" label="LinkedIn" wrap="li"]` funktioniert.
- `[display_post_type]` gibt das Singular Label aus.
- `[chat_category_grid]` gibt Kategorien aus, falls Taxonomie vorhanden ist.
- CF7-Kontaktformular redirectet.
- CF7-Checkout/Multi-Step redirectet nicht.
- FontAwesome laedt korrekt oder wird sauber uebersprungen.
- Tags fuer Pages.
- Tag-Archive enthalten erwartete Post Types, ohne bestehende Post Types zu ueberschreiben.
- Author-Archive zeigen `wg_seo_chat`, falls der CPT existiert.
- Author-Archive ohne Beitraege rendern Avatar und Profildaten bereits im initialen HTML.
- Author-Archive mit funktionierendem Beitragskontext enthalten keine doppelten Profildaten.
- Avatar-URLs der Author-Archive liefern HTTP 200 mit einem Bild-Content-Type.
- Yoast Tag-Breadcrumb.
- `list_terms`.
- `show_tag_descriptions`.
- SMTP nur mit Konstanten.
- GeneratePress Smooth Scroll.
- Ratgeber-Permalinks fuer Posts.
- Keine redeclared-function-Fehler oder doppelten Shortcode-Registrierungen.
- Permalink-Flush nach Aktivierung oder Basis-Slug-Aenderung.
- Keine PHP Notices im Debug Log.
