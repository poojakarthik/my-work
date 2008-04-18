//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_bulk_add.js
//----------------------------------------------------------------------------//
/**
 * service_bulk_add
 *
 * javascript required of the service_bulk_add functionality
 *
 * javascript required of the service_bulk_add functionality
 * 
 *
 * @file		service_bulk_add.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Define constants used by this class
//TODO! These should probably be defined elsewhere
if (Vixen.Constants == undefined)
{
	Vixen.Constants = {};
}

Vixen.Constants.ServiceType = {};
Vixen.Constants.ServiceType[100] = {Constant : "SERVICE_TYPE_ADSL", 		Description : "ADSL"}
Vixen.Constants.ServiceType[101] = {Constant : "SERVICE_TYPE_MOBILE", 		Description : "Mobile"}
Vixen.Constants.ServiceType[102] = {Constant : "SERVICE_TYPE_LAND_LINE", 	Description : "Land Line"}
Vixen.Constants.ServiceType[103] = {Constant : "SERVICE_TYPE_INBOUND", 		Description : "Inbound 1300/1800"}

for (i in Vixen.Constants.ServiceType)
{
	Vixen.Constants[Vixen.Constants.ServiceType[i].Constant] = parseInt(i);
}

// This should be moved to the constants.js file (although this file doesn't exist yet)
function $Const(strConstant)
{
	if (Vixen.Constants[strConstant] == undefined)
	{
		throw("ERROR: constant: '"+ strConstant +"' is undefined");
		return;
	}
	
	return Vixen.Constants[strConstant];
}

// class encapsulates the data stored in a single row of the Services table of the Service Bulk Add page
function ServiceInputComponent(elmServiceType, elmFnn, elmFnnConfirm, elmPlan, elmCostCentre)
{
	// Constructor
	this.intServiceType	= null;
	this.elmServiceType	= elmServiceType;
	this.elmFnn			= elmFnn;
	this.elmFnnConfirm	= elmFnnConfirm;
	this.elmPlan		= elmPlan;
	this.elmCostCentre	= elmCostCentre;
	this.intArrayIndex	= null;	// The object's position in its containing array
	
	this.objExtraDetails = null;

	this.FnnUpdateListener = function(objEvent)
	{
		this._CheckFnn();
	}
	
	this.PlanChangeListener = function(objEvent)
	{
		if (this.intServiceType != null)
		{
			Vixen.ServiceBulkAdd.objServiceTypeDetails[this.intServiceType].intLastPlanChosen = this.elmPlan.value;
		}
	}
	
	this.CostCentreChangeListener = function(objEvent)
	{
		Vixen.ServiceBulkAdd.intLastCostCentreChosen = this.elmCostCentre.value;
	}

	// Register event listeners
	this.elmFnn.addEventListener('keyup', this.FnnUpdateListener.bind(this), true);
	this.elmFnn.addEventListener('change', this.FnnUpdateListener.bind(this), true);
	this.elmFnnConfirm.addEventListener('keyup', this.FnnUpdateListener.bind(this), true);
	this.elmFnnConfirm.addEventListener('change', this.FnnUpdateListener.bind(this), true);
	this.elmPlan.addEventListener('change', this.PlanChangeListener.bind(this), true);
	this.elmCostCentre.addEventListener('change', this.CostCentreChangeListener.bind(this), true);

	// These event listeners prohibit the user from copying/pasting the FNNs
	this.elmFnn.addEventListener('keydown', FnnTextboxKeyHandler, true);
	this.elmFnn.addEventListener('select', FnnTextHighlightingHandler, true);
	this.elmFnnConfirm.addEventListener('keydown', FnnTextboxKeyHandler, true);
	this.elmFnnConfirm.addEventListener('select', FnnTextHighlightingHandler, true);
	
	// Prevents the user from highlighting the text in the Fnn Textboxes
	// This is done so that they have to physically type the fnn in twice.  They can't copy/paste it
	function FnnTextHighlightingHandler(objEvent)
	{
		objEvent.target.selectionStart = objEvent.target.selectionEnd;
		objEvent.preventDefault();
	}
	
	// Prevents the user from copying or pasting the text in the Fnn Textboxes
	// This is done so that they have to physically type the fnn in twice.  They can't copy/paste it
	function FnnTextboxKeyHandler(objEvent)
	{
		// keyCode 37 is the left key
		if (objEvent.shiftKey && objEvent.keyCode == 37)
		{
			// The user is trying to select text by pressing shift-left
			// Jog the cursor left one character
			objEvent.target.selectionEnd = objEvent.target.selectionStart = objEvent.target.selectionStart -1;
			objEvent.preventDefault();
		}
		else if (objEvent.ctrlKey || objEvent.keyCode == 45)
		{
			objEvent.preventDefault();
		}
		else
		{
			return true;
		}
	}

	// Checks if the Fnn textboxes match and prepares the row accordingly
	this._CheckFnn = function()
	{
		// Check if the FNNs are the same
		// This has been done as 2 conditions because I believe the first one is faster, and will usually pass
		if ((this.elmFnnConfirm.value.length != this.elmFnn.value.length) || (this.elmFnnConfirm.value != this.elmFnn.value) || (this.elmFnn.value.length == 0))
		{
			// The 2 text boxes differ, check if a ServiceType has been defined for this row
			if (this.intServiceType == null)
			{
				// A ServiceType has not yet been defined for this row, so you don't have to do anything
				return;
			}
			
			// ServiceType must have been set but now it shouldn't be
			// Remove the plans and the ServiceType image
			this.SetServiceType(null);
			return;
		}
		else
		{
			// The 2 FNNs must be the same, check what ServiceType they represent
			var intServiceType = this._CalculateServiceType();
			this.SetServiceType(intServiceType);
			
			// If this is the last Service in the list then add a new empty one
			if ((intServiceType != null) && (this.intArrayIndex == (Vixen.ServiceBulkAdd.arrServices.length - 1)))
			{
				Vixen.ServiceBulkAdd.AddMoreServices(1);
			}
		}
	}
	
	// This will set up the row based on the ServiceType specified
	// if intServiceType is null then it will set the row up as being blank
	this.SetServiceType = function(intServiceType)
	{
		if (this.intServiceType == intServiceType)
		{
			// The ServiceType hasn't actually changed
			return;
		}
		
		this.intServiceType = intServiceType;
		
		switch (intServiceType)
		{
			case Vixen.Constants.SERVICE_TYPE_MOBILE:
				this.elmServiceType.className = "ServiceTypeIconMobile";
				break;
				
			case Vixen.Constants.SERVICE_TYPE_LAND_LINE:
				this.elmServiceType.className = "ServiceTypeIconLandLine";
				break;
				
			case Vixen.Constants.SERVICE_TYPE_ADSL:
				this.elmServiceType.className = "ServiceTypeIconADSL";
				break;
				
			case Vixen.Constants.SERVICE_TYPE_INBOUND:
				this.elmServiceType.className = "ServiceTypeIconInbound";
				break;
				
			default:
				this.elmServiceType.className = "ServiceTypeIconBlank";
		}
		this.objExtraDetails = null;
		this.FlagFnnTextboxes();
		this._LoadPlans();
		
		if (intServiceType != null)
		{
			// Set the values of the plan and the CostCentre to that of the last one defined
			this.elmCostCentre.value = Vixen.ServiceBulkAdd.intLastCostCentreChosen;
			
			if (Vixen.ServiceBulkAdd.objServiceTypeDetails[this.intServiceType].intLastPlanChosen != null)
			{
				this.elmPlan.value = Vixen.ServiceBulkAdd.objServiceTypeDetails[this.intServiceType].intLastPlanChosen;
			}
		}
	}
	
	// This makes a reference to the Vixen.ServiceBulkAdd object
	this._LoadPlans = function()
	{
		var elmOption;
		this.elmPlan.value = null;
		
		while (this.elmPlan.options.length > 0)
		{
			this.elmPlan.removeChild(this.elmPlan.firstChild);
		}
		
		if (this.intServiceType == null)
		{
			// No ServiceType has been specified
			return;
		}
		
		var arrRatePlans = Vixen.ServiceBulkAdd.arrRatePlans[this.intServiceType];
		
		// Add each plan to the Plan combobox
		for (intId in arrRatePlans)
		{
			elmOption			= document.createElement("option");
			elmOption.value		= intId;
			elmOption.innerHTML	= arrRatePlans[intId];
			
			this.elmPlan.appendChild(elmOption);
		}
		this.elmPlan.selectedIndex = 0;
	}
	
	
	// Uses this.elmFnn.value to work out the ServiceType
	this._CalculateServiceType = function()
	{
		var strFnn = this.elmFnn.value;
		
		// Check that the FNN is a valid FNN
		var regexFnn = /^(0\d{9}[i]?$)|(13\d{4}$)|(1[389]00\d{6})$/;
		if (!regexFnn.test(strFnn))
		{
			return null;
		}
		
		var strPrefix = strFnn.substr(0, 2);
		
		if (strPrefix == '02' || strPrefix == '03' || strPrefix == '07' || strPrefix == '08' || strPrefix == '09')
		{
			// Landline or ADSL
			if (strFnn.substr(-1).toLowerCase() == 'i')
			{
				return Vixen.Constants.SERVICE_TYPE_ADSL;
			}
			else
			{
				return Vixen.Constants.SERVICE_TYPE_LAND_LINE;
			}
		}
		
		if (strPrefix == '04')
		{
			// Check that this doesn't end in an "i", as regexFnn does not account for this scenario
			if (strFnn.substr(-1).toLowerCase() == 'i')
			{
				return null;
			}
			return Vixen.Constants.SERVICE_TYPE_MOBILE;
		}
		
		if (strPrefix == '13' || strPrefix == '18' || strPrefix == '19')
		{
			return Vixen.Constants.SERVICE_TYPE_INBOUND;
		}
	}
	
	this.FlagFnnTextboxes = function(bolIsInvalid)
	{
		this.elmFnn.SetHighlight(bolIsInvalid);
		this.elmFnnConfirm.SetHighlight(bolIsInvalid);
	}
	
	// returns the details of the Service, as an object
	// some details will differ depending on the ServiceType of the service
	this.GetProperties = function()
	{
		objService =	{
							intServiceType	: this.intServiceType,
							strFNN			: this.elmFnn.value,
							intPlanId		: parseInt(this.elmPlan.value),
							intCostCentre	: parseInt(this.elmCostCentre.value),
							intArrayIndex	: this.intArrayIndex
						};
						
		if (this.objExtraDetails != null)
		{
			for (strKey in this.objExtraDetails)
			{
				objService[strKey] = this.objExtraDetails[strKey];
			}
		}
		
		return objService;
	}
	
	// Returns true if extra details have been defined
	// Note: ADSL services do not have any extra details
	this.HasExtraDetailsDefined = function()
	{
		return (boolean)(this.objExtraDetails != null);
	}
	
	this.NeedsExtraDetailsDefined = function()
	{
		return (Vixen.ServiceBulkAdd.objServiceTypeDetails[this.intServiceType].strPopupId != null);
	}
	
	this.LoadExtraDetailsPopup = function()
	{
		if (!this.NeedsExtraDetailsDefined())
		{
			alert("ERROR: "+ Vixen.Constants.ServiceType[this.intServiceType].Description +" services do not need extra details defined");
			return false;
		}

		switch (this.intServiceType)
		{
			case Vixen.Constants.SERVICE_TYPE_MOBILE:
				Vixen.ServiceBulkAdd.Mobile.LoadService(this);
				break;
				
			case Vixen.Constants.SERVICE_TYPE_LAND_LINE:
				Vixen.ServiceBulkAdd.LandLine.LoadService(this);
				break;
				
			case Vixen.Constants.SERVICE_TYPE_INBOUND:
				Vixen.ServiceBulkAdd.Inbound.LoadService(this);
				break;
		}
	}
}


//----------------------------------------------------------------------------//
// VixenServiceBulkAddClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceBulkAddClass
 *
 * Encapsulates all event handling required of the Service Bulk Add webpage
 *
 * Encapsulates all event handling required of the Service Bulk Add webpage
 * 
 *
 * @package	ui_app
 * @class	VixenServiceBulkAddClass
 * 
 */
