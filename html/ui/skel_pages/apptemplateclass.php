	//------------------------------------------------------------------------//
	// <MethodName>
	//------------------------------------------------------------------------//
	/**
	 * <MethodName>()
	 *
	 * Performs the logic for the <PageName>.php webpage
	 * 
	 * Performs the logic for the <PageName>.php webpage
	 *
	 * @return		void
	 * @method		<MethodName>
	 *
	 */
	function <MethodName>()
	{
		$pagePerms = PERMISSION_ADMIN;
		
		// Should probably check user authorization here
		AuthenticatedUser()->CheckAuth();
		
		AuthenticatedUser()->PermissionOrDie($pagePerms);	// dies if no permissions
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			// Add extra functionality for super-users
		}

		// Context menu
		ContextMenu()->Console();
		ContextMenu()->Logout();
		
		// Breadcrumb menu
				
		// Setup all DBO and DBL objects required for the page
		
		// EXAMPLE:
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('<PageName>');

		return TRUE;
	}
