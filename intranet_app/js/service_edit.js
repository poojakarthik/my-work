	
	function serviceEditSumit (form)
	{
		if (form.elements ['FNN[1]'].value != form.elements ['FNN[2]'].value)
		{
			alert (
				"You did not confirm the Line Number.\n" +
				"Please confirm this Line Number and try again."
			);
			return false;
		}
		
		if (form.elements ['Archived'].checked == true && form.elements ['Archived'].value == "0")
		{
			return window.confirm (
				"Warning: Unarchiving this Service will perform one of the following Actions:\n" +
				" \n" +
				"1. If the FNN is in use (or potentially in use) by another service, a Change of Lessee will be performed.\n" +
				"2. If the FNN is not in use by another Service and . . . \n" +
					". . . a. If the Service has been in use by another Service but the Service has , a new Service will be created.\n" +
					". . . b. If the Service has not been in use by another Service, the expiration is revoked.\n" +
				" \n" +
				"Are you sure you wish to continue?"
			);
		}
		
		return true;
	}
	
