<?php

$theme_header = wp_get_theme();

define( 'THEME_VER', $theme_header->get( 'Version' ) );
define( 'THEME_URL', get_stylesheet_directory_uri() );
define( 'THEME_DIR', get_stylesheet_directory() );

add_action( 'init', function() {
	
	wp_register_style( 
		'docpress-parent',
		trailingslashit( get_template_directory_uri() ) . 'style.css'
	);
	
	wp_register_style( 
		'docpress-child',
		trailingslashit( THEME_URL ) . 'dist/assets/css/app.css',
		array(),
		THEME_VER
	);
	
} );

add_action( 'wp_enqueue_scripts', function() {
	
	wp_enqueue_style( 'docpress-parent' );
	
	wp_enqueue_style( 'docpress-child' );
	
} );

add_filter( 'template_include', 'disconn_search_template' );
function disconn_search_template( $template ) {
	
	if ( is_search() ) {
		return locate_template( 'index.php' );
	}
	
	return $template;
	
}

add_action( 'category_edit_form_fields', 'add_wysiwyg_to_term_description', 10, 2);

function add_wysiwyg_to_term_description( $term, $taxonomy ) {
    ?>
    <tr valign="top">
        <th scope="row">Description</th>
        <td>
            <?php wp_editor( html_entity_decode( $term->description), 'description', array( 'media_buttons' => false ) ); ?>
            <script>
                jQuery( window ).ready( function(){
                    jQuery( 'label[for=description]' ).parent().parent().remove();
                } );
            </script>
        </td>
    </tr>
    <?php
} 

add_action( 'widgets_init', function() {
	
	register_sidebar( array(
		'id' => 'footer-wide-widgets',
		'name' => __( 'Footer Wide Widgets', 'docpress-child' ),
		'description' => __( 'This goes directly above the copyright.', 'docpress-child' ),
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	) );
	
} );