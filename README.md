# webGefaehrte Custom Functionality Plugin

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
- SMTP-Konfiguration nur ueber `wp-config.php`-Konstanten.
- Tags fuer Pages inklusive Tag-Archiv-Erweiterung.
- Shortcodes `list_terms` und `show_tag_descriptions`.
- Yoast-Breadcrumb-Anpassung fuer Tag-Archive.
- SSP-Helfer fuer Revisions-Support beim `podcast`-Post-Type.

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
- Das CFP ersetzt `%category%` durch `ratgeber/{kategorie}`.
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
- CF7-Kontaktformular redirectet.
- CF7-Checkout/Multi-Step redirectet nicht.
- FontAwesome laedt korrekt oder wird sauber uebersprungen.
- Tags fuer Pages.
- Yoast Tag-Breadcrumb.
- `list_terms`.
- `show_tag_descriptions`.
- SMTP nur mit Konstanten.
- GeneratePress Smooth Scroll.
- Ratgeber-Permalinks fuer Posts.
- Permalink-Flush nach Aktivierung oder Basis-Slug-Aenderung.
- Keine PHP Notices im Debug Log.
