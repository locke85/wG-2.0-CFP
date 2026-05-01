<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wg_cfp_is_main_site_context' ) || ! wg_cfp_is_main_site_context() ) {
    return;
}

if ( ! function_exists( 'wg_cfp_get_tag_archive_post_types' ) ) {
    function wg_cfp_get_tag_archive_post_types( $existing_post_types ) {
        if ( empty( $existing_post_types ) ) {
            $post_types = array( 'post', 'page' );
        } elseif ( is_array( $existing_post_types ) ) {
            $post_types = $existing_post_types;
        } else {
            $post_types = array( $existing_post_types );
        }

        if ( ! in_array( 'post', $post_types, true ) ) {
            $post_types[] = 'post';
        }

        if ( ! in_array( 'page', $post_types, true ) ) {
            $post_types[] = 'page';
        }

        if ( post_type_exists( 'wg_sharing_news' ) ) {
            $post_types[] = 'wg_sharing_news';
        }

        if ( post_type_exists( 'wg_seo_chat' ) ) {
            $post_types[] = 'wg_seo_chat';
        }

        return array_values( array_unique( $post_types ) );
    }
}

if ( ! function_exists( 'wg_cfp_extend_tag_archives_with_chat_post_types' ) ) {
    function wg_cfp_extend_tag_archives_with_chat_post_types( $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! $query->is_tag() ) {
            return;
        }

        $query->set( 'post_type', wg_cfp_get_tag_archive_post_types( $query->get( 'post_type' ) ) );
    }
}
add_action( 'pre_get_posts', 'wg_cfp_extend_tag_archives_with_chat_post_types', 20 );

if ( ! function_exists( 'wg_cfp_extend_author_archives_with_chat_post_type' ) ) {
    function wg_cfp_extend_author_archives_with_chat_post_type( $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! $query->is_author() || ! post_type_exists( 'wg_seo_chat' ) ) {
            return;
        }

        $query->set( 'post_type', array( 'post', 'wg_seo_chat' ) );
    }
}
add_action( 'pre_get_posts', 'wg_cfp_extend_author_archives_with_chat_post_type', 20 );

if ( ! function_exists( 'wg_cfp_add_author_support_to_wg_seo_chat' ) ) {
    function wg_cfp_add_author_support_to_wg_seo_chat() {
        if ( post_type_exists( 'wg_seo_chat' ) ) {
            add_post_type_support( 'wg_seo_chat', 'author' );
        }
    }
}
add_action( 'init', 'wg_cfp_add_author_support_to_wg_seo_chat', 20 );

if ( ! function_exists( 'wg_cfp_display_post_type_shortcode' ) ) {
    function wg_cfp_display_post_type_shortcode() {
        $post_type = get_post_type();
        if ( ! $post_type ) {
            return '';
        }

        $post_type_object = get_post_type_object( $post_type );
        if ( ! $post_type_object || ! isset( $post_type_object->labels->singular_name ) ) {
            return '';
        }

        return esc_html( $post_type_object->labels->singular_name );
    }
}

if ( ! shortcode_exists( 'display_post_type' ) ) {
    add_shortcode( 'display_post_type', 'wg_cfp_display_post_type_shortcode' );
}

if ( ! function_exists( 'wg_cfp_get_chat_category_image_html' ) ) {
    function wg_cfp_get_chat_category_image_html( $term ) {
        if ( function_exists( 'z_taxonomy_image' ) ) {
            ob_start();
            z_taxonomy_image( $term->term_id );
            $image_html = trim( ob_get_clean() );

            if ( '' !== $image_html ) {
                return '<div class="category-image">' . $image_html . '</div>';
            }
        }

        if ( function_exists( 'get_field' ) ) {
            $acf_image = get_field( 'wg_chat_category_image', 'term_' . $term->term_id );

            if ( is_string( $acf_image ) && '' !== trim( $acf_image ) ) {
                return '<div class="category-image">' . wp_kses_post( $acf_image ) . '</div>';
            }
        }

        return '';
    }
}

if ( ! function_exists( 'wg_cfp_chat_category_grid_shortcode' ) ) {
    function wg_cfp_chat_category_grid_shortcode() {
        if ( ! taxonomy_exists( 'wg_chat_category' ) || ! post_type_exists( 'wg_seo_chat' ) ) {
            return '';
        }

        $terms = get_terms(
            array(
                'taxonomy'   => 'wg_chat_category',
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => true,
            )
        );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return '';
        }

        ob_start();
        echo '<div class="category-grid">';

        foreach ( $terms as $term ) {
            $term_link = get_term_link( $term );
            if ( is_wp_error( $term_link ) ) {
                continue;
            }

            echo '<div class="category-container bereiche"><div class="category-item">';
            echo wg_cfp_get_chat_category_image_html( $term );
            echo '<a href="' . esc_url( $term_link ) . '"><h3 class="category-title">' . esc_html( $term->name ) . '</h3></a>';

            if ( ! empty( $term->description ) ) {
                echo '<div class="category-description">' . wp_kses_post( $term->description ) . '</div>';
            }

            $count = (int) $term->count;
            echo '<div class="category-button">';
            if ( 1 === $count ) {
                echo '<a class="button" href="' . esc_url( $term_link ) . '">1 Chat anzeigen</a>';
            } else {
                echo '<a class="button" href="' . esc_url( $term_link ) . '">' . esc_html( (string) $count ) . ' Chats anzeigen</a>';
            }
            echo '</div></div></div>';
        }

        echo '</div>';
        return ob_get_clean();
    }
}

if ( ! shortcode_exists( 'chat_category_grid' ) ) {
    add_shortcode( 'chat_category_grid', 'wg_cfp_chat_category_grid_shortcode' );
}

if ( ! function_exists( 'wg_cfp_register_chat_category_image_field_group' ) ) {
    function wg_cfp_register_chat_category_image_field_group() {
        if ( ! function_exists( 'acf_add_local_field_group' ) || ! taxonomy_exists( 'wg_chat_category' ) ) {
            return;
        }

        acf_add_local_field_group(
            array(
                'key'                   => 'group_wg_chat_category_image',
                'title'                 => 'WG Chat Category Image',
                'fields'                => array(
                    array(
                        'key'           => 'field_wg_chat_category_image',
                        'label'         => 'Category Image',
                        'name'          => 'wg_chat_category_image',
                        'type'          => 'textarea',
                        'instructions'  => 'Enter the HTML code for the chat category image.',
                        'required'      => 0,
                        'default_value' => '',
                        'rows'          => 4,
                    ),
                ),
                'location'              => array(
                    array(
                        array(
                            'param'    => 'taxonomy',
                            'operator' => '==',
                            'value'    => 'wg_chat_category',
                        ),
                    ),
                ),
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
            )
        );
    }
}
add_action( 'acf/init', 'wg_cfp_register_chat_category_image_field_group' );
