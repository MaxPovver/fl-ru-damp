<?php

class CModule {

	private $_id;
	private $_basePath;
	private $_modulePath;

	public function __construct($id) {
		$this->_id=$id;
	}

	public function getId() {
		return $this->_id;
	}

	public function setId($id) {
		$this->_id=$id;
	}

	public function getBasePath() {
		if($this->_basePath===null)
		{
			$class=new ReflectionClass(get_class($this));
			$this->_basePath=dirname($class->getFileName());
		}
		return $this->_basePath;
	}

	public function setBasePath($path) {
		if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath))
			throw new Exception(sprintf('Base path "%s" is not a valid directory', $path));
	}

	public function getModulePath() {
		if($this->_modulePath!==null)
			return $this->_modulePath;
		else
			return $this->_modulePath=$this->getBasePath().DIRECTORY_SEPARATOR.'modules';
	}

	public function setModulePath($value) {
		if(($this->_modulePath=realpath($value))===false || !is_dir($this->_modulePath))
			throw new Exception(sprintf('The module path "%s" is not a valid directory', $value));
	}
}
