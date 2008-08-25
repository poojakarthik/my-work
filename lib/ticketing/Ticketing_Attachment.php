<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Ticketing_Attachment_Type.php';

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

	public function isBlacklisted()
	{
		return $this->blacklistOverride !== ACTIVE_STATUS_ACTIVE && $this->getType()->isBlacklisted();
	}

	public function allowBlacklistOverride()
	{
		return $this->blacklistOverride === ACTIVE_STATUS_ACTIVE;
	}

	public function setBlacklistOverride($boolOverride)
	{
		$this->blacklistOverride = ($boolOverride ? ACTIVE_STATUS_ACTIVE : ACTIVE_STATUS_INACTIVE);
		$this->_saved = FALSE;
		$this->save();
	}

	public function isGreylisted()
	{
		return $this->getType()->isGreylisted();
	}

	public function isWhitelisted()
	{
		return $this->getType()->isWhitelisted();
	}

	public function getAppliedBlacklistStatus()
	{
		$id = $this->isGreylisted() ? TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY 
									 : ($this->isWhitelisted() ? TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE 
									 						   : TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK);
		return Ticketing_Attachment_Blacklist_Status::getForId($id);
	}

	public function getType()
	{
		return Ticketing_Attachment_Type::getForId($this->attachmentTypeId);
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
			'blacklist_override' => $this->blacklistOverride
		);
		if ($this->fileContent)
		{
			$arrValues['file_content'] = $this->fileContent;
		}
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

	public static function create(Ticketing_Correspondance $objCorrespondence, $strFileName, $strFileType, $strFileContent)
	{
		$objAttachmentType = self::discoverAttachmentType($strFileType, $strFileName);

		// We can't store attachments if there is no type!
		if ($objAttachmentType == NULL)
		{
			return NULL;
		}

		$objAttachment = new Ticketing_Attachment();

		$objAttachment->correspondanceId = $objCorrespondence->id;
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

	private static function getColumns()
	{
		return array(
			'id',
			'correspondance_id',
			'file_name',
			'attachment_type_id',
			'blacklist_override',
		);
	}

	public static function getForId($id)
	{
		return self::getFor('id = <ATTACHMENT_ID>', array('ATTACHMENT_ID' => $id));
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		// Note: Email address should be unique, so only fetch the first record
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			self::getColumns(), 
			$strWhere,
			$strSort,
			$strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load attachment: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[] = new Ticketing_Attachment($details);
			if (!$multiple)
			{
				return $arrInstances[0];
			}
		}
		return $arrInstances;
	}

	public static function load($id)
	{
		// Only load the fileName and fileType of the attachment
		$arrWhere = array('RecordId' => $this->id);
		$selAttachments = new StatementSelect('ticketing_attachment', self::getColumns(), 'id = <RecordId>');
		if (($outcome = $selAttachments->Execute()) === FALSE)
		{
			throw new Exception("Failed to load attachments for record '{$id}': " . $selAttachments->Error());
		}
		if (!$outcome)
		{
			throw new Exception("Attachment '$id' was not found.");
		}

		$objAttachment =& new Ticketing_Attachment($selAttachments->Fetch());

		return $objAttachment;
	}

	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{$name} = $value;
		}
		$this->_saved = TRUE;
		
	}

	public static function listForCorrespondence(Ticketing_Correspondance $correspondence)
	{
		$arrWhere = array('CorrespondenceId' => $correspondence->id);
		$selAttachments = new StatementSelect('ticketing_attachment', self::getColumns(), 'correspondance_id = <CorrespondenceId>');
		if (($outcome = $selAttachments->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load attachments for correspondence '{$correspondence->id}': " . $selAttachments->Error());
		}
		$arrAttchments = array();
		while ($attachment = $selAttachments->Fetch())
		{
			$arrAttchments[] = new Ticketing_Attachment($attachment);
		}
		return $arrAttchments;
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
		if (($outcome = $selAttachment->Execute($arrWhere)) === FALSE)
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
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
}

?>