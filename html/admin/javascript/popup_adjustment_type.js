
var Popup_Adjustment_Type = Class.create(Reflex_Popup,
{
	initialize : function($super, fnOnComplete)
	{
		$super(40);
		
		this._fnOnComplete = fnOnComplete;
		
		Flex.Constant.loadConstantGroup(Popup_Adjustment_Type.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
		// Create control fields
		this._oCodeControl = 	Control_Field.factory(
									'text',
									{
										sLabel		: 'Code',
										mMandatory	: true,
										mEditable	: true,
										fnValidate	: Reflex_Validation.stringOfLength.curry(0, 256)
									}
								);
		this._oCodeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._oDescriptionControl =	Control_Field.factory(
										'text',
										{
											sLabel		: 'Description',
											mMandatory	: true,
											mEditable	: true,
											fnValidate	: Reflex_Validation.stringOfLength.curry(0, 256)
										}
									);
		this._oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._oAmountControl =	Control_Field.factory(
									'text',
									{
										sLabel		: 'Amount',
										mMandatory	: true,
										mEditable	: true,
										fnValidate	: Reflex_Validation.float
									}
								);
		this._oAmountControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._oNatureControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Nature',
										mMandatory	: true,
										mEditable	: true,
										fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('transaction_nature')
									}
								);
		this._oNatureControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._oVisibilityControl =	Control_Field.factory(
										'select',
										{
											sLabel		: 'Visibility',
											mMandatory	: true,
											mEditable	: true,
											fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('adjustment_type_invoice_visibility')
										}
									);
		this._oVisibilityControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._oFixationControl =	Control_Field.factory(
										'checkbox',
										{
											sLabel		: 'Fixation',
											mMandatory	: false,
											mEditable	: true
										}
									);
		this._oFixationControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		this._aControls = [this._oCodeControl,
		                   this._oDescriptionControl,
		                   this._oAmountControl,
		                   this._oNatureControl,
		                   this._oVisibilityControl,
		                   this._oFixationControl];
		
		// Create container
		var oContentDiv =	$T.div({class: 'popup-adjustment-type'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Adjustment Code'),
											$T.td(this._oCodeControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(this._oDescriptionControl.getElement())
										),
										$T.tr(
											$T.th('Amount ($)'),
											$T.td(this._oAmountControl.getElement())
										),
										$T.tr(
											$T.th('Nature'),
											$T.td(this._oNatureControl.getElement())
										),
										$T.tr(
											$T.th('Visibility'),
											$T.td(this._oVisibilityControl.getElement())
										),
										$T.tr(
											$T.th('Fixation'),
											$T.td(
												this._oFixationControl.getElement(),
												$T.span({class: 'popup-adjustment-type-fixation-label'})
											)
										)
									)
								),
								$T.div({class: 'popup-adjustment-type-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle('New Adjustment Type');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_doSave : function(oResponse)
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
				Popup_Adjustment_Type._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				code 									: this._oCodeControl.getValue(),
				description 							: this._oDescriptionControl.getValue(),
				amount									: this._oAmountControl.getValue(),
				transaction_nature_id					: this._oNatureControl.getValue(),
				adjustment_type_invoice_visibility_id	: this._oVisibilityControl.getValue(),
				is_amount_fixed							: !!this._oFixationControl.getValue(),
				status_id								: $CONSTANT.STATUS_ACTIVE
			};
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment_Type', 'createAdjustmentType');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Adjustment_Type._ajaxError(oResponse, 'Could not save the Adjustment Type');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iAdjustmentTypeId);
		}
	}
});

Object.extend(Popup_Adjustment_Type, 
{
	REQUIRED_CONSTANT_GROUPS : ['status', 'transaction_nature', 'adjustment_type_invoice_visibility'],
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Popup_Adjustment_Type._validationError(oResponse.aErrors);
		}
		else
		{
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
			$T.div({class: 'alert-validation-error'},
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	}
});