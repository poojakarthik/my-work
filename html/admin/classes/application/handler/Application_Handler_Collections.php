<?php

class Application_Handler_Collections extends Application_Handler
{
	public function Accounts($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));
		$aDetailsToRender = array();
		try
		{
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage('Collections - Accounts');
			$this->LoadPage('collections_accounts', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Collections Accounts\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function Events($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));
		$aDetailsToRender = array();
		try
		{
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage('Collections - Events');
			$this->LoadPage('collections_events', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Collections Events\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function Configure($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));
		$aDetailsToRender = array();
		try
		{
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage('Collections - Configure');
			$this->LoadPage('collections_configure', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Collections Configure\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function OCAReferrals($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_OPERATOR, PERMISSION_OPERATOR_EXTERNAL));
		$aDetailsToRender = array();
		try
		{
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage('Collections - OCA Referrals');
			$this->LoadPage('collections_oca_referrals', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Collections - OCA Referrals\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}	
	
	public function DownloadLedgerFile($aSubPath)
	{
		try
		{
			// Proper admin required
			AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

			// Output for download
			$sFileName 	= $aSubPath[0];
			$sMIME		= $_GET['Type'];
			$sFilePath	= FILES_BASE_PATH."/temp/{$sFileName}";
			header("Content-type: {$sMIME}");
			header("Content-Disposition: attachment; filename=\"{$sFileName}\"");
			echo file_get_contents($sFilePath);
			unlink($sFilePath);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			echo $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
		}

		die;
	}
	
	public function Scenario($aSubPath)
	{
		$aDetailsToRender = array();
		try
		{
			$iScenarioId 		= null;
			$bRenderMode		= null;
			$bLoadOnly			= false;
			switch ($aSubPath[0])
			{
				case 'Create':
					if (isset($aSubPath[1]))
					{
						$iScenarioId = (int)$aSubPath[1];
					}
					
					$bRenderMode	= true;
					$bLoadOnly		= true;
					$sTitle			= 'Collections - Create Scenario';
					break;
				
				case 'View':
					if (!isset($aSubPath[1]))
					{
						throw new Exception("Incorrect use of page, no source Scenario supplied.");
					}
					
					$iScenarioId	= (int)$aSubPath[1];
					$bRenderMode	= false; 
					$sTitle			= 'Collections - View Scenario';
					break;
					
				case 'Edit':
					if (!isset($aSubPath[1]))
					{
						throw new Exception("Incorrect use of page, no edit Scenario supplied.");
					}
					
					$iScenarioId	= (int)$aSubPath[1];
					$bRenderMode	= true; 
					$sTitle			= 'Collections - Edit Scenario';
					break;
			}
			
			$aDetailsToRender['iScenarioId'] 	= $iScenarioId;
			$aDetailsToRender['bRenderMode'] 	= $bRenderMode;
			$aDetailsToRender['bLoadOnly']		= $bLoadOnly;
			
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage($sTitle);
			$this->LoadPage('collections_scenario', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"Collections - Scenario\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>
