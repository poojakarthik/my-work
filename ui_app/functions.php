<?php
//---------------
// functions.php
//---------------
require_once(TEMPLATE_STYLE_DIR.'html_elements.php');


//------------------------------------------------------------------------//
// RenderHTMLTemplate
//------------------------------------------------------------------------//
/**
 * RenderHTMLTemplate()
 *
 * Render a HTML Element
 *
 * Render a HTML Element by calling the associated function of the
 * HTMLElements class and passing in the array of parameters to use.
 *
 * @param	Array	$arrParams	The parameters to use when building the element
 *
 * @function
 */
function RenderHTMLTemplate($arrParams)
{
	// With overloading
	$rah = new HTMLElements;
	$rah->$arrParams['Template']($arrParams);
	
	// Without overloading
	//HTMLTemplate::$arrParams['Template']($arrParams);
}



//------------------------------------------------------------------------//
// DBO
//------------------------------------------------------------------------//
/**
 * DBO()
 *
 * Returns an instance of the Database Object Framework
 *
 * Returns an instance of the Database Object Framework
 *
 * @return	DBOFramework
 *
 * @method
 */
function DBO()
{
	return Singleton::Instance('DBOFramework');
}


//------------------------------------------------------------------------//
// ImplodeTables
//------------------------------------------------------------------------//
/**
 * ImplodeTables()
 *
 * Takes a Database Definition of a list of tables, and implodes to a FROM string
 *
 * Takes a Database Definition of a list of tables, and implodes to a FROM string
 *
 * @param	array		$arrTables		Table Definition list
 * 
 * @return	string
 *
 * @method
 */
function ImplodeTables($arrTables)
{
	$arrReturn = Array();
	
	// Make sure we are sane
	if (!is_array($arrTables))
	{
		return FALSE;
	}
	
	// Implode
	foreach ($arrTables AS $strAlias=>$arrTable)
	{
		// Name & Alias
		if ($strAlias != $arrTable['Table'])
		{
			$strTable = "{$arrTable['Table']} AS $strAlias";
		}
		else
		{
			$strTable = $arrTable['Table'];
		}
		
		// Index
		if ($arrTable['Index'])
		{
			$strTable .= " USE INDEX ({$arrTable['Index']})";
		}
		
		// add to return array
		$arrReturn[] = $strTable;
	}
	
	return implode(', ', $arrReturn);
}


//------------------------------------------------------------------------//
// AjaxRecieve INCOMPLETE
//------------------------------------------------------------------------//
/**
 * AjaxRecieve()
 * 
 * Function to act as a reciever for AJAX data.  
 * 
 * Function to act as a reciever for AJAX data. Converts to and from JSON format.
 *
 * @return	str				
 *
 * @brokenreturn
 * @comments
 * 
 * @function
 */
function _AjaxRecieve()
{
	$json = new Services_JSON();
	// get the JSON object and decode it into an object
	$input = file_get_contents('php://input', 1000000);
	$input = $json->decode($input);
	
	// expected to return an array of data if a connection was made
	// or false if not
	return $input;
}

//------------------------------------------------------------------------//
// AjaxReply
//------------------------------------------------------------------------//
/**
 * AjaxReply()
 * 
 * Send data via AJAX.
 * 
 * Send data via AJAX.
 *
 * @param	array	$arrReply				The array of data to send
 *
 * @return	void 
 *
 * @function
 */
function _AjaxReply($arrReply)
{
	$json = new Services_JSON();
	echo $json->encode($arrReply);
}

//------------------------------------------------------------------------//
// Config
//------------------------------------------------------------------------//
/**
 * Config()
 *
 * Returns the singleton Config object
 *
 * Returns the singleton Config object
 * Note that this will return a new Config object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	Config object
 *
 * @function
 * 
 */
function Config()
{
	$objConfig = Singleton::Instance('Config');
	return $objConfig;
}

//------------------------------------------------------------------------//
// PropertyToken
//------------------------------------------------------------------------//
/**
 * PropertyToken()
 *
 * Returns the singleton PropertyToken object
 *
 * Returns the singleton PropertyToken object
 * Note that this will return a new PropertyToken object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	PropertyToken object
 *
 * @function
 * 
 */
function PropertyToken()
{
	$objPropertyToken = Singleton::Instance('PropertyToken');
	return $objPropertyToken;
}


?>
