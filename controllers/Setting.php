<?php namespace UserReputation\Ctrl;

include_once( dirname(__DIR__) . '/models/Reputation.php' );

use \UserReputation\Exception\SysException;
use \UserReputation\Exception\DBException;
use \UserReputation\Model\Reputation as ReputationModel;

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
			'',
			array($this, 'generalSection'),
			'user-reputation');
	 	
	 	// Number items per page field
	 	add_settings_field($this->getConfig('prefix').'num_items_per_page',
			__('Number items per page', $this->getConfig('txt_domain')),
			array($this, 'generalNumItemsPerPageField'),
			'user-reputation',
			$this->getConfig('prefix').'general');

	 	// Users reputation page slug
	 	add_settings_field($this->getConfig('prefix').'page_slug',
			__('User reputation page slug', $this->getConfig('txt_domain')),
			array($this, 'generalPageSlugField'),
			'user-reputation',
			$this->getConfig('prefix').'general');

	 	// User own reputation page slug
	 	add_settings_field($this->getConfig('prefix').'own_page_slug',
			__('"my reputation" page slug', $this->getConfig('txt_domain')),
			array($this, 'generalOwnPageSlugField'),
			'user-reputation',
			$this->getConfig('prefix').'general');
	 	
	 	// Register our setting so that $_POST handling is done for us
	 	register_setting($this->getConfig('prefix').'general', $this->getConfig('prefix').'num_items_per_page');
	 	register_setting($this->getConfig('prefix').'general', $this->getConfig('prefix').'page_slug');
	 	register_setting($this->getConfig('prefix').'general', $this->getConfig('prefix').'own_page_slug');
	}

	public function generalSection()
	{
		echo '';
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

		echo '<strong>'.rtrim(get_bloginfo('url'), '/').'/</strong>'.$this->_form->text($this->getConfig('prefix').'page_slug', $value).'<strong>/%user-nickname%</strong>';
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

		echo '<strong>'.rtrim(get_bloginfo('url'), '/').'/</strong>'.$this->_form->text($this->getConfig('prefix').'own_page_slug', $value);
	}

	public function general()
	{
		$updated = (isset($_GET['settings-updated'])) ? (bool) $_GET['settings-updated'] : false;

		$this->loadView('admin/general.php', array(
			'updated' => $updated
		));
	}

	public function badge()
	{
		$edit = (isset($_GET['edit'])) ? (int) $_GET['edit'] : 0;
		$delete = (isset($_GET['delete'])) ? (int) $_GET['delete'] : 0;

		if ($_POST)
		{
			$nonce = (isset($_POST['_wpnonce'])) ? $_POST['_wpnonce'] : '';
			if (wp_verify_nonce( $nonce, $this->getConfig('prefix').'edit-badge' ))
			{
				$title 		  = (isset($_POST['title'])) ? $_POST['title'] : '';
				$type  		  = (isset($_POST['type'])) ? $_POST['type'] : '';
				$icon  		  = (isset($_POST['icon'])) ? $_POST['icon'] : '';
				$description  = (isset($_POST['description'])) ? $_POST['description'] : '';

				$data = array(
					'title' 	   => $title,
					'type'  	   => $type,
					'icon'  	   => $icon,
					'description'  => $description
				);

				$badge_id = $edit;

				if (empty($badge_id))
				{
					try {
						$badge_id = ReputationModel::addBadge($data);
					} catch (DBException $e) {
						error_log($e->getMessage());
					}
				}
				else
				{
					try {
						$badge_id = ReputationModel::updateBadge($badge_id, $data);
					} catch (DBException $e) {
						error_log($e->getMessage());
					}
				}

				if (!empty($badge_id))
				{
					?>
					<script type="text/javascript">
						location.href = "<?php echo add_query_arg('edit', $badge_id, \UserReputation\Lib\Utility::currentUrl()); ?>";
					</script>
					<?php
				}
			}
		}

		if (!empty($delete))
		{
			$nonce = (isset($_GET['nonce'])) ? $_GET['nonce'] : '';
			if (wp_verify_nonce( $nonce, $this->getConfig('prefix').'delete-badge' ))
			{
				try {
					ReputationModel::deleteBadge($delete);
				} catch (DBException $e) {
					error_log($e->getMessage());
				}

				?>
				<script type="text/javascript">
					location.href = "<?php echo remove_query_arg(array('delete', 'nonce'), \UserReputation\Lib\Utility::currentUrl()); ?>";
				</script>
				<?php
			}
		}

		wp_enqueue_media();

		$badge = $this->defBadge();

		if (!empty($edit))
		{
			$badge = ReputationModel::getBadge($edit);
		}

		if (empty($badge))
			$badge = $this->defBadge();

		$badges = ReputationModel::getBadge();
		$badge_types = $this->getConfig('badge_types');
		
		$this->loadView('admin/badge.php', array(
			'badge_types'   => $badge_types,
			'badges'        => $badges,
			'current_badge' => $badge
		));
	}

	private function defBadge()
	{
		$badge = new \stdClass;

		$badge->id = 0;
		$badge->title = '';
		$badge->type = 'bronze';
		$badge->icon = '';
		$badge->description = '';

		return $badge;
	}
}
