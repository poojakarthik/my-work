<?php

class Correspondence
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

			$mData['standard_fields']['tar_file_path'] = isset($mData['standard_fields']['tar_file_path'])?$mData['standard_fields']['tar_file_path']:null;

			$this->_oDO = new Correspondence_ORM($mData['standard_fields']);

			foreach ($mData['additional_fields'] as $key=>$value)
			{

				$this->_aAdditionalFields[$key] = new Correspondence_Data(array('value'=>$value, 'correspondence_template_column_id'=>null, 'correspondence_id'=>null));
			}

			//just for debugging
			$this->correspondence_delivery_method_id = 2;
		}
		else
		{
			$this->_oDO = $mData;
			$this->_oDO->setSaved();
			$this->_aAdditionalFields = Correspondence_Data::getForCorrespondence($this);
		}

	}

	public function toArray($bIncludeSystemFields = false)
	{
		//return an associative array that can be used for csv file genereation

		$aData = $this->_oDO->toArray();
		if (!$bIncludeSystemFields)
		{
			$aTemp = array();
			$aColumns = $this->_oCorrespondenceRun->getAllColumns();
			foreach ($aData as $sField=>$mValue)
			{
				if (in_array($sField, $aColumns))
					$aTemp[$sField]= $mValue;
			}
			$aData = $aTemp;

		}

		foreach($this->_aAdditionalFields as $sField=>$oData)
		{
			$aData[$sField]= $oData->value;
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

	public static function getStandardColumns($bPreprinted,$bIncludeNonSuppliedFields = false)
	{
		$aColumns = Correspondence_ORM::getFieldNames();


		if (!$bIncludeNonSuppliedFields)
		{
			foreach (Correspondence::$aCorrespondenceFieldsNotSupplied  as $sField)
			{
				$iIndex = array_search($sField,$aColumns);
				//unset($aColumns[$iIndex]);
				array_splice ( $aColumns ,$iIndex ,1);
			}
		}

		if (!$bPreprinted)
		{
				$iIndex = array_search('tar_file_path',$aColumns);
				//unset($aColumns[$iIndex]);
				array_splice ( $aColumns ,$iIndex ,1);
		}

		//we have to recreate the array as by now we possibly have created gaps

		return $aColumns;
	}

	public static function getForRun($oRun)
	{
		$aORM = Correspondence_ORM::getForRunId($oRun->id);
		$aCorrespondence = array();
		foreach ($aORM as $oORM)
		{

			$aCorrespondence[] = new Correspondence($oORM, $oRun);
		}
		return $aCorrespondence;
	}


}


?>