<?php namespace UserReputation\Lib;

class Loader
{
	private $_paths = array();
	private $_sul_classes = array();
	private $_sul_loaded_files  = array();
	private $_sul_varmap = array();
	private $_sul_cached_vars = array();

	public function __construct($paths)
	{
		$this->_paths = $paths;
	}

	public function config()
	{

	}

	public function controller($controller, $object_name = null)
	{
		if (empty($controller))
		{
			return false;
		}

		return $this->loadClass($this->_paths['controller'], $controller, null, $object_name);
	}

	public function library($library, $params = null, $object_name = null)
	{
		if (empty($library))
		{
			return false;
		}

		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->loadClass($this->_paths['library'], $class, $params, $object_name);
			}
		}
		else
		{
			$this->loadClass($this->_paths['library'], $library, $params, $object_name);
		}
	}

	public function view()
	{
		
	}

	public function load()
	{

	}
}
