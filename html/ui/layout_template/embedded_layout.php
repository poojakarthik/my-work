<?php	
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// embedded_layout.php
//----------------------------------------------------------------------------//
/**
 * embedded_layout
 *
 * Layout Template defining how to display a page that is embedded in another page
 *
 * Layout Template defining how to display a page that is embedded in another page
 *
 * @file		embedded_layout.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$arrScript = explode('.php', $_SERVER['REQUEST_URI'], 2);
$intLastSlash = strrpos($arrScript[0], "/");
$strBaseDir = substr($arrScript[0], 0, $intLastSlash + 1);
if ($_SERVER['HTTPS'])
{
	$strBaseDir = "https://{$_SERVER['SERVER_NAME']}$strBaseDir";
}
else
{
	$strBaseDir = "http://{$_SERVER['SERVER_NAME']}$strBaseDir";
}

echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
echo "<title>Flex : Embedded page</title>\n";
echo "<base href='$strBaseDir'/>\n";
$this->RenderCSS();
echo "</head>\n";
echo "<body class='EmbeddedComponent'>\n";

$this->RenderColumn(COLUMN_ONE);

$this->RenderFooter();

?>
