<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="wrap">
	<?php screen_icon(); ?>
	
  <h2><?php printf (__ ('%s | General Options', WP_MINI_GAMES_TEXT_DOMAIN), WP_MINI_GAMES_OPTIONS); ?></h2>	
	<form method="post" action="<?php echo $this->url ($_SERVER['REQUEST_URI']); ?>">
	<?php wp_nonce_field (WP_MINI_GAMES_ADMIN_REFERRER); ?>

	<table border="0" cellspacing="5" cellpadding="5" class="form-table">
		<tr>
			<th valign="top" align="right"><label for="default_mini_game_width"><?php _e ('Default Width', WP_MINI_GAMES_TEXT_DOMAIN) ?></label>
			</th>
			<td valign="top" >
				<input type="text" id="default_mini_game_width" name="default_mini_game_width" value="<?php echo($options['default_mini_game_width']); ?>" />
			</td>
		</tr>
		<tr>
			<th valign="top" align="right"><label for="default_mini_game_height"><?php _e ('Default Height', WP_MINI_GAMES_TEXT_DOMAIN) ?></label>
			</th>
			<td valign="top" >
				<input type="text" id="default_mini_game_height" name="default_mini_game_height" value="<?php echo($options['default_mini_game_height']); ?>" />
			</td>
		</tr>			
		<tr>
			<th/>
			<td>
				<input class="button-primary" type="submit" name="update" value="<?php echo __('Update Options &raquo;', WP_MINI_GAMES_TEXT_DOMAIN)?>" />
			</td>
		</tr>
	</table>
</form>
</div>
