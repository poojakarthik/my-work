<?php

class DO_Exception_Validation extends Exception
{
	private $errors;
	
	private $objectName;
	
	/*
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 * 
	 * @param	string	$strObjectName				The identifying name of the object which is invalid
	 * @param	mixed	$mixErrors		[optional]	array of strings	: If there are multiple issues with the validity of the object
	 * 												string				: If there is only one issue with the validity of the object
	 * 												null (default)		: If you don't want to report on what is specifically invalid
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($strObjectName, $mixErrors=null)
	{
		
		if (is_array($mixErrors))
		{
			// The errors are defined as an array
			$this->errors = array_values($mixErrors);
		}
		elseif (is_string($mixErrors))
		{
			// Must be a string
			$this->errors = array($mixErrors);
		}
		else
		{
			// Fine.  I won't record any specific errors
			$this->errors = array();
		}
		
		$this->objectName = $strObjectName;

		// Prepare the message
		$intErrorCount = count($this->errors);
		if ($intErrorCount > 1)
		{
			// Multiple issues
			$strErrorClause = "\n\t". implode("\n\t", $this->errors);
		}
		elseif ($intErrorCount == 1)
		{
			// One issue
			$strErrorClause = " - {$this->errors[0]}";
		}
		else
		{
			// No particular issues mentioned
			$strErrorClause = "";
		}

		parent::__construct("{$this->objectName} is invalid{$strErrorClause}");
	}
	
	public function __get($strPropName)
	{
		switch ($strPropName)
		{
			case 'errors':
				return $this->errors;
				
			case 'objectName':
				return $this->objectName;
				
			default:
				trigger_error(__METHOD__ ." - Invalid or restricted data attribute ". __CLASS__ ."->{$strPropName}", E_USER_ERROR);
		}
	}
	
}

?>