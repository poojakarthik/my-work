<?php

class JSON_Handler_Contact_Title extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataSet($bCountOnly=false, $iLimit=0, $iOffset=0, $oSort=null, $oFilter=null)
	{
		try
		{
			$aTitles	= Contact_Title::getAll();
			if ($bCountOnly)
			{
				// Count Only
				return 	array(
							"Success"		=> true,
							"iRecordCount"	=> count($aTitles)
						);
			}
			else
			{
				$aResults	= array();
				foreach ($aTitles as $oTitle)
				{
					$aResults[$oTitle->id]	=	array(
													'id'			=> $oTitle->id,
													'name'			=> $oTitle->name,
													'description'	=> $oTitle->description
												);
				}
				
				// If no exceptions were thrown, then everything worked
				return 	array(
							"Success"		=> true,
							"aRecords"		=> $aResults,
							"iRecordCount"	=> count($aTitles)
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