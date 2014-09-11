<?php

class JSON_Handler_State extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		try
		{
			$aStates	= State::getAll();
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> count($aStates)
						);
			}
			else
			{
				$aResults	= array();
				foreach ($aStates as $oState)
				{
					$aResults[$oState->id]	=	array(
													'id'	=> $oState->id,
													'name'	=> $oState->name,
													'code'	=> $oState->code
												);
				}

				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> count($aStates)
						);
			}
		}
		catch (Exception $e)
		{
			return 	array(
						"Success"	=> false,
						"Message"	=> Employee::getForId(Flex::getUserId())->isGod() ? $e->getMessage() : 'There was an error getting the dataset'
					);
		}
	}
}

?>