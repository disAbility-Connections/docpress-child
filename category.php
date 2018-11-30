<?php get_header(); ?>

	<div class="doc-main">
		<div class="container">
			<div class="row">
				<?php get_sidebar(); ?>
				<?php if ( is_active_sidebar( 'sidebar-widgets' ) ) : ?>
					<div class="content-area doclist col-sm-8 col-md-8">
				<?php else: ?>
					<div class="content-area doclist col-sm-12">
				<?php endif; ?>
					<div class="main-content page">
						
					<?php $term = get_queried_object(); ?>
						
					<header class="entry-header">
						<h1 class="entry-title"><?php echo $term->name ?></h1>
					</header>
						
					<hr />
						
					<?php if ( $term_description = term_description( $term->id, 'category' ) ) : ?> 
						
						<?php echo apply_filters( 'the_content', $term_description ); ?>
						
						<hr />
						
					<?php endif; ?>
						
					<?php if ( have_posts() ) : ?> 	

						<?php while ( have_posts() ) : the_post(); ?>
							<?php get_template_part( 'content' ); ?>
						<?php endwhile; ?>

					<?php else : ?>

						<?php get_template_part( 'content', 'none' ); ?>

					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

<?php get_footer(); ?>