<?

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../../' . 'flex.require.php';

class Cli_App_GenerateLateNotices extends Cli
{
	const SWITCH_FILES_BASE_PATH = "f";
	const SWITCH_LATE_NOTICE_TYPE = "t";

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$strBasePath = $arrArgs[self::SWITCH_FILES_BASE_PATH];
			$intNoticeType = constant('LETTER_TYPE_' . $arrArgs[self::SWITCH_LATE_NOTICE_TYPE]);

			$this->log("Generating ". GetConstantDescription($intNoticeType, "LetterType"). "s");
			$this->log("Outputting files to " . $strBasePath);

			$mixResult = GenerateLatePaymentNotices($intNoticeType, $strBasePath);

			if ($mixResult === FALSE)
			{
				$this->log("ERROR: Generating late notices failed, unexpectedly", TRUE);
				return 1;
			}
			else
			{
				$this->log("Notices successfully generated  : {$mixResult['Successful']}");
				$this->log("Notices that failed to generate : {$mixResult['Failed']}");
			}

			$this->log("Finished.");
			return 0;
		}
		catch(Exception $exception)
		{
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	function getCommandLineArguments()
	{
		$commandLineArguments = array(
			self::SWITCH_FILES_BASE_PATH => array(
				self::ARG_LABEL 		=> "FILES_BASE_BATH",
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the driectory under which the LATE_NOTICE_TYPE/xml/date/*.xml files are to be created",
				self::ARG_DEFAULT 	=> FILES_BASE_PATH,
				self::ARG_VALIDATION 	=> 'Cli::_validDir("%1$s", TRUE)'
			),

			self::SWITCH_LATE_NOTICE_TYPE => array(
				self::ARG_LABEL 		=> "LATE_NOTICE_TYPE",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "the type of late notice to be generated (OVERDUE, SUSPENSION or FINAL_DEMAND)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validInArray("%1$s", array("OVERDUE","SUSPENSION","FINAL_DEMAND"))'
			),

		);
		return $commandLineArguments;
	}

}


?>
