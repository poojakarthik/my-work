<?php

class Ticketing_Status_Type_Conglomerate extends Ticketing_Status_Type
{
	private $arrContituents = NULL;

	private $statusIds = NULL;
	private $id = NULL;

	const TICKETING_STATUS_TYPE_CONGLOMERATE_OPEN_OR_PENDING = 'open|pending'; 

	protected function __construct()
	{
		$this->arrConstituents = func_get_args();
		$this->id = array_shift($this->arrConstituents);
	}

	public function __get($property)
	{
		$property = strtolower($property);
		$getProp = ($property == 'constant' || $property == 'cssclass') ? 'name' : $property;
		$constituentGets = array();
		foreach ($this->arrConstituents as $status)
		{
			$constituentGets[] = $status->{$getProp};
		}

		switch ($property)
		{
			case 'id':
			case 'value':
				return $this->id;
			case 'name':
				return implode(' or ', $constituentGets);
			case 'description':
				return implode(' or ', $constituentGets);
			case 'constant':
				return strtoupper(__CLASS__ . '_' . str_replace(' ', '_', implode('_', $constituentGets)));
			case 'cssclass':
				return str_replace('_', '-', strtolower(__CLASS__ . '-' . str_replace(' ', '-', implode('-', $constituentGets))));
		}
	}

	public static function listAll()
	{
		static $instances;

		if (!defined($instances))
		{
			$instances = parent::listAll();
			$instances[self::TICKETING_STATUS_TYPE_CONGLOMERATE_OPEN_OR_PENDING] =  
				new self(	self::TICKETING_STATUS_TYPE_CONGLOMERATE_OPEN_OR_PENDING, 
							$instances[TICKETING_STATUS_TYPE_OPEN], 
							$instances[TICKETING_STATUS_TYPE_PENDING]);
		}
		return $instances;
	}

	public function listStatusIds()
	{
		if ($this->statusIds === NULL)
		{
			$this->statusIds = array();
			foreach ($this->arrConstituents as $status)
			{
				$this->statusIds = array_merge($this->statusIds, $status->listStatusIds());
			}
			$this->statusIds = array_unique($this->statusIds);
		}
		return $this->statusIds;
	}

	public static function getForId($id)
	{
		$instances = self::listAll();
		return $instances[$id];
	}

}

?>
