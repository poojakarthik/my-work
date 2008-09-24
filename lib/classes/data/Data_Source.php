<?php

class Data_Source
{
	const PRIMARY_DATA_SOURCE = 'flex';

	public function __construct()
	{
		
	}
	
	public static function get($strDataSourceName=self::PRIMARY_DATA_SOURCE, $bolNewConnection=FALSE)
	{
		$options = array(
			'debug'       => 2,
			'portability' => (MDB2_PORTABILITY_ALL - MDB2_PORTABILITY_FIX_CASE),
			'use_transactions' => TRUE,
		);
		
		if ($bolNewConnection)
		{
			return MDB2::connect(self::dsnForName($strDataSourceName), $options);
		}
		else
		{
			return MDB2::singleton(self::dsnForName($strDataSourceName), $options);
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
