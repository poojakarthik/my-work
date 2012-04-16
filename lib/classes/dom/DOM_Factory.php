<?php
class DOM_Factory {
	protected $_oDOMDocument;

	public function __construct(DOMDocument $oDOMDocument=null) {
		$this->_oDOMDocument = (isset($oDOMDocument)) ? $oDOMDocument : new DOMDocument('1.0', 'UTF-8');
	}

	public function getDOMDocument() {
		return $this->_oDOMDocument;
	}

	public function __elementFactory($sElement, $mAttributes=null) {
		//_log('Building Element: {$sElement}');
		$oDOMElement = $this->_oDOMDocument->createElement($sElement);

		// Optional Parameters
		$aChildren = func_get_args();
		array_shift($aChildren);
		if (!is_array($mAttributes) && !(is_object($mAttributes) && get_class($mAttributes) === 'stdClass')) {
			// No attributes
			$mAttributes = null;
		} else {
			// Attributes
			array_shift($aChildren);
		}

		// Attributes
		if ($mAttributes) {
			//_log('Adding Attributes...');
			$aAttributes = (array)$mAttributes;
			foreach ($aAttributes as $sKey=>$mValue) {
				if (is_string($sKey)) {
					//_log("Adding Attribute '{$sKey}' = '{$mValue}'");
					if ($mValue === true) {
						// Boolean declaration
						$oDOMElement->setAttribute($sKey, $sKey);
					} else if ($mValue !== false) {
						// Anything other than a boolean
						$oDOMElement->setAttribute($sKey, $mValue);
					}
				}
			}
		}

		// Children
		//_log('Adding Children...');
		$this->_appendChildren($oDOMElement, $aChildren);

		return $oDOMElement;
	}

	public function __entityFactory($sEntity) {
		return $this->_oDOMDocument->createEntityReference($sEntity);
	}

	public function __fragment() {
		//_log('Building Document Fragment');
		$oFragment = $this->_oDOMDocument->createDocumentFragment();

		//_log('Adding Children...');
		$aChildren = func_get_args();
		$this->_appendChildren($oFragment, $aChildren);

		return $oFragment;
	}

	protected function _appendChildren($oDOMNode, $aChildren) {
		foreach ($aChildren as $iIndex=>$mChild) {
			if (is_object($mChild) && $mChild instanceof DOMNode) {
				// DOM Node
				//_log('Adding DOM Node...');
				$oDOMNode->appendChild($mChild);
			} elseif (isset($mChild)) {
				//_log("Adding mixed content: '{$mChild}'");
				$oDOMNode->appendChild($this->_oDOMDocument->createTextNode($mChild));
			}
		}
	}

	public function __call($sMethod, $aArgs) {
		if (preg_match('/^\_[a-z0-9]/i', $sMethod)) {
			// _ prefix: Entity
			array_unshift($aArgs, substr($sMethod, 1));
			return call_user_func_array(array($this, '__entityFactory'), $aArgs);
		} else {
			// No prefix: Element
			array_unshift($aArgs, $sMethod);
			return call_user_func_array(array($this, '__elementFactory'), $aArgs);
		}
	}
}
?>