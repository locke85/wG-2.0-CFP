<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wg_cfp_is_main_site_context' ) || ! wg_cfp_is_main_site_context() ) {
    return;
}

if ( ! function_exists( 'wg_cfp_register_sharing_news_post_type' ) ) {
    function wg_cfp_register_sharing_news_post_type() {
        if ( post_type_exists( 'wg_sharing_news' ) ) {
            return;
        }

        $labels = array(
            'name'                  => _x( 'News', 'Post Type General Name', 'wg_sharing_news' ),
            'singular_name'         => _x( 'Sharing News', 'Post Type Singular Name', 'wg_sharing_news' ),
            'menu_name'             => __( 'News', 'wg_sharing_news' ),
            'name_admin_bar'        => __( 'News', 'wg_sharing_news' ),
            'archives'              => __( 'News Archives', 'wg_sharing_news' ),
            'attributes'            => __( 'News Attributes', 'wg_sharing_news' ),
            'parent_item_colon'     => __( 'Parent News:', 'wg_sharing_news' ),
            'all_items'             => __( 'All News', 'wg_sharing_news' ),
            'add_new_item'          => __( 'Add News', 'wg_sharing_news' ),
            'add_new'               => __( 'Add New', 'wg_sharing_news' ),
            'new_item'              => __( 'New News', 'wg_sharing_news' ),
            'edit_item'             => __( 'Edit News', 'wg_sharing_news' ),
            'update_item'           => __( 'Update News', 'wg_sharing_news' ),
            'view_item'             => __( 'View News', 'wg_sharing_news' ),
            'view_items'            => __( 'View News', 'wg_sharing_news' ),
            'search_items'          => __( 'Search News', 'wg_sharing_news' ),
            'not_found'             => __( 'Not found', 'wg_sharing_news' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wg_sharing_news' ),
            'featured_image'        => __( 'Featured Image', 'wg_sharing_news' ),
            'set_featured_image'    => __( 'Set featured image', 'wg_sharing_news' ),
            'remove_featured_image' => __( 'Remove featured image', 'wg_sharing_news' ),
            'use_featured_image'    => __( 'Use as featured image', 'wg_sharing_news' ),
            'insert_into_item'      => __( 'Insert into News', 'wg_sharing_news' ),
            'uploaded_to_this_item' => __( 'Uploaded to this News', 'wg_sharing_news' ),
            'items_list'            => __( 'News list', 'wg_sharing_news' ),
            'items_list_navigation' => __( 'News list navigation', 'wg_sharing_news' ),
            'filter_items_list'     => __( 'Filter News list', 'wg_sharing_news' ),
        );

        register_post_type(
            'wg_sharing_news',
            array(
                'label'               => __( 'News', 'wg_sharing_news' ),
                'description'         => __( 'Provides members with updates regarding the Sharing-System', 'wg_sharing_news' ),
                'labels'              => $labels,
                'supports'            => array( 'title', 'editor', 'thumbnail', 'comments', 'revisions', 'page-attributes', 'post-formats', 'excerpt' ),
                'taxonomies'          => array( 'post_tag' ),
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'menu_position'       => 5,
                'menu_icon'           => 'dashicons-excerpt-view',
                'show_in_admin_bar'   => true,
                'show_in_rest'        => true,
                'show_in_nav_menus'   => true,
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'rewrite'             => array(
                    'slug'       => 'sharing-news',
                    'with_front' => true,
                    'pages'      => true,
                    'feeds'      => true,
                ),
                'capability_type'     => 'page',
            )
        );

        register_taxonomy_for_object_type( 'post_tag', 'wg_sharing_news' );
    }
}
add_action( 'init', 'wg_cfp_register_sharing_news_post_type', 0 );

if ( ! function_exists( 'wg_cfp_register_sharing_news_metabox' ) ) {
    function wg_cfp_register_sharing_news_metabox() {
        if ( ! post_type_exists( 'wg_sharing_news' ) ) {
            return;
        }

        add_meta_box(
            'wg_cfp_sharing_news_options',
            'Sharing News Advanced Options',
            'wg_cfp_render_sharing_news_metabox',
            'wg_sharing_news',
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'wg_cfp_register_sharing_news_metabox' );

if ( ! function_exists( 'wg_cfp_get_sharing_news_meta_fields' ) ) {
    function wg_cfp_get_sharing_news_meta_fields() {
        return array(
            'wg_news_options_cta'           => 'url',
            'wg_news_options_sponsored'     => 'checkbox',
            'wg_news_options_media-caption' => 'text',
            'wg_news_options_placeholder'   => 'textarea',
            'wg_news_options_category'      => 'text',
        );
    }
}

if ( ! function_exists( 'wg_cfp_render_sharing_news_metabox' ) ) {
    function wg_cfp_render_sharing_news_metabox( $post ) {
        wp_nonce_field( 'wg_cfp_save_sharing_news_options', 'wg_cfp_sharing_news_options_nonce' );

        echo '<p>Additional fields to enhance NEWs sharing</p>';
        echo '<table class="form-table" role="presentation"><tbody>';

        printf(
            '<tr><th scope="row"><label for="wg_news_options_cta">CTA</label></th><td><input class="regular-text" id="wg_news_options_cta" name="wg_news_options_cta" type="url" value="%s"></td></tr>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_news_options_cta', true ) )
        );

        echo '<tr><th scope="row"><label for="wg_news_options_sponsored">Sponsored</label></th><td>';
        printf(
            '<label><input id="wg_news_options_sponsored" name="wg_news_options_sponsored" type="checkbox" %s> Check if the news items is sponsored by a partner</label>',
            checked( get_post_meta( $post->ID, 'wg_news_options_sponsored', true ), 'on', false )
        );
        echo '</td></tr>';

        printf(
            '<tr><th scope="row"><label for="wg_news_options_media-caption">Media Caption</label></th><td><input class="regular-text" id="wg_news_options_media-caption" name="wg_news_options_media-caption" type="text" value="%s"></td></tr>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_news_options_media-caption', true ) )
        );

        printf(
            '<tr><th scope="row"><label for="wg_news_options_placeholder">Placeholder</label></th><td><textarea class="regular-text" id="wg_news_options_placeholder" name="wg_news_options_placeholder" rows="5">%s</textarea></td></tr>',
            esc_textarea( (string) get_post_meta( $post->ID, 'wg_news_options_placeholder', true ) )
        );

        printf(
            '<tr><th scope="row"><label for="wg_news_options_category">Category</label></th><td><input class="regular-text" id="wg_news_options_category" name="wg_news_options_category" type="text" value="%s"></td></tr>',
            esc_attr( (string) get_post_meta( $post->ID, 'wg_news_options_category', true ) )
        );

        echo '</tbody></table>';
    }
}

