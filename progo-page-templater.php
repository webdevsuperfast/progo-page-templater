<?php
/*
Plugin Name: ProGo Page Templater
Plugin URI: http://www.ninthlink.com/
Description: Add custom page templates to any WordPress theme
Version: 1.0.1
Author: Ninthlink, Inc.
Author URI: http://www.ninthlink.com/
*/

class ProGoPageTemplater {

	/**
	 * A Unique Identifier
	 */
	protected $plugin_slug;

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;


	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {

		if( null == self::$instance ) {
			self::$instance = new ProGoPageTemplater();
		} 

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter(
			'page_attributes_dropdown_pages_args',
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);

		// Get array of page templates
		$templates = scandir( plugin_dir_path(__FILE__). 'templates/' );
		if ( is_array( $templates ) ) :
			foreach ( $templates as $template ) {

				if ( $template == '.' || $template == '..' || strpos( $template, '.' ) ) continue;
				$template_meta = get_file_data( plugin_dir_path(__FILE__). 'templates/' . $template . '/' . $template . '.php', array('name' => 'Template Name', 'description' => 'Description') );
				$this->templates[ $template . '/' . $template . '.php' ] = ( isset( $template_meta['name'] ) ? $template_meta['name'] : 'ProGo Page Template' );

			}
		endif;

		// Add action to register template functions.php
		add_action(
			'init', // <---- is this the right action? Appears to work...
			array( $this, 'register_template_functions' )
		);

    // Admin Menu
    add_action(
      'admin_menu',
      array( $this, 'progo_page_templater_menu' )
    );

    

	}

  public function progo_page_templater_menu() {
    add_options_page( 'ProGo Page Templater', 'ProGo Page Templater', 'manage_options', 'progo-page-templater', array( $this, 'progo_page_teplater_options' ) );
  }

  public function progo_page_teplater_options() {
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    print( '<ul class="wrap">' );
    if ( is_array($this->templates) ) {
      foreach ( $this->templates as $template => $name ) {
        print( '<li>' . $name . '</li>' );
      }
    }
    print( '</ul>' );
  }

	/**
	 * Adds our templates functions.php file to register sidebars, etc.
	 *
	 */
	public function register_template_functions() {
		if ( is_admin() ) {
			$post_id = false;

			if ( isset( $_GET['post'] ) ) {
				$post_id = $_GET['post'];
			}
			elseif( isset( $_POST['post'] ) ) {
				$post_id = $_POST['post'];
			}
								
			if ( $post_id !== false ) { // If we are editing a page (or post)...
				$template = get_post_meta($post_id,'_wp_page_template',TRUE); // get the page template...
				if ( array_key_exists( $template, $this->templates ) ) { // and if the template is in our list plugin page templates...
					$template_dir = plugin_dir_path( $template );
					$template_function = plugin_dir_path(__FILE__) . 'templates/' . $template_dir . 'functions.php'; // build the file location for our functions.php file...
					if ( file_exists( $template_function ) ) { // and if that file exists...
						include_once ( $template_function ); // include it
					}
				}
			}
		}
		else {
			// Not Admin side...
		}
	}


	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 *
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {

		global $post;

		if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
			return $template;
		}

		$file = plugin_dir_path(__FILE__). 'templates/' .get_post_meta( 
			$post->ID, '_wp_page_template', true 
		);
				
		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} 
		else { echo $file; }

		return $template;

	}


}

add_action( 'plugins_loaded', array( 'ProGoPageTemplater', 'get_instance' ) );

?>