<?php
	
	class ServiceType extends dataEnumerative
	{
		
		private $_UNKNOWN;
		
		private $_ADSL;
		private $_MOBLE;
		private $_LAND_LINE;
		private $_INBOUND;
		
		function __construct ($strServiceTypeName, $intServiceType)
		{
			parent::__construct ($strServiceTypeName);
			
			$this->_ADSL		= $this->Push (new dataString ($this->tagName (), "ADSL Connection"));
			$this->_MOBILE		= $this->Push (new dataString ($this->tagName (), "Mobile Telephone"));
			$this->_LAND_LINE	= $this->Push (new dataString ($this->tagName (), "Land Line Telephone"));
			$this->_INBOUND		= $this->Push (new dataString ($this->tagName (), "Inbound Call Number"));
			$this->_UNKNOWN		= $this->Push (new dataString ($this->tagName (), "Unspecified"));
			
			switch ($intServiceType)
			{
				case SERVICE_TYPE_ADSL:			$this->Select ($this->_ADSL);		break;
				case SERVICE_TYPE_MOBILE:		$this->Select ($this->_MOBILE);		break;
				case SERVICE_TYPE_LAND_LINE:	$this->Select ($this->_LAND_LINE);	break;
				case SERVICE_TYPE_INBOUND:		$this->Select ($this->_INBOUND);	break;
				default:						$this->Select ($this->_UNKNOWN);	break;
			}
		}
	}
	
?>
