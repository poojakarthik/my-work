	
	function provisioning_popup_history (Service)
	{
		var ProvisioningWindow = window.open (
			"provisioning_history.php?" +
			((Service != "") ? "Service=" + Service : ""),
			"",
			"width=750, height=550, scrollbars=yes, resize=yes"
		);
	}
	
	function provisioning_popup_requests (Service)
	{
		var ProvisioningWindow = window.open (
			"provisioning_requests.php?" +
			((Service != "") ? "Service=" + Service : ""),
			"",
			"width=750, height=550, scrollbars=yes, resize=yes"
		);
	}
	
