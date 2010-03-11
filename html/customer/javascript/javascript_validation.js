/*
 * File: javascript_validation.js
 */

/* check fields on normal contact form */
function validate_edit_details(thisform)
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
		{mixContact_Email.focus();return false;}
		if (validate_email(mixContact_Email,"Invalid E-mail Address! Please re-enter.")==false)
		{mixContact_Email.focus();return false;}
	}
}

function validate_support_request(thisform)
{
	with (thisform)
	{
		if (validate_required(mixAccount_Address1,"Address line 1 must be filled out!")==false)
		{mixAccount_Address1.focus();return false;}
		if (validate_required(mixAccount_Suburb,"Suburb must be filled out!")==false)
		{mixAccount_Suburb.focus();return false;}
		if (validate_required(mixAccount_State,"State must be filled out!")==false)
		{mixAccount_State.focus();return false;}
		if (validate_required(mixAccount_Postcode,"Postcode must be filled out!")==false)
		{mixAccount_Postcode.focus();return false;}
		if (validate_required(mixAccount_Country,"Country must be filled out!")==false)
		{mixAccount_Country.focus();return false;}
		if (validate_required(mixContact_Title,"Title must be filled out!")==false)
		{mixContact_Title.focus();return false;}
		if (validate_required(mixContact_FirstName,"First Name must be filled out!")==false)
		{mixContact_FirstName.focus();return false;}
		if (validate_required(mixContact_LastName,"Last Name must be filled out!")==false)
		{mixContact_LastName.focus();return false;}
		if (validate_required(mixContact_Email,"Email must be filled out!")==false)
		{mixContact_Email.focus();return false;}
		if (validate_email(mixContact_Email,"Invalid E-mail Address! Please re-enter.")==false)
		{mixContact_Email.focus();return false;}
		if (validate_phone(mixContact_Phone,"The phone number you entered is not valid.\r\nPlease enter a phone number with the format 0123456789 (ten numbers long).")==false)
		{mixContact_Phone.focus();return false;}
		if (validate_required(mixCustomerComments,"Details of the request are required!")==false)
		{mixCustomerComments.focus();return false;}
	}
}