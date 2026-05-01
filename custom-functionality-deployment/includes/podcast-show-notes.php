<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wg_cfp_is_main_site_context' ) || ! wg_cfp_is_main_site_context() ) {
    return;
}

if ( ! function_exists( 'wg_cfp_get_episode_guest_user' ) ) {
    function wg_cfp_get_episode_guest_user( $post_id = null ) {
        $post_id = $post_id ? absint( $post_id ) : get_the_ID();

        if ( ! $post_id ) {
            return null;
        }

        $user_id = (int) get_post_meta( $post_id, 'wg_interviewgast', true );
        if ( $user_id <= 0 ) {
            return null;
        }

        $user = get_user_by( 'id', $user_id );

        return $user instanceof WP_User ? $user : null;
    }
}

if ( ! function_exists( 'wg_cfp_guest_shortcode' ) ) {
    function wg_cfp_guest_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'field'   => 'display_name',
                'link'    => '0',
                'label'   => '',
                'rel'     => 'nofollow noopener',
                'blank'   => '1',
                'before'  => '',
                'after'   => '',
                'wrap'    => '',
                'class'   => '',
                'post_id' => '',
            ),
            $atts,
            'wg_guest'
        );

        $post_id = '' !== $atts['post_id'] ? absint( $atts['post_id'] ) : null;
        $user    = wg_cfp_get_episode_guest_user( $post_id );

        if ( ! $user ) {
            return '';
        }

        $core_fields = array(
            'display_name',
            'user_login',
            'user_nicename',
            'user_email',
            'user_url',
            'first_name',
            'last_name',
            'nickname',
            'description',
        );

        $field = sanitize_key( $atts['field'] );
        $value = '';

        if ( in_array( $field, $core_fields, true ) ) {
            if ( in_array( $field, array( 'first_name', 'last_name', 'nickname', 'description' ), true ) ) {
                $value = (string) get_user_meta( $user->ID, $field, true );
            } else {
                $value = isset( $user->{$field} ) ? (string) $user->{$field} : '';
            }
        } else {
            $value = (string) get_user_meta( $user->ID, $field, true );
        }

        $value = trim( $value );
        if ( '' === $value ) {
            return '';
        }

        $before = esc_html( $atts['before'] );
        $after  = esc_html( $atts['after'] );

        if ( '1' === (string) $atts['link'] ) {
            $url = esc_url( $value );
            if ( '' === $url ) {
                return '';
            }

            $label_value = '' !== $atts['label'] ? $atts['label'] : ( wp_parse_url( $url, PHP_URL_HOST ) ?: $user->display_name );
            $target      = '1' === (string) $atts['blank'] ? ' target="_blank"' : '';
            $rel         = '' !== $atts['rel'] ? ' rel="' . esc_attr( $atts['rel'] ) . '"' : '';
            $inner       = $before . '<a href="' . $url . '"' . $target . $rel . '>' . esc_html( $label_value ) . '</a>' . $after;
        } else {
            $inner = $before . esc_html( $value ) . $after;
        }

        $allowed_wrap_tags = array( 'li', 'p', 'div', 'span' );
        $wrap              = strtolower( sanitize_key( $atts['wrap'] ) );

        if ( in_array( $wrap, $allowed_wrap_tags, true ) ) {
            $class = '' !== $atts['class'] ? ' class="' . esc_attr( $atts['class'] ) . '"' : '';

            return '<' . $wrap . $class . '>' . $inner . '</' . $wrap . '>';
        }

        return $inner;
    }
}

if ( ! shortcode_exists( 'wg_guest' ) ) {
    add_shortcode( 'wg_guest', 'wg_cfp_guest_shortcode' );
}

if ( ! function_exists( 'wg_cfp_register_show_notes_metabox' ) ) {
    function wg_cfp_register_show_notes_metabox() {
        if ( ! post_type_exists( 'podcast' ) ) {
            return;
        }

        add_meta_box( 'wg_show_notes', 'wG Show Notes', 'wg_cfp_render_show_notes_box', 'podcast', 'normal', 'default' );
    }
}
add_action( 'add_meta_boxes', 'wg_cfp_register_show_notes_metabox' );

