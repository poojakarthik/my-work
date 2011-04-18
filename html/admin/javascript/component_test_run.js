
var Component_Test_Run = Class.create(Reflex_Component, {
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			// No additional config... yet
		}, this.CONFIG || {});
	
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this.NODE.addClassName('component-test-run');
	},
	
	_buildUI : function() {
		this._oBreadcrumbSelect = new Reflex_Breadcrumb_Select(
			'Choose Test', 
			[{fnPopulate: this._getTestClasses.bind(this), sName: 'Test Classes'}, 
			 {fnPopulate: this._getTestClassMethods.bind(this), sName: 'Methods'}],
			this._breadcrumbChange.bind(this)	
		);
		
		this.NODE = $T.div(
			new Component_Section({sTitle: 'Configure & Run'}),
			new Component_Section({sTitle: 'Output'})
		);
		
		var oConfigureComponent = this.NODE.select('.component-section').first().oReflexComponent;
		oConfigureComponent.getAttachmentNode('header-actions').appendChild(this._oBreadcrumbSelect.getElement());
		oConfigureComponent.getAttachmentNode('footer-actions').appendChild(
			$T.div(
				$T.button({class: 'icon-button', onclick: this._runTest.bind(this)},
					$T.img({src: '../admin/img/template/resultset_next.png', alt: '', title: ''}),
					$T.span('Run')
				)
			)
		);
		oConfigureComponent.getAttachmentNode().appendChild(
			$T.table({class: 'component-test-run-parameters reflex input'},
				$T.tbody({class: 'component-test-run-parameter-container'})
			)
		);
		
		var oOutputComponent = this.NODE.select('.component-section').last().oReflexComponent;
		oOutputComponent.getAttachmentNode().appendChild(
			$T.table({class: 'component-test-run-output'},
				$T.thead(
					$T.th('Result'),
					$T.th('Log')
				),
				$T.tbody(
					$T.tr(
						$T.td(
							$T.textarea({class: 'component-test-run-output-result'})
						),
						$T.td(
							$T.textarea({class: 'component-test-run-output-log'})
						)
					)
				)
			)
		);
	},
	
	_load : function(oResponse) {
		if (!oResponse) {
			// Request
			var oRequest = new Reflex_AJAX_Request('Test', 'getAllTestClassDetails', this._load.bind(this));
			oRequest.send();
		} else if (oResponse.hasException()) {
			// Error
			var oException = oResponse.getException();
			Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.getDebugLog()
			});
		} else {
			// Success!
			this._hTestClasses = oResponse.get('aClasses');
			this._syncUI();
		}
	},
	
	_syncUI : function() {
		if (!this._hTestClasses) {
			// No test class info, load it
			this._load();
		} else {
			// Got test class info, we're ready!
			this._oBreadcrumbSelect.populate();
			this._breadcrumbChange();
			this._onReady();
		}
	},
	
	_getTestClasses : function (oBreadcrumbSelect, fnCallback) {
		var aOptions = [];
		if (this._hTestClasses) {
			for (var sClass in this._hTestClasses) {
				var oClass 		= this._hTestClasses[sClass];
				var sTidyClass	= sClass.replace(/^Test_/, '');
				sTidyClass		= sTidyClass.replace(/(_)/g, ' ');
				aOptions.push({mValue: sClass, sText: sTidyClass + ': ' + oClass.sName});
			}
		}
		fnCallback(aOptions);
	},
	
	_getTestClassMethods : function (oBreadcrumbSelect, fnCallback) {
		var aOptions = [];
		if (this._hTestClasses) {
			var sClass = oBreadcrumbSelect.getValueAtLevel(0);
			if (this._hTestClasses[sClass]) {
				var hMethods = this._hTestClasses[sClass].aMethods;
				for (var sMethod in hMethods) {
					var oMethod		= hMethods[sMethod];
					var sTidyMethod = sMethod.replace(/([A-Z])/g, " $1");
					sTidyMethod		= sTidyMethod.substr(0, 1).toUpperCase() + sTidyMethod.substr(1);
					aOptions.push({mValue: sMethod, sText: sTidyMethod});
				}
			}
		}
		fnCallback(aOptions);
	},
	
	_breadcrumbChange : function () {
		// Clear current parameters and output
		this._aParameterControls 	= [];
		var oParameterContainer 	= this.NODE.select('.component-test-run-parameter-container').first();
		oParameterContainer.select('tr').each(Element.remove);
		this._clearOutput();
		
		var sClass	= this._oBreadcrumbSelect.getValueAtLevel(0);
		var sMethod = this._oBreadcrumbSelect.getValueAtLevel(1);
		if (sClass && sMethod) {
			var aParameters	= this._hTestClasses[sClass].aMethods[sMethod];
			for (var i = 0; i < aParameters.length; i++) {
				var oParameter		= aParameters[i];
				var bIsOptional		= !!oParameter.bIsOptional;
				var sType 			= oParameter.sName.charAt(0);
				var sTidyParameter	= oParameter.sName.substr(1);
				sTidyParameter		= sTidyParameter.replace(/([A-Z])/g, " $1");
				var oControl		= null;
				switch (sType) {
					case Component_Test_Run.PARAMETER_INTEGER:
						oControl = Control_Field.factory(
							'number',
							{
								sLabel		: sTidyParameter,
								mEditable	: true,
								mMandatory	: !bIsOptional
							}
						);
						break;
						
					case Component_Test_Run.PARAMETER_STRING:
						oControl = Control_Field.factory(
							'textarea',
							{
								sLabel		: sTidyParameter,
								mEditable	: true,
								mMandatory	: !bIsOptional
							}
						);
						break;
						
					case Component_Test_Run.PARAMETER_BOOLEAN:
						oControl = Control_Field.factory(
							'checkbox',
							{
								sLabel		: sTidyParameter,
								mEditable	: true,
								mMandatory	: false
							}
						);
						break;
					
					case Component_Test_Run.PARAMETER_FLOAT:
						oControl = Control_Field.factory(
							'number',
							{
								sLabel			: sTidyParameter,
								mEditable		: true,
								mMandatory		: !bIsOptional,
								iDecimalPlaces	: 2
							}
						);
						break;
				}
				
				if (oControl) {
					oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
					oParameterContainer.appendChild(
						$T.tr(
							$T.th(oControl.getLabel()),
							$T.td(oControl.getElement())
						)
					);
					this._aParameterControls.push(oControl);
				}
			}
			
			if (!this._aParameterControls.length) {
				oParameterContainer.appendChild(
					$T.tr(
						$T.td({colspan: 0}, 
							'No parameters'
						)
					)
				);
			}
		} else {
			// No test selected
			oParameterContainer.appendChild(
				$T.tr(
					$T.td({colspan: 0}, 
						'Please select a test to configure & run'
					)
				)
			);
		}
	},
	
	_clearOutput : function() {
		this.NODE.select('.component-test-run-output-result').first().value	= '';
		this.NODE.select('.component-test-run-output-log').first().value	= '';
	},
	
	_runTest : function(oResponse) {
		if (!oResponse || !Object.isUndefined(oResponse.target)) {
			// Validate parameters
			var aErrors 	= [];
			var aParameters	= [];
			for (var i = 0; i < this._aParameterControls.length; i++) {
				var oControl = this._aParameterControls[i];
				try {
					oControl.validate(false);
					aParameters.push(oControl.getElementValue());
				} catch (oEx) {
					aErrors.push(oEx);
				}
			}
			
			if (aErrors.length) {
				// Invalid parameters
				var oErrorElement = $T.ul();
				for (var i = 0; i < aErrors.length; i++)
				{
					oErrorElement.appendChild($T.li(aErrors[i]));
				}
				
				Reflex_Popup.alert(
					$T.div({class: 'alert-validation-error'},
						oErrorElement
					),
					{sTitle: 'Validation Error', iWidth: 30}
				);
				return;
			}
			
			this._oLoading = new Reflex_Popup.Loading('Running Test...');
			this._oLoading.display();
			
			// Request
			var sClass	= this._oBreadcrumbSelect.getValueAtLevel(0);
			var sMethod = this._oBreadcrumbSelect.getValueAtLevel(1);
			var fnResp	= this._runTest.bind(this);
			//var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Test', 'runTest');
			//fnReq(sClass, sMethod, aParameters);
			var oRequest = new Reflex_AJAX_Request('Test', 'runTest', this._runTest.bind(this));
			oRequest.send(sClass, sMethod, aParameters);
		} else {
			this._oLoading.hide();
			delete this._oLoading;
			
			var oOutputElement = this.NODE.select('.component-test-run-output-result').first();
			if (oResponse.hasException()) {
				// Error
				var oException 			= oResponse.getException();
				oOutputElement.value	= oException.sMessage;
			} else {
				// Show output
				oOutputElement.value = Component_Test_Run._getTidyJSON(Object.toJSON(oResponse.get('mResult')));
			}
			
			var sDebug = oResponse.getDebugLog();
			this.NODE.select('.component-test-run-output-log').first().value = (sDebug ? sDebug : 'No logging output, please make sure the Debug Log is enabled.');
		}
	}
});

