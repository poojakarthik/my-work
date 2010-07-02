<?php

class HtmlTemplate_Sale_View extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);


		$this->LoadJavascript('reflex_popup');
		$this->LoadJavascript('sp/validation');
		$this->LoadJavascript('sp/SalesPortal');

		$this->LoadJavascript('date_time_picker_dynamic');

		$productTypes = DO_Sales_ProductType::listAll();

		foreach ($productTypes as $productType)
		{
			$this->LoadJavascript('sp/product_type_module/Product_Type_Module_'.$productType->module);
		}
	}

	public function Render()
	{
		$objSale = $this->mxdDataToRender['Sale'];



		//$response = new Response_Sales_Portal();


		$p = $this->mxdDataToRender['ExtraPath'];
		$bodyClassName = 'data-display';
		if (count($p))
		{
			$saleId = $p[0];
			//$bodyClassName = 'data-display';
		}
		else
		{
			$saleId = 'null';
		}

		//$response->addTrailItem("Sales History", $this->request->getRequestedBaseURI() . "/sales/view/last/");
		if ($saleId != 'null')
		{
			// Viewing a sale
			//$response->addTrailItem("Sale $saleId");
		}
		else
		{
			// Creating a new sale
			//$response->addTrailItem("New Sale");
		}

		//if (Session::isAuthenticated())
		{
			//$response->addMenuItem("Logout", $this->request->getRequestedBaseURI() ."/logout");
		}

		//$response->openPage();


		$arrSaleTypes		= DO_Sales_SaleType::getAll();
		$arrSaleTypeIdName	= array();
		foreach($arrSaleTypes as $oSaleType) $arrSaleTypeIdName[$oSaleType->id] = '"' . $oSaleType->name . '"';

		$contactTitles = DO_Sales_ContactTitle::listAll();
		$arrContactIdName = array();
		foreach($contactTitles as $contactTitle) $arrContactIdName[$contactTitle->id] = '"' . $contactTitle->name . '"';

		$states = DO_Sales_State::listAll();
		$arrStateIdName = array();
		foreach($states as $state) $arrStateIdName[$state->id] = '"' . $state->name . '"';

		$vendors = DO_Sales_Vendor::getAll();
		$arrVendorIdName = array();
		foreach($vendors as $vendor) $arrVendorIdName[$vendor->id] = '"' . $vendor->name . '"';

		$all = DO_Sales_DirectDebitType::listAll();
		$arrDirectDebitTypeIdName = array();
		foreach($all as $obj) $arrDirectDebitTypeIdName[$obj->id] = '"' . $obj->description . '"';

		$all = DO_Sales_BillPaymentType::listAll();
		$arrBillPaymentTypeIdName = array();
		foreach($all as $obj) $arrBillPaymentTypeIdName[$obj->id] = '"' . $obj->description . '"';

		$all = DO_Sales_BillDeliveryType::listAll();
		$arrBillDeliveryTypeIdName = array();
		foreach($all as $obj) $arrBillDeliveryTypeIdName[$obj->id] = '"' . $obj->description . '"';

		$all = DO_Sales_CreditCardType::listAll();
		$arrCreditCardTypeIdName = array();
		foreach($all as $obj) $arrCreditCardTypeIdName[$obj->id] = '"' . $obj->description . '"';
?>

<div style="width:975px;margin-left:auto;margin-right:auto" id="sale_panel"></div>
<style>
	body.data-display .data-entry   { display: none !important; }
	body.data-entry   .data-display { display: none !important; }
	body.data-display .read-only .data-entry { display: none !important; }
	body.data-entry   .read-only .data-entry { display: none !important; }
	body.data-display .read-only .data-display { display: inline !important; }
	body.data-entry   .read-only .data-display { display: inline !important; }
	table.data-table > tbody > tr > td { width: 180px; }
	table.data-table > tbody > tr > td + td { width: auto; }
</style>
<script type='text/JavaScript' language='JavaScript'>

	function initiateSale()
	{
		var _sale = null;

		SP.Sale.canCancelSale = <?=Sales_Portal_Sale::canBeCancelled($objSale) ? 'true' : 'false'?>;
		SP.Sale.canAmendSale = <?=Sales_Portal_Sale::canBeAmended($objSale) ? 'true' : 'false'?>;
		SP.Sale.canVerifySale = <?=Sales_Portal_Sale::canBeVerified($objSale) ? 'true' : 'false'?>;
		SP.Sale.canRejectSale = <?=Sales_Portal_Sale::canBeRejected($objSale) ? 'true' : 'false'?>;
		SP.Sale.canBeSetToAwaitingDispatch = <?=(Sales_Portal_Sale::canBeVerified($objSale) && $objSale->hasBeenVerified()) ? 'true' : 'false'?>;

		SP.Sale.sale_type	= {
			ids		: [<?=implode(',', array_keys($arrSaleTypeIdName))?>],
			labels	: [<?=implode(',', array_values($arrSaleTypeIdName))?>],
		}

		SP.Sale.contactTitles = {
			ids: [<?=implode(',', array_keys($arrContactIdName))?>],
			labels: [<?=implode(',', array_values($arrContactIdName))?>]
		}

		SP.Sale.states = {
			ids: [<?=implode(',', array_keys($arrStateIdName))?>],
			labels: [<?=implode(',', array_values($arrStateIdName))?>]
		}

		SP.Sale.vendors = {
			ids: [<?=implode(',', array_keys($arrVendorIdName))?>],
			labels: [<?=implode(',', array_values($arrVendorIdName))?>]
		}

		SP.Sale.direct_debit_type = {
			ids: [<?=implode(',', array_keys($arrDirectDebitTypeIdName))?>],
			labels: [<?=implode(',', array_values($arrDirectDebitTypeIdName))?>]
		}

		SP.Sale.bill_payment_type = {
			ids: [<?=implode(',', array_keys($arrBillPaymentTypeIdName))?>],
			labels: [<?=implode(',', array_values($arrBillPaymentTypeIdName))?>]
		}

		SP.Sale.bill_delivery_type = {
			ids: [<?=implode(',', array_keys($arrBillDeliveryTypeIdName))?>],
			labels: [<?=implode(',', array_values($arrBillDeliveryTypeIdName))?>]
		}

		SP.Sale.credit_card_type = {
			ids: [<?=implode(',', array_keys($arrCreditCardTypeIdName))?>],
			labels: [<?=implode(',', array_values($arrCreditCardTypeIdName))?>]
		}


		_sale = new SP.Sale($ID('sale_panel'), <?=$saleId?>, '<?=$bodyClassName?>');
	}

	function initiateJS()
	{
		//var sSPOverride = 'sp/flex_sale.js';
		var loadSPOverride = function(){FW.requireScript(['sp/flex_sale.js', 'json.js'], initiateSale);};
		FW.requirePackage(['SP.Sale', 'SP.Sale.ProductTypeModule','SP.Sale.ProductTypeModule.Service_Mobile','SP.Sale.ProductTypeModule.Service_Landline','SP.Sale.ProductTypeModule.Service_Inbound','SP.Sale.ProductTypeModule.Service_ADSL'], loadSPOverride);
	}

	Event.observe(window, 'load', initiateJS, true);

</script>

<?php


		//$response->closePage();

		//Debug($objSale);
	}
}

?>
