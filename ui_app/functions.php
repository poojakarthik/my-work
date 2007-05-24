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



?>
