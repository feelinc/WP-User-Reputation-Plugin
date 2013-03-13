<?php namespace UserReputation\Ctrl;

class Setting extends \UserReputation\Lib\Base
{
	private $_form = null;

	public function __construct()
	{
		parent::__construct();

		include_once( $this->getPath('library') . 'Form.php' );

		$this->_form = new \UserReputation\Lib\Form();

		// Add the section to general settings so we can add our
	 	// fields to it
	 	add_settings_section($this->getConfig('prefix').'general',
			__('User Reputation', $this->getConfig('txt_domain')),
			array($this, 'generalSection'),
			'general');
	 	
	 	// Number items per page field
	 	add_settings_field($this->getConfig('prefix').'num_items_per_page',
			__('Number items per page', $this->getConfig('txt_domain')),
			array($this, 'generalNumItemsPerPageField'),
			'general',
			$this->getConfig('prefix').'general');

	 	// Users reputation page slug
	 	add_settings_field($this->getConfig('prefix').'page_slug',
			__('User reputation page slug', $this->getConfig('txt_domain')),
			array($this, 'generalPageSlugField'),
			'general',
			$this->getConfig('prefix').'general');

	 	// User own reputation page slug
	 	add_settings_field($this->getConfig('prefix').'own_page_slug',
			__('"my reputation" page slug', $this->getConfig('txt_domain')),
			array($this, 'generalOwnPageSlugField'),
			'general',
			$this->getConfig('prefix').'general');
	 	
	 	// Register our setting so that $_POST handling is done for us
	 	register_setting('general', $this->getConfig('prefix').'num_items_per_page');
	 	register_setting('general', $this->getConfig('prefix').'page_slug');
	 	register_setting('general', $this->getConfig('prefix').'own_page_slug');
	}

	public function generalSection()
	{
		echo '<em>'.__('User Reputation General Preferences', $this->getConfig('txt_domain')).'</em>';
	}

	public function generalNumItemsPerPageField()
	{
		echo $this->_form->text($this->getConfig('prefix').'num_items_per_page', $this->getOption('num_items_per_page'));
	}

	public function generalPageSlugField()
	{
		$value = $this->getOption('page_slug');

		// try to flush the rewrite rules
		$rules = get_option( 'rewrite_rules' );
		if ( ! isset( $rules['('.$value.')/([a-zA-Z0-9-])$'] ) )
		{
			global $wp_rewrite;
	   		$wp_rewrite->flush_rules();
	   	}
	   	unset($rules);

		echo '<strong>'.rtrim(get_bloginfo('siteurl'), '/').'/</strong>'.$this->_form->text($this->getConfig('prefix').'page_slug', $value).'<strong>/%user-nickname%</strong>';
	}

	public function generalOwnPageSlugField()
	{
		$value = $this->getOption('own_page_slug');

		// try to flush the rewrite rules
		$rules = get_option( 'rewrite_rules' );
		if ( ! isset( $rules['('.$value.')/([a-zA-Z0-9-])$'] ) )
		{
			global $wp_rewrite;
	   		$wp_rewrite->flush_rules();
	   	}
	   	unset($rules);

		echo '<strong>'.rtrim(get_bloginfo('siteurl'), '/').'/</strong>'.$this->_form->text($this->getConfig('prefix').'own_page_slug', $value);
	}
}
