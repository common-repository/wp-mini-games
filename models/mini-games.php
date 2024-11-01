<?php
// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or(at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.

define('WP_MINI_GAMES_OPTION_NAME', 'wpmg_mini_games_options');

class MiniGames {
	/**
	* Class declaration.
	*
	*/
	private function MiniGames() {
		
	}
	
	/**
     * Get the games array.
     *
     * @return $mini_games
     **/
	function get_games() {
		$mini_games = array(
			'governor-of-poker'		=> 'Governor of Poker',	
			'texas-holdem-poker'	=> 'Texas Holdem Poker',
			'quarterback'			=> 'Quarterback',
			'poktris_bwin'			=> 'Poktris',
			'avalanche_bwin'		=> 'Avalanche',
			'solitaire_bwin'		=> 'Solitaire',
			'daredevil_bwin'		=> 'Dare Devil',
			'outscounter_bwin'		=> 'Outs Counter',
			'potoddstrainer_bwin'	=> 'Pot Odds Trainer',
			'oddsgame_bwin'			=> 'Odds Game',
			'xenon_racing'			=> 'Xenon Prime Racing',
			'uphill_rush'			=> 'Uphill Rush',
			'gladiator_tournament'	=> 'Gladiator Tournament'
		);
		$game_list = fetch_feed("http://www.tubepress.net/api/game.php");
		$items = $game_list->get_items(0, 100);
		if(!empty($items)) {
			foreach($items as $item) {
				$link =  $item->get_link();
				$title = $item->get_title();
				$mini_games[$link] = $title;
			}
		}		
		return $mini_games;
	}
	
	/**
     * Get the default options.
     *
     * @return $default_options
     **/
	function get_default_options() {
		$mini_games = MiniGames::get_games();
		
		$default_options = array(
			'mini_games'				=> $mini_games,
			'default_mini_game'			=> WP_MINI_GAMES_DEFAULT_MINI_GAME,
			'default_mini_game_width'	=> WP_MINI_GAMES_DEFAULT_WIDTH,
			'default_mini_game_height'	=> WP_MINI_GAMES_DEFAULT_HEIGHT
		);
        
        return $default_options;
	}
	
	/**
     * Get options.
     *
     * @return $options
     **/
	function get_options() {
		$options = get_option(WP_MINI_GAMES_OPTION_NAME);
        
        if ($options === false)
            $options = array();
        
        $default_options = MiniGames::get_default_options();
        
        foreach ($default_options AS $key => $value) {
            if (!isset ($options[$key]))
                $options[$key] = $value;
        }
        
        return $options;
	}
	
	/**
     * Function to update options.
     *
     * @return void
     **/
    function update_options($options) {
        if (isset($_POST['update']) && check_admin_referer (WP_MINI_GAMES_ADMIN_REFERRER)) {
            $_POST = stripslashes_deep($_POST);
            
            $current_options = MiniGames::get_options();
            
            foreach ($options AS $key => $value) {
	            $current_options[$key] = $value;
	        }
	        
            update_option(WP_MINI_GAMES_OPTION_NAME, $current_options);
            
            $this->render_message(__('Your options have been updated', WP_MINI_GAMES_TEXT_DOMAIN));
        }
    }
    
    /**
     * Function to get country code.
     *
     * @return $country_code
     **/
    function get_country_code() {
		$country_code = strtolower(substr(get_bloginfo('language'), stripos(get_bloginfo('language'), '-') + 1));
		
		return $country_code;
	}
	
