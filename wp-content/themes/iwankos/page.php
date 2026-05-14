<?php get_header(); ?>
			<?php get_sidebar('top'); ?>
			<?php

			if (have_posts()) {
				/* Start the Loop */
				while (have_posts()) {
					the_post();
					get_template_part('content', 'page');
				}
			} else {
				theme_404_content();
			}
			?>
			<?php get_sidebar('bottom'); ?>
<?php get_footer(); ?>