if ( ! function_exists( 'wg_cfp_save_sharing_news_metabox' ) ) {
    function wg_cfp_save_sharing_news_metabox( $post_id ) {
        if ( 'wg_sharing_news' !== get_post_type( $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['wg_cfp_sharing_news_options_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wg_cfp_sharing_news_options_nonce'] ) ), 'wg_cfp_save_sharing_news_options' ) ) {
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

        foreach ( wg_cfp_get_sharing_news_meta_fields() as $meta_key => $type ) {
            if ( 'checkbox' === $type ) {
                $value = isset( $_POST[ $meta_key ] ) ? 'on' : '';
            } elseif ( isset( $_POST[ $meta_key ] ) ) {
                $raw_value = wp_unslash( $_POST[ $meta_key ] );

                switch ( $type ) {
                    case 'url':
                        $value = esc_url_raw( $raw_value );
                        break;
                    case 'textarea':
                        $value = sanitize_textarea_field( $raw_value );
                        break;
                    default:
                        $value = sanitize_text_field( $raw_value );
                        break;
                }
            } else {
                $value = '';
            }

            if ( '' === $value ) {
                delete_post_meta( $post_id, $meta_key );
            } else {
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }
}
add_action( 'save_post', 'wg_cfp_save_sharing_news_metabox' );
