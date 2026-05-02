<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wg_cfp_is_main_site_context' ) || ! wg_cfp_is_main_site_context() ) {
    return;
}

if ( ! function_exists( 'wg_cfp_get_chat_cpt_slug' ) ) {
    function wg_cfp_get_chat_cpt_slug() {
        return 'hilfe-chat';
    }
}

if ( ! function_exists( 'wg_cfp_get_chat_taxonomy_slug' ) ) {
    function wg_cfp_get_chat_taxonomy_slug() {
        return 'hilfe-chat-c';
    }
}

if ( ! function_exists( 'wg_cfp_register_chat_post_type' ) ) {
    function wg_cfp_register_chat_post_type() {
        if ( post_type_exists( 'wg_seo_chat' ) ) {
            return;
        }

        $labels = array(
            'name'                  => _x( 'Chats', 'Post Type General Name', 'wg_seo_chat' ),
            'singular_name'         => _x( 'Hilfe Chat', 'Post Type Singular Name', 'wg_seo_chat' ),
            'menu_name'             => __( 'Chat', 'wg_seo_chat' ),
            'name_admin_bar'        => __( 'Chat', 'wg_seo_chat' ),
            'archives'              => __( 'Chat Archives', 'wg_seo_chat' ),
            'attributes'            => __( 'Chat Attributes', 'wg_seo_chat' ),
            'parent_item_colon'     => __( 'Parent Chat:', 'wg_seo_chat' ),
            'all_items'             => __( 'All Chats', 'wg_seo_chat' ),
            'add_new_item'          => __( 'Add New Chat', 'wg_seo_chat' ),
            'add_new'               => __( 'Add New', 'wg_seo_chat' ),
            'new_item'              => __( 'New Chat', 'wg_seo_chat' ),
            'edit_item'             => __( 'Edit Chat', 'wg_seo_chat' ),
            'update_item'           => __( 'Update Chat', 'wg_seo_chat' ),
            'view_item'             => __( 'View Chat', 'wg_seo_chat' ),
            'view_items'            => __( 'View Chats', 'wg_seo_chat' ),
            'search_items'          => __( 'Search Chat', 'wg_seo_chat' ),
            'not_found'             => __( 'Not found', 'wg_seo_chat' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wg_seo_chat' ),
            'featured_image'        => __( 'Featured Image', 'wg_seo_chat' ),
            'set_featured_image'    => __( 'Set featured image', 'wg_seo_chat' ),
            'remove_featured_image' => __( 'Remove featured image', 'wg_seo_chat' ),
            'use_featured_image'    => __( 'Use as featured image', 'wg_seo_chat' ),
            'insert_into_item'      => __( 'Insert into Chat', 'wg_seo_chat' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Chat', 'wg_seo_chat' ),
            'items_list'            => __( 'Chat list', 'wg_seo_chat' ),
            'items_list_navigation' => __( 'Chat list navigation', 'wg_seo_chat' ),
            'filter_items_list'     => __( 'Filter Chat list', 'wg_seo_chat' ),
        );

        register_post_type(
            'wg_seo_chat',
            array(
                'label'               => __( 'Chat', 'wg_seo_chat' ),
                'description'         => __( 'Shares answers for user questions about SEO', 'wg_seo_chat' ),
                'labels'              => $labels,
                'supports'            => array( 'title', 'editor', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', 'excerpt', 'author' ),
                'taxonomies'          => array( 'wg_chat_category', 'post_tag' ),
                'hierarchical'        => true,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'menu_position'       => 5,
                'menu_icon'           => 'dashicons-format-chat',
                'show_in_admin_bar'   => true,
                'show_in_nav_menus'   => true,
                'show_in_rest'        => true,
                'can_export'          => true,
                'has_archive'         => wg_cfp_get_chat_cpt_slug(),
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'rewrite'             => array(
                    'slug'       => wg_cfp_get_chat_cpt_slug(),
                    'with_front' => true,
                    'pages'      => true,
                    'feeds'      => true,
                    'ep_mask'    => EP_PERMALINK,
                ),
                'capability_type'     => 'page',
            )
        );
    }
}
add_action( 'init', 'wg_cfp_register_chat_post_type', 0 );

if ( ! function_exists( 'wg_cfp_register_chat_taxonomy' ) ) {
    function wg_cfp_register_chat_taxonomy() {
        if ( taxonomy_exists( 'wg_chat_category' ) ) {
            return;
        }

        $labels = array(
            'name'                       => _x( 'Chat Categories', 'Taxonomy General Name', 'wg_chat_category' ),
            'singular_name'              => _x( 'Chat Category', 'Taxonomy Singular Name', 'wg_chat_category' ),
            'menu_name'                  => __( 'Chat Category', 'wg_chat_category' ),
            'all_items'                  => __( 'All Chat Categories', 'wg_chat_category' ),
            'parent_item'                => __( 'Parent Chat Category', 'wg_chat_category' ),
            'parent_item_colon'          => __( 'Parent Chat Category:', 'wg_chat_category' ),
            'new_item_name'              => __( 'New Chat Category Name', 'wg_chat_category' ),
            'add_new_item'               => __( 'Add New Chat Category', 'wg_chat_category' ),
            'edit_item'                  => __( 'Edit Chat Category', 'wg_chat_category' ),
            'update_item'                => __( 'Update Chat Category', 'wg_chat_category' ),
            'view_item'                  => __( 'View Chat Category', 'wg_chat_category' ),
            'separate_items_with_commas' => __( 'Separate Chat Categories with commas', 'wg_chat_category' ),
            'add_or_remove_items'        => __( 'Add or remove Chat Categories', 'wg_chat_category' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'wg_chat_category' ),
            'popular_items'              => __( 'Popular Chat Categories', 'wg_chat_category' ),
            'search_items'               => __( 'Search Chat Category', 'wg_chat_category' ),
            'not_found'                  => __( 'Not Found', 'wg_chat_category' ),
            'no_terms'                   => __( 'No Chat Categories', 'wg_chat_category' ),
            'items_list'                 => __( 'Chat Category list', 'wg_chat_category' ),
            'items_list_navigation'      => __( 'Chat Category list navigation', 'wg_chat_category' ),
        );

        register_taxonomy(
            'wg_chat_category',
            array( 'wg_seo_chat' ),
            array(
                'labels'            => $labels,
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'show_tagcloud'     => true,
                'rewrite'           => array( 'slug' => wg_cfp_get_chat_taxonomy_slug() ),
            )
        );
    }
}
add_action( 'init', 'wg_cfp_register_chat_taxonomy', 0 );

if ( ! function_exists( 'wg_cfp_add_chat_rewrite_rule' ) ) {
    function wg_cfp_add_chat_rewrite_rule() {
        if ( ! post_type_exists( 'wg_seo_chat' ) ) {
            return;
        }

        $slug = sanitize_title( wg_cfp_get_chat_cpt_slug() );

        add_rewrite_rule(
            '^' . $slug . '/([^/]+)/([^/]+)/?$',
            'index.php?post_type=wg_seo_chat&wg_chat_category=$matches[1]&wg_seo_chat=$matches[2]&name=$matches[2]',
            'top'
        );
    }
}
add_action( 'init', 'wg_cfp_add_chat_rewrite_rule', 20 );

if ( ! function_exists( 'wg_cfp_get_primary_chat_category_slug' ) ) {
    function wg_cfp_get_primary_chat_category_slug( $post_id ) {
        $terms = get_the_terms( $post_id, 'wg_chat_category' );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return '';
        }

        foreach ( $terms as $term ) {
            if ( isset( $term->slug ) && 'featured' !== $term->slug ) {
                return $term->slug;
            }
        }

        $term = reset( $terms );

        return ( $term && isset( $term->slug ) ) ? (string) $term->slug : '';
    }
}

if ( ! function_exists( 'wg_cfp_filter_chat_permalink' ) ) {
    function wg_cfp_filter_chat_permalink( $permalink, $post ) {
        if ( ! $post instanceof WP_Post || 'wg_seo_chat' !== $post->post_type ) {
            return $permalink;
        }

        $term_slug = wg_cfp_get_primary_chat_category_slug( $post->ID );

        if ( '' === $term_slug ) {
            return $permalink;
        }

        return home_url( user_trailingslashit( wg_cfp_get_chat_cpt_slug() . '/' . $term_slug . '/' . $post->post_name ) );
    }
}
add_filter( 'post_type_link', 'wg_cfp_filter_chat_permalink', 10, 2 );

if ( ! function_exists( 'wg_cfp_register_chat_options_metabox' ) ) {
    function wg_cfp_register_chat_options_metabox() {
        if ( ! post_type_exists( 'wg_seo_chat' ) ) {
            return;
        }

        add_meta_box(
            'wg_cfp_chat_options',
            'Chat-spezifische Felder',
            'wg_cfp_render_chat_options_metabox',
            'wg_seo_chat',
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'wg_cfp_register_chat_options_metabox' );

if ( ! function_exists( 'wg_cfp_get_chat_option_fields' ) ) {
    function wg_cfp_get_chat_option_fields() {
        return array(
            'wg_chat_options_type'        => 'text',
            'wg_chat_options_creation-date' => 'text',
            'wg_chat_options_user-name'   => 'text',
            'wg_chat_options_avatar'      => 'url',
            'wg_chat_options_question'    => 'html',
            'wg_chat_options_answer'      => 'html',
            'wg_chat_options_follow-up'   => 'html',
            'wg_chat_options_follow-date' => 'text',
            'wg_chat_options_follow-up-2' => 'html',
        );
    }
}

if ( ! function_exists( 'wg_cfp_render_chat_options_metabox' ) ) {
    function wg_cfp_render_chat_options_metabox( $post ) {
        wp_nonce_field( 'wg_cfp_save_chat_options', 'wg_cfp_chat_options_nonce' );

        $type = get_post_meta( $post->ID, 'wg_chat_options_type', true );
        if ( '' === $type ) {
            $type = 'option-one';
        }

        $question   = get_post_meta( $post->ID, 'wg_chat_options_question', true );
        $answer     = get_post_meta( $post->ID, 'wg_chat_options_answer', true );
        $follow_up  = get_post_meta( $post->ID, 'wg_chat_options_follow-up', true );
        $follow_up2 = get_post_meta( $post->ID, 'wg_chat_options_follow-up-2', true );

        echo '<p>Felder zur Eingabe von Chat spezifischen Informationen</p>';
        echo '<table class="form-table" role="presentation"><tbody>';

        echo '<tr><th scope="row">Type</th><td>';
        echo '<label><input type="radio" name="wg_chat_options_type" value="option-one" ' . checked( $type, 'option-one', false ) . '> FAQ</label><br>';
        echo '<label><input type="radio" name="wg_chat_options_type" value="option-two" ' . checked( $type, 'option-two', false ) . '> How-To</label>';
        echo '</td></tr>';

        printf(
            '<tr><th scope="row"><label for="wg_chat_options_creation-date">Creation-date</label></th><td><input class="regular-text" id="wg_chat_options_creation-date" name="wg_chat_options_creation-date" type="date" value="%s"></td></tr>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_chat_options_creation-date', true ) )
        );

        printf(
            '<tr><th scope="row"><label for="wg_chat_options_user-name">User-name</label></th><td><input class="regular-text" id="wg_chat_options_user-name" name="wg_chat_options_user-name" type="text" value="%s"></td></tr>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_chat_options_user-name', true ) )
        );

        echo '<tr><th scope="row"><label for="wg_chat_options_avatar">Avatar</label></th><td>';
        printf(
            '<input class="regular-text" id="wg_chat_options_avatar" name="wg_chat_options_avatar" type="url" value="%s"> <button type="button" class="button" id="wg_cfp_chat_avatar_button">Upload</button>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_chat_options_avatar', true ) )
        );
        echo '</td></tr>';

        echo '<tr><th scope="row">Question</th><td>';
        wp_editor(
            (string) $question,
            'wg_chat_options_question',
            array(
                'textarea_name' => 'wg_chat_options_question',
                'textarea_rows' => 6,
                'media_buttons' => true,
                'teeny'         => false,
            )
        );
        echo '</td></tr>';

        echo '<tr><th scope="row">Answer</th><td>';
        wp_editor(
            (string) $answer,
            'wg_chat_options_answer',
            array(
                'textarea_name' => 'wg_chat_options_answer',
                'textarea_rows' => 8,
                'media_buttons' => true,
                'teeny'         => false,
            )
        );
        echo '</td></tr>';

        echo '<tr><th scope="row">Follow-up</th><td>';
        wp_editor(
            (string) $follow_up,
            'wg_chat_options_follow-up',
            array(
                'textarea_name' => 'wg_chat_options_follow-up',
                'textarea_rows' => 6,
                'media_buttons' => true,
                'teeny'         => false,
            )
        );
        echo '</td></tr>';

        printf(
            '<tr><th scope="row"><label for="wg_chat_options_follow-date">Follow-date</label></th><td><input class="regular-text" id="wg_chat_options_follow-date" name="wg_chat_options_follow-date" type="date" value="%s"></td></tr>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_chat_options_follow-date', true ) )
        );

        echo '<tr><th scope="row">Follow-up-2</th><td>';
        wp_editor(
            (string) $follow_up2,
            'wg_chat_options_follow-up-2',
            array(
                'textarea_name' => 'wg_chat_options_follow-up-2',
                'textarea_rows' => 8,
                'media_buttons' => true,
                'teeny'         => false,
            )
        );
        echo '</td></tr>';

        echo '</tbody></table>';
    }
}

