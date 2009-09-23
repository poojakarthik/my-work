<?php
class Application_Handler_Developer extends Application_Handler
{
	const	URL_TYPE_JS		= 'onclick';
	const	URL_TYPE_HREF	= 'href';
	
	// View the Developer Page
	public function ViewList($subPath)
	{
		$bolIsGOD	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		try
		{
			// Build list of Developer Functions
			$arrFunctions	= array();
			
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Operation-based Permission Tests',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_OperationPermission();'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'AJAX Dataset & Pagination Test (Cached)',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_DatasetPagination(1);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'AJAX Dataset & Pagination Test (Uncached)',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_DatasetPagination(0);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Tab Control',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'new Developer_TabGroup();'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Datepicker',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["date.js", "reflex_date_format.js", "reflex_date_picker.js"], function(){alert("Callback");var oDatePicker = new Reflex_Date_Picker();alert("Loaded");oDatePicker.show();alert("Showing?");}, false);'
																)
													);
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['arrFunctions']		= $arrFunctions;
			
			$this->LoadPage('developer_console', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	protected static function _stdClassFactory($arrProperties)
	{
		$objStdClass	= new stdClass();
		
		foreach ($arrProperties as $strName=>$mixValue)
		{
			$objStdClass->{$strName}	= $mixValue;
		}
		
		return $objStdClass;
	}
}
?>