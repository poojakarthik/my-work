<?php
class DOM_Factory {
	protected $_oDOMDocument;

	public function __construct(DOMDocument $oDOMDocument=null) {
		$this->_oDOMDocument	= (isset($oDOMDocument)) ? $oDOMDocument : new DOMDocument('1.0', 'UTF-8');
	}

	public function getDOMDocument() {
		return $this->_oDOMDocument;
	}

	protected function __elementFactory($sElement) {
		//_log('Building Element: {$sElement}');
		$oDOMElement		= $this->_oDOMDocument->createElement($sElement);

		$aArgs	= func_get_args();
		array_shift($aArgs);

		foreach ($aArgs as $iIndex=>$mArg) {
			if (is_object($mArg) && $mArg instanceof DOMNode) {
				// DOM Node
				//_log('Adding DOM Node...');
				$oDOMElement->appendChild($mArg);
			} else if ($iIndex === 0 && (is_array($mArg) || is_object($mArg))) {
				// Attributes
				//_log('Adding Attributes...');
				$aAttributes	= (array)$mArg;
				foreach ($aAttributes as $sKey=>$mValue) {
					if (is_string($sKey)) {
						//_log("Adding Attribute '{$sKey}' = '{$mValue}'");
						$oDOMElement->setAttribute($sKey, $mValue);
					}
				}
			} elseif (isset($mArg)) {
				//_log("Adding mixed content: '{$mArg}'");
				$oDOMElement->appendChild($this->_oDOMDocument->createTextNode($mArg));
			}
		}

		return $oDOMElement;
	}

	protected function __entityFactory($sEntity) {
		return $this->_oDOMDocument->createEntityReference($sEntity);
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