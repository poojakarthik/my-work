/*
 * Author: Ryan Forrester
 *
 * File: javascript_error_box.js
 * Purpose: validates form data, email fields, phone numbers, etc..
 *
 * Usage:
 * <script language="JavaScript" src="js/javascript_validation.js"></script>
 * <script language="JavaScript" src="js/javascript_error_box.js"></script>
 * <form method="foo" onsubmit="return validate_form(this)" name="form1">
 *
 * Add function at the bottom of javascript_input_validation.js, when creating new form which requires validation.
 *
 * Associated files:
 * - javascript_functions.js (contains the functions which do the validation, add more here if required.)
 * - javascript_input_validation.js (contains the code which creates a popup error box.)
 */

function show_it(field,alerttxt)
{
	/* ie6/ie7 */
	if(document.all)
	{
		alert(alerttxt)
		return false;
	}
	/* firefox */
	else
	{
		document.getElementById('error_box').innerHTML = "<div class='content_0045'>Error</div><div class='content_0048'></div><div class='content_0046'>" + alerttxt + "</div><div class='content_0047'><INPUT TYPE='button' VALUE=' Ok ' onClick='hide_it(), document.all."+field.name+".focus()');'></div></div>";

		document.all.error_box.style.visibility = "visible";
		/* alert(alerttxt) */

		return false;
		
	}
}
function hide_it()
{
	document.all.error_box.style.visibility = "hidden";
	return false;
}
