function validate_required(field,alerttxt)
{
	with (field)
	{
		if (value==null||value=="")
		{alert(alerttxt);return false;}
		else {return true}
	}
}
function validate_form(thisform)
{
	with (thisform)
	{
		if (validate_required(mixAccount_Address1,"Address line 1 must be filled out.")==false)
		{mixAccount_Address1.focus();return false;}

		'if (validate_required(mixAccount_Address2,"Address line 2 must be filled out.")==false)
		'{mixAccount_Address2.focus();return false;}

		if (validate_required(mixAccount_Suburb,"Suburb must be filled out.")==false)
		{mixAccount_Suburb.focus();return false;}

		if (validate_required(mixAccount_Postcode,"Postcode must be filled out.")==false)
		{mixAccount_Postcode.focus();return false;}

		if (validate_required(mixAccount_Title,"Title must be filled out.")==false)
		{mixAccount_Title.focus();return false;}

		if (validate_required(mixContact_FirstName,"FirstName must be filled out.")==false)
		{mixContact_FirstName.focus();return false;}

		if (validate_required(mixContact_LastName,"LastName must be filled out.")==false)
		{mixContact_LastName.focus();return false;}

		'if (validate_required(mixContact_JobTitle,"JobTitle must be filled out.")==false)
		'{mixContact_JobTitle.focus();return false;}

		if (validate_required(mixContact_Email,"Email must be filled out.")==false)
		{mixContact_Email.focus();return false;}

		if (validate_required(mixContact_Phone,"Phone must be filled out.")==false)
		{mixContact_Phone.focus();return false;}

		if (validate_required(mixContact_Mobile,"Mobile must be filled out.")==false)
		{mixContact_Mobile.focus();return false;}

		'if (validate_required(mixContact_Fax,"Fax must be filled out.")==false)
		'{mixContact_Fax.focus();return false;}
	}
}