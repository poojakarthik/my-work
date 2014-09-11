<?php
class Application_Handler_Barring extends Application_Handler
{
	public function Ledger($aSubPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		$aDetailsToRender = array();
		try
		{
			BreadCrumb()->Employee_Console();
			BreadCrumb()->SetCurrentPage("Barring Ledger");
			
			// Merge the PHP with the HTML template
			if ($aSubPath[0] == 'Authorisation')
			{
				$this->LoadPage('barring_authorisation_ledger', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
			}
			else
			{
				$this->LoadPage('barring_action_ledger', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
			}
		}
		catch (Exception $eException)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $eException->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
	
	public function DownloadActionLedgerFile($aSubPath)
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
}
?>