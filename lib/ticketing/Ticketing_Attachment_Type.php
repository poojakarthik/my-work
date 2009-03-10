<?php

class Ticketing_Attachment_Type
{
	private $id = NULL;
	private $extension = NULL;
	private $mimeType = NULL;
	private $blacklistStatusId = NULL;

	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public function isBlacklisted()
	{
		return $this->blacklistStatusId === TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK;
	}

	public function setBlacklisted()
	{
		$this->blacklistStatusId = TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK;
		$this->_saved = FALSE;
	}

	public function isGreylisted()
	{
		return $this->blacklistStatusId === TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY;
	}

	public function setGreylisted()
	{
		$this->blacklistStatusId = TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY;
		$this->_saved = FALSE;
	}

	public function isWhitelisted()
	{
		return $this->blacklistStatusId === TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE;
	}

	public function setWhitelisted()
	{
		$this->blacklistStatusId = TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE;
		$this->_saved = FALSE;
	}

	public function getBlacklistStatus()
	{
		return Ticketing_Attachment_Blacklist_Status::getForId($this->blacklistStatusId);
	}

	public function setBlacklistStatus(Ticketing_Attachment_Blacklist_Status $blacklistStatus)
	{
		$this->blacklistStatusId = $blacklistStatus->id;
		$this->_saved = false;
	}

	public static function getColumnNames()
	{
		return array(
			'id',
			'extension',
			'mime_type',
			'blacklist_status_id',
		);
	}

	protected function getValuesToSave()
	{
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach ($arrColumns as $strColumn)
		{
			if ($strColumn == 'id') 
			{
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	}

	public function save()
	{
		if ($this->_saved)
		{
			// Nothing to save
			return TRUE;
		}
		$arrValues = $this->getValuesToSave();

		// No id means that this must be a new record
		if (!$this->id)
		{
			$statement = new StatementInsert('ticketing_attachment_type', $arrValues);
		}
		// This must be an update
		else
		{
			$arrValues['id'] = $this->id;
			$statement = new StatementUpdateById('ticketing_attachment_type', $arrValues);
		}
		if (($outcome = $statement->Execute($arrValues)) === FALSE)
		{
			throw new Exception('Failed to save attachment type details: ' . $statement->Error());
		}
		if (!$this->id)
		{
			$this->id = $outcome;
		}
		$this->_saved = TRUE;
		return TRUE;
	}

	public static function getForId($id)
	{
		return self::getFor('id = <TYPE_ID>', array('TYPE_ID' => $id));
	}

	public static function getForExtension($strExtension)
	{
		return self::getFor('extension = <Extension>', array('Extension' => $strExtension));
	}

	public static function getForExtensionAndMimeType($strExtension, $strMimeType, $blacklistStatus=NULL)
	{
		$objType = self::getForExtension($strExtension);
		if (!$objType)
		{
			$objType = new Ticketing_Attachment_Type();
			$objType->extension = $strExtension;
			$objType->mimeType = $strMimeType;
			// Default the attachment type to being grey-listed.
			$objType->blacklistStatusId = $blacklistStatus ? $blacklistStatus->id : TICKETING_ATTACHMENT_BLACKLIST_STATUS_GREY;
			$objType->save();

			// Send an email to ybs to inform of the newly created attachment type, so that they may blacklist if necessary
			if (!$blacklistStatus)
			{
				self::sendEmailNotification($objType);
			}
		}
		return $objType;
	}

	private static function getColumns()
	{
		return array('id', 'extension', 'mime_type', 'blacklist_status_id');
	}

	private static function getFor($strWhere, $arrWhere, $multiple=FALSE, $strSort=NULL, $strLimit=NULL)
	{
		if (!$strSort || empty($strSort))
		{
			$strSort = 'extension ASC';
		}
		// Note: Email address should be unique, so only fetch the first record
		$selMatches = new StatementSelect(
			strtolower(__CLASS__), 
			self::getColumns(), 
			$strWhere,
			$strSort,
			$strLimit);
		if (($outcome = $selMatches->Execute($arrWhere)) === FALSE)
		{
			throw new Exception("Failed to load attachment type: " . $selMatches->Error());
		}
		if (!$outcome)
		{
			return $multiple ? array() : NULL;
		}
		$arrInstances = array();
		while($details = $selMatches->Fetch())
		{
			$arrInstances[$details['id']] = new Ticketing_Attachment_Type($details);
			if (!$multiple)
			{
				return current($arrInstances);
			}
		}
		return $arrInstances;
	}

	public static function listAll()
	{
		return self::getFor('', array(), TRUE);
	}

	public function sendEmailNotification($objType)
	{
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'Email_Notification.php';
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'dom' . DIRECTORY_SEPARATOR . 'Flex_Dom_Document.php';

		$email = new Email_Notification(EMAIL_NOTIFICATION_TICKETING_SYSTEM_ADMIN_MESSAGE);

		$email->addHeader("X-Priority", "1 (Highest)");
		$email->addHeader("X-MSMail-Priority", "High");
		$email->addHeader("Importance", "High");

		$email->subject = "URGENT: An unknown file type has entered the ticketing system (Ext: {$objType->extension}, Mime: {$objType->mimeType})";

		$email->text  = "An email attachment has been received into the ticketing system that has an unrecognised file extension: {$objType->extension}
A grey-listed ticket_attachment_type (id = {$objType->id}) has been created for the extension with a mime type of: {$objType->mimeType}
If this file type poses a threat to ticketing system users please manually change it's blacklist_status_id to TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK (" . TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK . ") immediately.
If this file type is safe please manually change it's blacklist_status_id to TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE (" . TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE . ").\n";

		$body = new Flex_Dom_Document();
		$body->HTML->BODY->H2->setValue('TICKETING SYSTEM WARNING');
		$body->HTML->BODY->P()->B->setValue('An unrecognised file has entered the ticketing system.');
		$body->HTML->BODY->TABLE(0)->TR(0)->TD()->setValue('Extension:');
		$body->HTML->BODY->TABLE(0)->TR(0)->TD()->setValue($objType->extension);
		$body->HTML->BODY->TABLE(0)->TR(1)->TD()->setValue('Mime Type:');
		$body->HTML->BODY->TABLE(0)->TR(1)->TD()->setValue($objType->mimeType);
		$body->HTML->BODY->P()->setValue("A grey-listed ticket_attachment_type (id = {$objType->id}) has been created for the extension with a mime type of: {$objType->mimeType}");
		$P = $body->HTML->BODY->P();
		$P->B->SPAN(0)->setValue("If this file type poses a threat to ticketing system users please manually change it's blacklist_status_id to TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK (" . TICKETING_ATTACHMENT_BLACKLIST_STATUS_BLACK . ") ");
		$P->B->SPAN(1)->setValue(" IMMEDIATELY");
		$P->B->SPAN(1)->style = "color: red;";
		$P->B->SPAN(2)->setValue(".");
		$body->HTML->BODY->P()->setValue("If this file type is safe please manually change it's blacklist_status_id to TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE (" . TICKETING_ATTACHMENT_BLACKLIST_STATUS_WHITE . ").");

		$email->html = $body->saveHTML();

		$email->send();
	}

	protected function init($arrProperties)
	{
		foreach($arrProperties as $name => $property)
		{
			$this->{$name} = $property;
		}
		$this->_saved = TRUE;
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
			if ($this->{$strName} !== $mxdValue)
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
