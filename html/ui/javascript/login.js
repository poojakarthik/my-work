//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// login.js
//----------------------------------------------------------------------------//
/**
 * login
 *
 * javascript required of the login popup
 *
 * javascript required of the login popup
 *
 *
 * @file		login.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenLoginClass
//----------------------------------------------------------------------------//
/**
 * VixenLoginClass
 *
 * Encapsulates all event handling required of the Login popup
 *
 * Encapsulates all event handling required of the Login popup
 * 
 *
 * @package	ui_app
 * @class	VixenLoginClass
 */
function VixenLoginClass()
{
	this.objInputs = {};
	
	this.oLoginSuccessRetryData	= null;
	
	// Initialises the Login popup
	this.InitialisePopup = function()
	{
		// Get references to the username and password controls
		var formLoginDetails = $ID("LoginForm");
		var strName;
		
		for (i=0; i < formLoginDetails.length; i++)
		{
			if (formLoginDetails[i].name)
			{
				strName = formLoginDetails[i].name;
				this.objInputs[strName] = formLoginDetails[i];
			}
		}
		
		this.objInputs.UserName.focus();
	}

	// Submits the user's login details to the server
	this.Submit = function()
	{
		// Compile data to be sent to the server
		var objData	= 	{
							Login	:	{
											UserName	: this.objInputs.UserName.value,
											Password	: this.objInputs.Password.value
										}
						};
		
		Vixen.Popup.ShowPageLoadingSplash("Please Wait");
		Vixen.Ajax.CallAppTemplate("User", "SubmitLoginDetails", objData, null, false, true, this.SubmitReturnHandler.bind(this));
	}
	
	// Return handler for the ajax request made in the Submit() method
	this.SubmitReturnHandler = function(objXMLHttpRequest)
	{
		// If this function is run, then the account was successfully found
		var objReply = JSON.parse(objXMLHttpRequest.responseText);
		
		if (objReply != undefined && typeof objReply == "object" && objReply.Success != undefined)
		{
			if (objReply.Success)
			{
				// The login was successful
				if (this.oLoginSuccessRetryRequestData)
				{
					Vixen.Popup.Alert("Successfully logged in", null, null, null, null, this.retryRequestAfterLoginSuccess.bind(this));
				}
				else
				{
					$Alert("Successfully logged in");
				}
				
				Vixen.Popup.Close(this.objInputs.UserName);
			}
			else
			{
				// the login was unsuccessful
				$Alert("Login attempt failed.  Invalid login details.");
			}
		}
		else
		{
			// The request has not been sent a valid reply
			$Alert("Login doesn't appear to have been successful.  But you might be logged in.  Try something");
			Vixen.Popup.Close(this.objInputs.UserName);
		}
	}
	
	this.retryRequestAfterLoginSuccess	= function()
	{
		Vixen.Ajax.Send(this.oLoginSuccessRetryRequestData);
		this.oLoginSuccessRetryRequestData	= null;
	}
	
	this.setLoginSuccessRetryRequestData	= function(oData)
	{
		this.oLoginSuccessRetryRequestData	= oData;
	}
}

if (Vixen.Login == undefined)
{
	Vixen.Login = new VixenLoginClass;
}
