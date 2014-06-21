<?php
/*
Plugin Name: [NAME]
Description: [DESCRIPTION]
Author: [AUTHOR]
Author URI: [URL]
Version: 1.0.0
*/



/*
.========================================================.
|               |  Installation |                        |
.========================================================.
*/

// Setup Plugin Activation/Deactivation Hooks

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'myplugin_install');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'myplugin_remove' );


function save_error(){
    update_option('plugin_error',  ob_get_contents());
}

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'save_error');

function myplugin_install() {

    global $wpdb;
    $i = 1;

    $plugin_pages = array(
		array(
			"page_title" => "Page 1",
			"page_name" => "page-1"
			),
		array(
			"page_title" => "Page 2",
			"page_name" => "page-2"
		),
	);

	foreach ($plugin_pages as $page) {

		$page_title = $page["page_title"];
		$page_name = $page["page_name"];

	    // the menu entry...
	    delete_option("myplugin_page".$i."_title");
	    add_option("myplugin_page".$i."_title", $page_title, '', 'yes');
	    // the slug...
	    delete_option("myplugin_page".$i."_name");
	    add_option("myplugin_page".$i."_name", $page_name, '', 'yes');
	    // the id...
	    delete_option("myplugin_page".$i."_id");
	    add_option("myplugin_page".$i."_id", '0', '', 'yes');

	    $the_page = get_page_by_title( $page_title );

	    if ( ! $the_page ) {

	        // Create post object
	        $_p = array();
	        $_p['post_title'] = $page_title;
	        $_p['post_content'] = '['.$page_name.']';
	        $_p['post_status'] = 'publish';
	        $_p['post_type'] = 'page';
	        $_p['comment_status'] = 'closed';
	        $_p['post_parent'] = "";
	        $_p['ping_status'] = 'closed';
	        $_p['post_category'] = array(1); // the default 'Uncatrgorized'

	        // Insert the post into the database
	        $the_page_id = wp_insert_post( $_p );

	    }
	    else {
	        // the plugin may have been previously active and the page may just be trashed...
	        $the_page_id = $the_page->ID;

	        //make sure the page is not trashed...
	        $the_page->post_status = 'publish';
	        $the_page_id = wp_update_post( $the_page );

	    }

	    delete_option( 'myplugin_page'.$i.'_id' );
	    add_option( 'myplugin_page'.$i.'_id', $the_page_id );

	    $i++;
	}
}

function myplugin_remove() {

    global $wpdb;
    $i = 1;

    $plugin_pages = array(
		array(
			"page_title" => "Page 1",
			"page_name" => "page-1"
			),
		array(
			"page_title" => "Page 2",
			"page_name" => "page-2"
		),
	);

	foreach ($plugin_pages as $page) {

	    //the id of our page...
	    $the_page_id = get_option( 'myplugin_page'.$i.'_id' );
	    if( $the_page_id ) {

	        wp_delete_post( $the_page_id ); // to the trash!

	    }

	    //Flush Through the Stored Info
	    delete_option("myplugin_page".$i."_title");
	    delete_option("myplugin_page".$i."_name");
	    delete_option("myplugin_page".$i."_id");

	    $i++;
	}
}


/*
.========================================================.
|           |  Templates for Custom Pages |              |
.========================================================.
*/

// Templates for custom pages are created via WP shortcodes so that we can retain the theme
add_shortcode("page-1", "page_1_handler");
function page_1_handler() {
	$my_plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	$template = $my_plugin_path . '/templates/page-1.php';
	include($template);
}
add_shortcode("page-2", "page_2_handler");
function page_2_handler() {
	$my_plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	$template = $my_plugin_path . '/templates/page-2.php';
	include($template);
}

/*
.========================================================.
|                |  Initialize Classes  |                 |
.========================================================.
*/

/**
 * Load Classes
 *
 * Classes are stored in the woocommerce-myplugin/classes folder
 *
 * require($my_plugin_path.'/classes/Class_Example.php');
 * $my_class = new Class_Example();
 */


