<?php
class CURL
{
	protected	$_rSession;
	protected	$_aTransferInfo;
	protected	$_bExceptionOnExecuteFailure;
	
	public function __construct()
	{
		$this->_rSession	= curl_init();
	}
	
	public function execute($sURL=null)
	{
		// Set URL
		if ($sURL)
		{
			$this->URL	= $sURL;
		}
		
		// Execute
		if (($mResult = curl_exec($this->_rSession)) === false)
		{
			throw new Exception("cURL Execution Error (".curl_errno($this->_rSession)."): ".curl_error($this->_rSession));
		}
		
		// Prefetch Transfer Info
		$this->_aTransferInfo	= curl_getinfo($this->_rSession);
		
		return $mResult;
	}
	
	public function getTransferInfo($iOption=null)
	{
		return curl_getinfo($this->_rSession, $iOption);
	}
	
	public function setOption($iOption, $mValue)
	{
		return curl_setopt($this->_rSession, $iOption, $mValue);
	}
	
	public function setOptions($aOptions)
	{
		foreach ($aOptions as $iOption=>$mValue)
		{
			$this->setOption($iOption, $mValue);
		}
	}
	
	public function __destruct()
	{
		curl_close($this->_rSession);
	}
	
	// Sets Options (curl_setopt)
	public function __set($sProperty, $mValue)
	{
		if (defined('CURLOPT_'.strtoupper($sProperty)))
		{
			$this->setOption(constant('CURLOPT_'.strtoupper($sProperty)), $mValue);
		}
	}
	
	// Gets Transfer Info (curl_getinfo)
	public function __get($sProperty)
	{
		if (isset($this->_aTransferInfo[strtolower($sProperty)]))
		{
			return $this->_aTransferInfo[strtolower($sProperty)];
		}
		elseif (defined('CURLINFO_'.strtoupper($sProperty)))
		{
			return $this->getTransferInfo(constant('CURLINFO_'.strtoupper($sProperty)));
		}
	}
	
	// Aliases (to match curl_* functions)
	public function exec(){return $this->execute();}
	
	public function setopt($iOption, $mValue){return $this->setOption($iOption, $mValue);}
	
	public function setopt_array($aOptions){return $this->setOptions($aOptions);}
	
	public function getinfo($iOption=null){return $this->getTransferInfo($iOption);}
}
?>