<?php namespace UserReputation\Lib;

include_once( __DIR__ . '/Exceptions.php' );
include_once( __DIR__ . '/Utility.php' );

class Base
{
	private static $_folders = array(
		'config'     => 'configs',
		'controller' => 'controllers',
		'library'    => 'libraries',
		'model'      => 'models',
		'view'       => 'views',
		'asset'      => 'assets'
	);

	private $_paths = array();
	private $_configs = null;

	public function __construct()
	{
		foreach(self::$_folders as $key => $folder)
		{
			$this->_paths[$key] = array(
				'folder' => $folder,
				'path'   => dirname( __DIR__ ) . '/'. $folder . '/',
				'url'    => rtrim( plugins_url( $folder, dirname(__FILE__) ), '/' ) . '/'
			);
		}
	}

	public function getOption($name)
	{
		$value = get_option($this->getConfig('prefix').$name);
		if (empty($value))
			$value = $this->getConfig($name);

		return $value;
	}

	public function getFolder($name)
	{
		if (isset($this->_paths[$name]))
			return $this->_paths[$name]['folder'];

		return '';
	}

	public function getPath($name)
	{
		if (isset($this->_paths[$name]))
			return $this->_paths[$name]['path'];

		return '';
	}

	public function getUrl($name)
	{
		if (isset($this->_paths[$name]))
			return $this->_paths[$name]['url'];

		return '';
	}

	public function getConfig($name)
	{
		if ($this->_configs === null)
		{
			include( $this->_paths['config']['path'] . '/configs.php' );

			$this->_configs = $config;

			unset($config);
		}
		
		return (isset($this->_configs[$name])) ? $this->_configs[$name] : '';
	}

	public function getImage($file, $return_path = false)
	{
		return self::getAsset('img/'.$file, $return_path);
	}

	public function getCss($file, $return_path = false)
	{
		return self::getAsset('css/'.$file, $return_path);
	}

	public function getScript($file, $return_path = false)
	{
		return self::getAsset('js/'.$file, $return_path);
	}

	public function getAsset($file, $return_path = false)
	{
		if ($return_path)
			return $this->_paths['asset']['path'].$file;

		return $this->_paths['asset']['url'].$file;
	}

	public function loadView($file, $data = array())
	{
		extract($data);

		include($this->getPath('view').$file);
	}
}
