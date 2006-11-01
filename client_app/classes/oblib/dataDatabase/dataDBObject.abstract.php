<?php
	
	abstract class dataDBRecord
		{
			
			private $nodeSource;
				
			private $Fields;
			private $Indexes;
		
			function __construct ($nodeName, $nodeSource)
				{
					$this->nodeSource = $nodeSource;
					
					parent::__construct ($nodeName);
				}
		
			public function Field ($fieldName, $fieldType)
				{
				
					if (!class_exists ($fieldType))
						{
							throw new Exception
								(
									'The class `' . $fieldType . '` does not exist and cannot ' .
									'be used as a valid Field'
								);
						}
			
					
					$this->Fields [$fieldName] = new dataSQLField
						(
							$fieldName, 
							$fieldType
						);
				}
		
			public function Index (&$indexNode, $indexValue)
				{
					foreach ($this->Fields AS &$_FIELD)
						{
							if ($_FIELD === $indexNode)
								{
									$this->Indexes [$_FIELD->getName ()] = &$_FIELD;
								}
						}
				}
		}
	
?>