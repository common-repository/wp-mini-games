<?php
/*
Plugin Name: WP Mini Games
Plugin URI: http://www.tubepress.net/wp-mini-games
Description: Add Mini Games to your blog and make your visitors addicted to your site. Widget ready.
Version: 1.0.6
Author: Mario Mansour
Author URI: http://www.mariomansour.org/
============================================================================================================
1.0.6	- Fixed bug in game embed
1.0.5	- Added Governor of poker, a texas holdem poker based game
1.0.4	- Fixed a bug with game urls in widgets
1.0.3	- 100 Mini Games added to the Widget. Possibility of hotlinking to an swf game file when using minigame parameter 
1.0.2	- New games added (Uphill Rush, Xenon Racing, Gladiator Tournament)
1.0.1	- Bug fix in links
1.0.0	- First version

============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

include dirname (__FILE__).'/plugin.php';
include dirname (__FILE__).'/models/mini-games.php';
include dirname (__FILE__).'/widget.php';

define('WP_MINI_GAMES_TEXT_DOMAIN', 'wp-mini-games');
define('WP_MINI_GAMES_DEFAULT_WIDTH', 200);
define('WP_MINI_GAMES_DEFAULT_HEIGHT', 350);
define('WP_MINI_GAMES_DEFAULT_MINI_GAME', 'governor-of-poker');
define('WP_MINI_GAMES_FOLDER', 'http://www.tubepress.net/minigames/');//http://wp-mini-games.googlecode.com/files/
define('WP_MINI_GAMES_ADMIN_REFERRER', 'wpmg_mini_games_options');
/**
 * The TP Mini Games plugin
 *
 * @package wp-mini-games
 **/

class wpmg_MiniGamesAdmin extends wpmg_MiniGamesPlugin
{
	/**
	 * Constructor sets up page types, starts all filters and actions
	 *
	 * @return void
	 **/
	function wpmg_MiniGamesAdmin() {
		$this->register_plugin (WP_MINI_GAMES_TEXT_DOMAIN, __FILE__);
		
		$this->add_action('wp_print_scripts');
		$this->add_action('wp_print_styles');
		
		$this->add_shortcode('wp-mini-games', 'shortcode');
		
		if (is_admin ()) {
			$this->add_action('admin_menu');
			$this->add_filter('admin_head');
			
			$this->add_action('init', 'init', 15);
			$this->add_action('wp_dashboard_setup');
			
			$this->add_filter('contextual_help', 'contextual_help', 10, 2);
			$this->register_plugin_settings( __FILE__ );
		}
	}
	
