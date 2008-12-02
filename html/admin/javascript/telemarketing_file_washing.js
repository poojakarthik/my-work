// Class: Telemarketing
// Handles the Telemarketing Washing popups in Flex
var Telemarketing	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this._objPopups	= {
							uploadProposed	: {
												strId		: 'Telemarketing_Wash_Proposed',
												strSize		: 'medium',
												strAlign	: 'centre',
												strNature	: 'modal',
												strTitle	: 'Upload Proposed Dialling List'
											}
						};	
	},
	
	renderPopup	: function()
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (response.Success)
		{
			// Render the popup
			Vixen.Popup.Create(
								this._objPopups[arguments[0]].strId, 
								response.PopupContent, 
								this._objPopups[arguments[0]].strSize, 
								this._objPopups[arguments[0]].strAlign, 
								this._objPopups[arguments[0]].strNature, 
								this._objPopups[arguments[0]].strTitle
							);
		}
		else
		{
			$Alert("Failed to open the '" + this._objPopups[arguments[0]].strTitle + "' popup" + ((response.ErrorMessage != undefined)? "<br />" + response.ErrorMessage : ""));
		}
	},
	
	uploadProposedList	: function()
	{
		remoteClass		= 'Telemarketing_Wash';
		remoteMethod	= 'buildProposedUploadPopup';
		jsonFunc		= jQuery.json.jsonFunction(this.displayPopupReturnHandler.bind(this, 'uploadProposed'), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	}
});

// Init
if (Flex_Telemarketing == undefined)
{
	Flex_Telemarketing	= new Telemarketing();
}