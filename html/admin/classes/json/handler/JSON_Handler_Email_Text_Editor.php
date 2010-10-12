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

	public function save($aTemplateDetails, $bConfirm = false)
	{
		$aTemplateDetails = is_array($aTemplateDetails)?$aTemplateDetails:(array)$aTemplateDetails;

		/*
		 Email_Template_Logic::CREATE);
			Email_Template_Logic::EDIT);
			 Email_Template_Logic::READ);
		*/


		count(Email_template_Logic::validateTemplateDetails((array)$aTemplateDetails['oTemplateDetails']))>0?$bConfirm = false:null;


		if ($bConfirm)
		{	///if there are future versions that are affected by this one, process them into the database
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
			$aNewVersion['email_html']	= trim($aTemplateDetails['email_html']);
			$aNewVersion['email_text'] = trim($aTemplateDetails['email_text']);
			$aNewVersion['effective_datetime'] = $aTemplateDetails['effective_datetime'];
			$aNewVersion['email_subject'] = trim($aTemplateDetails['email_subject']);
			$aNewVersion['description'] = trim($aTemplateDetails['description']);
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

			$aErrors = array();
			trim($aTemplateDetails['email_text']) == ''?$aErrors[]= "Your template must have a text version.":null;
			trim($aTemplateDetails['email_subject']) == ''?$aErrors[]= "Your template must have a subject.":null;
			trim($aTemplateDetails['description']) == ''?$aErrors[]= "Your template must have a description.":null;

			return	array(
							'Success'		=> true,
							'oTemplateDetails'		=> $aTemplateDetails,
							'Confirm'			=> $bConfirm,
							'Report'			=> Email_template_Logic::processHTML($aTemplateDetails['email_html'], true),
							'Errors'			=>$aErrors
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
		$aVars = Email_Template_Details::getVariablesForTemplateVersion($iTemplateDetailsId);

		return	array(
						'Success'		=> true,
						'variables'		=> $aVars,
						'oTemplateDetails' =>$aTemplateDetails->toArray()
					);
	}

	function getTemplate($iTemplateId)
	{
		$oTemplate = Email_Template::getForId($iTemplateId);
		$oCustomerGroup = Customer_Group::getForId($oTemplate->customer_group_id);
		$oTemplateType =  Email_Template_Type::getForId($oTemplate->email_template_type_id);
		$aVars = Email_Template::getVariablesForTemplate($iTemplateId);

				return	array(
						'Success'		=> true,
						'oTemplateDetails' =>array('template'=>$oTemplate->toArray(),'customer_group'=>$oCustomerGroup->toArray(),'template_type'=>$oTemplateType->toArray()),
						'variables'		=> $aVars
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