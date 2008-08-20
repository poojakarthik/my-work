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
	 * @param	string	strFrameId					The id of the iframe used to store the file upload control
	 * @param	string	strImportReportContainer	The id of the container for the Import Report
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
	
	//------------------------------------------------------------------------//
	// ImportAsDraft
	//------------------------------------------------------------------------//
	/**
	 * ImportAsDraft
	 *
	 * Event handler for when the "Import As Draft" button is pressed
	 *
	 * Event handler for when the "Import As Draft" button is pressed
	 *
	 * @param	bool	bolConfirmed		true if the user has confirmed they 
	 *										want to carry out this button's action
	 *
	 * @return	void
	 * @method
	 */
	this.ImportAsDraft = function(bolConfirmed)
	{
		if (bolConfirmed == null)
		{
			var elmFileInput = this._elmUploadFrame.contentDocument.getElementById("RateGroupCSVFile");
			if (elmFileInput.value == "")
			{
				Vixen.Popup.Alert("Please declare a file to import");
				return;
			}
		
			var strMsg = "Are you sure you want to import this Rate Group and save it as a draft?";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.RateGroupImport.ImportAsDraft(true);});
			return;
		}
	
		// Specify which button was triggered
		var elmSubmitButtonValue = this._elmUploadFrame.contentDocument.getElementById("SubmitButtonValue");
		elmSubmitButtonValue.value = "Import as Draft";
		
		// Display the Pablo Splash
		Vixen.Popup.ShowPageLoadingSplash("Importing RateGroup", null, null, null, 1000);
		
		// Submit the form
		this._elmUploadFrame.contentDocument.forms[0].submit();
	}
	
	//------------------------------------------------------------------------//
	// ImportAndCommit
	//------------------------------------------------------------------------//
	/**
	 * ImportAndCommit
	 *
	 * Event handler for when the "Import And Commit" button is pressed
	 *
	 * Event handler for when the "Import And Commit" button is pressed
	 *
	 * @param	bool	bolConfirmed		true if the user has confirmed they 
	 *										want to carry out this button's action
	 *
	 * @return	void
	 * @method
	 */
	this.ImportAndCommit = function(bolConfirmed)
	{
		if (bolConfirmed == null)
		{
			var elmFileInput = this._elmUploadFrame.contentDocument.getElementById("RateGroupCSVFile");
			if (elmFileInput.value == "")
			{
				Vixen.Popup.Alert("Please declare a file to import");
				return;
			}
		
			var strMsg = "Are you sure you want to import and commit this Rate Group?<br />Committed RateGroups cannot be modified or deleted";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.RateGroupImport.ImportAndCommit(true);});
			return;
		}
		
		// Specify which button was triggered
		var elmSubmitButtonValue = this._elmUploadFrame.contentDocument.getElementById("SubmitButtonValue");
		elmSubmitButtonValue.value = "Import and Commit";
		
		// Display the Splash
		Vixen.Popup.ShowPageLoadingSplash("Importing RateGroup", null, null, null, 1000);
		
		// Submit the form
		this._elmUploadFrame.contentDocument.forms[0].submit();
	}
	
	//------------------------------------------------------------------------//
	// OnImportFailure
	//------------------------------------------------------------------------//
	/**
	 * OnImportFailure
	 *
	 * Event handler for when the Importing of a RateGroup fails
	 *
	 * Event handler for when the Importing of a RateGroup fails
	 *
	 * @param	string	strReport	Import Report
	 *
	 * @return	void
	 * @method
	 */
	this.OnImportFailure = function(strReport)
	{
		// Update the Import Report
		this._elmImportReport.scrollTop = 0;
		this._elmImportReport.innerHTML = strReport;
		
		// Close the Splash
		Vixen.Popup.ClosePageLoadingSplash();
		
		Vixen.Popup.Alert("The RateGroup could not be imported<br />Please review the Import Report");
	}
	
	//------------------------------------------------------------------------//
	// OnImportSuccess
	//------------------------------------------------------------------------//
	/**
	 * OnImportSuccess
	 *
	 * Event handler for when the Importing of a RateGroup succeeds
	 *
	 * Event handler for when the Importing of a RateGroup succeeds
	 * This closes the popup and updates the AddRatePlan page, if it is open
	 *
	 * @param	object	objRateGroup	stores properties of the new RateGroup
	 *									must contain: Id, Name, Description, Fleet, Draft, RecordType
	 *
	 * @return	void
	 * @method
	 */
	this.OnImportSuccess = function(strReport, objRateGroup)
	{
		// Update the appropriate combobox on the AddRatePlan page
		if (Vixen.RatePlanAdd)
		{
			Vixen.RatePlanAdd.UpdateRateGroupCombo(objRateGroup);
		}
		
		// Update the Import Report
		this._elmImportReport.scrollTop = 0;
		this._elmImportReport.innerHTML = strReport;
		
		// Close the Splash
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Notify the user
		if (objRateGroup.Draft)
		{
			// Ask the user if they want to download an updated version of the CSV file used to create this draft RateGroup
			// (for future editing of the RateGroup)
			this.ExportRateGroup(objRateGroup);
		}
		else
		{
			// The RateGroup has been committed
			Vixen.Popup.Alert("The RateGroup has been successfully imported and committed<br />RateGroup name: "+ objRateGroup.Name);
		}

	}
	
	//------------------------------------------------------------------------//
	// ExportRateGroup
	//------------------------------------------------------------------------//
	/**
	 * ExportRateGroup
	 *
	 * Prompts the user to Export an updated version of the RateGroup CSV file which was just used to import a draft RateGroup
	 *
	 * Prompts the user to Export an updated version of the RateGroup CSV file which was just used to import a draft RateGroup
	 *
	 * @param	object	objRateGroup	stores properties of the new RateGroup
	 *									must contain: Id, Name, Description, Fleet, Draft, RecordType
	 *
	 * @return	void
	 * @method
	 */
	this.ExportRateGroup = function(objRateGroup, bolConfirmed)
	{
		var strMsg = "";
		if (bolConfirmed == null)
		{
			if (objRateGroup.DraftUpdate)
			{
				// The importing updated a draft RateGroup which was already in the database
				strMsg =	"The RateGroup has been successfully updated and saved as a draft.<br />";
			}
			else
			{
				strMsg =	"The RateGroup has been successfully imported as a draft.<br />";
			}
			
			strMsg += 		"The CSV file is now out of date.<br />" +
							"Download an updated version of the RateGroup as a CSV file?";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.RateGroupImport.ExportRateGroup(objRateGroup, true);});
			return;
		}
		
		// Export the RateGroup
		window.location = "flex.php/RateGroup/Export/?RateGroup.Id=" + objRateGroup.Id;
	}
}

// instanciate the objects
if (Vixen.RateGroupImport == undefined)
{
	Vixen.RateGroupImport = new VixenRateGroupImportClass;
}
