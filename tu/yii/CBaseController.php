<?php

class CBaseController {


	public function renderFile($viewFile,$data=null,$return=false) {
		return $this->renderInternal($viewFile,$data,$return);
	}

	public function renderInternal($_viewFile_,$_data_=null,$_return_=false) {
		// we use special variable names here to avoid conflict when extracting data
		if(is_array($_data_))
			extract($_data_,EXTR_PREFIX_SAME,'data');
		else
			$data=$_data_;
		if($_return_)
		{
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		}
		else
			require($_viewFile_);
	}

	public function createWidget($controller, $className, $properties=array()) {
		/** @var $widget CWidget */
		$widget = new $className($controller);
		foreach($properties as $name=>$value) {
			if (property_exists($widget, $name)) {
				$widget->$name=$value;
			}
		}
		$widget->init();
		return $widget;
	}

	public function widget($className, $properties=array(), $captureOutput=false) {
		if($captureOutput) {
			ob_start();
			ob_implicit_flush(false);
			$widget = $this->createWidget($this, $className, $properties);
			$widget->run();
			return ob_get_clean();
		} else {
			$widget = $this->createWidget($this, $className, $properties);
			$widget->run();
			return $widget;
		}
	}
}