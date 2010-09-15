<?php

class Correspondence_Logic
{
	protected $_oDO;
	protected $_oCorrespondenceRun;
	public static  $aCorrespondenceFieldsNotSupplied = array( 'correspondence_run_id');
	protected $_aAdditionalFields = array();


	public function __construct($mData, $oCorrrespondenceRun = null)
	{
		$this->_oCorrespondenceRun = $oCorrrespondenceRun;
		if (is_array($mData))
		{
			foreach (self::$aCorrespondenceFieldsNotSupplied as $sField)
			{
				$mData['standard_fields'][$sField] = null;
			}

			$mData['standard_fields']['pdf_file_path'] = isset($mData['standard_fields']['pdf_file_path'])?$mData['standard_fields']['pdf_file_path']:null;

			$this->_oDO = new Correspondence($mData['standard_fields']);

			foreach ($mData['additional_fields'] as $key=>$value)
			{

				$this->_aAdditionalFields[$key] = new Correspondence_Logic_Data(array('value'=>$value, 'correspondence_template_column_id'=>null, 'correspondence_id'=>null));
			}


			$this->correspondence_delivery_method_id = Correspondence_Delivery_Method::getForSystemName($mData['standard_fields']['correspondence_delivery_method_id'])->id;
		}
		else
		{
			$this->_oDO = $mData;
			$this->_oDO->setSaved();
			$this->_aAdditionalFields = Correspondence_Logic_Data::getForCorrespondence($this);
			if ($this->_oCorrespondenceRun == null)
				$this->_oCorrespondenceRun = Correspondence_Logic_Run::getForId($this->correspondence_run_id, false);

		}

	}

	public function toArray($bIncludeSystemFields = false, $bIncludeRun = false)
	{
		//return an associative array that can be used for csv file genereation

		$aData = $this->_oDO->toArray();
		$aData['postcode'] = str_pad($aData['postcode'], 4, "0", STR_PAD_LEFT);
		if (!$bIncludeSystemFields)
		{
			$aTemp = array();
			$aColumns = $this->_oCorrespondenceRun->getAllColumns();
			foreach ($aData as $sField=>$mValue)
			{
				if (in_array($sField, $aColumns))
					$aTemp[$sField]= $sField == 'postcode'?str_pad($mValue, 4, "0", STR_PAD_LEFT):$mValue;
			}
			$aData = $aTemp;

		}

		foreach($this->_aAdditionalFields as $sField=>$oData)
		{
			$aData[$sField]= $oData->value;
		}

		if ($bIncludeRun)
		{

			$aData['correspondencde_run'] = $this->_oCorrespondenceRun->toArray();

		}

		return $aData;
	}


	public function save()
	{

		if ($this->_oCorrespondenceRun == null)
			throw new Exception();
		if ($this->_oCorrespondenceRun->id == null)
			$this->_oCorrespondenceRun->save();
		$this->correspondence_run_id = $this->_oCorrespondenceRun->id;
		$this->_oDO->save();

		foreach ($this->_aAdditionalFields as $sName => $oField)
		{
			$oField->correspondence_id = $oField->correspondence_id==null?$this->id:$oField->correspondence_id;
			$oField->correspondence_template_column_id = $oField->correspondence_template_column_id==null?$this->_oCorrespondenceRun->getTemplate()->getColumnIdForName($sName):$oField->correspondence_template_column_id;
			$oField->save();
		}
	}

	public function __set($sField, $mValue)
	{
		switch ($sField)
		{
			case '_oCorrespondenceRun':
									$this->_oCorrespondenceRun = $mValue;
									break;
			case 'correspondence_run_id':
									$this->_oDO->correspondence_run_id = $mValue;
									break;
			case 'correspondence_delivery_method_id':
									$this->_oDO->correspondence_delivery_method_id = $mValue;
									break;


		}
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public static function getStandardColumnCount($bPreprinted, $bIncludeNonSuppliedFields = false)
	{
		return count (self::getStandardColumns($bPreprinted, $bIncludeNonSuppliedFields));
	}

	public function getAllColumns()
	{
		$bPreprinted = $this->_oCorrespondenceRun->preprinted==1?true:false;
		return $this->_oCorrespondenceRun->getTemplate()->createFullColumnSet( $bPreprinted);

	}

	public function getAdditionalColumns()
	{
		return $this->_oCorrespondenceRun->getTemplate()->getAdditionalColumnSet();
	}

	public static function getStandardColumns($bPreprinted,$bIncludeNonSuppliedFields = false)
	{
		$aColumns = Correspondence::getFieldNames();


		if (!$bIncludeNonSuppliedFields)
		{
			foreach (Correspondence_Logic::$aCorrespondenceFieldsNotSupplied  as $sField)
			{
				$iIndex = array_search($sField,$aColumns);
				//unset($aColumns[$iIndex]);
				array_splice ( $aColumns ,$iIndex ,1);
			}
		}

		if (!$bPreprinted)
		{
				$iPdf = array_search('pdf_file_path',$aColumns);
				//unset($aColumns[$iIndex]);
				array_splice ( $aColumns ,$iPdf ,1);
		}

		//we have to recreate the array as by now we possibly have created gaps

		return $aColumns;
	}

	public static function getForRun($oRun)
	{
		$aORM = Correspondence::getForRunId($oRun->id);
		$aCorrespondence = array();
		foreach ($aORM as $oORM)
		{
			$aCorrespondence[] = new Correspondence_Logic($oORM, $oRun);
		}
		return $aCorrespondence;
	}

	public static function getForAccountId($iAccount, $bToArray = false)
	{
		$aORM = Correspondence::getForAccountId($iAccount);
		$aCorrespondence = array();
		foreach ($aORM as $oORM)
		{

			$x =  new Correspondence_Logic($oORM);
			$aCorrespondence[] = $bToArray?$x->toArray(true, true):$x;
		}
		return $aCorrespondence;
	}


}


?>