/*
.========================================================.
|            |  Plugin Functions |                       |
.========================================================.
*/

// Does Class Exists Yet?.. If Yes GO!
if ( ! class_exists( 'WC_Plugin' ) ) {

	class WC_Plugin {

		public function __construct() {

			$my_plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

			// called only after woocommerce has finished loading
			add_action( 'woocommerce_init', array( &$this, 'woocommerce_loaded' ) );

			// called after all plugins have loaded
			add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );

			// called just before the woocommerce template functions are included
			add_action( 'init', array( &$this, 'include_template_functions' ), 20 );


			// indicates we are running the admin
			if ( is_admin() ) {
				//...
			}

			// indicates we are being served over ssl
			if ( is_ssl() ) {
				// ...
			}

		}

// -----------------------------------------------------------------------------------------------------------------------------------------------

		/**
		 * Take care of anything that needs woocommerce to be loaded.
		 * For instance, if you need access to the $woocommerce global
		 */
		public function woocommerce_loaded() {

			global $woocommerce;

			/**
			 * Override Woo Template Files
			 */
			function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {

				$my_plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

			  	$_template = $template;
			  	if ( ! $template_path ) $template_path = $woocommerce->template_url;
			  	$plugin_path  = $my_plugin_path . '/templates/';


			  	// Modification: Get the template from this plug-in, if it exists - THIS IS PRIORITY
			  	if ( file_exists( $plugin_path . $template_name ) )
			    	$template = $plugin_path . $template_name;

				// Look within passed path within the theme if template is not in plugin - this is second priority
				if (! file_exists( $plugin_path . $template_name ) )
			  	$template = locate_template(
				    array(
				      	$template_path . $template_name,
				      	$template_name
				    )
				);

			  	// Use default template
			  	if ( ! $template )
			    	$template = $_template;

			  	// Return what we found
			 	return $template;
			}
			add_filter( 'woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 80, 3 );

			// --------------------------------------------------------------------------------------------------------------
			/**
			 * Load Custom JS and CSS (after woo)
			 */
			function myplugin_css_and_js() {
				wp_register_style('myplugin_css', plugins_url('css/coolbluewoo.css',__FILE__ ));
				wp_enqueue_style('myplugin_css');
				wp_register_style('font_awesome', plugins_url('css/font-awesome.min.css',__FILE__ ));
				wp_enqueue_style('font_awesome');
				wp_register_script( 'myplugin_js', plugins_url('js/scripts.js',__FILE__ ));
				wp_enqueue_script('myplugin_js');
			}
			add_action( 'wp_enqueue_scripts','myplugin_css_and_js');
			// --------------------------------------------------------------------------------------------------------------

		}

		/**
		 * Take care of anything that needs all plugins to be loaded
		 */
		public function plugins_loaded() {

			/**
			 * Custom Page Templates
			 */
			// add_action("template_redirect", 'myplugin_custom_pages');

			// function myplugin_custom_pages() {
			//     global $wp;
			//     $my_plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

			//     if ($wp->query_vars["pagename"] == 'free-pound-select') {
			//         $templatefilename = 'freepound.php';
			//         if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
			//             $return_template = TEMPLATEPATH . '/' . $templatefilename;
			//         } else {
			//             $return_template = $my_plugin_path . '/templates/onepagecheckout/' . $templatefilename;
			//         }
			//         do_theme_redirect($return_template);
			//     }
			// }

			// function do_theme_redirect($url) {
			//     global $post, $wp_query;
			//     if (have_posts()) {
			//         include($url);
			//         die();
			//     } else {
			//         $wp_query->is_404 = true;
			//     }
			// }

		}

		/**
		* Override any of the template functions from woocommerce/woocommerce-template.php
		* with our own template functions file
		*/
		public function include_template_functions() {
			include( 'woocommerce-template.php' );
		}

	}

	// Instantiate our plugin class and add it to the set of globals
	$GLOBALS['wc_plugin'] = new WC_Plugin();

}