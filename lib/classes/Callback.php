<?php
final class Callback
{
	private	$_mContext;
	private	$_sFunction;
	private	$_aParameters	= array();
	
	public function __construct($sFunction, $mContext=null, array $aParameters=array())
	{
		$this->_sFunction	= $sFunction;
		
		if (is_object($mContext) || (is_string($mContext) && class_exists($mContext)))
		{
			$this->_mContext	= $mContext;
		}
		
		$this->_aParameters	= $aParameters;
	}
	
	public function invoke()
	{
		$aArgs		= (func_num_args()) ? func_get_args() : array();
		return $this->invokeArray($aArgs);
	}
	
	public function invokeArray($aArgs)
	{
		$mCallback	= (isset($this->_mContext)) ? array($this->_mContext, $this->_sFunction) : $this->_sFunction;
		return call_user_func_array($mCallback, array_merge($this->_aParameters, $aArgs));
	}
	
	public function curry()
	{
		$aArgs		= (func_num_args()) ? func_get_args() : array();
		return $this->curryArray($aArgs);
	}
	
	public function curryArray($aArgs)
	{
		return new self($this->_sFunction, $this->_mContext, array_merge($this->_aParameters, $aArgs));
	}
	
	static public function create($sFunction, $mContext=null, array $aParameters=array())
	{
		return new self($sFunction, $mContext, $aParameters);
	}
}
?>