<?php
class Email_Template_Logic
{
	private		$_oEmailTemplate	= null;
	protected	$_aVariables		= array();
	
	public function __construct($oEmailTemplate)
	{
		$this->_oEmailTemplate	= $oEmailTemplate;
	}
	
	// getHTMLContent: Return the 'ready-to-send' HTML content for the given array of data
	public function getHTMLContent($aData)
	{
		try
		{
			$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$oParser	= new Email_HTML_Document($oDetails->email_html);
			$sHTML		= $oParser->getHTML(true);
			//$sHTML	= self::processHTML($oDetails->email_html);
			foreach ($this->_aVariables as $sObject => $aProperties)
			{
				if (isset($aData[$sObject]))
				{
					$aDataProperties	= $aData[$sObject];
					foreach ($aProperties as $sProperty)
					{
						$mValue	= $aDataProperties[$sProperty];
						if (isset($aDataProperties[$sProperty]))
						{
							// Replace all references with value
							$sHTML	= preg_replace("/\<variable(\s+)object=['\"]{$sObject}['\"](\s+)field=['\"]{$sProperty}['\"](\s?)\/\>/", "{$mValue}", $sHTML);
						}
					}
				}
			}
			return $sHTML;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get HTML content. ".$oException->getMessage());
		}
	}

	// getTextContent: Return the 'ready-to-send' Text content for the given array of data
	public function getTextContent($aData)
	{
		try
		{
			$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText		= $this->_replaceVariablesInText($oDetails->email_text, $aData);
			return $sText;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get text content. ".$oException->getMessage());
		}
	}
	
	// getSubjectContent: Return the 'ready-to-send' Subject content for the given array of data
	public function getSubjectContent($aData)
	{
		try
		{
			$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText		= $this->_replaceVariablesInText($oDetails->email_subject, $aData);
			return $sText;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get subject content. ".$oException->getMessage());
		}
	}
	
	protected function _replaceVariablesInText($sText, $aData)
	{
		foreach ($this->_aVariables as $sObject => $aProperties)
		{
			if (isset($aData[$sObject]))
			{
				$aDataProperties	= $aData[$sObject];
				foreach ($aProperties as $sProperty)
				{
					$mValue	= $aDataProperties[$sProperty];
					if (isset($aDataProperties[$sProperty]))
					{
						// Replace all references with value
						$sText	= preg_replace("/\\{{$sObject}.{$sProperty}\\}/", "{$mValue}", $sText);
					}
				}
			}
		}
		return $sText;
	}
	
	protected function _generateEmail($aData, Email_Flex $mEmail=null)
	{
		$oEmail	= ($mEmail !== null ? $mEmail : new Email_Flex());
		
		$sSubject	= $this->getSubjectContent($aData);
		$sHTML		= $this->getHTMLContent($aData);
		$sText		= $this->getTextContent($aData);
		
		$oEmail->setBodyText($sText);
		if ($sHTML && $sHTML !== '')
		{
			$oEmail->setBodyHtml($sHTML);
		}
		$oEmail->setSubject($sSubject);
		
		return $oEmail;
	}
	
	// getInstance: Returns the appropriate sub class for the email template type given
	public static function getInstance($iEmailTemplateType, $iCustomerGroup)
	{
		try
		{
			$oEmailTemplateType	= Email_Template_Type::getForId($iEmailTemplateType);
			if (!$oEmailTemplateType)
			{
				// Couldn't find the template
				throw new Exception("Invalid email template type id supplied.");
			}
			
			if (!class_exists($oEmailTemplateType->class_name))
			{
				// Bad class name in database
				throw new Exception("Invalid class_name value in email_template_type {$iEmailTemplateType}, class_name='{$oEmailTemplateType->class_name}'");
			}
			
			// All good, return the instance
			$oEmailTemplate	= Email_Template::getForCustomerGroupAndType($iCustomerGroup, $iEmailTemplateType);
			return new $oEmailTemplateType->class_name($oEmailTemplate);
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get Email_HTML_Template instance. ".$oException->getMessage());
		}
	}
}