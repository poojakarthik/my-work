<?php

define ('RESOURCE_BASE_PATH', SHARED_BASE_PATH . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'resource' .   DIRECTORY_SEPARATOR);
define ('COMMON_RESOURCE_BASE_PATH', SHARED_BASE_PATH . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'resource' .   DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR);


class Flex_Pdf_Resource_Manager
{
	const RESOURCE_BASE_PATH 		= RESOURCE_BASE_PATH;
	const COMMON_RESOURCE_BASE_PATH = COMMON_RESOURCE_BASE_PATH;

	private static $handlers = array();
	
	private $cache = array();

	private $customerGroup = NULL;
	private $effectiveDate = NULL;
	private $xsltString = NULL;

	private function __construct($customerGroup, $effectiveDate)
	{
		$this->customerGroup = $customerGroup;
		$this->effectiveDate = $effectiveDate;
	}
	
	public function getXSLT($documentType)
	{
		if ($this->xsltString === NULL)
		{
			// TODO: Need to do the database work here!!
			$this->xsltString = "";
			echo "<hr>Use of database xsl documents is not implemented!!!!<hr>";
			exit;
		}
		return $this->xsltString;
	}

	public function getResourcePath($relativePath)
	{
		$relativePath  = trim($relativePath);
		if (!array_key_exists($relativePath, $this->cache))
		{
			// if the path refers to a database resource...
			if (strpos($relativePath, "fdbp://") === 0)
			{
				// TODO: Make this go to the database for the actual path for the resource 
				echo "<hr>Use of database resource paths is not implemented!!!!<hr>";
				exit;
			}
			// if the path is relative (as in the case on font files)...
			else if ($relativePath == "." || file_exists(self::COMMON_RESOURCE_BASE_PATH . "/" . $relativePath))
			{
				// make absolute...
				$this->cache[$relativePath] = self::COMMON_RESOURCE_BASE_PATH . "/" . $relativePath;
			}
			// the path must be absolute already...
			else
			{
				$this->cache[$relativePath] = $relativePath;
			}
		}
		return $this->cache[$relativePath];
	}	

	public function getResourceManager($customerGroup, $effectiveDate)
	{
		if (!array_key_exists($customerGroup, self::$handlers))
		{
			self::$handlers[$customerGroup] = array();
		}
		if (!array_key_exists($effectiveDate, self::$handlers[$customerGroup]))
		{
			self::$handlers[$customerGroup][$effectiveDate] = new Flex_Pdf_Resource_Manager($customerGroup, $effectiveDate);
		}
		return self::$handlers[$customerGroup][$effectiveDate];
	}
}

?>
