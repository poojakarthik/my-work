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
	
	renderPopup	: function(strPopupName, objResponse)
	{
		Vixen.Popup.ClosePageLoadingSplash();
		
		if (!objResponse.PopupContent)
		{
			alert(objResponse.Debug);
		}
		
		if (objResponse.Success)
		{
			// Render the popup
			Vixen.Popup.Create(
								this._objPopups[strPopupName].strId, 
								objResponse.PopupContent, 
								this._objPopups[strPopupName].strSize, 
								this._objPopups[strPopupName].strAlign, 
								this._objPopups[strPopupName].strNature, 
								this._objPopups[strPopupName].strTitle
							);
		}
		else
		{
			$Alert("Failed to open the '" + this._objPopups[strPopupName].strTitle + "' popup" + ((objResponse.ErrorMessage != undefined)? "<br />" + objResponse.ErrorMessage : ""));
		}
	},
	
	uploadProposedList	: function()
	{
		remoteClass		= 'Telemarketing_Wash';
		remoteMethod	= 'buildProposedUploadPopup';
		jsonFunc		= jQuery.json.jsonFunction(this.renderPopup.bind(this, 'uploadProposed'), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	}
});

// Init
if (Flex.Telemarketing == undefined)
{
	Flex.Telemarketing	= new Telemarketing();
}