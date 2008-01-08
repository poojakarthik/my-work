//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import.js
//----------------------------------------------------------------------------//
/**
 * rate_group_import
 *
 * javascript required of the "Import Rate Group" popup webpage
 *
 * javascript required of the "Import Rate Group" popup webpage
 * 
 *
 * @file		rate_group_import.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRateGroupImportClass
//----------------------------------------------------------------------------//
/**
 * VixenRateGroupImportClass
 *
 * Encapsulates all event handling required of the "Import Rate Group" popup webpage
 *
 * Encapsulates all event handling required of the "Import Rate Group" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRateGroupImportClass
 * 
 */
function VixenRateGroupImportClass()
{
	//------------------------------------------------------------------------//
	// _elmUploadFrame
	//------------------------------------------------------------------------//
	/**
	 * _elmUploadFrame
	 *
	 * Reference to the iframe element used for the RateGroup csv file upload
	 *
	 * Reference to the iframe element used for the RateGroup csv file upload
	 * 
	 * @type		object (html iframe element)
	 *
	 * @property
	 */
	this._elmUploadFrame = null;
	this._elmImportReport = null;

	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the popup
	 *
	 * Initialises the popup
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(strFrameId, strImportReportContainer)
	{
		// Store a reference to the iframe
		this._elmUploadFrame = document.getElementById(strFrameId);

		// Store a reference to the ImportReport container
		this._elmImportReport = document.getElementById(strImportReportContainer);
	}
	
	// Handles form submittion when importing the RateGroup as a draft
	this.ImportAsDraft = function()
	{
		// Specify which button was triggered
		var elmSubmitButtonValue = this._elmUploadFrame.contentDocument.getElementById("SubmitButtonValue");
		elmSubmitButtonValue.value = "Import as Draft";
		
		//TODO! Display the Pablo Splash
		
		// Submit the form
		this._elmUploadFrame.contentDocument.forms[0].submit();
	}
	
	// Handles form submittion when importing the RateGroup and commiting it
	this.ImportAndCommit = function()
	{
		// Specify which button was triggered
		var elmSubmitButtonValue = this._elmUploadFrame.contentDocument.getElementById("SubmitButtonValue");
		elmSubmitButtonValue.value = "Import and Commit";
		
		//TODO! Display the Pablo Splash
		
		// Submit the form
		this._elmUploadFrame.contentDocument.forms[0].submit();
	}
	
	// Updates the contents of the import report container
	this.UpdateImportReport = function(strReport)
	{
		//TODO! Close the Pablo Splash
		this._elmImportReport.scrollTop = 0;
		this._elmImportReport.innerHTML = strReport;
		//Vixen.Popup.Alert(strReport);
	}

	// This will update the appropriate combobox of the Add/Edit RatePlan page.
	// This should be the only page that this popup can be opened from
	this.UpdateRatePlanPage = function(arrNewRateGroupDetails)
	{
		//TODO! Check that the Vixen.RatePlanAdd object exists, and only update it if it does
		// I don't know if I should have this method here, or if I should just check if Vixen.RatePlanAdd
		// exists, and if so, run, Vixen.RatePlanAdd.UpdateRateGroupCombo(arrNewRateGroupDetails)
		// I'm thinking the later.
		
		// I would also have to close the popup
	}
}

// instanciate the objects
if (Vixen.RateGroupImport == undefined)
{
	Vixen.RateGroupImport = new VixenRateGroupImportClass;
}
