<?php

class Application_Handler_CDR extends Application_Handler
{


	function MoveDelinquentCDRs($subPath)
	{


		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Delinquent CDRs");

		try
		{
			$this->LoadPage('delinquent_cdr');

		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message'] 		= "An error occured while trying to build the \"View Email History\" page";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}




	}

	function DownloadCSV($subPath)
	{
		// TODO: Check permissions

		if (!$subPath[0])
		{
			throw new Exception('Invalid error file path supplied');
		}

		$sFileBaseName	= urldecode($subPath[0]);
		$sFilePath		= FILES_BASE_PATH."temp/{$sFileBaseName}";

		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="'.$sFileBaseName.'"');
		echo @file_get_contents($sFilePath);
		die;


	}



}

?>
