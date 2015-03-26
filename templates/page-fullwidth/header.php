<?php
/**
 * Blank template header.
 *
 */
?><!DOCTYPE html>
<?php tha_html_before(); ?>
<html <?php language_attributes(); ?>>
<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?php wp_title( '|', true, 'right' ); ?></title>

	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php if ( is_admin_bar_showing() ) { ?>
	<style>
	#page { top: 32px; position: relative; }
	</style>
	<?php } ?>

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>