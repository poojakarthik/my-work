<?php

class Ticketing_Config
{
	private static $objInstance = NULL;
	private $arrProperties = array();
	
	protected function __construct()
	{
		// Load the config from the database
		$arrColumns = array(
			'id' 		=> 'id',
			'protocol' 	=> 'protocol',
			'host' 		=> 'host',
			'port' 		=> 'port',
			'username' 	=> 'username',
			'password' 	=> 'password',
		);
		$selConfig = new StatementSelect('ticketing_config', $arrColumns, NULL, 'id DESC', '0,1');
		if (!($outcome = $selConfig->Execute()))
		{
			throw new Exception("Failed to load ticketing configuration. " . ($outcome === FALSE ? $qryQuery->Error() : 'Configuration not defined.'));
		}
		$this->arrProperties = $selConfig->Fetch();
	}

	public static function load()
	{
		if (self::$objInstance == NULL)
		{
			self::$objInstance = new Ticketing_Config();
		}
		return self::$objInstance;
	}

	public function __get($property)
	{
		if (array_key_exists($property, $this->arrProperties))
		{
			return $this->arrProperties[$property];
		}
		return NULL;
	}

	public function __set($property, $value)
	{
		if (array_key_exists($property, $this->arrProperties))
		{
			$this->arrProperties[$property] = $value;
		}
	}

	public function save()
	{
		// Save the value back to the DB
	}
}


?>