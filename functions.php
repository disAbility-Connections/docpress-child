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

add_action( 'pre_get_posts', function( $query ) {
	
	$query->set( 'orderby', 'title' );
	$query->set( 'order', 'ASC' );
  
} );

add_filter( 'posts_join', 'accessforall_custom_posts_join', 10, 2 );
/**
 * Callback for WordPress 'posts_join' filter.'
 *
 * @global $wpdb
 *
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 *
 * @param string $join The sql JOIN clause.
 * @param WP_Query $wp_query The current WP_Query instance.
 *
 * @return string $join The sql JOIN clause.
 */
function accessforall_custom_posts_join( $join, $query ) {

    global $wpdb;

    if ( is_main_query() && is_search() ) {

        $join .= "
        LEFT JOIN
		(
			{$wpdb->term_relationships} as relationships
			INNER JOIN
				{$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = relationships.term_taxonomy_id
			INNER JOIN
				{$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
		)
		ON {$wpdb->posts}.ID = relationships.object_id ";

    }

    return $join;

}

add_filter( 'posts_where', 'accessforall_custom_posts_where', 10, 2 );
/**
 * Callback for WordPress 'posts_where' filter.
 *
 * Modify the where clause to include searches against a WordPress taxonomy.
 *
 * @global $wpdb
 *
 * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 *
 * @param string $where The where clause.
 * @param WP_Query $query The current WP_Query.
 *
 * @return string The where clause.
 */
function accessforall_custom_posts_where( $where, $query ) {

    global $wpdb;

    if ( is_main_query() && is_search() ) {

        // get additional where clause for the user
        $user_where = accessforall_custom_get_user_posts_where();

        $where .= " OR (
						( 
							{$wpdb->term_taxonomy}.taxonomy IN( 'category' )
							AND
							{$wpdb->terms}.name LIKE '%" . esc_sql( get_query_var( 's' ) ) . "%'
							{$user_where}
							" . apply_filters( 'accessforall_custom_posts_where_category', '' ) . "
						)
						" . apply_filters( 'accessforall_custom_posts_where_after', '' ) . "
					)";

    }

    return $where;

}

// This accounts for searching by Sub-Category Name within a Category
add_filter( 'accessforall_custom_posts_where_category', function( $where ) {
	
	$queried_object = get_queried_object();
	
	if ( ! is_a( $queried_object, 'WP_Term' ) ) return $where;
	
	global $wpdb;
	
	$where .= ' AND ';
	$where .= "{$wpdb->terms}.slug LIKE '" . esc_sql( $queried_object->slug ) . "%'";
	
	return $where;
	
} );

// This accounts for searching by Tag without leaking into other Categories
add_filter( 'accessforall_custom_posts_where_after', function( $where ) {
	
	$queried_object = get_queried_object();
	
	if ( ! is_a( $queried_object, 'WP_Term' ) ) return $where;
	
	if ( $queried_object->taxonomy !== 'category' ) return $where;
	
	global $wpdb;
	
	$user_where = accessforall_custom_get_user_posts_where();
	
	$where .= ' OR ( ';
	
		$where .= "{$wpdb->term_taxonomy}.taxonomy IN( 'post_tag' )";
		$where .= " AND ";
		$where .= "{$wpdb->terms}.name LIKE '%" . esc_sql( get_query_var( 's' ) ) . "%'";
		$where .= " AND ";
		$where .= "{$wpdb->term_relationships}.term_taxonomy_id IN (" . esc_sql( $queried_object->term_id ) . ") ";
		$where .= $user_where;
	
	$where .= ' ) ';
	
	return $where;
	
} );

/**
 * Get a where clause dependent on the current user's status.
 *
 * @global $wpdb https://codex.wordpress.org/Class_Reference/wpdb
 *
 * @uses get_current_user_id()
 * @see http://codex.wordpress.org/Function_Reference/get_current_user_id
 *
 * @return string The user where clause.
 */
function accessforall_custom_get_user_posts_where() {

    global $wpdb;

    $user_id = get_current_user_id();
	
	$sql = " AND ({$wpdb->posts}.post_status = 'publish'";

    if ( $user_id ) {

        $status[] = "'private'";

        $sql .= " OR {$wpdb->posts}.post_author = {$user_id} AND {$wpdb->posts}.post_status = 'private'";

    }
	
	$sql .= ")";

    return $sql;

}

add_filter( 'posts_groupby', 'accessforall_custom_posts_groupby', 10, 2 );
/**
 * Callback for WordPress 'posts_groupby' filter.
 *
 * Set the GROUP BY clause to post IDs.
 *
 * @global $wpdb https://codex.wordpress.org/Class_Reference/wpdb
 *
 * @param string $groupby The GROUPBY caluse.
 * @param WP_Query $query The current WP_Query object.
 *
 * @return string The GROUPBY clause.
 */
function accessforall_custom_posts_groupby( $groupby, $query ) {

    global $wpdb;

    if ( is_main_query() && is_search() ) {
        $groupby = "{$wpdb->posts}.ID";
    }

    return $groupby;

}

/*

In case we ever have to do an import again, this along with https://wordpress.org/plugins/term-management-tools/ and search-replacing the WP XML for only Categories in order to prepend the slug with the County name speeds things up immensely. 

add_action( 'init', function() {
	
	$_query = new WP_Query( array(
		'post_type' => 'post',
		'posts_per_page' => -1,
		'fields' => 'ids',
	) );

	foreach ( $_query->posts as $post_id ) {
		
		$cat_post = array();
		$cats = wp_get_post_categories( $post_id, array( 'fields' => 'all' ) );
		
		if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
			
			foreach ( $cats as $cat ) {
				
				$cat_post[ $cat->term_id ] = $cat->slug;
				
				if ( $cat->parent == 0 ) continue;
				
				$cat_post = accessforall_complete_parent_category( $cat->parent, $cat_post );
				
			}
			
			if ( count( $cats ) == count( $cat_post ) ) continue;
			
			$data = array(
				'ID' => $post_id,
				'post_category' => array_keys( $cat_post ),
			);
			
			wp_update_post( $data );
			
		}
		
	}
	
} );

function accessforall_complete_parent_category( $term_id, $dep=array() ) {
    $category = get_term( $term_id, 'category' );
    $dep[ $category->term_id ] = $category->slug;
    if( $category->parent ) {
      $dep = accessforall_complete_parent_category( $category->parent, $dep );
    }
    return $dep;
}

*/