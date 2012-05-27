<?php
class PropertyToken {
	private $_dboOwner;
	private $_strProperty;

	function __construct() {
		$this->_dboOwner = null;
		$this->_strProperty = null;
	}

	public static function instance() {
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	function _Property($dboOwner, $strProperty) {
		$this->_dboOwner = $dboOwner;
		$this->_strProperty = $strProperty;
		return $this;
	}

	function __get($strName) {
		// Are we after one of our "magic" variables?
		switch (strtolower($strName)) {
			// The property's value
			case "value":
				if (array_key_exists($this->_strProperty, $this->_dboOwner->_arrProperties)) {
					return $this->_dboOwner->_arrProperties[$this->_strProperty];
				}
				return null;

			// The property's validity
			case "valid":
				return $this->_dboOwner->_arrValid[$this->_strProperty];

			// true if the property has been set at all
			case "isset":
				return isset($this->_dboOwner->_arrProperties[$this->_strProperty]);

			// The property's value with html special chars escaped
			case "htmlsafevalue":
				return htmlspecialchars($this->_dboOwner->_arrProperties[$this->_strProperty], ENT_QUOTES);
		}
		
		// Do we have a Define property by this name?
		$intContext = (int)$this->_dboOwner->_intContext;
		if (isset($this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName])) {
			return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName];
		}
		
