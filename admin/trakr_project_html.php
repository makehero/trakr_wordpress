<h2><?php _e( 'Trakr configuration', $this->plugin_name ); ?></h2>
<div class="nds_add_user_meta_form">
<form action="<?php print esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="trakr_settings_form" >
	<input type="hidden" name="action" value="trakr_project_form_response">
	<input type="hidden" name="trakr_settings_form_nonce" value="<?php print $wp_nonce; ?>" />
	<div>
		<br />
		<label for="<?php echo $this->plugin_name; ?>-api_token"> <?php _e('API token', $this->plugin_name); ?> </label><br />
		<input required id="<?php echo $this->plugin_name; ?>-api_token" type="text" name="<?php print $this->plugin_name . '_settings'; ?>[api_token]" value="<?php print $default_token; ?>" <?php if(!empty($trakr_project)): ?>disabled="disabled"<?php endif; ?> />
		<p class="description">Your API token, you can find this information under your user profile in Trakr. If you do not have a Trakr user account, sign up at <a href="http://www.trakr.tech" target="_blank">trakr.tech</a> for free</p>
		<br />
		<hr />
		<fieldset>
			<legend>Linked project</legend>
			<ul>
			<?php foreach($trakr_project as $label => $value): ?>
				<li><strong><?php print $label; ?></strong>:<?php print $value;?></li>
			<?php endforeach; ?>
			</ul>
			<?php if (empty($trakr_project)): ?>
				<input type="submit" name="link_project" id="link_project" class="button button-secondary" value="Link project">
			<?php else: ?>
				<input type="submit" name="delete_project" id="delete_project" class="button button-secondary" value="Delete project">
			<?php endif; ?>
		</fieldset>
	</div>
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save configuration"></p>
</form>
<br/><br/>
<div id="trakr_form_feedback"></div>
<br/><br/>
</div>
