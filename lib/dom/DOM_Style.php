<?php
class DOM_Style {
	const	DEBUG	= false;

	protected	$_oDOMDocument;
	protected	$_oDOMXPath;
	
	public function __construct(DOMDocument $oDOMDocument) {
		$this->_oDOMDocument	= $oDOMDocument;
		$this->_oDOMXPath		= new DOMXPath($oDOMDocument);
	}

	public function apply($aStyleMap) {
		$aStyleMap	= array_reverse($aStyleMap, true);	// Apply the styles in reverse order, so we can emulate CSS specificity

		// Iterate over our selectors
		Log::getLog()->logIf(self::DEBUG, "Styling the Document...");
		foreach ($aStyleMap as $sXPathSelector=>$sDeclarationBlock) {
			$this->applyStyle($sXPathSelector, $sDeclarationBlock);
			Log::getLog()->logIf(self::DEBUG, '');
		}
	}

	public function applyStyle($sXPathSelector, $sDeclarationBlock) {
		Log::getLog()->logIf(self::DEBUG, "\t{$sXPathSelector} {");
		
		// Clean up the declaration block
		$sDeclarationBlock	= trim(preg_replace('/\r?\n+/', ' ', preg_replace('/[\t]/', '', $sDeclarationBlock)));

		Log::getLog()->logIf(self::DEBUG, "\t\t$sDeclarationBlock");
		Log::getLog()->logIf(self::DEBUG, "\t}");

		// Iterate over our matched Nodes
		$oNodes	= $this->_oDOMXPath->query($sXPathSelector, $this->_oDOMDocument);
		Log::getLog()->logIf(self::DEBUG, "\tFound {$oNodes->length} Nodes that match {$sXPathSelector}");
		foreach ($oNodes as $oNode) {
			// Prepend the styles to this Node
			Log::getLog()->logIf(self::DEBUG, "\t\tApplying styles to {$oNode->tagName} element");
			$oNode->setAttribute('style',
				($oNode->hasAttribute('style')) ? $sDeclarationBlock.$oNode->getAttribute('style') : $sDeclarationBlock
			);
		}
	}

	public static function style(DOMDocument $oDOMDocument, $aStyleMap) {	
		$oInstance	= new self($oDOMDocument);
		$oInstance->apply($aStyleMap);
		return $oDOMDocument;
	}
}
?>