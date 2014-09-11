<?php
class Correspondence_Logic_Run_Dispatch
{

	const DELIVERED 		= 1;
	const DELIVERY_FAILED 	= 2;
	const NODATA 			= 3;

	const FILE_EXPORT_API	= 'Delivered By Flex';

	protected $oException;
	protected $_oDO;
	protected $_aDeliveryMethods = array();

	public function __construct($mDefinition)
	{
		$this->_oDO = is_numeric($mDefinition) ? Correspondence_Run_Dispatch::getForId($mDefinition) : (get_class($mDefinition) == 'Correspondence_Run_Dispatch' ? $mDefinition :new Correspondence_Run_Dispatch($mDefinition));

		if ($this->id != null)
		{
			$aORMs = Correspondence_Run_Dispatch_Delivery_Method::getForDispatchId($this->id);
			foreach ($aORMs as $oORM)
			{
				$x = Correspondence_Delivery_Method::getForId($oORM->correspondence_delivery_method_id);
				$this->_aDeliveryMethods[$x->system_name] = $oORM->correspondence_delivery_method_id;
			}
		}
	}

	public function getCorrespondenceTotalsPerDeliveryMethod()
	{
		$aCounts	= array();
		foreach ($this->_aDeliveryMethods as $sMethod => $iMethodId)
		{
			$aCorrespondence	= Correspondence::getForDeliveryMethodAndRunId($iMethodId, $this->correspondence_run_id);
			$aCounts[$sMethod]	= count($aCorrespondence);
		}
		return $aCounts;
	}

	public function setException($e)
	{
		$this->oException = $e;
	}

	public function getException()
	{
		return $this->oException;
	}

	public function setDeliveryMethods($aMethods)
	{

		$aHashedMethods = array();
		foreach ($aMethods as $iMethod)
		{
			 $x = Correspondence_Delivery_Method::getForId($iMethod);
			$aHashedMethods[$x->system_name] = $iMethod;
		}


		$this->_aDeliveryMethods = $aHashedMethods;
	}

	public static function getForRunAndTemplateCarrierModule($iRunId, $iTemplateCarrierModuleId)
	{
		$x = Correspondence_Run_Dispatch::getForRunAndTemplateCarrierModule($iRunId, $iTemplateCarrierModuleId);
		return new self($x);
	}

	public function save()
	{
		$this->_oDO->save();

		foreach ($this->_aDeliveryMethods as $iMethod)
		{
			$y = Correspondence_Run_Dispatch_Delivery_Method::getForDeliveryMethodAndFileExportRecord($iMethod, $this->id);
			if ($y===null)
			{
				$y = new Correspondence_Run_Dispatch_Delivery_Method(array('correspondence_run_dispatch_id'=>$this->id,'correspondence_delivery_method_id'=>$iMethod ));
				$y->save();
			}
		}
	}

	public function getDataFileName()
	{
		if (($this->oException !== null) && ($this->oException instanceof Correspondence_Dispatcher_Exception))
		{
			return $this->oException->failureReasonToString();
		}
		else
		{
			$oTemplateCarrierModule = Correspondence_Template_Carrier_Module::getForId($this->correspondence_template_carrier_module_id);
			$oCarrierModule			= Carrier_Module::getForId($oTemplateCarrierModule->carrier_module_id);
			$sModuleClass			= $oCarrierModule->Module;
			$oCarrierModuleInstance	= new $sModuleClass($oCarrierModule);
			return $oCarrierModuleInstance->getFileNameForCorrespondenceRunDispatch($this);
		}
	}

	public function getFileInfo()
	{
		$sFileName 		= $this->getDataFileName();
		$sCarrier 		= $this->getCarrierName();
		$oBatch 		= $this->correspondence_run_batch_id != null ? Correspondence_Run_Batch::getForId($this->correspondence_run_batch_id) : null;
		$sDispatchDate 	= $oBatch !=null ? $oBatch->batch_datetime : null;
		$iStatus 		= $this->getStatus();
		$sStatus 		= (($iStatus == self::DELIVERED) ? 'Dispatched' : ($iStatus== self::DELIVERY_FAILED ? 'Dispatch Failed' : 'No File Dispatched'));
		return 	array(
					'file'				=> $sFileName, 
					'carrier'			=> $sCarrier, 
					'batch'				=> $this->correspondence_run_batch_id, 
					'dispatch_date'		=> $sDispatchDate, 
					'delivery_methods'	=> $this->_aDeliveryMethods, 
					'status'			=> $sStatus, 
					'status_code'		=> $this->getStatus()
				);
	}

	public function getStatus()
	{
		$iStatus	= null;
		if ($this->correspondence_run_batch_id === null)
		{
			$iStatus = self::DELIVERY_FAILED;
		}
		else if (count($this->_aDeliveryMethods) == 0)
		{
			$iStatus = self::NODATA;
		}
		else
		{
			$iStatus = self::DELIVERED;
		}
		return $iStatus;
	}

	public function isForCarrierModule($iCorrespondenceTemplateCarrierModuleId)
	{
		return ($this->correspondence_template_carrier_module_id == $iCorrespondenceTemplateCarrierModuleId);
	}

	public function isForDeliveryMethod($sMethod)
	{
		try
		{
			return (in_array($sMethod, array_keys($this->_aDeliveryMethods)) ? true : false);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	public function getCarrierName()
	{
		try
		{
			$oTemplateCarrierModule = Correspondence_Template_Carrier_Module::getForId($this->correspondence_template_carrier_module_id);
			$oCarrierModule			= Carrier_Module::getForId($oTemplateCarrierModule->carrier_module_id);
			$oCarrier 				= Carrier::getForId($oCarrierModule->Carrier);
			return $oCarrier->name;
		}
		catch(Exception $oException)
		{
			return null;
		}
	}

	public function __get($sField)
	{
		return $this->_oDO->$sField;
	}

	public function __set($sField, $mValue)
	{
		$this->_oDO->$sField =$mValue;
	}

	public static function getForRunId($iRunId)
	{
		$aORMs =  Correspondence_Run_Dispatch::getForRunId($iRunId);
		$aResult = array();
		foreach ($aORMs as $oORM)
		{
			$aResult[] = new self($oORM);
		}
		return $aResult;
	}
}
?>