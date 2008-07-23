<?php

class Ticketing_Attachment
{
	private $id = NULL;
	private $fileName = NULL;
	private $attachmentTypeId = NULL;
	private $fileContent = NULL;
	private $correspondanceId = NULL;
	private $blacklistOverride = NULL;

	private $_saved = FALSE;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = array(
			'correspondance_id' => $this->correspondanceId, 
			'file_name' => $this->fileName, 
			'attachment_type_id' => $this->attachmentTypeId, 
			'file_content' => $this->fileContent, 
			'blacklist_override' => $this->blacklistOverride
		);
		// No id means that this must be a new record
		if (!$this->id)
		{
			$statement = new StatementInsert('ticketing_attachment', $arrValues);
		}
		// This must be an update
		else
		{
			
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_attachment', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save attachment details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;
		return TRUE;
	}

	public static function create(Ticketing_Correspondance $objCorrespondance, $strFileName, $strFileType, $strFileContent)
	{
		$objAttachmentType = self::discoverAttachmentType($strFileType, $strFileName);

		// We can't store attachments if there is no type!
		if ($objAttachmentType == NULL)
		{
			return NULL;
		}

		$objAttachment = new Ticketing_Attachment();

		$objAttachment->correspondanceId = $objCorrespondance->id;
		$objAttachment->fileName = trim($strFileName);
		$objAttachment->attachmentTypeId = $objAttachmentType->id;
		$objAttachment->fileContent = $strFileContent;
		$objAttachment->blacklistOverride = ACTIVE_STATUS_INACTIVE;

		$objAttachment->save();
	}

	private static function discoverAttachmentType($strFileType, $strFileName)
	{
		// Tidy the file type (could be in format "a/b;charset", but we don't want the charset part)
		$strFileType = str_replace(' ', '', trim(array_shift(explode(' ', preg_replace("/[^a-z0-9\-\.\/]+/", ' ', strtolower(trim($strFileType)))))));

		// We also need to look at the filename extension, as this is what we will be filtering on.
		// (the mime type is only required if the extension is unknown)
		$arrParts = explode('.', trim($strFileName));
		$strExtension = strtolower(array_pop($arrParts));
		if (!count($arrParts))
		{
			// There was no file extension!
			return NULL;
		}

		return Ticketing_Attachment_Type::getForExtensionAndMimeType($strExtension, $strFileType);
	}

	public static function load($id)
	{
		// Only load the fileName and fileType of the attachment
		$arrWhere = array('RecordId' => $this->id);
		$selAttachments = new StatementSelect('ticketing_attachment', array('id', 'file_name', 'attachment_type_id', 'correspondance_id', 'blacklist_override'), 'id = <RecordId>');
		if (($outcome = $selAttachments->Execute()) === FALSE)
		{
			throw new Exception("Failed to load attachments for correspondance '{$correspondance->id}': " . $selAttachments->Error());
		}
		if (!$outcome)
		{
			throw new Exception("Attachment '$id' was not found.");
		}

		$objAttachment =& new Ticketing_Attachment($selAttachments->Fetch());

		return $objAttachment;
	}

	private function init($arrProps)
	{
		$this->id = $arrProps['id'];
		$this->correspondanceId = $arrProps['correspondance_id'];
		$this->fileName = $arrProps['file_name'];
		$this->fileType = $arrProps['file_type'];
		$this->fileContent = NULL;
		$this->_saved = TRUE;
	}

	public static function listForCorrespondance(Ticketing_Correpondance $correspondance)
	{
		$arrWhere = array('CorrespondanceId' => $correspondance->id);
		$selAttachments = new StatementSelect('ticketing_attachment', array('id', 'file_name', 'attachment_type_id', 'correspondance_id', 'blacklist_override'), 'correspondance_id = <CorrespondanceId>');
		if (($outcome = $selAttachments->Execute()) === FALSE)
		{
			throw new Exception("Failed to load attachments for correspondance '{$correspondance->id}': " . $selAttachments->Error());
		}
		$arrAttchments = array();
		while ($attachment = $selAttachments->Fetch())
		{
			$objAttachment =& new Ticketing_Attachment();
			$objAttachment->init($attachment);
			$arrAttchments[] = $objAttachment;
		}
	}

	public function getFileContent()
	{
		// If file content is currently stored in memory, return it.
		if ($this->fileContent !== NULL)
		{
			return $this->fileContent;
		}

		if (!$this->id)
		{
			// We have no fileContent!
			return NULL;
		}

		// Load and return the fileContent from the database.
		// Do not store as it could cause memory issues.
		$arrWhere = array('RecordId' => $this->id);
		$selAttachment = new StatementSelect('ticketing_attachment', array('file_content'), 'id = <RecordId>');
		if (($outcome = $selAttachment->Execute()) === FALSE)
		{
			throw new Exception("Failed to load attachment file content for attachment '{$this->id}': " . $selAttachments->Error());
		}
		$strFileContent = NULL;
		if($attachment = $selAttachment->Fetch())
		{
			$strFileContent = $attachment['file_content'];
		}
		return $strFileContent;
	}

	public function __get($strName)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}

	public function __set($strName, $mxdValue)
	{
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			if ($this->{$strName} != $mxdValue)
			{
				$this->{$strName} = $mxdValue;
				$this->_saved = FALSE;
			}
		}
	}

	private function tidyName($name)
	{
		return strtolower(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
	}
}

?>