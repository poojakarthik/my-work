<?php
/*
 * Created on 26/06/2008
 *
 */

class Cli_App_RawDeflate extends Cli
{
	const SWITCH_FLATE_FILE_LOCATION = "f";

	private $logFile = NULL;

	function run()
	{
		try
		{
			$this->log("Setting the include path for Zend.");
			set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/' . ".." . '/' . ".." . '/') . '/');

			// Include the Zend library...
			$this->log("Including the Zend PDF library");
			require_once "Zend/Pdf.php";
			require_once "Zend/Pdf/Filter/Compression/Flate.php";

			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$this->log("Processing command line parameters.");

			$strSource = $arrArgs[self::SWITCH_FLATE_FILE_LOCATION];

			// Get the flate files to be deflated
			$arrFiles = array();
			if (is_file($strSource))
			{
				// Store the destination file for the source file
				$arrFiles[$strSource] = $strSource . ".raw";
			}
			else
			{
				// Look for files in the source directory...
				$arrSourceContents = scandir($strSource);
				for ($i = 0, $l = count($arrSourceContents); $i < $l; $i++)
				{
					$strPath = $strSource . '/' . $arrSourceContents[$i];
					// Ignore directories (including source directory '.' and parent directory '..')
					if (is_file($strPath))
					{
						$arrFiles[$strPath] = $strPath . ".raw";
					}
				}
			}

			$this->log("Processing " . count($arrFiles) . " possible flate compressed files.");

			foreach ($arrFiles as $strSource => $strDestination)
			{
				$this->log("Processing flate file: $strSource");
				// Make sure we have enough time to process this...
				set_time_limit(180);

				try
				{
					$deflate = Zend_Pdf_Filter_Compression_Flate::decode(file_get_contents($strSource));
				}
				catch (Exception $e)
				{
					$this->log("Failed to deflate $strSource. It is not a valid flate compressed file. Error message: " . $e->getMessage(), TRUE);
					continue;
				}
				$this->log("Stripping graphics state commands.");
				$deflate = preg_replace("/\/a[0-9]+ gs/", "", $deflate);
				$deflate = preg_replace("/q \/s[0-9]+ gs \/x[0-9]+ Do Q/", null, $deflate);

				$this->log("Stripping extra white spaces/return characters.");
				$deflate = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $deflate);

				$this->log("Writing deflated content to $strDestination.");
				$f = @fopen($strDestination, "w+b");
				@fwrite($f, $deflate);
				@fclose($f);

				$this->log("Validating saved, deflated content.");
				if (!file_exists($strDestination) || file_get_contents($strDestination) != $deflate)
				{
					$this->log("Writing deflated content to $strDestination failed validation. Check the file permissions of the source directory.", TRUE);
					continue;
				}
			}

			$this->log("\nCompleted successfully.\n", FALSE, FALSE, TRUE);

			// Must have worked! Exit with 'OK' code 0
			return 0;
		}
		catch(Exception $exception)
		{
			$this->showUsage("ERROR: " . $exception->getMessage());
		}
	}

	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_FLATE_FILE_LOCATION => array(
				self::ARG_LABEL 		=> "FLATE_FILE_LOCATION",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path to a file containing flate compressed data or directory containing such files",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validReadableFileOrDirectory("%1$s")'
			),

		);
		return $commandLineArguments;
	}

}

?>
