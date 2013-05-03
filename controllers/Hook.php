<?php namespace UserReputation\Ctrl;

/*
 * (author) Sulaeman <me@sulaeman.com> @sulaeman
 */

include_once( __DIR__ . '/UserReputation.php' );

class Hook extends \UserReputation\Lib\Base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function filterGenerateRewriteRules($wp_rewrite)
	{
		$user_page_slug = get_option($this->getConfig('prefix').'page_slug');
		if (empty($user_page_slug))
			$user_page_slug = $this->getConfig('user_page_slug');

		$user_own_page_slug = get_option($this->getConfig('prefix').'own_page_slug');
		if (empty($user_own_page_slug))
			$user_own_page_slug = $this->getConfig('user_own_page_slug');
		
		$new_rules = array(
	        $user_page_slug.'/(.?.+?)?/?$' => 'index.php?'.$this->getConfig('prefix').'page=$matches[1]',
	        '('.$user_own_page_slug.')?/?$' => 'index.php?'.$this->getConfig('prefix').'own_page=$matches[1]'
	    );

	    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;

	    return $wp_rewrite->rules;
	}

	public function filterRewriteRulesArray($rules)
	{
		$user_page_slug = $this->getOption('page_slug');
		$user_own_page_slug = $this->getOption('own_page_slug');

		$new_rules = array(
	        $user_page_slug.'/(.?.+?)?/?$' => 'index.php?'.$this->getConfig('prefix').'page=$matches[1]',
	        '('.$user_own_page_slug.')?/?$' => 'index.php?'.$this->getConfig('prefix').'own_page=$matches[1]'
	    );
		
		return $new_rules + $rules;
	}

	public function filterQueryVars($vars)
	{
		array_push($vars, $this->getConfig('prefix').'page');
		array_push($vars, $this->getConfig('prefix').'own_page');
		
		return $vars;
	}

	public function paginationHistory()
	{
		$user_id = (isset($_POST['user'])) ? (int) $_POST['user'] : 0;
		$page = (isset($_POST['page'])) ? (int) $_POST['page'] : 1;
		$limit = (isset($_POST['limit'])) ? (int) $_POST['limit'] : $this->getOption('num_items_per_page');
		
		\UserReputation::getHistoryView($user_id, $limit, $page);
		die();
	}

	public function init()
	{
		if ( !session_id() )
		{
			session_start();
		}

		wp_register_style( 'user-reputation-style', $this->getUrl('asset').'css/user.reputation.css' );

		wp_register_script( 'purl-script', $this->getUrl('asset').'js/purl.js', array('jquery'), '2.2.1', true );
		wp_register_script( 'jquery-history-script', $this->getUrl('asset').'js/jquery.history.js', array('jquery'), '1.7.1', true );
		wp_register_script( 'user-reputation-script', $this->getUrl('asset').'js/user.reputation.js', array('jquery', 'jquery-history-script', 'purl-script'), '1.0.0', true );
	}

	public function adminInit()
	{
		include_once( $this->getPath('controller') . 'Setting.php' );

		new Setting();
	}

	public function adminMenu()
	{
		include_once( $this->getPath('controller') . 'Setting.php' );

		$setting = new Setting();

		$page = add_menu_page(__('User Reputation', $this->getConfig('txt_domain')),
			__('Reputation', $this->getConfig('txt_domain')),
			'manage_options',
			'user-reputation',
			array(&$setting, 'general'), 
			$this->getUrl('asset').'img/user_card_basic_blue.png', 79);

		add_submenu_page('user-reputation', 
			__('General Preferences', $this->getConfig('txt_domain')), 
			__('General', $this->getConfig('txt_domain')),
			'manage_options',
			'user-reputation',
			array(&$setting, 'general'));

		add_submenu_page('user-reputation', 
			__('Reputation Badges', $this->getConfig('txt_domain')), 
			__('Badges', $this->getConfig('txt_domain')),
			'manage_options',
			'user-reputation-badges',
			array(&$setting, 'badge'));
	}

	public function wpFooter()
	{
		?>
		<script type="text/javascript">
			var urAjaxEndpoint = "<?php echo admin_url('admin-ajax.php'); ?>";
			var urPrefix = "<?php echo $this->getConfig('prefix'); ?>";
			var urNumItemPerPage = "<?php echo $this->getOption('num_items_per_page'); ?>";
		</script>
		<?php
	}

	public function wpEnqueueScripts()
	{
		wp_enqueue_style( 'user-reputation-style' );
		wp_enqueue_script( 'user-reputation-script' );
	}

	public function templateRedirect()
	{
		$user_reputation = get_query_var($this->getConfig('prefix').'page');
		$user_own_reputation = get_query_var($this->getConfig('prefix').'own_page');

		if (!empty($user_reputation))
		{
			$theme_template_path = rtrim(get_template_directory(), '/').'/'.$this->getConfig('page_template').'.php';
			if (file_exists($theme_template_path))
			{
				include_once($theme_template_path);
			}
			else
			{
				include_once($this->getPath('view').$this->getConfig('page_template').'.php');
			}

			exit();
		}

		if (!empty($user_own_reputation))
		{
			$theme_template_path = rtrim(get_template_directory(), '/').'/'.$this->getConfig('own_page_template').'.php';
			if (file_exists($theme_template_path))
			{
				include_once($theme_template_path);
			}
			else
			{
				include_once($this->getPath('view').$this->getConfig('own_page_template').'.php');
			}

			exit();
		}	
	}
}
