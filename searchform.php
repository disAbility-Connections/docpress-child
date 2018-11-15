<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
	<div class="form-group">
		<div class="input-group">
			
			<input class="form-control" type="search" placeholder="<?php esc_html_e( 'Search', 'docpress' ) ?>" value="<?php echo esc_html( get_search_query() ) ?>" name="s" title="<?php _e( 'Search for:', 'docpress' ) ?>" />
			
			<?php 
	
			$slug = '';
	
			if ( is_category() || 
				isset( $_GET['category_name'] ) ) : 
				   
				   $term = get_queried_object();
				   $slug = ( isset( $_GET['category_name'] ) && $_GET['category_name'] ) ? $_GET['category_name'] : $term->slug;
			
			?>
			
				<input class="hidden" type="hidden" name="category_name" value="<?php echo $slug; ?>" />
			
			<?php endif; ?>
			
			<span class="input-group-btn">
				<input type="submit" class="btn btn-primary" value="<?php esc_html_e( 'Search', 'docpress' ) ?>" />
			</span>
		</div>
	</div>
</form>