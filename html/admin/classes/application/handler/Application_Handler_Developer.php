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
			
			$arrFunctions[]	= self::_stdClassFactory(
														array	(
																	'strName'	=> 'Destination Import',
																	'strType'	=> self::URL_TYPE_JS,
																	'strURL'	=> 'JsAutoLoader.loadScript(["popup_destination_import.js","popup_destination_import_manual.js","control_field.js","control_field_select.js","control_field_text_ajax.js","filter.js"], function(){(new Popup_Destination_Import()).display()}, true);'
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
	
	public function MatchDestinationsFromCSV($subPath)
	{
		//require_once(dirname(__FILE__).'/../../json/handler/JSON_Handler_Destination.php');
		
		try
		{
			if (($sFileContents = @file_get_contents(ini_get('upload_tmp_dir').'/'.$_FILES['destinations']['tmp_name'])) === false)
			{
				throw new Exception("There was an error while reading the uploaded file (".$php_errormsg.")");
			}
			$aIgnoreWords	= preg_split('/[\s\,\;\|]+/', $_REQUEST['ignore_words'], null, PREG_SPLIT_NO_EMPTY);
			
			$aResult	= JSON_Handler_Destination::matchDestinationsCSV($sFileContents, ($aIgnoreWords) ? $aIgnoreWords : array());
		}
		catch (Exception $oException)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			$sMessage	= $bUserIsGod ? $oException->getMessage() : 'There was an error accessing the database. Please contact YBS for assistance.';
			$aResult	= array(
							'Success'	=> false,
							'sMessage'	=> $sMessage,
							'Message'	=> $sMessage
						);
		}
		$sJSONResponse	= @JSON_Services::instance()->encode($aResult);
		
		if ($sJSONResponse === false)
		{
			echo "Error producing JSON output: ".$php_errormsg;
		}
		elseif (PEAR::isError($sJSONResponse))
		{
			echo "PEAR Error:\n";
			echo print_r($sJSONResponse, true);
		}
		else
		{
			//echo "Debug:\n";
			//echo print_r($sJSONResponse, true);
			echo $sJSONResponse;
		}
		
		die;
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