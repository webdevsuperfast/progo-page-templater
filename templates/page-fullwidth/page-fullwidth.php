<?php
/*
Template Name: ProGo - Full Width
Description: Full-width page template with no styling
*/

$template = dirname(__FILE__); ?>

<?php require_once($template.'/header.php'); ?>

	<!-- page container -->
	<div id="progo-container">

		<!-- content -->
		<div id="container">

			<?php
			/* display Page content */
			if (have_posts()) :
			while (have_posts()) : the_post();

			the_content();

			endwhile;
			endif;
			?>

		</div>

	</div><!-- /.progo-container -->
		
<?php require_once($template.'/footer.php'); ?>