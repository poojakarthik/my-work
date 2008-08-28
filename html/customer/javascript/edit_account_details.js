function validate_required(field,alerttxt)
{
	with (field)
	{
		if (value==null||value=="")
		{
			alert(alerttxt);
			return false;
		}
		else {
			return true;
		}
	}
}

function validate_form(thisform)
{
	with (thisform)
	{
		if (validate_required(mixContact_Title,"Title must be filled out!")==false)
		{mixContact_Title.focus();return false;}
		if (validate_required(mixContact_FirstName,"FirstName must be filled out!")==false)
		{mixContact_FirstName.focus();return false;}
		if (validate_required(mixContact_LastName,"LastName must be filled out!")==false)
		{mixContact_LastName.focus();return false;}
		if (validate_required(mixAccount_Suburb,"Suburb must be filled out!")==false)
		{mixAccount_Suburb.focus();return false;}
		if (validate_required(mixAccount_Address1,"Street Address must be filled out!")==false)
		{mixAccount_Address1.focus();return false;}
		if (validate_required(mixContact_Email,"Email must be filled out!")==false)
		{
			mixContact_Email.focus();
			return false;
		}
	}


	if (/^\w+([\+\.-]?\w+)*@\w+([\+\.-]?\w+)*(\.\w{2,6})+$/.test(thisform.mixContact_Email.value))
	{
		return (true)
	}
	alert("Invalid E-mail Address! Please re-enter.")
	return (false)
}