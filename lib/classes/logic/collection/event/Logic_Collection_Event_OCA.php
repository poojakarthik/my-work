<?php
/**
 * Description of Collection_Logic_Event_Action
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Event_OCA extends Logic_Collection_Event
{
    protected $oDO;
    
    public function __construct($mDefinition)
    {       
        if ($mDefinition instanceof Logic_Collection_Event_Instance)
        {
           $this->oCollectionEventInstance = $mDefinition;
           $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
           $this->oDO = Collection_Event_OCA::getForCollectionEventId($this->oParentDO->id);
        }
        else
        {
           throw new Exception ('Bad definition of Logic_Collection_Event_Charge, possibly a configuration error');
        }
    }

    /**
     * 1.close the account - see the logic in AppTempleateAccount::SaveDetails() for this. create a new close() method on Account, which optionally also sets the default plan on each service
     *     use the new method above to  move all service onto the default plan for their service type. To decide which plan to move to: first check RatePlan.override_default_rate_plan_id, if no value found there try to find one in the default_rate_plan table, if no value found there: do nothing.
     * 2. apply the legal fee charge
     * 3. generate the final invoice
     * 4. insert a record in the account_ocs_referral table    
     */

    protected function _invoke($aParameters = null)
    {             
            $oAccount = $this->getAccount();
             $aInvoice = Invoice::getForAccount($oAccount->Id);

             if (count($aInvoice) > 0 )
             {
                 $oInvoice = array_pop($aInvoice);
                 $oInvoiceRun = Invoice_Run::getForId($oInvoice->invoice_run_id);
                 $iInvoiceRunType = $oInvoiceRun->invoice_run_type_id;
                 if ($iInvoiceRunType != INVOICE_RUN_TYPE_LIVE )
                 {
                          throw new Exception("The most recent invoice 'live'. The OCA event should not be invoked in this case, and will have to wait until the most recent invoice has gone 'live'.");
                 }
             }
             else
             {
                 throw new Exception("There are no been 'live' invoices for this account. The OCA event should not be run in this case.");
             }

             //close the account - this will also disconnect all services and change them to their default rateplans
            $oAccount->close(true);
           

            //apply legal fees
            $oCharge = new Charge();
            $oChargeType = Charge_Type::getForId($this->legal_fee_charge_type_id);
            $oCharge->Account = $oAccount->id;
            $oCharge->AccountGroup = $oAccount->AccountGroup;
            $oCharge->CreatedBy = Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
            $oCharge->ApprovedBy =   Flex::getUserId()!=null?Flex::getUserId():Employee::SYSTEM_EMPLOYEE_ID;
            $oCharge->ChargeType = $oChargeType->ChargeType;
            $oCharge->charge_type_id = $oChargeType->id;
            $oCharge->Description = $oChargeType->Description;
            $oCharge->ChargedOn = Data_Source_Time::currentDate();
            $oCharge->Nature = $oChargeType->Nature;
            $oCharge->Amount= $oChargeType->Amount;
            $oCharge->Notes = "";
            $oCharge->Status =  CHARGE_APPROVED;
            $oCharge->global_tax_exempt =  0;
            $oCharge->charge_model_id =  $oChargeType->charge_model_id;
            $oCharge->CreatedOn = Data_Source_Time::currentDate();

            $oCharge->save();

            //generate invoice

            $intInvoiceDatetime	= strtotime(date('Y-m-d', strtotime('+1 day')));
            try
            {
                $objInvoiceRun	= new Invoice_Run();
                $objInvoiceRun->generateSingle($oAccount->CustomerGroup, INVOICE_RUN_TYPE_FINAL , $intInvoiceDatetime, $oAccount->Id);
            }
            catch (Exception $eException)
            {
                // Perform a Revoke on the Temporary Invoice Run
                if ($objInvoiceRun->Id)
                {
                        $objInvoiceRun->revoke();
                }
                throw $eException;
            }


            //insert record in the account_oca_referral table
            $oReferral = new Account_OCA_Referral();
            $oReferral->account_id = $oAccount->id;
            $oReferral->account_collection_event_history_id = $this->oCollectionEventInstance->id;
            $oReferral->invoice_run_id = $objInvoiceRun->Id;
            $oReferral->account_oca_referral_status_id = ACCOUNT_OCA_REFERRAL_STATUS_PENDING;
            $oReferral->save();           
           
        
    }

    public static function complete($aEventInstances)
    {
         foreach ($aEventInstances as $oInstance)
        {
            $oInstance->complete();
        }
    }

    public function __get($sField)
    {
        return $this->oDO->$sField;
    }
}
?>
