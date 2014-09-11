<?php

class Ticketing_Status_Type
{
	protected $arrProperties = array();
	private $statusIds = NULL;

	protected function __construct($arrProps)
	{
		$this->arrProperties = $arrProps;
	}

	public function __get($property)
	{
		switch (strtolower($property))
		{
			case 'id':
			case 'value':
				return $this->arrProperties['Id'];
			case 'name':
				return $this->arrProperties['Name'];
			case 'description':
				return $this->arrProperties['Description'];
			case 'constant':
				return $this->arrProperties['Constant'];
			case 'cssclass':
				return str_replace('_', '-', strtolower($this->arrProperties['Constant']));
		}
	}

	public function getStatusIds()
	{
		return implode(',', $this->listStatusIds());
	}

	public static function listAll()
	{
		static $instances;
		if (!isset($instances))
		{
			$instances = array();
			foreach ($GLOBALS['*arrConstant'][strtolower(__CLASS__)] as $id => $props)
			{
				$props['Id'] = $id;
				$instances[$id] = new self($props);
			}
		}
		return $instances;
	}

	public function listStatusIds()
	{
		if ($this->statusIds === NULL)
		{
			$selSelect = new StatementSelect('ticketing_status', 'id', 'status_type_id = <StatusTypeId>');
			$arrWhere = array('StatusTypeId' => $this->id);
			if (($outcome = $selSelect->Execute($arrWhere)) === FALSE)
			{
				throw new Exception('Failed to find statuses for status type ' . $this->id);
			}
			$this->statusIds = array();
			while($props = $selSelect->Fetch())
			{
				$this->statusIds[] = $props['id'];
			}
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
