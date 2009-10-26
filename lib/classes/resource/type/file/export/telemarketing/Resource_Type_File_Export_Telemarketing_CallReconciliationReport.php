<?php
/**
 * Resource_Type_File_Export_Telemarketing_CallReconciliationReport
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Telemarketing_CallReconciliationReport
 */
class Resource_Type_File_Export_Telemarketing_CallReconciliationReport
{
	
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * @constructor
	 */
	public function __construct($iCarrier)
	{
		$this->_iCarrier	= $iCarrier;
	}
	
	/**
	 * export()
	 *
	 * Finalises the Export of a file.  Only to be called after
	 * 
	 * @return	array						Array of Error messages
	 * 
	 * @method
	 */
	public function export($aRecords=array())
	{
		$aErrors	= array();
		
		$oCSVFile	= new File_CSV();
		$oCSVFile->setColumns(array_values(self::$_aColumns));
		
		// If we were given any additional records, then render them first
		foreach ($aRecords as $aRecord)
		{
			$oCSVFile->addRow(self::_renderRecord($aRecord)); 
		}
		
		// Dump the data to the Export file
		$sFileName	= 'reconciled_'.date("YmdHis").'.csv';
		$sCarrier	= GetConstantName($this->_iCarrier, 'Carrier');
		$sFilePath	= FILES_BASE_PATH."export/telemarketing/{$sCarrier}/".__CLASS__.'/'.$sFileName;
		
		$sCSVContents	= $oCSVFile->save();
		
		return $aErrors;
	}
	
	/**
	 * _renderRecord()
	 *
	 * Converts a Flex Data array to a render-ready state
	 * 
	 * @param	array	$aRecord					Array representation of Output Data
	 * 
	 * @return	array								Rendered data
	 * 
	 * @method
	 */
	protected static function _renderRecord($aRecord)
	{
		$aRendered	= array();
		
		$oTelemarketingDialledFNN	= Telemarketing_FNN_Dialled::getForId($aRecord['telemarketing_fnn_dialled_id']);
		
		$aRendered[self::$_aColumns['FNN']]					= $aRecord['oTelemarketingDialledFNN']->fnn;
		$aRendered[self::$_aColumns['DATE_DIALLED']]		= $oTelemarketingDialledFNN->dialled_on;
		$aRendered[self::$_aColumns['CALL_OUTCOME']]		= Telemarketing_FNN_Dialled_Result::getForId($oTelemarketingDialledFNN->telemarketing_fnn_dialled_result_id)->description;
		
		// Some FNNs will not have been permitted
		if ($aRecord['telemarketing_fnn_proposed_id'])
		{
			$oTelemarketingProposedFNN	= Telemarketing_FNN_Proposed::getForId($aRecord['telemarketing_fnn_proposed_id']);
			
			$aRendered[self::$_aColumns['PROPOSED_FILENAME']]	= File_Import::getForId($oTelemarketingProposedFNN->proposed_list_file_import_id)->FileName;
			$aRendered[self::$_aColumns['DATE_WASHED']]			= File_Export::getForId($oTelemarketingProposedFNN->permitted_list_file_export_id)->ExportedOn;
			$aRendered[self::$_aColumns['WASH_OUTCOME']]		= ($oTelemarketingProposedFNN->telemarketing_fnn_withheld_reason_id) ? 'Withheld: ' . Telemarketing_FNN_Withheld_Reason::getForId($oTelemarketingProposedFNN->telemarketing_fnn_withheld_reason_id)->description : 'Permitted';
			$aRendered[self::$_aColumns['PERMITTED_START']]		= $oTelemarketingProposedFNN->call_period_start;
			$aRendered[self::$_aColumns['PERMITTED_END']]		= $oTelemarketingProposedFNN->call_period_end;
		}
		
		return $aRendered;
	}
}