Object.extend(Component_Test_Run, {
	PARAMETER_INTEGER	: 'i',
	PARAMETER_STRING	: 's',
	PARAMETER_BOOLEAN	: 'b',
	PARAMETER_FLOAT		: 'f',
	
	createAsPopup : function () {
		var	oComponent	= Component_Test_Run.constructApply($A(arguments)),
			oPopup		= new Reflex_Popup(60);
		
		oPopup.setTitle('Test Interface');
		oPopup.addCloseButton();
		oPopup.setContent(oComponent.getNode());
		
		return oPopup;
	},
	
	_getTidyJSON : function(sText)
	{
		var bInString 		= false;
		var bEscaped		= false;
		var iTabCount		= 0;
		var sFinalString	= '';
		
		for (var i = 0; i < sText.length; i++)
		{
			var sChar = sText[i];
			
			if (sChar === "\\" && !bEscaped)
			{
				bEscaped = true;
				sFinalString += sChar;
				continue;
			}
			else if (sChar === '"' && !bEscaped)
			{
				bInString = !bInString;
				sFinalString += sChar;
				continue;
			}
			else if (!bInString)
			{
				switch (sChar)
				{
					case '{':
					case '[':
						iTabCount++;
						sFinalString += sChar + "\n" + Component_Test_Run._getTabString(iTabCount);
						break;
					case '}':
					case ']':
						iTabCount--;
						sFinalString += "\n" + Component_Test_Run._getTabString(iTabCount) + sChar;
						break;
					case ',':
						sFinalString += sChar + "\n" + Component_Test_Run._getTabString(iTabCount);
						break;
					case ':':
						sFinalString += ' ' + sChar + ' ';
						break;
					default:
						sFinalString += sChar;
				}
			}
			else if (bInString)
			{
				sFinalString += sChar;
			}
			
			if (bEscaped)
			{
				bEscaped = false;
			}
		}
		return sFinalString;
	},
	
	_getTabString : function(iCount)
	{
		var sStr = '';
		for (var i = 0; i < iCount; i++)
		{
			sStr += "\t";
		}
		return sStr;
	}
});