	/**
	 * Plugin settings
	 *
	 * @return void
	 **/
	function plugin_settings ($links)	{
		$settings_link = '<a href="options-general.php?page='.basename( __FILE__ ).'">'.__('Settings', WP_MINI_GAMES_TEXT_DOMAIN).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	/**
	 * Setup dashboard
	 *
	 * @return void
	 **/
	function wp_dashboard_setup() {
		if (function_exists ('wp_add_dashboard_widget'))
			wp_add_dashboard_widget ('dashboard_wpmg', __ ('WP Mini Games', WP_MINI_GAMES_TEXT_DOMAIN), array (&$this, 'wpmg_dashboard'));
	}
	
	/**
	 * Dashboard feeds
	 *
	 * @return void
	 **/
	function wpmg_dashboard() {
		$news = fetch_feed( 'http://www.tubepress.net/feed' );
			
		if ( false === $plugin_slugs = get_transient( 'plugin_slugs' ) ) {
			$plugin_slugs = array_keys( get_plugins() );
			set_transient( 'plugin_slugs', $plugin_slugs, 86400 );
		}
			
		foreach ( array( 'news' => __('News') ) as $feed => $label ) {
			if ( is_wp_error($$feed) || !$$feed->get_item_quantity() )
				continue;
			
			$items = $$feed->get_items(0, 5);
			
			// Pick a random, non-installed plugin
			while ( true ) {
				// Abort this foreach loop iteration if there's no plugins left of this type
				if ( 0 == count($items) )
					continue 2;
			
				$item_key = array_rand($items);
				$item = $items[$item_key];
			
				list($link, $frag) = explode( '#', $item->get_link() );
			
				$link = esc_url($link);
				if ( preg_match( '|/([^/]+?)/?$|', $link, $matches ) )
					$slug = $matches[1];
				else {
					unset( $items[$item_key] );
					continue;
				}
			
				// Is this random plugin's slug already installed? If so, try again.
				reset( $plugin_slugs );
				foreach ( $plugin_slugs as $plugin_slug ) {
					if ( $slug == substr( $plugin_slug, 0, strlen( $slug ) ) ) {
						unset( $items[$item_key] );
						continue 2;
					}
				}
			
				// If we get to this point, then the random plugin isn't installed and we can stop the while().
				break;
			}
			
			// Eliminate some common badly formed plugin descriptions
			while ( ( null !== $item_key = array_rand($items) ) && false !== strpos( $items[$item_key]->get_description(), 'Plugin Name:' ) )
				unset($items[$item_key]);
			
			if ( !isset($items[$item_key]) )
				continue;
			
			// current bbPress feed item titles are: user on "topic title"
			if ( preg_match( '/&quot;(.*)&quot;/s', $item->get_title(), $matches ) )
				$title = $matches[1];
			else // but let's make it forward compatible if things change
				$title = $item->get_title();
			$title = esc_html( $title );
			
			$description = esc_html( strip_tags(@html_entity_decode($item->get_description(), ENT_QUOTES, get_option('blog_charset'))) );
						
			echo "<h4>$label</h4>\n";
			echo "<h5><a href='$link'>$title</a></h5>\n";
			echo "<p>$description</p>\n";
		}	
	}
	
	/**
	 * Render dashboard
	 *
	 * @return void
	 **/
	function dashboard() {
		//$settings  = $wpmg->get_current_settings ();
		//$simple   = $wpmg->modules->get_restricted ($wpmg->get_simple_modules (), $settings, 'page');
		
		$this->render_admin ('dashboard', array ('simple' => $simple, 'advanced' => $advanced));
	}
	
	/**
	 * Initialization function
	 *
	 * @return void
	 **/
	function init() {
		// Allow some customisation over core features
		if (file_exists (dirname (__FILE__).'/settings.php'))
			include dirname (__FILE__).'/settings.php';
		else
		{
			define ('WP_MINI_GAMES_OPTIONS', __ ('WP Mini Games', WP_MINI_GAMES_TEXT_DOMAIN));
			define ('WP_MINI_GAMES_ROLE', 'manage_options');
		}
	}
	
	/**
	 * Add WP Mini Games menu
	 *
	 * @return void
	 **/
	function admin_menu() {
		add_options_page(WP_MINI_GAMES_OPTIONS, WP_MINI_GAMES_OPTIONS, WP_MINI_GAMES_ROLE, basename (__FILE__), array ($this, 'admin_options'));
	}
	
	/**
	 * Display the options screen
	 *
	 * @return void
	 **/
	function admin_options() {
		// Save
		if (isset($_POST['update']) && check_admin_referer (WP_MINI_GAMES_ADMIN_REFERRER)) {
			$options['default_mini_game_width'] = $_POST['default_mini_game_width'];
			$options['default_mini_game_height'] = $_POST['default_mini_game_height'];
			
			MiniGames::update_options($options);
		}
		
		$this->render_admin('options', array ('options' => MiniGames::get_options()));
	}
	
	/**
	 * Display the management screen
	 *
	 * @return void
	 **/
	function admin_management() {
		$xml_file = 'http://searchtng.bwinlabs.com/searchtng/search?q=soccer&apiversion=2&partnerid=piw&format=xml';
		$xml_file_data = file_get_contents($xml_file);
		$xml_data = new SimpleXMLElement($xml_file_data);
		foreach($xml_data->event as $event) {
			echo('Event: ' . $event->details->name . '<br />');
		}
		// Save
		if (isset ($_POST['save']) && check_admin_referer ('wpmg-update_options')) {
			$options = $this->get_options ();
			$options['affiliate_id'] = isset ($_POST['affiliate_id']) ? true : false;

			update_option ('wpmg_options', $options);
			$this->render_message(__('Your options have been updated', WP_MINI_GAMES_TEXT_DOMAIN));
		}
		
		$this->render_admin ('management', array ('options' => $this->get_options ()));
	}
	
	/**
	 * Insert JS into the header
	 *
	 * @return void
	 **/
	function wp_print_scripts() {
		global $wp_scripts;
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('wpmg-popup', $this->url ().'/js/popup.js', array ('jquery'), $this->version());
		
		// Stop this being called again
		//remove_action('wp_print_scripts', array(&$this, 'wp_print_scripts'));
	}
	
	/**
	 * Insert CSS into the header
	 *
	 * @return void
	 **/
	function wp_print_styles() {
		wp_enqueue_style('wpmg-popup', $this->url ().'/css/popup.css', array (), $this->version ());
		
		// Stop this being called again
		//remove_action('wp_print_styles', array(&$this, 'wp_print_styles'));
	}
	
	/**
	 * Insert CSS and JS into administration page
	 *
	 * @return void
	 **/
	function admin_head() {
		
	}
	
	/**
	 * Get plugin version number
	 *
	 * @return $version
	 **/
	function version() {
		$plugin_data = implode ('', file (__FILE__));
		
		if (preg_match ('|Version:(.*)|i', $plugin_data, $version))
			return trim ($version[1]);
		return '';
	}
	
	/**
	 * Display contextual help
	 *
	 * @return $help
	 **/
	function contextual_help($help, $screen) {
		if ($screen == 'settings_page_wpmg') {
			$help .= '<h5>' . __('WP Mini Games Help', WP_MINI_GAMES_TEXT_DOMAIN) . '</h5><div class="metabox-prefs">';
			$help .= '<a href="http://www.tubepress.net/wp-mini-games" target="_blank">'.__ ('WP Mini Games Documentation', WP_MINI_GAMES_TEXT_DOMAIN).'</a><br/>';
			$help .= '</div>';
		}
		
		return $help;
	}
	
	/**
     * Function to handle shortcodes.
     *
     * @return void
     **/
    function shortcode($atts) {
    	$options = MiniGames::get_options();
    	
    	$default_mini_game = $options['default_mini_game'];
    	$default_mini_game_width = $options['default_mini_game_width'];
		$default_mini_game_height = $options['default_mini_game_height'];

    	extract(shortcode_atts(array(
    		'minigame'	=> $default_mini_game,
            'width'		=> $default_mini_game_width,
			'height'	=> $default_mini_game_height
        ), $atts));

        $output = MiniGames::get_embed_code($minigame, $width, $height);
        
        return $output;
    }
}


/**
 * Instantiate the plugin
 *
 * @global
 **/
$wpmg = new wpmg_MiniGamesAdmin;
?>