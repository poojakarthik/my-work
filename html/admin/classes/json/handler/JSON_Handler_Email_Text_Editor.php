<?php

class JSON_Handler_Email_Text_Editor extends JSON_Handler
{
	protected	$_JSONDebug	= '';


	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function getTemplates($bCountOnly=false, $iLimit=null, $iOffset=null, $sSortDirection='DESC')
	{
		$aTemplates = Email_Template_Type::getForAllCustomerGroups($bCountOnly, $iLimit, $iOffset, $sSortDirection);
		return	array(
						'Success'		=> true,
						'aRecords'		=> $aTemplates,
						'iRecordCount'	=> count($aTemplates)
					);
	}


	public function getTemplateDetails($iTemplateId)
	{
		$oDetails = Email_Template_Details::getCurrentDetailsForTemplateId($iTemplateId);
		return	array(
						'bSuccess'						=> true,
						'aTemplateDetails'		=> $oDetails->toArray()
					);
	}

	public function save($aTemplateDetails)
	{
		$aTemplateDetails = is_array($aTemplateDetails)?$aTemplateDetails:(array)$aTemplateDetails;
		$oHTML = new Email_HTML_Document($aTemplateDetails['email_html']);
		$aTemplateDetails['id'] = null;
		$aTemplateDetails['created_timestamp'] = null;
		$aTemplateDetails['created_employee_id'] = Flex::getUserId();
		$aTemplateDetails['email_html']	= $oHTML->getHTML();
		$aTemplateDetails['effective_datetime'] = Data_Source_Time::currentTimestamp();
		$oDetails = new Email_Template_Details($aTemplateDetails);
		$oDetails->save();

		return	array(
						'Success'		=> true,
						'oTemplateDetails'		=> $oDetails->toArray()
					);
	}

	public function toText($sHTML)
	{
		$oEmail = new Email_HTML_Document($sHTML);

		return	array(
						'Success'		=> true,
						'text'		=> implode("",$oEmail->getText())
					);
	}

	public function processHTML($sHTML)
	{






		$oEmail = new Email_HTML_Document($sHTML);
		return	array(
						'Success'		=> true,
						'html'		=> $oEmail->getHTML(true)
					);
	}

	public function getVariables()
	{
		$aVars = Email_HTML_Document::getVariables();
		return	array(
						'Success'		=> true,
						'variables'		=> $aVars
					);
	}



	//this does the job, but it looks like using $node->parentNode->removeChild($node) works just as well, and much simpler!
	public function removeNode($oNodeToRemove, $parentNode = null)
	{
		$parentNode = $parentNode ==null?$this->xml->documentElement:$parentNode;
		try
		{

			$x = $parentNode->tagName;
			if ($parentNode->removeChild($oNodeToRemove))
			{
				return true;
			}
			else
			{
				throw new Exception();
			}
		}
		catch (Exception $e)
		{
			$x = $parentNode->childNodes;
			if ($x!=null)
			{
				foreach ($x as $node)
				{
					if ($this->removeNode($oNodeToRemove,$node ))
					{
						return true;
					}
				}
			}
		}

		return false;

	}


}



?>