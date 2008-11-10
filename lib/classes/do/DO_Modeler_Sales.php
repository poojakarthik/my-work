<?php

class DO_Modeler_Sales extends DO_Modeler
{
	public function getDoDatabase($strDataSourceName, $dbConnection)
	{
		return new DO_Database_Sales($strDataSourceName, $dbConnection);
	}

}

class DO_Database_Sales extends DO_Database
{
	public function getDoTable($tableName, $className)
	{
		return new DO_Table_Sales($this, $tableName, $className);
	}
}

class DO_Table_Sales extends DO_Table
{
	public function getDoForeignKey($constraint, $fromTable, $fromFields, $toTable, $toFields, $onUpdate, $onDelete)
	{
		return new DO_Foreign_Key_Sales($constraint, $fromTable, $fromFields, $toTable, $toFields, $onUpdate, $onDelete);
	}
	
	public function getDoField($colName, $attributes, $propName)
	{
		return new DO_Field_Sales($this, $colName, $attributes, $propName);
	}

	public function getDoUniqueKey($name, $fields)
	{
		return new DO_Unique_Key($this, $name, $fields);
	}
	
}

class DO_Foreign_Key_Sales extends DO_Foreign_Key
{
	private $determinedName = null;
	
	public function determineName()
	{
		if ($this->determinedName !== null) return $this->determinedName;
		
		$from = DO_Table::getTable($this->from);
		
		$name = $this->name;
		
		$fromFields = array_keys($this->map);
		
		// If there is only one field and it is in the form of xxx_id, use getXxx()
		if (count($fromFields) === 1 && preg_match("/_id\$/", $fromFields[0]))
		{
			$name = DO_Modeler::codifyName(substr($fromFields[0], 0, -3), true);
		}

		// If the name is in the format fk_FROM_xxx_Id
		else if (strpos($name, 'fk_' . $this->from . '_') === 0 && preg_match("/_id\$/", $name))
		{
			$name = DO_Modeler::codifyName(substr(substr($name, strlen('fk_' . $this->from . '_')), 0, -3) , true);
		}
		// Else use the fk name as this should be unique
		else
		{
			$name = DO_Modeler::codifyName($name, true);
		}
		
		$this->determinedName = $name;
		return $this->determinedName;
	}
}

class DO_Unique_Key_Sales extends DO_Foreign_Key
{

}

class DO_Field_Sales extends DO_Field
{
	
}

?>
