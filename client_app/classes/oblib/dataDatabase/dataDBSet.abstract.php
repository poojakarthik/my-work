<?php
	
	abstract class dataDBSet extends dataCollation
		{
		
			function __construct ($nodeName, $nodeType)
				{
					if (!class_exists ($nodeType))
						{
							throw new Exception
								(
									'The class `' . $nodeType . '` does not exist'
								);
						}
						
					if (!is_subclass_of ($nodeType, 'data'))
						{
							throw new Exception
								(
									'The class `' . $nodeType . '` does not extend the class `data`'
								);
						}
						
					parent::__construct
						(
							$nodeName, 
							$nodeType
						);
				}
		
			abstract public function ItemID ($itemIndex);
		}
	
?>