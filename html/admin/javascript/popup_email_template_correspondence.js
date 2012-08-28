
var Popup_Email_Template_Correspondence = Class.create(Reflex_Popup,
{
	initialize : function($super, fnOnComplete)
	{
		$super(60);
		
		this._fnOnComplete	= fnOnComplete;
		this._aControls 	= [];
		
		this._oLoading = new Reflex_Popup.Loading();
		this._oLoading.display();
		
		Flex.Constant.loadConstantGroup(Popup_Email_Template_Correspondence.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(hCustomerGroups)
	{
		if (!hCustomerGroups)
		{
			Customer_Group.getAll(this._buildUI.bind(this))
			return;
		}
		
		// Controls
		var oNameControl =	Control_Field.factory(
								'text',
								{
									sLabel		: 'Name',
									mMandatory	: true, 
									mEditable	: true,
									fnValidate	: Reflex_Validation.Exception.stringOfLength.curry(0, 255)
								}
							);
		var oDescriptionControl =	Control_Field.factory(
										'text',
										{
											sLabel		: 'Description',
											mMandatory	: true, 
											mEditable	: true,
											fnValidate	: Reflex_Validation.Exception.stringOfLength.curry(0, 255)
										}
									);
		
		// A checkbox for each customer group
		this._hCustomerGroupControls	= {};
		var oCustomerGroupTBody 		= $T.tbody();
		for (var iId in hCustomerGroups)
		{
			var oCheckbox =	Control_Field.factory(
								'checkbox',
								{
									sLabel		: 'Customer Group: ' + hCustomerGroups[iId].internal_name,
									mEditable	: true
								}
							);
			oCheckbox.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			
			oCustomerGroupTBody.appendChild(
				$T.tr(
					$T.td(
						oCheckbox.getElement()
					),
					$T.td(
						$T.span({class: 'pointer'},
							hCustomerGroups[iId].internal_name
						).observe('click', this._toggleCheckboxControl.bind(this, oCheckbox))
					)
				)	
			);
			
			this._hCustomerGroupControls[iId] = oCheckbox;
		}
		
		var oTemplateControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Correspondence Template',
										mMandatory	: true, 
										mEditable	: true,
										fnPopulate	: Popup_Email_Template_Correspondence._getCorrespondenceTemplateOptions
									}
								);
		var oSQLControl =	Control_Field.factory(
								'textarea',
								{
									sLabel		: 'Data Source SQL',
									mMandatory	: true, 
									mEditable	: true,
									rows		: 10
								}
							);
		oSQLControl.cancelFocusShiftOnTab();

		oNameControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oTemplateControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oSQLControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);;
		
		this._aControls.push(oNameControl);
		this._aControls.push(oDescriptionControl);
		this._aControls.push(oTemplateControl);
		this._aControls.push(oSQLControl);
		
		// Dom
		var oContentDiv = 	$T.div({class: 'popup-email-template-correspondence'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Name'),
											$T.td(oNameControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(oDescriptionControl.getElement())
										),
										$T.tr(
											$T.th('Customer Groups'),
											$T.td(
												$T.table(oCustomerGroupTBody)
											)
										),
										$T.tr(
											$T.th('Correspondence Template'),
											$T.td(oTemplateControl.getElement())
										),
										$T.tr(
											$T.th('Data Source SQL'),
											$T.td(oSQLControl.getElement())
										)
									)
								),
								$T.div({class: 'popup-email-template-correspondence-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/decline.png'}),
										$T.span('Cancel')
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle('New Correspondence Email Template');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
		
		this._oLoading.hide();
		delete this._oLoading;
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aControls.length; i++)
			{
				try
				{
					this._aControls[i].validate(false);
					this._aControls[i].save(true);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Popup_Email_Template_Correspondence._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				name						: this._aControls[0].getValue(),
				description					: this._aControls[1].getValue(),
				correspondence_template_id	: this._aControls[2].getValue(),
				datasource_sql				: this._aControls[3].getValue(),
				customer_group_ids			: []
			};
			
			// Add customer group allegiances using this._hCustomerGroupControls
			for (var iId in this._hCustomerGroupControls)
			{
				if (this._hCustomerGroupControls[iId].getElementValue())
				{
					oDetails.customer_group_ids.push(iId);
				}
			}
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Email_Template', 'createTemplate');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Email_Template_Correspondence._ajaxError(oResponse, 'Could not save the Email Template');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iEmailTemplateId);
		}
	},
	
	_toggleCheckboxControl : function(oControl)
	{
		if (oControl.getElementValue())
		{
			oControl.setValue(false);
		}
		else
		{
			oControl.setValue(true);
		}
	}
});

Object.extend(Popup_Email_Template_Correspondence, 
{
	REQUIRED_CONSTANT_GROUPS : [],
	
	_ajaxError : function(oResponse, sMessage) {
		if (oResponse.aErrors) {
			// Validation errors
			Popup_Email_Template_Correspondence._validationError(oResponse.aErrors);
		} else {
			// Exception
			jQuery.json.errorPopup(oResponse, sMessage);
		}
	},
	
	_validationError : function(aErrors)
	{
		var oErrorElement = $T.ul();
		for (var i = 0; i < aErrors.length; i++)
		{
			oErrorElement.appendChild($T.li(aErrors[i]));
		}
		
		Reflex_Popup.alert(
			$T.div(
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	},
	
	_getCorrespondenceTemplateOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Popup_Email_Template_Correspondence._getCorrespondenceTemplateOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Correspondence_Template', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Correspondence_Template._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var i in oResponse.aResults)
		{
			aOptions.push(
				$T.option({value: i},
					oResponse.aResults[i].name	
				)
			);
		}
		fnCallback(aOptions);
	}
});
