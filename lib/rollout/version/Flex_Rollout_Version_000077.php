<?php

/**
 * Version 77 of database update.
 *
 * Adds two options to admin interface,
 * 1. ability to upload an advertisement image
 * 2. ability to set a url for an advertisement image
 */

class Flex_Rollout_Version_000077 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		 
		$strSQL = "ALTER TABLE CustomerGroup 
		ADD customer_advert_image BLOB NULL COMMENT 'this field is used to store the raw image data for an advertisement in the customer interface',
		ADD customer_advert_image_type CHAR( 9 ) NULL COMMENT 'this field sets the image type for the advertisement image uploaded, e.g. image/jpeg',
		ADD customer_advert_url VARCHAR( 255 ) NULL COMMENT 'this url is used for the advertisement image';";

		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table survey' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE CustomerGroup
		DROP customer_advert_image,
		DROP customer_advert_image_type,
		DROP customer_advert_url;";
	}
	
	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>
