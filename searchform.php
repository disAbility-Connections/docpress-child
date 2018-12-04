<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
	
	<?php 
	
	global $skip_counties;
	
	if ( ! $skip_counties ) : ?>
	
		<div class="form-group row category-group">

			<label for="category_name" class="col-sm-4 col-form-label text-right">
				<h3>
					<?php echo esc_html__( 'within', 'docpress-child' ); ?>
				</h3>
			</label>

			<div class="col-sm-5 text-left">

				<?php 

				$slug = '';

				if ( is_category() || 
					isset( $_GET['category_name'] ) ) : 

					   $term = get_queried_object();
					   $slug = ( isset( $_GET['category_name'] ) && $_GET['category_name'] ) ? $_GET['category_name'] : $term->slug;

				endif; 

				$categories = get_terms( 
					'category', 
					array(
						'parent' => 0,
					)
				);

				?>

				<select name="category_name" class="form-control">
					<option value="" <?php selected( $slug, '' ); ?>>
						<?php echo esc_html__( 'Choose County', 'docpress-child' ); ?>
					</option>
					<?php foreach ( $categories as $term ) : ?>
						<option value="<?php echo esc_html( $term->slug ); ?>" <?php selected( $slug, $term->slug ); ?>>
							<?php echo esc_html( $term->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>

			</div>

		</div>
	
	<?php endif; ?>
	
	<div class="form-group">
		
		<div class="input-group">
			
			<input class="form-control" type="search" placeholder="<?php esc_html_e( 'Search', 'docpress' ) ?>" value="<?php echo esc_html( get_search_query() ) ?>" name="s" title="<?php _e( 'Search for:', 'docpress' ) ?>" />
			
			<span class="input-group-btn">
				<input type="submit" class="btn btn-primary" value="<?php esc_html_e( 'Search', 'docpress' ) ?>" />
			</span>
		</div>
	</div>
</form>