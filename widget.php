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

class wpmg_MiniGamesWidget extends WP_Widget {
	/**
	* Declares the widget class.
	*
	*/
	function wpmg_MiniGamesWidget() {
		$widget_ops = array('classname' => 'wpmg_MiniGamesWidget', 'description' => __('Display WP Mini Games in your sidebar.', WP_MINI_GAMES_TEXT_DOMAIN));
	    $control_ops = array('width' => 300, 'height' => 300);
	    $this->WP_Widget('wpmg_MiniGamesWidget', __('WP Mini Game', WP_MINI_GAMES_TEXT_DOMAIN), $widget_ops, $control_ops);
	}
	
	/**
	* Displays the Widget
	*
	*/
	function widget($args, $instance) {
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$mini_game = empty($instance['miniGame']) ? WP_MINI_GAMES_DEFAULT_MINI_GAME : $instance['miniGame'];
		$mini_game_width = empty($instance['miniGameWidth']) ? WP_MINI_GAMES_DEFAULT_WIDTH : (int)$instance['miniGameWidth'];
		$mini_game_height = empty($instance['miniGameHeight']) ? WP_MINI_GAMES_DEFAULT_HEIGHT : (int)$instance['miniGameHeight'];
		
		echo($before_widget);
		if ( $title )
			echo($before_title . $title . $after_title);
		
		echo(MiniGames::get_embed_code($mini_game, $mini_game_width, $mini_game_height));
		echo($after_widget);
	}
	
	/**
	* Saves the widgets settings.
	*
	*/
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['miniGame'] = strip_tags(stripslashes($new_instance['miniGame']));
		$instance['miniGameWidth'] = strip_tags(stripslashes($new_instance['miniGameWidth']));
		$instance['miniGameHeight'] = strip_tags(stripslashes($new_instance['miniGameHeight']));
		
		return $instance;
	}
	
	/**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array('title'=>__('WP Mini Game', WP_MINI_GAMES_TEXT_DOMAIN), 'miniGame'=>'texas-holdem-poker') );
		
		$title = htmlspecialchars($instance['title']);
		
		$miniGames = MiniGames::get_games();
		
		$miniGameWidth = htmlspecialchars(empty($instance['miniGameWidth']) ? (string)WP_MINI_GAMES_DEFAULT_WIDTH : $instance['miniGameWidth']);
		$miniGameHeight = htmlspecialchars(empty($instance['miniGameHeight']) ? (string)WP_MINI_GAMES_DEFAULT_HEIGHT : $instance['miniGameHeight']);
		
		echo('<p><label for="' . $this->get_field_name('title') . '">' . __('Title', WP_MINI_GAMES_TEXT_DOMAIN) . ':<br /><input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>');
		
		$currentMiniGame = htmlspecialchars($instance['miniGame']);
		
		echo('<p><label for="' . $this->get_field_name('miniGame') . '">' . __('Mini Game', WP_MINI_GAMES_TEXT_DOMAIN) . ':<br />
		<select name="' . $this->get_field_name('miniGame') . '" id="' . $this->get_field_id('miniGame') . '">');
		
		foreach($miniGames as $key => $miniGame) {
			if($currentMiniGame == $key)
				$selected = ' selected';
			else
				$selected = '';
			
			echo('<option value="' . $key . '"' . $selected . '>' . $miniGame . '</option>');
		}
		
		echo('</select></label></p>');
		
		echo '<p><label for="' . $this->get_field_name('miniGameWidth') . '">' . __('Mini Game Width', WP_MINI_GAMES_TEXT_DOMAIN) . ':<br /><input style="width: 100px;" id="' . $this->get_field_id('miniGameWidth') . '" name="' . $this->get_field_name('miniGameWidth') . '" type="text" value="' . $miniGameWidth . '" /></label></p>';
		echo '<p><label for="' . $this->get_field_name('miniGameHeight') . '">' . __('Mini Game Height', WP_MINI_GAMES_TEXT_DOMAIN) . ':<br /><input style="width: 100px;" id="' . $this->get_field_id('miniGameHeight') . '" name="' . $this->get_field_name('miniGameHeight') . '" type="text" value="' . $miniGameHeight . '" /></label></p>';
	}
}

function wpmgMiniGamesWidgetInit() {
	register_widget('wpmg_MiniGamesWidget');
}

add_action('widgets_init', 'wpmgMiniGamesWidgetInit');
?>