<?php

define ('RESOURCE_BASE_PATH', SHARED_BASE_PATH . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'resource' .   DIRECTORY_SEPARATOR);
define ('COMMON_RESOURCE_BASE_PATH', SHARED_BASE_PATH . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'resource' .   DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR);


class Flex_Pdf_Resource_Manager
{
	const RESOURCE_BASE_PATH 		= RESOURCE_BASE_PATH;
	const COMMON_RESOURCE_BASE_PATH = COMMON_RESOURCE_BASE_PATH;

	private static $handlers = array();
	
	private $cache = array();
	private $resources = NULL;

	private $customerGroup = NULL;
	private $effectiveDate = NULL;
	private $xsltString = NULL;

	private function __construct($customerGroupId, $generationDate)
	{
		$this->customerGroup = $customerGroupId;
		$this->effectiveDate = $generationDate;
	}
	
	public function getXSLT($documentType)
	{
		// If we haven't already fetched the xslt...
		if ($this->xsltString === NULL)
		{
			// TODO: Need to do the database work here!!
			
			// Need to run the following: -
			/*
			 $sql =  "
				  select Source
				    from DocumentTemplate
				   where CustomerGroup=$this->customerGroup
				     and TemplateType=$templateType
				     and EffectiveOn is not null
				     and EffectiveOn <= '$this->effectiveDate'
				order by EffectiveOn desc
				   limit 0, 1
				";
			*/
			$strWhere = "CustomerGroup = <CustomerGroup> AND TemplateType = <TemplateType> AND EffectiveOn IS NOT NULL AND EffectiveOn <= <GenerationDate>";
			$arrWhere = Array(	"CustomerGroup"		=> $this->customerGroup, 
								"TemplateType"		=> $templateType, 
								"GenerationDate"	=> $this->effectiveDate);

			$selDocumentTemplate = new StatementSelect("DocumentTemplate", "Source", $strWhere, "EffectiveOn desc", "0, 1");

			$mixResult = $selDocumentTemplate->Execute($arrWhere);

			if ($mixResult === FALSE)
			{
				throw new Exception("An error occurred when fetching document type '$documentType' template for Customer Group Id'$this->customerGroup' on generation date '$generationDate'");
			}
			else if (!$mixResult)
			{
				throw new Exception("No template found for document type '$documentType' for Customer Group Id '$this->customerGroup' on generation date '$generationDate'");
			}
			else
			{
				$arrRecordSet = $selDocumentTemplate->FetchAll();
				$this->xsltString = $arrRecordSet[0]['Source'];
			}
		}
		// Return the cached xslt
		return $this->xsltString;
	}
	
	private function loadResources()
	{
		if ($this->resources === NULL) 
		{
			// Need to run the following (or something like it): -
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
				INNER JOIN DocumentResourceType RT on DR.Type = RT.Id"

			$arrColumns = Array( "PlaceHolder" 	=> "RT.PlaceHolder",
			 					 "Id" 			=> "DR.Id",
			 					 "Extension" 	=> "FT.Extension");

			$strWhere = "DR.createdOn = (
					SELECT max(DR2.CreatedOn) FROM DocumentResource DR2
					WHERE <GenerationDate> between StartDatetime and EndDatetime
					AND DR2.CustomerGroup = <CustomerGroup>
					AND DR2.Type = DR.Type
					group by Type)"

			$arrWhere = Array(	"CustomerGroup"		=> $this->customerGroup, 
								"GenerationDate"	=> $this->effectiveDate);

			$selDocumentResources = new StatementSelect($strTables, $arrColumns, $strWhere);

			$mixResult = $selDocumentResources->Execute($arrWhere);

			if ($mixResult === FALSE)
			{
				throw new Exception("An error occurred when fetching template resources for Customer Group Id'$this->customerGroup' on generation date '$generationDate'");
			}

			$arrRecordSet = $selDocumentTemplate->FetchAll();

			$this->resources = array();

			for ($i = 0; $i < $mixResult; $i++)
			{
				$this->resources[$arrRecordSet[$i]['PlaceHolder']] = Array("Id" => $arrRecordSet[$i]['Id'], "Extension" => $arrRecordSet[$i]['Extension']);
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

				// Get the placeholder from the relative path
				$placeholder = strtolower(substr($relativePath, 7));
				
				// Construct the absolute path if we have a match
				if (array_key_exists($placeholder, $this->resources))
				{
					$this->cache[$relativePath] = self::RESOURCE_BASE_PATH . "/" . $this->customerGroup . "/" 
						. $this->resources[$placeholder]["Id"] . "." . $this->resources[$placeholder]["Extension"];
				}
				// ... else record the fact that we didn't find the resource
				else
				{
					$this->cache[$relativePath] = "";
				}
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

		// Return the cached absolute path
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
