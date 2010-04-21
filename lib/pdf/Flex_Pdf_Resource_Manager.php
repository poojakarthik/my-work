<?php

if (!defined('SHARED_BASE_PATH'))
{
	define("SHARED_BASE_PATH", realpath(dirname(__FILE__) . '/' . ".." . '/'));
}

define ('COMMON_RESOURCE_BASE_PATH', SHARED_BASE_PATH . '/' . 'template' . '/' . 'resource' .   '/' . 'common' . '/');


class Flex_Pdf_Resource_Manager
{
	const COMMON_RESOURCE_BASE_PATH = COMMON_RESOURCE_BASE_PATH;

	private static $handlers = array();
	
	private $cache = array();
	private $resources = NULL;

	private $customerGroup = NULL;
	private $effectiveDate = NULL;
	private $xsltStrings = array();

	private function __construct($customerGroupId, $generationDate)
	{
		$this->customerGroup = $customerGroupId;
		$this->effectiveDate = $generationDate;
	}
	
	public function getXSLT($documentType)
	{
		// If we haven't already fetched the xslt...
		if (!array_key_exists($documentType, $this->xsltStrings))
		{
			// Need to run the following: -
			/*
			 $sql =  "
				  select Source
				    from DocumentTemplate
				   where CustomerGroup=<CustomerGroup>
				     and TemplateType=<TemplateType>
				     and EffectiveOn is not null
				     and EffectiveOn <= <GenerationDate>
				order by EffectiveOn desc
				   limit 0, 1
				";
			*/
			$strWhere = "CustomerGroup = <CustomerGroup> AND TemplateType = <TemplateType> AND EffectiveOn IS NOT NULL AND EffectiveOn <= <GenerationDate>";
			$arrWhere = Array(	"CustomerGroup"		=> $this->customerGroup, 
								"TemplateType"		=> $documentType, 
								"GenerationDate"	=> $this->effectiveDate);

			$selDocumentTemplate = new StatementSelect("DocumentTemplate", "Source", $strWhere, "CreatedOn desc", "0, 1");

			$mixResult = $selDocumentTemplate->Execute($arrWhere);

			if ($mixResult === FALSE)
			{
				throw new Exception("An error occurred when fetching document type '$documentType' template for Customer Group Id '$this->customerGroup' on generation date '$this->effectiveDate'.");
			}
			else if (!$mixResult)
			{
				throw new Exception("No template found for document type '$documentType' for Customer Group Id '$this->customerGroup' on generation date '$this->effectiveDate'.");
			}
			else
			{
				$arrRecordSet = $selDocumentTemplate->FetchAll();
				$this->xsltStrings[$documentType] = $arrRecordSet[0]['Source'];
			}
		}
		// Return the cached xslt
		return $this->xsltStrings[$documentType];
	}
	
