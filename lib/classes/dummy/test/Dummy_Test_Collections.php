<?php
class Dummy_Test_Collections
{
	public static function initialise()
	{
		// Currently base dummy data is always removed & recreated, but it can persist througout the session by commenting this line
		unset($_SESSION['Dummy_Data']);
		
		if (isset($_SESSION['Dummy_Data']))
		{
			Log::getLog()->log("Base dataset already initialised");
			return;
		}
		
		Log::getLog()->log("Initialising base dataset");
		
		self::_loadFromFile('Dummy_Test_Collections_Constants');
		self::_loadFromFile('Dummy_Test_Collections_Config');
		self::_loadFromFile('Dummy_Test_Collections_Records');
	}
	
	private static function _loadFromFile($sFilename)
	{
		$sFilePath	= dirname(__FILE__)."/{$sFilename}.txt";
		if (!file_exists($sFilePath))
		{
			return;
		}
		
		$aLines			= explode("\n", file_get_contents($sFilePath));
		$sCurrentClass	= null;
		foreach ($aLines as $sLine)
		{
			if (preg_match('/^\s?\/\//', $sLine) || (trim($sLine) == ''))
			{
				// Comment or Empty Line
				continue;
			}
			
			if (preg_match('/\[([\w_]+)\]/', $sLine, $aMatches))
			{
				$sCurrentClass	= $aMatches[1];
			}
			else if ($sCurrentClass !== null)
			{
				$sTableName = strtolower($sCurrentClass);
				if (class_exists("Dummy_{$sCurrentClass}"))
				{
					// Dummy class exists
					$sClass		= "Dummy_{$sCurrentClass}";
					$oRecord 	= new $sClass();
				}
				else
				{
					// No dummy class, must be a constant table
					$oRecord = new Dummy_Constant($sTableName);
				}
				
				$aPropertyNames	= $oRecord->getPropertyNames();
				$aFields 		= explode(',', $sLine);
				foreach ($aFields as $i => $sValue)
				{
					$oRecord->{$aPropertyNames[$i]} = trim($sValue);
				}
				$oRecord->save();
			}
		}
	}
}
?>