function VixenServiceBulkAddClass()
{
	this.intAccountId	= null;
	this.arrRatePlans	= null;
	this.arrServices				= new Array();
	this.tableServices				= null;
	this.elmGenericTableRow			= null;

	this.objServiceTypeDetails = {};
	this.objServiceTypeDetails[Vixen.Constants.SERVICE_TYPE_ADSL]		= 	{	intLastPlanChosen : null,
																				strPopupId : null,
																			};
	this.objServiceTypeDetails[Vixen.Constants.SERVICE_TYPE_MOBILE]		= 	{	intLastPlanChosen : null,
																				strPopupId : "ExtraDetailMobile",
																			};
	this.objServiceTypeDetails[Vixen.Constants.SERVICE_TYPE_LAND_LINE]	=	{	intLastPlanChosen : null,
																				strPopupId : "ExtraDetailLandLine",
																			};
	this.objServiceTypeDetails[Vixen.Constants.SERVICE_TYPE_INBOUND]	= 	{	intLastPlanChosen : null,
																				strPopupId : "ExtraDetailInbound",
																			};
	
	this.intLastCostCentreChosen = 0;
	
	// I think this isn't needed anymore
	this.intServiceTypeOfCurrentlyLoadedPopup = null;
	
	this.Initialise = function(intAccountId, arrRatePlans)
	{
		this.intAccountId = intAccountId;
		this.arrRatePlans = arrRatePlans;
		
		this.tableServices = document.getElementById("Services");
		
		// Store a copy of the first record of the table, so that it can be used to add more records
		this.elmGenericTableRow = this.tableServices.rows[1].cloneNode(true);
		
		// Clean up this row
		this.elmGenericTableRow.removeAttribute("id");
		var i = 0;
		while (i < this.elmGenericTableRow.childNodes.length)
		{
			if (this.elmGenericTableRow.childNodes[i].nodeType == Node.TEXT_NODE)
			{
				// Delete this node as it is probably just a new line character
				this.elmGenericTableRow.removeChild(this.elmGenericTableRow.childNodes[i]);
			}
			else if (this.elmGenericTableRow.childNodes[i].tagName == "SCRIPT")
			{
				// Delete this node as it is a script element which isn't required
				this.elmGenericTableRow.removeChild(this.elmGenericTableRow.childNodes[i]);
			}
			
			else
			{
				i++;
			}
		}
		
		// Replace the first row with the cleaner version
		var elmRow1 = this.elmGenericTableRow.cloneNode(true);
		this.AddToServiceArray(elmRow1);
		this.tableServices.tBodies[0].replaceChild(elmRow1, this.tableServices.rows[1]);
		
		this.arrServices[0].elmFnn.focus();
		
		//this.AddMoreServices(4);
	}
	
	// Stores references to the various elements contained in a row which may require manipulation
	this.AddToServiceArray = function(elmRow)
	{
		// Retrieve refernces to the controls that can be manipulated
		var elmServiceType	= elmRow.cells[0].firstChild;
		var elmFnn			= elmRow.cells[1].firstChild;
		var elmFnnConfirm	= elmRow.cells[2].firstChild;
		var elmPlan			= elmRow.cells[3].firstChild;
		var elmCostCentre	= elmRow.cells[4].firstChild;

		var intArrayIndex = this.arrServices.push(new ServiceInputComponent(elmServiceType, elmFnn, elmFnnConfirm, elmPlan, elmCostCentre)) - 1;
		this.arrServices[intArrayIndex].intArrayIndex = intArrayIndex;
	}
	
	// Closes all the "ExtraDetails" popups, except for the one with ServiceType == intSeviceTypeException if it has been defined
	this.CloseAllPopups = function(intServiceTypeException)
	{	
		for (intServiceType in Vixen.Constants.ServiceType)
		{
			if ((this.objServiceTypeDetails[intServiceType].strPopupId != null) && (intServiceType != intServiceTypeException))
			{
				Vixen.Popup.Close(this.objServiceTypeDetails[intServiceType].strPopupId);
			}
		}
	}
	
	this.AddMoreServices = function(intNumServices)
	{
		intNumServices = parseInt(intNumServices);
		
		if (isNaN(intNumServices) || intNumServices < 1)
		{
			return;
		}
		
		var intCurrentNumOfRows = this.tableServices.rows.length - 1;
		if (intCurrentNumOfRows + intNumServices > 100)
		{
			// Don't allow them to add more than 100 at any one time
			intNumServices = 100 - intCurrentNumOfRows;
		}

		var strClassName = this.tableServices.rows[this.tableServices.rows.length-1].className;
		
		for (i=0; i < intNumServices; i++)
		{
			strClassName = (strClassName == "Even")? "Odd" : "Even";
			
			elmNewRow = this.elmGenericTableRow.cloneNode(true);
			elmNewRow.className = strClassName;
			
			// Save references to the input elements of the row
			//this.AddToServiceArray(elmNewRow, intCurrentNumOfRows + i);
			this.AddToServiceArray(elmNewRow);
			
			// Register event listeners
			//this.RegisterListeners(intCurrentNumOfRows + i);
			
			this.tableServices.tBodies[0].appendChild(elmNewRow);
		}
	}
	
	// Prompts the user to see if they really want to add the services to the account
	this.ConfirmSave = function()
	{
		// Notify the user as to how many services they are trying to add
		// (this should probably be broken down into how many of each ServiceType they are trying to add)
		var intServiceCount = 0;
		for (i in this.arrServices)
		{
			if (this.arrServices[i].intServiceType != null)
			{
				intServiceCount++;
			}
		}
		
		if (intServiceCount)
		{
			Vixen.Popup.Confirm("Are you sure you want to add "+ intServiceCount +" services to this account?", function(){Vixen.ServiceBulkAdd._ValidateFNNs();});
		}
		else
		{
			Vixen.Popup.Alert("No services have been properly specified");
		}
	}
	
	// Performs initial validation of details
	this._ValidateFNNs = function()
	{
		// Check that no new service has the same fnn as any of the other new services
		var arrDuplicatedServices = new Array();
		for (i=0; i < this.arrServices.length; i++)
		{
			if (this.arrServices[i].intServiceType != null)
			{
				// i points to a valid service
				for (j=0; j < this.arrServices.length; j++)
				{
					if (i == j || this.arrServices[j].intServiceType == null)
					{
						continue;
					}
					
					if (this.arrServices[i].elmFnn.value == this.arrServices[j].elmFnn.value)
					{
						// The 2 services have the same FNN
						arrDuplicatedServices.push(i);
					}
				}
			}
		}
		if (arrDuplicatedServices.length > 0)
		{
			this.FlagInvalidFNNs(arrDuplicatedServices, true);
			Vixen.Popup.Alert("ERROR: Duplicate services are highlighted");
			return;
		}

		// Check that the new services aren't already defined in the database
		var objService;
		var arrDeclaredServices = new Array();
		for (i in this.arrServices)
		{
			if (this.arrServices[i].intServiceType != null)
			{
				arrDeclaredServices.push(this.arrServices[i].GetProperties());
			}
		}
		
		// Make the AJAX call to the server for preliminary validation of the services
		var objObjects		= {};
		objObjects.Services	= {};
		objObjects.Services.Data	= arrDeclaredServices;
		objObjects.Account 			= {Id : this.intAccountId};
		
		Vixen.Popup.ShowPageLoadingSplash("Performing preliminary validation of FNNs");
		Vixen.Ajax.CallAppTemplate("Service", "BulkValidateFNNs", objObjects, null, false, true);
	}
	
	// The server will trigger this method, after validating the FNNs
	this.ValidateFNNsReturnHandler = function(bolAllValid, arrInvalidServiceIndexes, strErrorMsg)
	{
		if (!bolAllValid)
		{
			// At least one of the new services is invalid
			this.FlagInvalidFNNs(arrInvalidServiceIndexes, true);
			Vixen.Popup.Alert(strErrorMsg);
			return;
		}
		
		// All Services passed preliminary validation
		this.FlagInvalidFNNs(null, true);
		
		// Load up each of the ServiceType "Extra Details" popups so as to aquire the extra information required of each Service
		this.GetExtraDetailsForNextService();
	}
	
	// intLastService is the index into this.arrServices, of the last Service to have its details displayed, optional
	// 
	// if bolMoveBack is true then it will load up the details of the 
	this.GetExtraDetailsForNextService = function(intLastService, bolMoveBack)
	{
		// Order all the services as to how they should be ordered for the adding of extra details
		// omiting any that don't require extra details defined
		var arrServiceOrder = new Array();
		var bolAllServicesProperlyDefined = false;
		
		for (i in Vixen.Constants.ServiceType)
		{
			if (this.objServiceTypeDetails[i].strPopupId != null)
			{
				// Services of this ServiceType require extra details to be defined
				for (j in this.arrServices)
				{
					if (this.arrServices[j].intServiceType == i)
					{
						// This service is of the correct ServiceType
						arrServiceOrder.push(j);
					}
				}
			}
		}

		if (arrServiceOrder.length == 0)
		{
			// There aren't any services that require extra details defined
			this._SaveServices();
			return;
		}

		// Work out which Service details to display
		var intServiceOrderIndex = null;
		if (intLastService == null)
		{
			intServiceOrderIndex = 0;
		}
		else
		{
			for (i = 0; i < arrServiceOrder.length; i++)
			{
				// Find the last service which was handled
				if (arrServiceOrder[i] == intLastService)
				{
					break;
				}
			}

			if (bolMoveBack)
			{
				// Move back 1 Service
				if (i == 0)
				{
					// The last service handled, was the very first one
					intServiceOrderIndex = 0;
				}
				else
				{
					intServiceOrderIndex = i - 1;
				}
			}
			else
			{
				// Move forward 1 Service
				if (i == (arrServiceOrder.length - 1))
				{
					// The last service handled, is the very last one
					bolAllServicesProperlyDefined = TRUE;
				}
				else
				{
					intServiceOrderIndex = i + 1;
				}
			}
		}

		// If all services have had their details defined, then make the final request to the server
		if (bolAllServicesProperlyDefined)
		{
			// Close any ExtraDetails popup that might be open
			this.CloseAllPopups();
			
			// Submit all the data
			this._SaveServices();
		}
		else
		{
			this.arrServices[arrServiceOrder[intServiceOrderIndex]].LoadExtraDetailsPopup();
		}
	}
	
	// This will highlight the textboxes for all services that have invalid fnns
	// arrInvalidServices is an array of indexes of the this.arrServices array
	this.FlagInvalidFNNs = function(arrInvalidServices, bolClear)
	{
		if (bolClear)
		{
			// Clear all of the flags, before flagging the ones defined in arrInvalidServices
			for (i in this.arrServices)
			{
				this.arrServices[i].FlagFnnTextboxes(false);
			}
		}
		
		if (arrInvalidServices != undefined)
		{
			for (i in arrInvalidServices)
			{
				this.arrServices[arrInvalidServices[i]].FlagFnnTextboxes(true);
			}
		}
	}
	
	
	// Submits all the data to the server, to save all the services
	this._SaveServices = function(bolConfirmed)
	{
		// Compile all the data
		var objService;
		var arrDeclaredServices = new Array();
		for (i in this.arrServices)
		{
			if (this.arrServices[i].intServiceType != null)
			{
				arrDeclaredServices.push(this.arrServices[i].GetProperties());
			}
		}
		
		if (!bolConfirmed)
		{
			var intNumOfServices = arrDeclaredServices.length;
			var strServices = (intNumOfServices == 1)? "this service?" : "these "+ intNumOfServices +" services?";
			var strMessage = "All details have now been declared.  Are you sure you want to add " + strServices;
			Vixen.Popup.Confirm(strMessage, function(){Vixen.ServiceBulkAdd._SaveServices(true);});
			return;
		}
		
		// Make the AJAX call to the server for preliminary validation of the services
		var objObjects		= {};
		objObjects.Services	= {};
		objObjects.Services.Data	= arrDeclaredServices;
		objObjects.Account 			= {Id : this.intAccountId};
		
		Vixen.Popup.ShowPageLoadingSplash("Creating new services");
		Vixen.Ajax.CallAppTemplate("Service", "BulkSave", objObjects, null, false, true);
	}
}

if (Vixen.ServiceBulkAdd == undefined)
{
	Vixen.ServiceBulkAdd = new VixenServiceBulkAddClass;
}
