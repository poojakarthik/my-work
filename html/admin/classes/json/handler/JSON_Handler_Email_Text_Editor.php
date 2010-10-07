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

	public function save($aTemplateDetails, $bConfirm = false, $iSaveMode)
	{
		$aTemplateDetails = is_array($aTemplateDetails)?$aTemplateDetails:(array)$aTemplateDetails;

		/*
		 Email_Template_Logic::CREATE);
			Email_Template_Logic::EDIT);
			 Email_Template_Logic::READ);
		*/


		if ($bConfirm)
		{

			///if there are future versions that are affected by this one, process them into the database
			if (array_key_exists('aFutureVersions', $aTemplateDetails) && $aTemplateDetails['aFutureVersions']!=null)
			{
				foreach ($aTemplateDetails['aFutureVersions'] as $oFutureVersion)
				{
					$x = new Email_Template_Details((array)$oFutureVersion);
					$x->save();
				}



			}

			//set the end date on the current version
				$y = Email_Template_Details::getCurrentDetailsForTemplateId($aTemplateDetails['oTemplateDetails']->email_template_id);
				if ($y->end_datetime>$aTemplateDetails['oTemplateDetails']->effective_datetime)
				{
					$y->end_datetime = $aTemplateDetails['oTemplateDetails']->effective_datetime;
					$y->save();

				}
			//save the new version

			$aTemplateDetails = (array)$aTemplateDetails['oTemplateDetails'];

			$aNewVersion = array();
			$aNewVersion['id'] = null;
			$aNewVersion['email_template_id'] = $aTemplateDetails['email_template_id'];
			$aNewVersion['created_timestamp'] = null;
			$aNewVersion['created_employee_id'] = Flex::getUserId();
			$aNewVersion['email_html']	= $aTemplateDetails['email_html'];
			$aNewVersion['email_text'] = $aTemplateDetails['email_text'];
			$aNewVersion['effective_datetime'] = $aTemplateDetails['effective_datetime'];
			$aNewVersion['email_subject'] = $aTemplateDetails['email_subject'];
			$aNewVersion['description'] = $aTemplateDetails['description'];
			$aNewVersion['end_datetime'] = $aTemplateDetails['end_datetime'];

			$oDetails = new Email_Template_Details($aNewVersion);
			$oDetails->save();
			return	array(
							'Success'		=> true,
							'oTemplateDetails'		=> $oDetails->toArray(),
							'Confirm'			=> $bConfirm,
							'Report'			=> array()
						);
		}
		else
		{
			$oHTML = new Email_HTML_Document($aTemplateDetails['email_html']);

			return	array(
							'Success'		=> true,
							'oTemplateDetails'		=> $aTemplateDetails,
							'Confirm'			=> $bConfirm,
							'Report'			=> Email_template_Logic::processHTML($aTemplateDetails['email_html'], true)
						);

		}
	}

	public function toText($sHTML)
	{
		$oEmail = new Email_HTML_Document($sHTML);

		return	array(
						'Success'		=> true,
						'text'		=> implode("",Email_template_Logic::toText($sHTML))
					);
	}

	public function processHTML($sHTML)
	{

		//		$oEmail = new Email_HTML_Document($sHTML);
		return	array(
						'Success'		=> true,
						'html'		=> Email_template_Logic::processHTML($sHTML)
					);
	}

	public function getVariables($iTemplateDetailsId)
	{
		$aTemplateDetails = Email_Template_Details::getForId($iTemplateDetailsId);
		$aVars = Email_HTML_Document::getVariables();

		return	array(
						'Success'		=> true,
						'variables'		=> $aVars,
						'oTemplateDetails' =>$aTemplateDetails->toArray()
					);
	}



	function getFutureVersions($iVersionId)
	{

		return	array(
						'Success'		=> true,

						'aTemplateDetails' =>Email_Template_Details::getFutureVersionsForId($iVersionId)
					);

	}


}



?>