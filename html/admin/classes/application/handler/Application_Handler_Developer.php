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
																	'strURL'	=> 'JsAutoLoader.loadScript(["reflex_slider.js", "reflex_slider_handle.js", "reflex_date_picker.js"], function(){var oDatePicker = new Reflex_Date_Picker(); oDatePicker.show();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'FX',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["developer_animation.js"], function(){var oPopup = new Developer_Animation(25); oPopup.setContent("<div style=\\"margin: 2.5em;\\">Magical animated Popup!</div>"); oPopup.addCloseButton(); oPopup.display();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Tree',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["reflex_style.js", "reflex_fx_reveal.js", "reflex_control.js", "reflex_control_tree.js", "reflex_control_tree_node.js", "reflex_control_tree_node_root.js", "developer_tree.js"], function(){var oPopup = new Developer_Tree(); oPopup.display();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Ticker',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["reflex_control.js", "reflex_control_ticker.js", "developer_ticker.js"], function(){var oPopup = new Developer_Ticker(); oPopup.display();}, true);'
																)
													);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Old Date Time Picker',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["component_date_picker.js", "developer_old_date_picker.js"], function(){var oPopup = new Developer_Old_Date_Picker();}, true);'
																)
													);
			$aScripts	= array	(
									'reflex_control.js',
									'reflex_control_textfield.js',
									//'reflex_control_textarea.js',
									//'reflex_control_fieldset.js',
									'developer_controls.js',
								);
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Form Controls',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["'.implode('","', $aScripts).'"], function(){var oPopup = new Developer_Ticker(); oPopup.display();}, true);'
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