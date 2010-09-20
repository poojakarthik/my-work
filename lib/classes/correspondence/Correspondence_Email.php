<?php
class Correspondence_Email extends Email_Notification
{
	protected $_oBody;
	protected $_oTable;
	protected $html;

	const PIVOT_TH_STYLE				= "text-align: right; vertical-align: top;color: #eee; background-color: #333; width: 15em; padding-right:10px;";
	const PIVOT_TD_STYLE				= "text-align: left;vertical-align: top; color: #333; background-color: #eee; padding-left:10px;";
	const TDWIDTH_STYLE					= "min-width: 15em; max-width: 15em;";
	const TABLE_STYLE					= "font-family: Calibri, Arial, sans-serif; width:99%; border: .1em solid #333; border-spacing: 0; border-collapse: collapse;";
	const FONT_STYLE					= "font-family: Calibri, Arial, sans-serif;";
	const TH_STYLE						= "text-align:left; color: #eee; background-color: #333;  width: 15em; padding-left:10px;";
	const TD_STYLE						= "text-align:left; color: #333; background-color: #eee; padding-left:20px;font-size:90%;";
	const TD_ALTERNATIVE_STYLE			= "text-align:left; color: #333; background-color: #FFFFFF; padding-left:20px;font-size:90%;";
	const FONT_BOLD_STYLE				= "font-family: Calibri, Arial, sans-serif;font-weight:bold;";


	public function __construct($intEmailNotification=0)
	{
		parent::__construct($intEmailNotification);
		$this->_oBody = new Flex_Dom_Document();
	}

	public function getBody()
	{
		return $this->_oBody;
	}

	public function getTable()
	{
		return $this->_oTable;
	}

	public function setTable($aHeaders = array())
	{
		$this->_oTable = $this->_oBody->html->body->table();
		$this->_oTable->style =Correspondence_Email::TABLE_STYLE;
		$this->setTableHeaders($aHeaders);
		return $this->_oTable;
	}

	public function setTableHeaders($aHeaders = array())
	{
		$iCount = 0;
		foreach ($aHeaders as $sHeader)
		{
			$this->_oTable->tr(0)->th($iCount)->setValue($sHeader);
			$this->_oTable->tr(0)->th($iCount)->style = self::TH_STYLE;
			$iCount++;
		}
	}

	public function setBodyHtml()
	{
		$this->html = $this->_oBody->saveHTML();
		parent::setBodyHtml($this->html);
	}

	public function addPivotTableRow($key, $mValue)
	{
		$tr =& $this->_oTable->tr();
		$tr->td(0)->setValue($key);
		if (is_array($mValue))
		{
			$td = $tr->td(1);
			$iDivCount = 0;
			foreach ($mValue as $value)
			{
				$td->div($iDivCount)->setValue($value);
				$iDivCount++;
			}
		}
		else
		{
			$tr->td(1)->setValue($mValue);
		}

		$tr->td(0)->style = self::PIVOT_TH_STYLE;
		$tr->td(1)->style = self::PIVOT_TD_STYLE;
	}


	public function appendSignature()
	{
		$this->_oBody->div();
		$div = $this->_oBody->div();
		$div->setValue("Regards");
		$div->style = self::FONT_STYLE;
		$div = $this->_oBody->div();
		$div->setValue("Flexor");
		$div->style = self::FONT_BOLD_STYLE;
	}

	public function toString()
	{
		//return $this->_bodyHtml->getContent();
		return $this->html;
	}



}