		return null;
	}

	function __set($strName, $mixValue) { 
		// Validate
		// TODO
		
		// Set the value & return
		switch (strtolower($strName)) {
			// The property's value
			case "value":
				return (bool)($this->_dboOwner->_arrProperties[$this->_strProperty] = $mixValue);
		}
		
		// Do we have a define property by this name?
		$intContext = (int)$this->_dboOwner->_intContext;
		return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext][$strName] = $mixValue;
	}

	function Trim($strTrimType=null, $strCharList=null) {
		switch (strtolower($strTrimType)) {
			case "ltrim":
				$strFunc = "ltrim";
				break;

			case "rtrim":
				$strFunc = "rtrim";
				break;

			default:
				$strFunc = "trim";
				break;
		}
		
		$arrArgs = array();
		$arrArgs[] = $this->_dboOwner->_arrProperties[$this->_strProperty];
		
		if ($strCharList !== null) {
			$arrArgs[] = $strCharList;
		}
		
		$this->_dboOwner->_arrProperties[$this->_strProperty] = call_user_func_array($strFunc, $arrArgs);
	}

	function RenderInput($intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true, $arrAdditionalArgs=null) {
		echo $this->_RenderIO(RENDER_INPUT, $intContext, $bolRequired, $bolApplyOutputMask, $arrAdditionalArgs);
		
		return array_key_exists($this->_strProperty, $this->_dboOwner->_arrProperties) ? $this->_dboOwner->_arrProperties[$this->_strProperty] : null;
	}

	function RenderOutput($intContext=CONTEXT_DEFAULT) {
		echo $this->_RenderIO(RENDER_OUTPUT, $intContext);
		
		return array_key_exists($this->_strProperty, $this->_dboOwner->_arrProperties) ? $this->_dboOwner->_arrProperties[$this->_strProperty] : null;
	}

	private function _RenderIO($strType, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true, $arrAdditionalArgs=null) {
		$intContext = $this->_CalculateContext($intContext);
		
		// Build up parameters for HtmlElements
		$arrParams = $this->_BuildParams($intContext, $strType, $bolRequired, $bolApplyOutputMask);

		return HtmlElements()->$arrParams['Definition'][$strType.'Type']($arrParams, $arrAdditionalArgs);
	}

	private function _CalculateContext($intCurrentContext, $mixValue=null) {
		// if a value has not been specified then use the current value of the property
		if ($mixValue === null && array_key_exists($this->_strProperty, $this->_dboOwner->_arrProperties)) {
			$mixValue = $this->_dboOwner->_arrProperties[$this->_strProperty];
		}
		
		$intContext = $intCurrentContext;
		
		// work out if the context of the property is subject to its value
		if (is_array($this->_dboOwner->_arrDefine) && array_key_exists($this->_strProperty, $this->_dboOwner->_arrDefine) && array_key_exists('ConditionalContexts', $this->_dboOwner->_arrDefine[$this->_strProperty]) && is_array($this->_dboOwner->_arrDefine[$this->_strProperty]['ConditionalContexts'])) {
			// test each defined condition and use the context of the first one that is found to be true
			foreach ($this->_dboOwner->_arrDefine[$this->_strProperty]['ConditionalContexts'] as $arrCondition) {
				if (IsConditionTrue($mixValue, $arrCondition['Operator'], $arrCondition['Value'])) {
					// set the context to use
					$intContext = $arrCondition['Context'];
					break;
				}
			}
		}

		return $intContext;
	}

	private function _BuildParams($intContext, $strType=RENDER_OUTPUT, $bolRequired=false, $bolApplyOutputMask=true) {
		$arrParams = array();
		if (!$this->_dboOwner->_arrDefine[$this->_strProperty][$intContext]) {
			// A UIAppDocumentation record could not be found for this particular context.  Use defaults
			$strLabel = $this->_TokenizeStudlyString($this->_strProperty);
			$arrParams['Definition'] = array(
				"OutputType" => "Label",
				"InputType" => "InputText",
				"Label" => $strLabel,
				"OutputLabel" => null,
				"OutputMask" => null,
				"Class" => "Default"
			);
		} else {
			// A UIAppDocumentation record was found
			$arrParams['Definition'] = $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext];
		}
		
		$arrParams['Object'] = $this->_dboOwner->_strName;
		$arrParams['Property'] = $this->_strProperty;
		$arrParams['Context'] = $intContext;
		$arrParams['Value'] = array_key_exists($this->_strProperty, $this->_dboOwner->_arrProperties) ? $this->_dboOwner->_arrProperties[$this->_strProperty] : null;
		if (array_key_exists($this->_strProperty, $this->_dboOwner->_arrValid)) {
			$arrParams['Valid'] = $this->_dboOwner->_arrValid[$this->_strProperty];
		}
		$arrParams['Required'] = $bolRequired;
		$arrParams['Type'] = $strType;
		$arrParams['ApplyOutputMask'] = $bolApplyOutputMask;

		// work out the base class to use
		$arrParams['Definition']['BaseClass'] = CLASS_DEFAULT; // Default
		if (array_key_exists('Valid', $arrParams) && $arrParams['Valid'] === false) {
			$arrParams['Definition']['BaseClass'] .= "Invalid"; // DefaultInvalid
		}
		
		return $arrParams;
	}

	function strGetLabel($intContext=CONTEXT_DEFAULT) {
		return $this->_dboOwner->_arrDefine[$this->_strProperty][$intContext]['Label'];
	}
	
	function Render($strOutputMask=null) {
		$strValue = HtmlElements()->ApplyOutputMask($this->_dboOwner->_arrProperties[$this->_strProperty], $strOutputMask);
		
		echo $strValue;
		return $this->_dboOwner->_arrProperties[$this->_strProperty]; 
	}

	private function _Value($intContext=CONTEXT_DEFAULT, $bolUseConditionalContext=false) {
		if ($bolUseConditionalContext) {
			$intContext = $this->_CalculateContext($intContext);
		}

		// build up parameters
		$arrParams = $this->_BuildParams($intContext);
		
		return HtmlElements()->RenderValue($arrParams);
	}

	function RenderValue($intContext=CONTEXT_DEFAULT, $bolUseConditionalContext=false) {
		echo $this->_Value($intContext, $bolUseConditionalContext);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	function AsValue($intContext=CONTEXT_DEFAULT, $bolUseConditionalContext=false) {
		return $this->_Value($intContext, $bolUseConditionalContext);
	}

	function AsInput($intContext=CONTEXT_DEFAULT, $bolRequired=null, $bolApplyOutputMask=true, $arrAdditionalArgs=null) {
		return $this->_RenderIO(RENDER_INPUT, $intContext, $bolRequired, $bolApplyOutputMask, $arrAdditionalArgs);
	}

	function AsOutput($intContext=CONTEXT_DEFAULT) {
		return $this->_RenderIO(RENDER_OUTPUT, $intContext);
	}

	function FormattedValue($intContext=CONTEXT_DEFAULT, $mixArbitrary=null) {
		$intContext = $this->_CalculateContext($intContext);

		// Build up parameters for HtmlElements
		$arrParams = $this->_BuildParams($intContext);
		
		if ($mixArbitrary !== null) {
			// An arbitrary value has been specified, use it instead
			$arrParams['Value'] = $mixArbitrary;
		}
		
		$strFormattedValue = HtmlElements()->BuildOutputValue($arrParams);
		return $strFormattedValue;
	}

	private function _Link($strHref, $intContext = CONTEXT_DEFAULT) {
		$intContext = $this->_CalculateContext($intContext);

		// build up parameters
		$arrParams = $this->_BuildParams($intContext);
		
		return HtmlElements()->RenderLink($arrParams, $strHref);
	}
	
	function AsLink($strHref, $intContext=CONTEXT_DEFAULT) {
		return $this->_Link($strHref, $intContext);
	}

	function RenderLink($strHref, $intContext=CONTEXT_DEFAULT) {
		echo $this->_Link($strHref, $intContext);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	private function _Arbitrary($mixValue, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true) {
		$intContext = $this->_CalculateContext($intContext, $mixValue);

		// build up parameters
		$arrParams = $this->_BuildParams($intContext, $strRenderType, $bolRequired, $bolApplyOutputMask);
		
		// set the arbitrary value as the value to render
		$arrParams['Value'] = $mixValue;
		
		// Render the value
		switch ($strRenderType) {
			case RENDER_INPUT:
				return HtmlElements()->$arrParams['Definition']['InputType']($arrParams);
				break;

			case RENDER_OUTPUT:
				return HtmlElements()->$arrParams['Definition']['OutputType']($arrParams);
				break;

			case RENDER_VALUE:
			default:
				return HtmlElements()->RenderValue($arrParams);
				break;
		}
	}

	function AsArbitrary($mixValue, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true) {
		return $this->_Arbitrary($mixValue, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
	}

	function RenderArbitrary($mixValue, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true) {
		echo $this->_Arbitrary($mixValue, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	private function _Callback($mixCallbackFunc, $arrAdditionalArgs=null, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true) {
		$intContext = $this->_CalculateContext($intContext);

		// build up parameters
		$arrParams = $this->_BuildParams($intContext, $strRenderType, $bolRequired, $bolApplyOutputMask);
		
		// build arguement array for the callback function
		$arrArgs = array($arrParams['Value']);
		if (is_array($arrAdditionalArgs)) {
			$arrArgs = array_merge($arrArgs, $arrAdditionalArgs);
		}
		
		// execute the callback function
		$arrParams['Value'] = call_user_func_array($mixCallbackFunc, $arrArgs);

		// Render the value
		switch ($strRenderType) {
			case RENDER_INPUT:
				return HtmlElements()->$arrParams['Definition']['InputType']($arrParams);
				break;

			case RENDER_OUTPUT:
				return HtmlElements()->$arrParams['Definition']['OutputType']($arrParams);
				break;

			case RENDER_VALUE:
			default:
				return HtmlElements()->RenderValue($arrParams);
				break;
		}
	}

	function AsCallback($strCallbackFunc, $arrAdditionalArgs=null, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true) {
		return $this->_Callback($strCallbackFunc, $arrAdditionalArgs, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
	}

	function RenderCallback($mixCallbackFunc, $arrAdditionalArgs=null, $strRenderType=RENDER_VALUE, $intContext=CONTEXT_DEFAULT, $bolRequired=false, $bolApplyOutputMask=true) {
		echo $this->_Callback($mixCallbackFunc, $arrAdditionalArgs, $strRenderType, $intContext, $bolRequired, $bolApplyOutputMask);
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	private function _Hidden() {
		$arrParams['Object'] = $this->_dboOwner->_strName;
		$arrParams['Property'] = $this->_strProperty;
		$arrParams['Value'] = $this->_dboOwner->_arrProperties[$this->_strProperty];

		// Render the value as hidden
		return HtmlElements()->InputHidden($arrParams);
	}

	function AsHidden() {
		return $this->_Hidden();
	}

	function RenderHidden() {
		echo $this->_Hidden();
		
		return $this->_dboOwner->_arrProperties[$this->_strProperty];
	}

	function Validate($intContext=CONTEXT_DEFAULT) {
		return $this->_dboOwner->ValidateProperty($this->_strProperty, $intContext);
	}

	function SetToInvalid() {
		$this->_dboOwner->_arrValid[$this->_strProperty] = false;
	}

	private function _TokenizeStudlyString($strSource) {
		$intSourceLength = strlen($strSource);
		$intLittleA = ord("a");
		$intLittleZ = ord("z");
		
		$strDestination = $strSource[0];
		for ($i=1; $i < $intSourceLength; $i++) {
			if ($strSource[$i] == "_") {
				// Convert underscores to spaces
				$strDestination .= " ";
				continue;
			}

			$intChar = ord($strSource[$i]);

			if ($intChar < $intLittleA || $intChar > $intLittleZ) {
				// The character is not a lower case letter
				$intPreviousChar = ord($strSource[($i-1)]);
				if ($intPreviousChar >= $intLittleA && $intPreviousChar <= $intLittleZ) {
					// The previous character is a lowercase letter, so place a space here
					$strDestination .= " ";
				}
			}
			
			$strDestination .= $strSource[$i];
		}

		if ($strDestination == strtolower($strDestination)) {
			$strDestination = ucwords($strDestination);
		}
	
		return $strDestination;
	}

	function IsInvalid() {
		return array_key_exists($this->_strProperty, $this->_dboOwner->_arrValid) && $this->_dboOwner->_arrValid[$this->_strProperty] === false;
	}

	function ValidateProperty(&$arrValidationErrors, $bolRequired, $intContext=CONTEXT_DEFAULT, $strValidationFunction=null, $strValidationMessage="<label> is invalid.") {
		if (strlen($this->Value) == 0) {
			if ($bolRequired) {
				$this->SetToInvalid();
				$strLabel = $this->strGetLabel($intContext);
				$arrValidationErrors[] = "$strLabel is required.";
				return false;
			}
			return true;
		}
		if ($strValidationFunction !== null) {
			if (!Validation::$strValidationFunction($this->Value)) {
				$this->SetToInvalid();
				$strLabel = $this->strGetLabel($intContext);
				$arrValidationErrors[] = str_ireplace("<label>", $strLabel, $strValidationMessage);
				return false;
			}
		}
		return true;
	}
}