	/**
	* Get the embed code.
	*
	* @return $mini_game_embed_code
	*/
	function get_embed_code($mini_game, $width, $height) {
		$attribution_link = MiniGames::get_attribution_link();
		switch($mini_game) {
			case 'governor-of-poker':
			case 'texas-holdem-poker':
				$src = "http://www.miniclip.com/games/governor-of-poker/en/governorofpoker_web.swf";
			break;
			default:
				$src = preg_match("/http:\/\//i",$mini_game) ? $mini_game : WP_MINI_GAMES_FOLDER.$mini_game.'.swf';
			break;
		}
		$mini_game_embed_code = '<div class="wpmg-embed-code"><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="' . (string)$width . '" height="' . (string)($height) . '">
<param name="allowScriptAccess" value="sameDomain">
<param name="movie" value="'.$src.'">
<param name="quality" value="high">
<embed src="'.$src.'" quality="high" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width="' . (string)$width . '" height="' . (string)($height) . '">
</object><br />';

/*$mini_game_embed_code .= '<div class="wpmg-embed-field"><a href="#" onclick="return false;">' . __('Add this mini game to your blog', WP_MINI_GAMES_TEXT_DOMAIN) . '</a></div><div id="wpmg-popup">
	<a id="wpmg-popup-close">x</a>
	<h1>' . __('Add this mini game to your blog', WP_MINI_GAMES_TEXT_DOMAIN) . '</h1>
	<p id="wpmg-popup-text">
		' . __('Copy the code below to add this game to your blog', WP_MINI_GAMES_TEXT_DOMAIN) . ':<br />
		<textarea rows="2">%%WP_MINI_GAMES_EMBED_CODE%%</textarea>
	</p>
</div>
<div id="wpmg-popup-background"></div>
*/
$mini_game_embed_code .= '<div class="wpmg-attribution-text">' . $attribution_link . '</div></div>';

		$mini_game_embed_code = str_replace('%%WP_MINI_GAMES_EMBED_CODE%%', htmlentities($mini_game_embed_code), $mini_game_embed_code);
		
		return $mini_game_embed_code;
	}
	
	/**
	* Get the attribution link.
	*
	* @return $attribution_link
	*/
	function get_attribution_link() {
		$country_code = MiniGames::get_country_code();
		
		$supported_country_codes = array(
			'ar' 	=> '&#216;&#185;&#216;&#177;&#216;&#168;&#217;',
			//'bg'	=> '&#1041;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;',
			'de'	=> 'Deutsch', 
			//'ca'	=> 'Catal&#224;', // ca is Canada
			//'cz'	=> '&#268;esky',
			//'dk'	=> 'Dansk', 
			'es'	=> 'Espa&#241;ol',
			'fr'	=> 'Fran&#231;ais', 
			//'gr'	=> '&#917;&#955;&#955;&#951;&#957;&#953;&#954;&#940;',
			//'hr'	=> 'Hrvatski',
			//'hu'	=> 'Magyar',
			'it'	=> 'Italiano', 
			//'nl'	=> 'Nederlands',
			//'no'	=> 'Norsk', 
			//'pl'	=> 'Polski', 
			'pt'	=> 'Portugu&#234;s',
			//'ro'	=> 'Rom&#226;n&#259;',
			'ru'	=> '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;',
			'se'	=> 'Svenska',
			//'sk'	=> 'Slovensky',
			//'sl'	=> 'Slovenski',
			//'tr'	=> 'T&#252;rk&#231;e'
		);
		
		$wpmg_base_url = 'http://www.tubepress.net/';
		$plugin_base_url = 'https://poker.bwin.com/';
		
		$country_code_keys = array_keys($supported_country_codes);
		
		if(in_array($country_code, $country_code_keys)) {
			$wpmg_url = $wpmg_base_url.$country_code;
			$plugin_url = $plugin_base_url.$country_code.'/poker.aspx';
		} else {
			$wpmg_url = $wpmg_base_url;
			$plugin_url = $plugin_base_url;
		}
		
		$attribution_link = sprintf(__('<p style="font-size:8px;text-align:center;"><a href="%s" target="_blank">Play Poker Online Bwin</a> Powered by <a href="%s" target="_blank">TubePress.NET</a></p>', WP_MINI_GAMES_TEXT_DOMAIN), $plugin_url, $wpmg_url);
		
		return $attribution_link;
	}
	
	/**
	* Get the singleton object.
	*
	*/
	function &get () {
	    static $instance;
		
	    if (!isset ($instance)) {
			$c = __CLASS__;
			$instance = new $c;
	    }
		
	    return $instance;
	}
}

// Cause the singleton to fire
MiniGames::get();
?>