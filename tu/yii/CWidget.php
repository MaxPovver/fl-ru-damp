<?php

class CWidget extends CBaseController
{
	private static $_viewPaths;
	private static $_counter=0;
	private $_owner;

	public function __construct($owner=null)
	{
		$this->_owner=$owner;
	}

	public function getOwner()
	{
		return $this->_owner;
	}

	public function getController()
	{
		return $this->_owner;
	}

	public function init()
	{
	}

	public function run()
	{
	}

	public function getViewPath()
	{
		$className=get_class($this);
		if(isset(self::$_viewPaths[$className]))
			return self::$_viewPaths[$className];
		else
		{
			$class=new ReflectionClass($className);
			return self::$_viewPaths[$className]=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'views';
		}
	}

	public function getViewFile($viewName)
	{
		$extension='.php';
		$viewFile=$this->getViewPath().DIRECTORY_SEPARATOR.$viewName.$extension;

		if(is_file($viewFile)) {
			return $viewFile;
		}
		#echo 'No file "', $viewFile, '" <br/>';
		return false;
	}

	public function render($view,$data=null,$return=false)
	{
		if(($viewFile=$this->getViewFile($view))!==false)
			return $this->renderFile($viewFile,$data,$return);
		else
			throw new Exception(sprintf('%s cannot find the view "%s" [ path %s ]', get_class($this), $view, $viewFile));
	}
}