if ( ! function_exists( 'wg_cfp_enqueue_chat_admin_assets' ) ) {
    function wg_cfp_enqueue_chat_admin_assets( $hook_suffix ) {
        if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || 'wg_seo_chat' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script( 'jquery' );
        wp_add_inline_script(
            'jquery-core',
            "jQuery(function($){var frame;$('#wg_cfp_chat_avatar_button').on('click',function(e){e.preventDefault();if(frame){frame.open();return;}frame=wp.media({title:'Choose a file',button:{text:'Select this file'},multiple:false});frame.on('select',function(){var attachment=frame.state().get('selection').first().toJSON();$('#wg_chat_options_avatar').val(attachment.url||'');});frame.open();});});",
            'after'
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wg_cfp_enqueue_chat_admin_assets' );

if ( ! function_exists( 'wg_cfp_save_chat_options_metabox' ) ) {
    function wg_cfp_save_chat_options_metabox( $post_id ) {
        if ( 'wg_seo_chat' !== get_post_type( $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['wg_cfp_chat_options_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wg_cfp_chat_options_nonce'] ) ), 'wg_cfp_save_chat_options' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        foreach ( wg_cfp_get_chat_option_fields() as $meta_key => $type ) {
            if ( ! isset( $_POST[ $meta_key ] ) ) {
                if ( 'wg_chat_options_type' === $meta_key ) {
                    delete_post_meta( $post_id, $meta_key );
                }
                continue;
            }

            $raw_value = wp_unslash( $_POST[ $meta_key ] );

            switch ( $type ) {
                case 'html':
                    $value = wp_kses_post( $raw_value );
                    break;
                case 'url':
                    $value = esc_url_raw( $raw_value );
                    break;
                default:
                    $value = sanitize_text_field( $raw_value );
                    break;
            }

            if ( '' === $value ) {
                delete_post_meta( $post_id, $meta_key );
            } else {
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }
}
add_action( 'save_post', 'wg_cfp_save_chat_options_metabox' );

if ( ! function_exists( 'wg_cfp_can_submit_chat_from_frontend' ) ) {
    function wg_cfp_can_submit_chat_from_frontend() {
        return is_user_logged_in() && current_user_can( 'edit_posts' );
    }
}

if ( ! function_exists( 'wg_cfp_get_frontend_chat_authors' ) ) {
    function wg_cfp_get_frontend_chat_authors() {
        if ( ! current_user_can( 'edit_others_posts' ) ) {
            $current_user = wp_get_current_user();

            if ( ! $current_user instanceof WP_User || 0 === (int) $current_user->ID ) {
                return array();
            }

            return array( $current_user );
        }

        return get_users(
            array(
                'capability' => 'edit_posts',
                'orderby'    => 'display_name',
                'order'      => 'ASC',
            )
        );
    }
}

if ( ! function_exists( 'wg_cfp_get_frontend_chat_categories' ) ) {
    function wg_cfp_get_frontend_chat_categories() {
        if ( ! taxonomy_exists( 'wg_chat_category' ) ) {
            return array();
        }

        $terms = get_terms(
            array(
                'taxonomy'   => 'wg_chat_category',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );

        if ( is_wp_error( $terms ) ) {
            return array();
        }

        return array_values(
            array_filter(
                $terms,
                function( $term ) {
                    return isset( $term->slug ) && 'featured' !== $term->slug;
                }
            )
        );
    }
}

if ( ! function_exists( 'wg_cfp_get_chat_form_notice' ) ) {
    function wg_cfp_get_chat_form_notice() {
        $submitted = isset( $_GET['wg_cfp_chat_submitted'] ) ? sanitize_text_field( wp_unslash( $_GET['wg_cfp_chat_submitted'] ) ) : '';
        $error     = isset( $_GET['wg_cfp_chat_error'] ) ? sanitize_key( wp_unslash( $_GET['wg_cfp_chat_error'] ) ) : '';

        if ( '1' === $submitted ) {
            return '<div class="wg-cfp-chat-form-notice wg-cfp-chat-form-success"><p>Der Chat wurde als Entwurf zur Prüfung gespeichert.</p></div>';
        }

        if ( '' === $error ) {
            return '';
        }

        $messages = array(
            'unauthorized'     => 'Sie haben keine Berechtigung, Chats im Frontend anzulegen.',
            'invalid_request'  => 'Die Anfrage konnte nicht geprüft werden. Bitte erneut absenden.',
            'missing_question' => 'Bitte eine Nutzerfrage eingeben.',
            'missing_answer'   => 'Bitte eine Antwort eingeben.',
            'missing_category' => 'Bitte eine Kategorie auswählen.',
            'save_failed'      => 'Der Chat konnte nicht gespeichert werden.',
        );

        if ( ! isset( $messages[ $error ] ) ) {
            return '';
        }

        return '<div class="wg-cfp-chat-form-notice wg-cfp-chat-form-error"><p>' . esc_html( $messages[ $error ] ) . '</p></div>';
    }
}

if ( ! function_exists( 'wg_cfp_build_frontend_chat_redirect_url' ) ) {
    function wg_cfp_build_frontend_chat_redirect_url( $args = array() ) {
        $redirect_to = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : '';

        if ( '' === $redirect_to ) {
            $redirect_to = wp_get_referer();
        }

        if ( ! is_string( $redirect_to ) || '' === $redirect_to ) {
            $redirect_to = home_url( '/' );
        }

        $redirect_to = remove_query_arg( array( 'wg_cfp_chat_submitted', 'wg_cfp_chat_error' ), $redirect_to );

        return add_query_arg( $args, $redirect_to );
    }
}

if ( ! function_exists( 'wg_cfp_handle_frontend_chat_submission' ) ) {
    function wg_cfp_handle_frontend_chat_submission() {
        if ( ! wg_cfp_can_submit_chat_from_frontend() ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'unauthorized' ) ) );
            exit;
        }

        if ( ! isset( $_POST['wg_cfp_frontend_chat_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wg_cfp_frontend_chat_nonce'] ) ), 'wg_cfp_frontend_chat_submit' ) ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'invalid_request' ) ) );
            exit;
        }

        $question     = isset( $_POST['wg_chat_options_question'] ) ? sanitize_text_field( wp_unslash( $_POST['wg_chat_options_question'] ) ) : '';
        $answer       = isset( $_POST['wg_chat_options_answer'] ) ? wp_kses_post( wp_unslash( $_POST['wg_chat_options_answer'] ) ) : '';
        $follow_up    = isset( $_POST['wg_chat_options_follow-up'] ) ? sanitize_text_field( wp_unslash( $_POST['wg_chat_options_follow-up'] ) ) : '';
        $follow_up_2  = isset( $_POST['wg_chat_options_follow-up-2'] ) ? wp_kses_post( wp_unslash( $_POST['wg_chat_options_follow-up-2'] ) ) : '';
        $category_id  = isset( $_POST['wg_chat_category'] ) ? absint( wp_unslash( $_POST['wg_chat_category'] ) ) : 0;
        $selected_author_id = isset( $_POST['author_name'] ) ? absint( wp_unslash( $_POST['author_name'] ) ) : get_current_user_id();

        if ( '' === $question ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'missing_question' ) ) );
            exit;
        }

        if ( '' === trim( wp_strip_all_tags( $answer ) ) ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'missing_answer' ) ) );
            exit;
        }

        if ( $category_id <= 0 || ! term_exists( $category_id, 'wg_chat_category' ) ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'missing_category' ) ) );
            exit;
        }

        if ( ! current_user_can( 'edit_others_posts' ) || $selected_author_id <= 0 ) {
            $selected_author_id = get_current_user_id();
        }

        if ( $selected_author_id <= 0 ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'unauthorized' ) ) );
            exit;
        }

        $author = get_user_by( 'id', $selected_author_id );

        if ( ! $author instanceof WP_User || ! user_can( $author, 'edit_posts' ) ) {
            $selected_author_id = get_current_user_id();
            $author             = get_user_by( 'id', $selected_author_id );
        }

        $current_date = current_time( 'Y-m-d' );
        $post_id      = wp_insert_post(
            array(
                'post_title'   => $question,
                'post_content' => $answer,
                'post_status'  => 'pending',
                'post_type'    => 'wg_seo_chat',
                'post_author'  => $selected_author_id,
                'tax_input'    => array(
                    'wg_chat_category' => array( $category_id ),
                ),
                'meta_input'   => array(
                    'wg_chat_options_type'          => 'option-one',
                    'wg_chat_options_question'      => $question,
                    'wg_chat_options_answer'        => $answer,
                    'wg_chat_options_follow-up'     => $follow_up,
                    'wg_chat_options_follow-up-2'   => $follow_up_2,
                    'wg_chat_options_creation-date' => $current_date,
                    'wg_chat_options_user-name'     => $author instanceof WP_User ? $author->display_name : '',
                ),
            ),
            true
        );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_error' => 'save_failed' ) ) );
            exit;
        }

        wp_safe_redirect( wg_cfp_build_frontend_chat_redirect_url( array( 'wg_cfp_chat_submitted' => '1' ) ) );
        exit;
    }
}
add_action( 'admin_post_wg_cfp_submit_chat', 'wg_cfp_handle_frontend_chat_submission' );