if ( ! function_exists( 'wg_cfp_get_show_notes_legacy_lines' ) ) {
    function wg_cfp_get_show_notes_legacy_lines( $legacy_value ) {
        if ( ! is_string( $legacy_value ) || '' === trim( $legacy_value ) ) {
            return array();
        }

        $normalized = preg_replace( '/<\s*\/?\s*(ul|li)[^>]*>/i', '', $legacy_value );
        $lines      = preg_split( "/\r\n|\r|\n/", (string) $normalized );

        return array_values( array_filter( array_map( 'trim', (array) $lines ) ) );
    }
}

if ( ! function_exists( 'wg_cfp_render_show_notes_box' ) ) {
    function wg_cfp_render_show_notes_box( $post ) {
        wp_nonce_field( 'wg_show_notes_save', 'wg_show_notes_nonce' );

        $erfolgsrezept = get_post_meta( $post->ID, 'wg_erfolgsrezept', true );
        $invest        = array();
        $return        = array();

        for ( $i = 1; $i <= 5; $i++ ) {
            $invest[ $i ] = get_post_meta( $post->ID, "wg_invest_{$i}", true );
            $return[ $i ] = get_post_meta( $post->ID, "wg_return_{$i}", true );
        }

        if ( empty( array_filter( $invest ) ) ) {
            $legacy_invest = wg_cfp_get_show_notes_legacy_lines( get_post_meta( $post->ID, 'wg_invest', true ) );

            for ( $i = 1; $i <= 5 && isset( $legacy_invest[ $i - 1 ] ); $i++ ) {
                $invest[ $i ] = $legacy_invest[ $i - 1 ];
            }
        }

        if ( empty( array_filter( $return ) ) ) {
            $legacy_return = wg_cfp_get_show_notes_legacy_lines( get_post_meta( $post->ID, 'wg_return', true ) );

            for ( $i = 1; $i <= 5 && isset( $legacy_return[ $i - 1 ] ); $i++ ) {
                $return[ $i ] = $legacy_return[ $i - 1 ];
            }
        }

        $transcript_id  = (int) get_post_meta( $post->ID, 'wg_transcript', true );
        $transcript_url = $transcript_id ? wp_get_attachment_url( $transcript_id ) : '';
        $interviewgast  = (int) get_post_meta( $post->ID, 'wg_interviewgast', true );
        $users          = get_users(
            array(
                'orderby' => 'display_name',
                'order'   => 'ASC',
                'fields'  => array( 'ID', 'display_name' ),
            )
        );

        echo '<div class="wg-field">';
        echo '<label for="wg_interviewgast">Interviewgast</label>';
        echo '<select id="wg_interviewgast" name="wg_interviewgast" class="widefat">';
        echo '<option value="">- Kein Gast -</option>';
        foreach ( $users as $user ) {
            printf( '<option value="%d" %s>%s</option>', $user->ID, selected( $interviewgast, (int) $user->ID, false ), esc_html( $user->display_name ) );
        }
        echo '</select>';
        echo '</div>';

        echo '<div class="wg-field">';
        echo '<label for="wg_erfolgsrezept">Erfolgsrezept</label>';
        echo '<input type="text" id="wg_erfolgsrezept" name="wg_erfolgsrezept" class="widefat" value="' . esc_attr( $erfolgsrezept ) . '">';
        echo '</div>';

        echo '<div class="wg-grid-2">';
        echo '<div class="wg-card">';
        echo '<label>Invest</label>';
        for ( $i = 1; $i <= 5; $i++ ) {
            echo '<div class="wg-row">';
            echo '<span class="idx">' . esc_html( (string) $i ) . '.</span>';
            printf( '<input type="text" name="wg_invest_%1$d" id="wg_invest_%1$d" class="widefat" value="%2$s">', $i, esc_attr( (string) $invest[ $i ] ) );
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="wg-card">';
        echo '<label>Return</label>';
        for ( $i = 1; $i <= 5; $i++ ) {
            echo '<div class="wg-row">';
            echo '<span class="idx">' . esc_html( (string) $i ) . '.</span>';
            printf( '<input type="text" name="wg_return_%1$d" id="wg_return_%1$d" class="widefat" value="%2$s">', $i, esc_attr( (string) $return[ $i ] ) );
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="wg-field">';
        echo '<label for="wg_transcript">Transkript (WebVTT aus Mediathek)</label>';
        echo '<input type="hidden" id="wg_transcript" name="wg_transcript" value="' . esc_attr( (string) $transcript_id ) . '">';
        echo '<div id="wg_transcript_preview" class="wg-mono">' . ( $transcript_url ? esc_html( $transcript_url ) : 'Kein VTT ausgewählt' ) . '</div>';
        echo '<button type="button" class="button" id="wg_transcript_btn">VTT auswählen</button> ';
        echo '<button type="button" class="button" id="wg_transcript_clear">Entfernen</button>';
        echo '</div>';
    }
}

if ( ! function_exists( 'wg_cfp_enqueue_show_notes_admin_assets' ) ) {
    function wg_cfp_enqueue_show_notes_admin_assets( $hook_suffix ) {
        if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || 'podcast' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_media();
        wp_register_style( 'wg-cfp-show-notes-admin', false, array(), null );
        wp_enqueue_style( 'wg-cfp-show-notes-admin' );
        wp_add_inline_style(
            'wg-cfp-show-notes-admin',
            '.wg-field{margin-bottom:16px}.wg-field label{display:block;font-weight:600;margin-bottom:6px}.wg-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}.wg-card{border:1px solid #e2e2e2;border-radius:6px;padding:12px;background:#fafafa}.wg-row{display:grid;grid-template-columns:28px 1fr;gap:8px;align-items:center;margin-bottom:8px}.wg-row .idx{color:#666;text-align:right}.wg-mono{margin:6px 0;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,\"Liberation Mono\",\"Courier New\",monospace;}'
        );
        wp_register_script( 'wg-cfp-show-notes-admin', false, array( 'jquery', 'media-editor' ), null, true );
        wp_enqueue_script( 'wg-cfp-show-notes-admin' );
        wp_add_inline_script(
            'wg-cfp-show-notes-admin',
            '(function($){var frame;$("#wg_transcript_btn").on("click",function(e){e.preventDefault();if(frame){frame.open();return;}frame=wp.media({title:"VTT-Datei wählen",button:{text:"Übernehmen"},library:{type:"text/vtt"},multiple:false});frame.on("select",function(){var att=frame.state().get("selection").first().toJSON();$("#wg_transcript").val(att.id);$("#wg_transcript_preview").text(att.url);});frame.open();});$("#wg_transcript_clear").on("click",function(e){e.preventDefault();$("#wg_transcript").val("");$("#wg_transcript_preview").text("Kein VTT ausgewählt");});})(jQuery);'
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wg_cfp_enqueue_show_notes_admin_assets' );

if ( ! function_exists( 'wg_cfp_save_show_notes_meta' ) ) {
    function wg_cfp_save_show_notes_meta( $post_id ) {
        if ( 'podcast' !== get_post_type( $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['wg_show_notes_nonce'] ) ) {
            return;
        }

        $nonce = sanitize_text_field( wp_unslash( $_POST['wg_show_notes_nonce'] ) );
        if ( ! wp_verify_nonce( $nonce, 'wg_show_notes_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $single_fields = array(
            'wg_interviewgast'  => 'absint',
            'wg_erfolgsrezept'  => 'sanitize_text_field',
            'wg_transcript'     => 'absint',
        );

        foreach ( $single_fields as $meta_key => $sanitizer ) {
            $raw_value = isset( $_POST[ $meta_key ] ) ? wp_unslash( $_POST[ $meta_key ] ) : '';
            $value     = call_user_func( $sanitizer, $raw_value );

            if ( '' === (string) $value || 0 === (int) $value && in_array( $meta_key, array( 'wg_interviewgast', 'wg_transcript' ), true ) ) {
                delete_post_meta( $post_id, $meta_key );
            } else {
                update_post_meta( $post_id, $meta_key, $value );
            }
        }

        for ( $i = 1; $i <= 5; $i++ ) {
            foreach ( array( "wg_invest_{$i}", "wg_return_{$i}" ) as $meta_key ) {
                $value = isset( $_POST[ $meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) ) : '';

                if ( '' === $value ) {
                    delete_post_meta( $post_id, $meta_key );
                } else {
                    update_post_meta( $post_id, $meta_key, $value );
                }
            }
        }
    }
}
add_action( 'save_post', 'wg_cfp_save_show_notes_meta' );
