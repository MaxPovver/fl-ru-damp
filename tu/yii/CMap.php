<?php

class CMap implements /*IteratorAggregate,*/ArrayAccess,Countable {
	private $_d=array();
	private $_r=false;
	public function __construct($data=null,$readOnly=false)
	{
		if($data!==null)
			$this->copyFrom($data);
		$this->setReadOnly($readOnly);
	}
	public function getReadOnly()
	{
		return $this->_r;
	}
	protected function setReadOnly($value)
	{
		$this->_r=$value;
	}
#	public function getIterator()
#	{
#		return new CMapIterator($this->_d);
#	}
	public function count()
	{
		return $this->getCount();
	}
	public function getCount()
	{
		return count($this->_d);
	}
	public function getKeys()
	{
		return array_keys($this->_d);
	}
	public function itemAt($key)
	{
		if(isset($this->_d[$key]))
			return $this->_d[$key];
		else
			return null;
	}
	public function add($key,$value)
	{
		if(!$this->_r)
		{
			if($key===null)
				$this->_d[]=$value;
			else
				$this->_d[$key]=$value;
		}
		else
			throw new Exception('The map is read only');
	}
	public function remove($key)
	{
		if(!$this->_r)
		{
			if(isset($this->_d[$key]))
			{
				$value=$this->_d[$key];
				unset($this->_d[$key]);
				return $value;
			}
			else
			{
				// it is possible the value is null, which is not detected by isset
				unset($this->_d[$key]);
				return null;
			}
		}
		else
			throw new Exception('The map is read only');
	}
	public function clear()
	{
		foreach(array_keys($this->_d) as $key)
			$this->remove($key);
	}
	public function contains($key)
	{
		return isset($this->_d[$key]) || array_key_exists($key,$this->_d);
	}
	public function toArray()
	{
		return $this->_d;
	}
	public function copyFrom($data)
	{
		if(is_array($data) || $data instanceof Traversable)
		{
			if($this->getCount()>0)
				$this->clear();
			if($data instanceof CMap)
				$data=$data->_d;
			foreach($data as $key=>$value)
				$this->add($key,$value);
		}
		elseif($data!==null)
			throw new Exception('Map data must be an array or an object implementing Traversable.');
	}
	public function mergeWith($data,$recursive=true)
	{
		if(is_array($data) || $data instanceof Traversable)
		{
			if($data instanceof CMap)
				$data=$data->_d;
			if($recursive)
			{
				if($data instanceof Traversable)
				{
					$d=array();
					foreach($data as $key=>$value)
						$d[$key]=$value;
					$this->_d=self::mergeArray($this->_d,$d);
				}
				else
					$this->_d=self::mergeArray($this->_d,$data);
			}
			else
			{
				foreach($data as $key=>$value)
					$this->add($key,$value);
			}
		}
		elseif($data!==null)
			throw new CException(Yii::t('yii','Map data must be an array or an object implementing Traversable.'));
	}
	public static function mergeArray($a,$b)
	{
		$args=func_get_args();
		$res=array_shift($args);
		while(!empty($args))
		{
			$next=array_shift($args);
			foreach($next as $k => $v)
			{
				if(is_integer($k))
					isset($res[$k]) ? $res[]=$v : $res[$k]=$v;
				elseif(is_array($v) && isset($res[$k]) && is_array($res[$k]))
					$res[$k]=self::mergeArray($res[$k],$v);
				else
					$res[$k]=$v;
			}
		}
		return $res;
	}
	public function offsetExists($offset)
	{
		return $this->contains($offset);
	}
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}
	public function offsetSet($offset,$item)
	{
		$this->add($offset,$item);
	}
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
}