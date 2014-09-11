<?php

class Application_Handler_Permission extends Application_Handler
{
	public function ProfileList($aSubPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_ADMIN));
		
		try
		{
			$aDetailsToRender	= array();
			
			// Load page_template
			$this->LoadPage('permission_profile_view', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
		catch (Exception $e)
		{
			$aDetailsToRender['Message']		= "An error occured";
			$aDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $aDetailsToRender);
		}
	}
}

?>
