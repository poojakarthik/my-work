<?php

abstract class DO_Sales extends DO_Base
{
	const DEFAULT_DATA_SOURCE_NAME = 'sales';

	protected static function getDataSourceName()
	{
		return self::DEFAULT_DATA_SOURCE_NAME;
	}
}

?>
