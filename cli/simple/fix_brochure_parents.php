<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

try
{
	if (!DataAccess::getDataAccess()->TransactionStart())
	{
		throw new Exception("Unable to start a Transaction");
	}
	$qryQuery	= new Query();
	
	$arrCustomerGroupDocuments	= array();
	
	// Select all Plans with a Plan Brochure which has no parent
	$resPlansWithBrochures	= $qryQuery->Execute("SELECT DISTINCT RatePlan.* FROM RatePlan JOIN document_content ON RatePlan.brochure_document_id = document_content.document_id WHERE parent_document_id IS NULL");
	if (!$resPlansWithBrochures)
	{
		throw new Exception($qryQuery->Error());
	}
	while ($arrPlan = $resPlansWithBrochures->fetch_assoc())
	{
		Log::getLog()->log(" [+] ".GetConstantDescription($arrPlan['customer_group'], 'CustomerGroup')."::{$arrPlan['Name']}...");
		
		$objRatePlan			= new Rate_Plan($arrPlan);
		$objBrochureDocument	= new Document(array('id'=>$objRatePlan->brochure_document_id), true);
		$objBrochureContent		= $objBrochureDocument->getContent();
		
		$objCustomerGroupDir	= Document::getByPath("/Plan Brochures/{$objRatePlan->customer_group}/");
		if (!$objCustomerGroupDir)
		{
			throw new Exception("'/Plan Brochures/{$objRatePlan->customer_group}/' doesn't exist yet!");
		}
		
		// Replace the Brochure with itself
		$objBrochureContent->id					= null;
		$objBrochureContent->parent_document_id	= $objCustomerGroupDir->id;
		$objBrochureContent->save();
	}
	
	throw new Exception("TEST MODE");
	
	if (!DataAccess::getDataAccess()->TransactionCommit())
	{
		throw new Exception("Unable to commit the Transaction");
	}
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}


?>