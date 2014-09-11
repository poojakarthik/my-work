<?php
/*
 * Created on 25/11/2010
 *
 * Flate Extraction:
 * 1. Using Inkscape, save the SVG image as a PDF v1.4 file, converting text to paths.
 * 2. Run Cli_App_FlateExtract to extract Flate information
 * 3. Run Cli_App_RawDeflate to decode Flate information into .raw format. 
 */

	require_once dirname(__FILE__) . "/../lib/cli/Cli.php";	

	Cli::execute("Cli_App_FlateExtract");
?>
