<?php

class JSON_Handler_Email_Text_Editor extends JSON_Handler
{
	protected	$_JSONDebug	= '';


	 function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	 function getTemplates($bCountOnly=false, $iLimit=null, $iOffset=null, $sSortDirection='DESC')
	{
		try
		{

			$aTemplates = Email_Template_Type::getForAllCustomerGroups($bCountOnly, $iLimit, $iOffset, $sSortDirection);
			return	array(
							'Success'		=> true,
							'aRecords'		=> $aTemplates,
							'iRecordCount'	=> count($aTemplates)
						);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}
	}


	 function getTemplateDetails($iTemplateId)
	{
		try
		{

			$oDetails = Email_Template_Details::getCurrentDetailsForTemplateId($iTemplateId);

			return	array(
							'bSuccess'						=> true,
							'aTemplateDetails'		=> $oDetails->toArray()
						);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}

	}

	 function save($aTemplateDetails, $bConfirm = false)
	{
		$aTemplateDetails = is_array($aTemplateDetails)?$aTemplateDetails:(array)$aTemplateDetails;




		count(Email_Template_Logic::validateTemplateDetails((array)$aTemplateDetails['oTemplateDetails']))>0?$bConfirm = false:null;


		if ($bConfirm)
		{
				$oDataAccess	= DataAccess::getDataAccess();
				if (!$oDataAccess->TransactionStart())
				{
					return 	array(
								"Success"	=> false,
								"Message"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD)) ? 'Could not start database transaction.' : false,
							);
				}
				try
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
					$aNewVersion['email_html']	= trim($aTemplateDetails['email_html']);
					$aNewVersion['email_text'] = trim($aTemplateDetails['email_text']);
					$aNewVersion['effective_datetime'] = $aTemplateDetails['effective_datetime'];
					$aNewVersion['email_subject'] = trim($aTemplateDetails['email_subject']);
					$aNewVersion['description'] = trim($aTemplateDetails['description']);
					$aNewVersion['end_datetime'] = $aTemplateDetails['end_datetime'];

					$oDetails = new Email_Template_Details($aNewVersion);
					$oDetails->save();
					$oDataAccess->TransactionCommit();
					return	array(
									'Success'				=> true,
									'Confirm'				=> true
								);


				}
			catch (Exception $e)
			{
				return	array(
									'Success'			=> false,
									'message'			=> $e->__toString(),
									'Confirm'			=>true
								);

			}

		}
		else//this is a validation only, not the actual save
		{

			try
			{


					$aErrors = Email_Template_Logic::validateTemplateDetails($aTemplateDetails);


				return	array(
								'Success'			=> true,
								'oTemplateDetails'	=> $aTemplateDetails,
								'Confirm'			=> $bConfirm,
								'Report'			=> Email_Template_Logic::processHTML($aTemplateDetails['email_html'], true),
								'Errors'			=>$aErrors
							);
			}
			catch (Exception $e)
			{
				return	array(
									'Success'			=> false,
									'message'			=> $e->__toString(),
									'Confirm'			=>false
								);

			}

		}
	}

	 function toText($sHTML)
	{
		try
		{

			return	array(
							'Success'		=> true,
							'text'		=> Email_Template_Logic::toText($sHTML)
						);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}
	}

	 function processHTML($sHTML, $iTemplateId)
	{
		try
		{

			$oTemplate = Email_Template::getForId($iTemplateId);
			$oEmailTemplateType	= Email_Template_Type::getForId($oTemplate->email_template_type_id);
			$oTemplateDetails = new Email_Template_Details(array( 'email_html'=>$sHTML));
			$oTemplateLogicObject = new $oEmailTemplateType->class_name($oTemplate, $oTemplateDetails);

			$aSampleData = $oTemplateLogicObject->getSampleData($iTemplateId);
			$sHTML		= $oTemplateLogicObject->getHTMLContent($aSampleData);




			return	array(
							'Success'		=> true,
							'html'		=> $oTemplateLogicObject->getHTMLContent($aSampleData)
						);
		}
		catch (EmailTemplateEditException $e)
		{

			return	array(
								'Summary'			=>$e->sSummaryMessage,
								'LineNo'			=>$e->iLineNumber,
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);


		}
		catch (Exception $e)
		{

			return	array(
								'Success'			=> false,
								'message'			=> print_r($e, true)
							);
		}
	}

	 function getTemplateVersionDetails($iTemplateDetailsId)
	{
		try
		{

			$aTemplateDetails = Email_Template_Details::getForId($iTemplateDetailsId);
			$aVars = Email_Template_Details::getVariablesForTemplateVersion($iTemplateDetailsId);

			return	array(
							'Success'		=> true,
							'variables'		=> $aVars,
							'oTemplateDetails' =>$aTemplateDetails->toArray()
						);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}
	}

	 function  getTemplateVariables($iTemplateId)
	{
		try
		{

			$aVars = Email_Template::getVariablesForTemplate($iTemplateId);

			return	array(
					'Success'		=> true,
					//'oTemplateDetails' =>array('template'=>$oTemplate->toArray(),'customer_group'=>$oCustomerGroup->toArray(),'template_type'=>$oTemplateType->toArray()),
					'variables'		=> $aVars
				);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}

	}

	 function getFutureVersions($iTemplateId)
	{

		try
		{

			return	array(
							'Success'		=> true,
							'aTemplateDetails' =>Email_Template_Details::getFutureVersionsForTemplateId($iTemplateId)
						);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}

	}


	function sendTestEmail($oData, $iTemplateId)
	{

		try
		{

			Email_Template_Logic::sendTestEmail((array)$oData, $iTemplateId);
			return	array(
							'Success'		=> true,
						);
		}
		catch (Exception $e)
		{
			return	array(
								'Success'			=> false,
								'message'			=> $e->__toString(),
							);
		}

	}

}



?>