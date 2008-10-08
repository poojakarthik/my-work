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

		$unwantedPortabilityOptions = MDB2_PORTABILITY_FIX_CASE;

		$options = array(
			'debug'       => 2,
			'portability' => ((MDB2_PORTABILITY_ALL | $unwantedPortabilityOptions) ^ $unwantedPortabilityOptions),
			'use_transactions' => TRUE,
		);

		if ($bolNewConnection)
		{
			return MDB2::connect(self::dsnForName($strDataSourceName), $options);
		}
		else
		{
			// Implement a 'singleton' function replacement.
			// The MDB2 'singleton' function provides one connection per database, rather than 
			// one per user per database. This would make the admin and flex users share a connection
			// with the privileges of whichever connected first.
			if (!array_key_exists($strDataSourceName, $arrRequestedDSNs))
			{
				$arrRequestedDSNs[$strDataSourceName] = MDB2::connect(self::dsnForName($strDataSourceName), $options);
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
}

?>
