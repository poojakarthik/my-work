/*
 * Author: Ryan Forrester
 *
 * File: javascript_validation.js
 * Purpose: validates form data, email fields, phone numbers, etc..
 *
 * Usage:
 * <script language="JavaScript" src="js/javascript_validation.js"></script>
 * <script language="JavaScript" src="js/javascript_error_box.js"></script>
 * <form method="foo" onsubmit="return validate_form(this)" name="form1">
 *
 * Add function at the bottom if creating new form which requires validation.
 *
 * Associated files:
 * - javascript_functions.js (contains the functions which do the validation, add more here if required.)
 * - javascript_error_box.js (contains the code which creates a popup error box.)
 *
 * A note on some of the available functions: 
 * validate_phone(), validate_required(), validate_email()
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
		if (validate_required(mixAccount_Address2,"Address line 2 must be filled out!")==false)
		{mixAccount_Address2.focus();return false;}
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
		if (validate_required(mixContact_JobTitle,"Job Title must be filled out!")==false)
		{mixContact_JobTitle.focus();return false;}
		if (validate_required(mixContact_FirstName,"First Name must be filled out!")==false)
		{mixContact_FirstName.focus();return false;}
		if (validate_required(mixContact_LastName,"Last Name must be filled out!")==false)
		{mixContact_LastName.focus();return false;}
		if (validate_required(mixContact_Email,"Email must be filled out!")==false)
		{mixContact_Email.focus();return false;}
		if (validate_email(mixContact_Email,"Invalid E-mail Address! Please re-enter.")==false)
		{mixContact_Email.focus();return false;}
		if (validate_phone(mixContact_Phone,"Phone must be filled out!")==false)
		{mixContact_Phone.focus();return false;}
		if (validate_phone(mixContact_Mobile,"Mobile must be filled out!")==false)
		{mixContact_Mobile.focus();return false;}
		if (validate_required(mixContact_Fax,"Fax must be filled out!")==false)
		{mixContact_Fax.focus();return false;}
		if (validate_required(mixAdditionalComments,"Details of the request are required!")==false)
		{mixAdditionalComments.focus();return false;}
	}
}