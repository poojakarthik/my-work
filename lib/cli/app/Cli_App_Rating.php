<?php
/**
 * Cli_App_Rating
 *
 * @parent	Cli
 */
class Cli_App_Rating extends Cli
{
	const	SWITCH_TEST_RUN			= 't';
	const	SWITCH_CDR_ID			= 'i';
	const	SWITCH_COMPARISON_MODE	= 'c';
	const	SWITCH_RERATE_MODE		= 'r';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_arrArgs = $this->getValidatedArguments();
			
			$iCDRId	= (isset($this->_arrArgs[self::SWITCH_CDR_ID])) ? (int)$this->_arrArgs[self::SWITCH_CDR_ID] : null;
			
			if ($this->_arrArgs[self::SWITCH_COMPARISON_MODE])
			{
				// Comparison Mode (higher priority than re-Rate mode)
				if (!$iCDRId)
				{
					// Full Comparison Run
				}
				$this->_rerateCDR($iCDRId);
			}
			elseif ($this->_arrArgs[self::SWITCH_RERATE_MODE])
			{
				if (!$iCDRId)
				{
					throw new Exception("Re-Rate mode only works on single CDRs");
				}
				$this->_rerateCDR($iCDRId);
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	protected function _rerateCDR($iCDRId)
	{
		// TODO
	}
	
	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_COMPARISON_MODE => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Compares existing CDR Charge values with recalculated values.  No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_RERATE_MODE => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "Peforms Rating on ",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_CDR_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "CARRIER_ID",
				self::ARG_DESCRIPTION	=> "CDR Id (limits run to this CDR only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}
?>