if ( ! function_exists( 'wg_cfp_render_frontend_chat_form_shortcode' ) ) {
    function wg_cfp_render_frontend_chat_form_shortcode() {
        if ( ! wg_cfp_can_submit_chat_from_frontend() ) {
            return '';
        }

        $categories = wg_cfp_get_frontend_chat_categories();

        if ( empty( $categories ) ) {
            return '';
        }

        $authors = wg_cfp_get_frontend_chat_authors();

        ob_start();
        echo wg_cfp_get_chat_form_notice();
        echo '<form class="wg-cfp-chat-form" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post">';
        echo '<input type="hidden" name="action" value="wg_cfp_submit_chat">';
        wp_nonce_field( 'wg_cfp_frontend_chat_submit', 'wg_cfp_frontend_chat_nonce' );
        wp_referer_field();

        echo '<p><label for="wg_chat_options_question">Nutzerfrage</label><br>';
        echo '<input type="text" id="wg_chat_options_question" name="wg_chat_options_question" required class="widefat"></p>';

        echo '<p><label for="wg_chat_options_answer">Antwort</label><br>';
        echo '<textarea id="wg_chat_options_answer" name="wg_chat_options_answer" rows="8" required class="widefat"></textarea></p>';

        echo '<p><label for="wg_chat_options_follow-up">Folgefrage</label><br>';
        echo '<input type="text" id="wg_chat_options_follow-up" name="wg_chat_options_follow-up" class="widefat"></p>';

        echo '<p><label for="wg_chat_options_follow-up-2">Zweite Antwort</label><br>';
        echo '<textarea id="wg_chat_options_follow-up-2" name="wg_chat_options_follow-up-2" rows="6" class="widefat"></textarea></p>';

        echo '<p><label for="wg_chat_category">Kategorie</label><br>';
        echo '<select id="wg_chat_category" name="wg_chat_category" required class="widefat">';
        echo '<option value="">Kategorie auswählen...</option>';
        foreach ( $categories as $category ) {
            printf(
                '<option value="%d">%s</option>',
                (int) $category->term_id,
                esc_html( $category->name )
            );
        }
        echo '</select></p>';

        if ( count( $authors ) > 1 && current_user_can( 'edit_others_posts' ) ) {
            echo '<p><label for="author_name">Autor</label><br>';
            echo '<select id="author_name" name="author_name" class="widefat">';
            foreach ( $authors as $author ) {
                printf(
                    '<option value="%d">%s</option>',
                    (int) $author->ID,
                    esc_html( $author->display_name )
                );
            }
            echo '</select></p>';
        } else {
            echo '<input type="hidden" name="author_name" value="' . esc_attr( (string) get_current_user_id() ) . '">';
        }

        echo '<p><button type="submit" class="button button-primary">Chat als Entwurf speichern</button></p>';
        echo '</form>';

        return ob_get_clean();
    }
}

if ( ! shortcode_exists( 'wg_seo_chat_form' ) ) {
    add_shortcode( 'wg_seo_chat_form', 'wg_cfp_render_frontend_chat_form_shortcode' );
}
