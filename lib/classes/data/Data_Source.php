<?php

class Data_Source
{
	const PRIMARY_DATA_SOURCE = 'flex';

	public function __construct()
	{
		
	}
	
	public static function get($strDataSourceName=self::PRIMARY_DATA_SOURCE, $bolNewConnection=FALSE, $bolFooble=FALSE)
	{
		static $arrRequestedDSNs;
		if (!isset($arrRequestedDSNs))
		{
			MDB2::classExists('who cares? this forces MDB2 to be loaded!!');
			$arrRequestedDSNs = array();
		}

		$unwantedPortabilityOptions = MDB2_PORTABILITY_FIX_CASE | MDB2_PORTABILITY_EMPTY_TO_NULL;

		$options = array(
			'debug'       => 2,
			'portability' => ((MDB2_PORTABILITY_ALL | $unwantedPortabilityOptions) ^ $unwantedPortabilityOptions),
			'use_transactions' => TRUE,
		);

		if ($bolNewConnection)
		{
			// Don't specify the name of the data source.  It will be anonomous
			$objMDB2 = MDB2::connect(self::dsnForName($strDataSourceName), $options);
			if (PEAR::isError($objMDB2))
			{
				throw new Exception("Failed to connect to data source $strDataSourceName: " . $objMDB2->getMessage());
			}
			return new Data_Source_MDB2_Wrapper($objMDB2, NULL);
		}
		else
		{
			// Implement a 'singleton' function replacement.
			// The MDB2 'singleton' function provides one connection per database, rather than 
			// one per user per database. This would make the admin and flex users share a connection
			// with the privileges of whichever connected first.
			if (!array_key_exists($strDataSourceName, $arrRequestedDSNs))
			{
				$arrRequestedDSNs[$strDataSourceName] = new Data_Source_MDB2_Wrapper(MDB2::connect(self::dsnForName($strDataSourceName), $options), $strDataSourceName);
				if (PEAR::isError($arrRequestedDSNs[$strDataSourceName]))
				{
					throw new Exception("Failed to connect to data source $strDataSourceName: " . $arrRequestedDSNs[$strDataSourceName]->getMessage());
				}
			}
			return $arrRequestedDSNs[$strDataSourceName];
		}
	}
	
	private static function dsnForName($strDataSourceName)
	{
		return array(
			'phptype'	=> $GLOBALS['**arrDatabase'][$strDataSourceName]['Type'],
			'username'	=> $GLOBALS['**arrDatabase'][$strDataSourceName]['User'],
			'password'	=> $GLOBALS['**arrDatabase'][$strDataSourceName]['Password'],
			'hostspec'	=> $GLOBALS['**arrDatabase'][$strDataSourceName]['URL'],
			'database'	=> $GLOBALS['**arrDatabase'][$strDataSourceName]['Database'],
		);
	}
	
	public static function listDataSources()
	{
		return array_keys($GLOBALS['**arrDatabase']);
	}
	
	// Returns TRUE if a data source definition exists for $strDataSourceName, else returns FALSE
	public static function dsnExists($strDataSourceName)
	{
		return array_key_exists($strDataSourceName, $GLOBALS['**arrDatabase']);
	}
}

?>
