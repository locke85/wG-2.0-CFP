<?php
/**
 * Browserbasierter Dark Mode ohne eigenen Umschalter.
 *
 * Das CSS-Muster folgt der CSS-only-Variante aus der Minimal-Mistakes-
 * GitHub-Diskussion: https://github.com/mmistakes/minimal-mistakes/discussions/2033
 */

if ( ! function_exists( 'wg_cfp_get_dark_mode_custom_css' ) ) {
    function wg_cfp_get_dark_mode_custom_css() {
        return <<<'CSS'
/* Dark Mode: Color Manager variables */
@media (prefers-color-scheme: dark) {
:root {
    --contrast: var(--dark-contrast);
    --contrast-2: var(--dark-contrast-2);
    --contrast-3: var(--dark-contrast-3);
    --base: var(--dark-base);
    --base-2: var(--dark-base-2);
    --base-3: var(--dark-base-3);
    --accent: var(--dark-accent);
    --accent-2: var(--dark-accent-2);
}
}
CSS;
    }
}

if ( ! function_exists( 'wg_cfp_install_dark_mode_custom_css' ) ) {
    function wg_cfp_install_dark_mode_custom_css() {
        $stylesheet = get_stylesheet();
        $css_post   = wp_get_custom_css_post( $stylesheet );
        $custom_css = $css_post && isset( $css_post->post_content ) ? $css_post->post_content : '';
        $snippet    = wg_cfp_get_dark_mode_custom_css();

        if ( false !== strpos( $custom_css, '/* Dark Mode: Color Manager variables */' ) ) {
            return;
        }

        if ( '' !== trim( $custom_css ) ) {
            $custom_css .= "\n\n";
        }

        wp_update_custom_css_post(
            $custom_css . $snippet,
            array( 'stylesheet' => $stylesheet )
        );
    }
}
