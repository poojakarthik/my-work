/*
 * Author: Ryan Forrester
 *
 * File: javascript_functions.js
 * Purpose: mostly validation..
 *
 * 
 */

/* function to validate empty fields */
function validate_required(field,alerttxt)
{
	if (field.value==null|| field.value=="")
	{
		//var field = field
		show_it(field,alerttxt)
		return false;
	}
	else 
	{
		return true;
	}
}

/* function to validate an email address */
function validate_email(field,alerttxt)
{
	if (/^\w+([\+\.-]?\w+)*@\w+([\+\.-]?\w+)*(\.\w{2,6})+$/.test(field.value))
	{
		return true;
	}
	else 
	{
		show_it(field,alerttxt)
		return false;
	}
}

/* function to validate a phone number */
function validate_phone(field,alerttxt)
{
   /* can be used for xx-xxxx-xxxx
    * if(field.value.search(/\d{2}\-\d{4}\-\d{4}/)==-1) 
    */

   if(field.value.search(/\d{2}\d{4}\d{4}/)==-1)
   {
      show_it(field,alerttxt)
      return false;
   }
}


function view_faq (faqid)
{
	var left = Math.floor((screen.availWidth - 600) / 2);
	//var top = Math.floor((screen.availHeight - 600) / 2);

	faqpopup = window.open("./flex.php/Console/FAQ/?view="+faqid, "FAQ" , "width=600,height=450,left="+left+",top=100,resizable=0,menubar=0,toolbar=0,location=0,directories=0,scrollbars=1,status=0");
	//faqpopup.moveTo(300,200);
	faqpopup.focus();
}