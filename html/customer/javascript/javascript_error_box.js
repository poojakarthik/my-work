/*
 * File: javascript_error_box.js
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
