	
	function notes_popup (AccountGroup, Account, Service, Contact)
	{
		var NotesWindow = window.open (
			"note_list.php?" +
			((AccountGroup != "") ? "AccountGroup=" + AccountGroup : "") +
			((Account != "") ? "Account=" + Account : "") +
			((Service != "") ? "Service=" + Service : "") +
			((Contact != "") ? "Contact=" + Contact : ""),
			"",
			"width=600, height=400, scrollbars=yes, resize=yes"
		);
	}
	
