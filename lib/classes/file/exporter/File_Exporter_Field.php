<?php
class File_Exporter_Field
{
	protected	$_sPaddingString	= ' ';
	protected	$_iMinimumLength	= 0;
	protected	$_iMaximumLength	= null;
	protected	$_iPaddingStyle		= STR_PAD_RIGHT;
	protected	$_sValidationRegex	= null;
	protected	$_bTruncate			= false;
	protected	$_mDefaultValue		= null;
	
	public function process($mValue)
	{
		return $this->_validate($this->_pad($this->_truncate($mValue)));
	}
	
	protected function _truncate($mValue)
	{
		if ($this->_iMaximumLength && strlen($mValue) > $this->_iMaximumLength)
		{
			if ($this->_bTruncate)
			{
				return substr($mValue, 0, $this->_iMaximumLength);
			}
			else
			{
				throw new Exception("Value '{$mValue}' exceeds maximum field length of {$this->_iMaximumLength}");
			}
		}
		return $mValue;
	}
	
	protected function _pad($mValue)
	{
		return str_pad($mValue, $this->_sPaddingString, $this->_iMinimumLength, $this->_iPaddingStyle);
	}
	
	protected function _validate($mValue)
	{
		if ($this->_sValidationRegex)
		{
			if (!preg_match($this->_sValidationRegex))
			{
				throw new Exception("Value '{$mValue}' doesn't match validation regex '{$this->_sValidationRegex}'");
			}
		}
		return $mValue;
	}
	
	public function setPaddingString($sPaddingString)
	{
		$this->_sPaddingString	= (strlen((string)$sPaddingString)) ? $sPaddingString : ' ';
		return $this;
	}
	
	public function setPaddingStyle($iPaddingStyle)
	{
		switch ($iPaddingStyle)
		{
			case STR_PAD_LEFT:
			case STR_PAD_RIGHT:
			case STR_PAD_BOTH:
				$this->_iPaddingStyle	= $iPaddingStyle;
				break;
			
			default:
				throw new Exception("Invalid Padding Style '{$iPaddingStyle}' provided");
				break;
		}
		return $this;
	}
	
	public function setMinimumLength($iMinimumLength)
	{
		$iMinimumLength	= (int)$iMinimumLength;
		$this->_iMinimumLength	= ($iMinimumLength > 0) ? $iMinimumLength : 0;
		return $this;
	}
	
	public function setMaximumLength($iMaximumLength, $bTruncate=false)
	{
		$iMaximumLength	= (int)$iMaximumLength;
		$this->_iMaximumLength	= ($iMaximumLength > 0) ? $iMaximumLength : null;
		
		$this->_bTruncate	= !!$bTruncate;
		return $this;
	}
	
	public function setValidationRegex($sValidationRegex)
	{
		$this->_sValidationRegex	= (is_string($sValidationRegex)) ? $sValidationRegex : null;
		return $this;
	}
	
	public function setDefaultValue($mValue)
	{
		$this->_mDefaultValue	= $mValue;
		return $this;
	}
	
	public function getDefaultValue()
	{
		return $this->_mDefaultValue;
	}
	
	public static function factory()
	{
		return new self();
	}
}
?>