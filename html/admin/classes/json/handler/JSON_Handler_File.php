<?php

class JSON_Handler_File extends JSON_Handler
{
	
	public function convertPDFToRaw($oRequest) {

		// Input file.
		$sName		= $oRequest->sName;
		$mContent	= $oRequest->mContent;
		$sMimeType	= $oRequest->sMimeType;

		// Conversion
		$sFlateContent = self::_extractFlateFromPDF(file_get_contents($mContent));
		list($sRawData, $sDecodedData) = self::_extractRawPDFCommandsFromFlate($sFlateContent);

		// Output
		return array(
			'sName' => $sName,
			'mContent' => $mContent,
			'mContent.length' => strlen($mContent),
			'sMimeType' => $sMimeType,
			'sRawData' => base64_encode($sRawData),
			'sDecodedData' => "data:file/flate-decoded;base64," . base64_encode($sDecodedData),
			'sFlateContent' => "data:file/flate-encoded;base64," . base64_encode($sFlateContent)
		);
	}

	protected static function _extractFlateFromPDF($mData) {
		$iCount					= preg_match_all("|stream(.*)endstream|Us", $mData, $aMatches, PREG_PATTERN_ORDER);
		$sFlateContent			= "";

		for ($i=0; $i<$iCount; $i++) {
			$sFlateContent .= trim($aMatches[1][$i]);
		}
		return $sFlateContent;
	}

	protected static function _extractRawPDFCommandsFromFlate($mData) {

		// For inner requires.
		set_include_path(get_include_path() . PATH_SEPARATOR . Flex::getBase() . 'lib');

		Flex::requireOnce('lib/Zend/Pdf.php');
		Flex::requireOnce('lib/Zend/Pdf/Filter/Compression/Flate.php');
		
		try {

			$sDecodedData = Zend_Pdf_Filter_Compression_Flate::decode($mData);

			// Stripping graphics state commands.
			$sRawData = preg_replace("/\/a[0-9]+ gs/", "", $sDecodedData);
			$sRawData = preg_replace("/q \/s[0-9]+ gs \/x[0-9]+ Do Q/", null, $sRawData);

			// Stripping extra white spaces/return characters.
			$sRawData = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $sRawData);

		} catch (Exception $oException) {
			return $oException->getMessage();
		}

		return array($sRawData, $sDecodedData);
	}

}

?>