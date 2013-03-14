<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e('General Preferences', $this->getConfig('txt_domain')); ?></h2>
	
	<?php if ($updated) : ?>
	<div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php _e('Settings saved.'); ?></strong></p></div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php settings_fields( $this->getConfig('prefix').'general' ); ?>
		<?php do_settings_sections( 'user-reputation' ); ?>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>" />
		</p>
	</form>
</div>