	private function loadResources()
	{
		if ($this->resources === NULL) 
		{
			/*
			 $sql =  "
				SELECT RT.PlaceHolder PlaceHolder, DR.Id Id, FT.Extension Extension
				FROM DocumentResource DR
				INNER JOIN FileType FT on DR.FileType = FT.Id
				INNER JOIN DocumentResourceType RT on DR.Type = RT.Id
				WHERE DR.createdOn = (
					SELECT max(DR2.CreatedOn) FROM DocumentResource DR2
					WHERE <GenerationDate> between StartDatetime and EndDatetime
					AND DR2.CustomerGroup = <CustomerGroup>
					AND DR2.Type = DR.Type
					group by Type)";
			*/

			$strTables = "DocumentResource DR
				INNER JOIN FileType FT on DR.FileType = FT.Id
				INNER JOIN DocumentResourceType RT on DR.Type = RT.Id";

			$arrColumns = Array( "PlaceHolder" 	=> "RT.PlaceHolder",
			 					 "Id" 			=> "DR.Id",
			 					 "Extension" 	=> "FT.Extension");

			$strWhere = "DR.CreatedOn = (
					SELECT max(DR2.CreatedOn) FROM DocumentResource DR2
					WHERE <GenerationDate> between StartDatetime and EndDatetime
					AND DR2.CustomerGroup = <CustomerGroup>
					AND DR2.Type = DR.Type
					group by Type)";

			$arrWhere = Array(	"CustomerGroup"		=> $this->customerGroup, 
								"GenerationDate"	=> $this->effectiveDate);

			$selDocumentResources = new StatementSelect($strTables, $arrColumns, $strWhere);

			$mixResult = $selDocumentResources->Execute($arrWhere);

			if ($mixResult === FALSE)
			{
				throw new Exception("An error occurred when fetching template resources for Customer Group Id '$this->customerGroup' on generation date '$this->effectiveDate'.");
			}

			$arrRecordSet = $selDocumentResources->FetchAll();

			$this->resources = array();

			for ($i = 0; $i < $mixResult; $i++)
			{
				$this->resources[strtolower($arrRecordSet[$i]['PlaceHolder'])] = Array("Id" => $arrRecordSet[$i]['Id'], "Extension" => $arrRecordSet[$i]['Extension']);
			}
		}
	}

	public function getResourcePath($relativePath)
	{
		// Strip off any whitespace (invalid at start and end of paths)
		$relativePath  = trim($relativePath);
		
		// If we haven't already found this, go look for it...
		if (!array_key_exists($relativePath, $this->cache))
		{
			// if the path refers to a database resource...
			if (strpos($relativePath, "fdbp://") === 0)
			{
				// Ensure the resources for the customer group and effective date have been loaded
				$this->loadResources();

				require_once(dirname(__FILE__) . '/' . "Flex_Database_Protocol.php");

				// Get the placeholder from the relative path
				$placeholder = strtolower(substr($relativePath, 7));
				
				// Construct the absolute path if we have a match
				if (array_key_exists($placeholder, $this->resources))
				{
					$this->cache[$relativePath] = Flex_Database_Protocol::FDBP_PROTOCOL . "://" . $this->customerGroup . "/" 
						. $placeholder . "/" . $this->resources[$placeholder]["Id"] . "." . $this->resources[$placeholder]["Extension"];
				}
				// ... else throw an exception to show that we didn't find the resource
				else
				{
					throw new Exception("No resource found for '$relativePath' for Customer Group Id '$this->customerGroup' on generation date '$this->effectiveDate'.");
				}

				/*
				// Make sure it exits
				if (!file_exists($this->cache[$relativePath]) || !is_file($this->cache[$relativePath]))
				{
					throw new Exception("Resource file '" . $this->cache[$relativePath] . "' does not exist for resource '$relativePath' and Customer Group Id '$this->customerGroup'.");
				}

				// Ensure that we can read it
				if (!is_readable($this->cache[$relativePath]))
				{
					throw new Exception("Resource file '" . $this->cache[$relativePath] . "' is unreadable for resource '$relativePath' and Customer Group Id '$this->customerGroup'.");
				}
				*/
			}
			// if the path is relative (as in the case on font files)...
			else if ($relativePath == "." || file_exists(self::COMMON_RESOURCE_BASE_PATH . $relativePath))
			{
				// make absolute...
				$this->cache[$relativePath] = self::COMMON_RESOURCE_BASE_PATH . $relativePath;

				// Check that the file exists
				if (!file_exists($this->cache[$relativePath]) || !is_file($this->cache[$relativePath]))
				{
					throw new Exception("No resource file found for '$relativePath'.");
				}

				// Ensure that we can read it
				if (!is_readable($this->cache[$relativePath]))
				{
					throw new Exception("Resource file '" . $this->cache[$relativePath] . "' is unreadable.");
				}
			}
			// the path must be absolute already...
			else
			{
				// We could try to copy the file from the absolute (possibly remote) location to a tmp local directory.
				// This would allow us to handle retrieval problems at this point.
				$this->cache[$relativePath] = $relativePath;

				// Make sure it exits
				if (!file_exists($relativePath))
				{
					throw new Exception("Resource file '$relativePath' does not exist.");
				}

				// Ensure that we can read it
				if (!is_readable($relativePath))
				{
					throw new Exception("Resource file '$relativePath' is unreadable.");
				}
			}
		}
		
		// Return the cached absolute path
		return $this->cache[$relativePath];
	}	

	public static function getResourceManager($customerGroup, $effectiveDate)
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
