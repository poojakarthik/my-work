	
	function ViewRateGroupDetails (Id)
	{
		window.open (
			"rates_group_view.php?Id=" + Id,
			"",
			"channelmode=no, directories=no, location=no, menubar=no, resizable=yes, scrollbars=yes, titlebar=no, width=800, height=400"
		);
		
		return false;
	}
