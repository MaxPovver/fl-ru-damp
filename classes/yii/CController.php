<?php

include_once(dirname(__FILE__).'/CBaseController.php');

abstract class CController extends CBaseController {

	private $_id;

	/** @var CModule */
	private $_module;
	private $_clips;

	public $layout;

	public $renderOutput;

	public function __construct($id,$module=null) {
		$this->_id=$id;
		$this->_module=$module;
	}

	public function init() {
	}

	public function beforeAction($action) 
        {
            return TRUE;
	}

	public function afterAction($action) 
        {
            //todo
	}

	public function run($action = 'index', $arguments = null) {
		$methodName = 'action'.ucfirst($action);
		if (method_exists($this, $methodName)) {

			if (false!==$this->beforeAction($action)) {
				$methodArguments = array_slice(func_get_args(), 1);
				call_user_func_array(array($this,$methodName), $methodArguments);
				$this->afterAction($action);
			}
		} else {

			$this->missingAction($action);
		}
	}

        
	public function missingAction($action) 
        {
                //@todo: инклуд 404 проходит с ошибкой в главном шаблоне
                //так как там есть вызов users::GetField()
                //который в свою очередь использует get_class($this);
                //и тот получает не корректное имя класса - почему то контроллера
                //Посему используем пока редирект на 404
            
                //include ABS_PATH . '/404.php'; 
                header('Location: /404.php');
                exit;
	}

        
	public function getId()
	{
		return $this->_id;
	}

	public function getUniqueId()
	{
		return (($module=$this->getModule())!==null)
				? $module->getId().'/'.$this->_id
				: $this->_id;
	}

	public function getModule()
	{
		return $this->_module;
	}

	public function getViewPath()
	{
		return 'views'.DIRECTORY_SEPARATOR.$this->getId(); // /path/to/app/views/controllerId
	}


	public function getViewFile($viewName)
	{
		$baseViewPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->getViewPath();
		$moduleViewPath = null;
		if(($module=$this->getModule())!==null) {
			$moduleViewPath = $module->getBasePath().DIRECTORY_SEPARATOR.$this->getViewPath(); // /path/to/app/moduleId/views
		}
		return $this->resolveViewFile($viewName,$baseViewPath,$moduleViewPath);
	}

	public function getLayoutFile($layoutName)
	{
		if ('//layouts/' == substr($layoutName, 0, 10)) {
			$layoutName = substr($layoutName, 10);
		}

		$baseViewPath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'layouts';
		$moduleViewPath = null;
		if(($module=$this->getModule())!==null) {
			$moduleViewPath = $module->getBasePath().DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'layouts'; // /path/to/app/moduleId/views/layouts
		}
		return $this->resolveViewFile($layoutName,$baseViewPath,$moduleViewPath);
	}

	public function renderPartial($view, $data=null, $return=false) {
		if(($viewFile=$this->getViewFile($view))!==false) {
			$output=$this->renderFile($viewFile,$data,true);
			if(($layoutFile=$this->getLayoutFile($this->layout))!==false) {
				$output = $this->renderFile($layoutFile, array('content'=>$output), true);
			}

			if($return)
				return $output;
			else
				echo $output;
		}
		else
			throw new Exception(sprintf('%s cannot find the requested view "%s"', get_class($this), $view));
	}

	public function render($view, $data=null, $return=false) {

		$this->renderOutput = $this->renderPartial($view,$data,true);
		if ($return) {
			return $this->renderOutput;
		}
	}

	public function resolveViewFile($viewName,$baseViewPath,$moduleViewPath=null)
	{
		#echo "resolveViewFile($viewName,$baseViewPath,$moduleViewPath)<br/>";
		if(empty($viewName))
			return false;

		if($moduleViewPath===null)
			$moduleViewPath=$baseViewPath;

		$extension='.php';

		$viewFile=$baseViewPath.DIRECTORY_SEPARATOR.$viewName.$extension;
		if(is_file($viewFile)) {
			return $viewFile;
		} else {
			$viewFile=$moduleViewPath.DIRECTORY_SEPARATOR.$viewName.$extension;
			if(is_file($viewFile)) {
				return $viewFile;
			}
		}
		#echo "No file $viewFile<br/>";
		return false;
	}

	public function getClips() {
		if($this->_clips!==null)
			return $this->_clips;
		else
			return $this->_clips = new CMap;
	}

	public function renderClip($name, $params=array(), $return=false) {
		$text = isset($this->_clips[$name]) ? strtr($this->_clips[$name], $params) : '';
		if($return)
			return $text;
		else
			echo $text;
	}
        
        
        public function redirect($url, $terminate = true, $statusCode = 302) 
        {
            header('Location: ' . $url, true, $statusCode);
            if ($terminate) exit;
        }
}