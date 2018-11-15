<?php

add_action( 'init', function() {
	
	wp_register_style( 
		'docpress-parent',
		trailingslashit( get_template_directory_uri() ) . 'style.css'
	);
	
} );

add_action( 'wp_enqueue_scripts', function() {
	
	wp_enqueue_style( 'docpress-parent' );
	
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