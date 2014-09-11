<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Add in list of accounts
/*$arrAccounts[]	= 1000009145;
$arrAccounts[]	= 1000007460;
$arrAccounts[]	= 1000008407;
$arrAccounts[]	= 1000157133;
$arrAccounts[]	= 1000161583;
$arrAccounts[]	= 1000158216;
$arrAccounts[]	= 1000157698;
$arrAccounts[]	= 1000160393;
$arrAccounts[]	= 1000158098;
$arrAccounts[]	= 1000155964;
$arrAccounts[]	= 1000160897;

$arrAccounts[]	= 1000155466;
$arrAccounts[]	= 1000154838;
$arrAccounts[]	= 1000010826;
$arrAccounts[]	= 1000154946;
$arrAccounts[]	= 1000154811;
$arrAccounts[]	= 1000155253;
$arrAccounts[]	= 1000156068;
$arrAccounts[]	= 1000155105;
$arrAccounts[]	= 1000155666;
$arrAccounts[]	= 1000155637;
$arrAccounts[]	= 1000155676;
$arrAccounts[]	= 1000155650;
$arrAccounts[]	= 1000155468;
$arrAccounts[]	= 1000155090;
$arrAccounts[]	= 1000009313;
$arrAccounts[]	= 1000004847;
$arrAccounts[]	= 1000158138;
$arrAccounts[]	= 1000155669;
$arrAccounts[]	= 1000155182;
$arrAccounts[]	= 1000155629;
$arrAccounts[]	= 1000155463;
$arrAccounts[]	= 1000155462;
$arrAccounts[]	= 1000154972;

$arrAccounts[]	= 1000157470;
$arrAccounts[]	= 1000155675;
$arrAccounts[]	= 1000160134;
$arrAccounts[]	= 1000156140;
$arrAccounts[]	= 1000162484;
$arrAccounts[]	= 1000160091;
$arrAccounts[]	= 1000162036;
$arrAccounts[]	= 1000162126;
$arrAccounts[]	= 1000160474;
$arrAccounts[]	= 1000162398;
$arrAccounts[]	= 1000155448;
$arrAccounts[]	= 1000162399;
$arrAccounts[]	= 1000159676;
$arrAccounts[]	= 1000162272;
$arrAccounts[]	= 1000161896;
$arrAccounts[]	= 1000161662;
$arrAccounts[]	= 1000162422;
$arrAccounts[]	= 1000154838;
$arrAccounts[]	= 1000155425;
$arrAccounts[]	= 1000160187;
$arrAccounts[]	= 1000161442;
$arrAccounts[]	= 1000158564;
$arrAccounts[]	= 1000159454;
$arrAccounts[]	= 1000162403;
$arrAccounts[]	= 1000156445;
$arrAccounts[]	= 1000158849;
$arrAccounts[]	= 1000162265;
$arrAccounts[]	= 1000160762;
$arrAccounts[]	= 1000155640;
$arrAccounts[]	= 1000161203;*/

// FEB INVOICES W/ -VE TOTAL
$arrAccounts[]	= 1000007119;
$arrAccounts[]	= 1000160627;
$arrAccounts[]	= 1000156623;
$arrAccounts[]	= 1000007747;
$arrAccounts[]	= 1000158855;
$arrAccounts[]	= 1000161414;
$arrAccounts[]	= 1000157656;
$arrAccounts[]	= 1000160366;
$arrAccounts[]	= 1000156671;
$arrAccounts[]	= 1000159240;
$arrAccounts[]	= 1000157890;
$arrAccounts[]	= 1000157773;
$arrAccounts[]	= 1000157424;
$arrAccounts[]	= 1000008776;
$arrAccounts[]	= 1000162552;
$arrAccounts[]	= 1000155462;
$arrAccounts[]	= 1000008828;
$arrAccounts[]	= 1000162146;
$arrAccounts[]	= 1000162096;
$arrAccounts[]	= 1000006692;
$arrAccounts[]	= 1000158937;
$arrAccounts[]	= 1000158008;
$arrAccounts[]	= 1000158857;
$arrAccounts[]	= 1000162724;
$arrAccounts[]	= 1000161641;
$arrAccounts[]	= 1000160297;
$arrAccounts[]	= 1000162115;
$arrAccounts[]	= 1000162655;
$arrAccounts[]	= 1000157294;
$arrAccounts[]	= 1000161721;
$arrAccounts[]	= 1000006622;
$arrAccounts[]	= 1000160407;
$arrAccounts[]	= 1000161395;
$arrAccounts[]	= 1000157670;
$arrAccounts[]	= 1000158708;
$arrAccounts[]	= 1000160535;
$arrAccounts[]	= 1000161498;
$arrAccounts[]	= 1000156547;
$arrAccounts[]	= 1000007066;
$arrAccounts[]	= 1000162337;
$arrAccounts[]	= 1000159751;
$arrAccounts[]	= 1000162422;
$arrAccounts[]	= 1000161896;
$arrAccounts[]	= 1000161006;
$arrAccounts[]	= 1000157227;
$arrAccounts[]	= 1000162550;
$arrAccounts[]	= 1000006978;
$arrAccounts[]	= 1000160684;
$arrAccounts[]	= 1000162394;
$arrAccounts[]	= 1000155184;
$arrAccounts[]	= 1000161559;
$arrAccounts[]	= 1000162286;
$arrAccounts[]	= 1000162527;
$arrAccounts[]	= 1000008228;
$arrAccounts[]	= 1000161034;
$arrAccounts[]	= 1000159314;
$arrAccounts[]	= 1000159926;
$arrAccounts[]	= 1000162636;
$arrAccounts[]	= 1000162607;
$arrAccounts[]	= 1000162804;
$arrAccounts[]	= 1000158243;
$arrAccounts[]	= 1000160777;
$arrAccounts[]	= 1000161752;
$arrAccounts[]	= 1000162186;
$arrAccounts[]	= 1000161555;
$arrAccounts[]	= 1000160721;
$arrAccounts[]	= 1000162677;
$arrAccounts[]	= 1000160358;
$arrAccounts[]	= 1000162433;
$arrAccounts[]	= 1000011005;
$arrAccounts[]	= 1000162753;
$arrAccounts[]	= 1000162528;
$arrAccounts[]	= 1000162348;
$arrAccounts[]	= 1000156577;
$arrAccounts[]	= 1000162427;
$arrAccounts[]	= 1000162511;
$arrAccounts[]	= 1000162817;
$arrAccounts[]	= 1000157423;
$arrAccounts[]	= 1000160063;
$arrAccounts[]	= 1000161926;
$arrAccounts[]	= 1000154895;
$arrAccounts[]	= 1000156646;
$arrAccounts[]	= 1000160583;
$arrAccounts[]	= 1000161402;
$arrAccounts[]	= 1000161529;
$arrAccounts[]	= 1000159124;
$arrAccounts[]	= 1000161630;
$arrAccounts[]	= 1000161915;
$arrAccounts[]	= 1000160757;
$arrAccounts[]	= 1000159949;
$arrAccounts[]	= 1000155908;
$arrAccounts[]	= 1000161618;
$arrAccounts[]	= 1000158071;
$arrAccounts[]	= 1000162159;
$arrAccounts[]	= 1000162525;
$arrAccounts[]	= 1000155750;
$arrAccounts[]	= 1000161011;
$arrAccounts[]	= 1000009184;
$arrAccounts[]	= 1000160731;
$arrAccounts[]	= 1000162521;
$arrAccounts[]	= 1000157801;
$arrAccounts[]	= 1000159680;
$arrAccounts[]	= 1000162670;
$arrAccounts[]	= 1000161225;
$arrAccounts[]	= 1000161586;
$arrAccounts[]	= 1000158361;
$arrAccounts[]	= 1000007060;
$arrAccounts[]	= 1000162612;
$arrAccounts[]	= 1000161164;
$arrAccounts[]	= 1000161096;
$arrAccounts[]	= 1000162782;
$arrAccounts[]	= 1000009476;
$arrAccounts[]	= 1000006842;
$arrAccounts[]	= 1000158879;
$arrAccounts[]	= 1000161770;
$arrAccounts[]	= 1000161591;
$arrAccounts[]	= 1000161895;
$arrAccounts[]	= 1000161338;
$arrAccounts[]	= 1000161620;
$arrAccounts[]	= 1000160730;
$arrAccounts[]	= 1000160642;
$arrAccounts[]	= 1000158804;
$arrAccounts[]	= 1000156335;
$arrAccounts[]	= 1000162707;
$arrAccounts[]	= 1000161352;
$arrAccounts[]	= 1000162605;
$arrAccounts[]	= 1000162579;
$arrAccounts[]	= 1000156418;
$arrAccounts[]	= 1000162434;
$arrAccounts[]	= 1000161265;
$arrAccounts[]	= 1000161204;
$arrAccounts[]	= 1000162460;
$arrAccounts[]	= 1000006717;
$arrAccounts[]	= 1000162135;
$arrAccounts[]	= 1000156336;
$arrAccounts[]	= 1000157844;
$arrAccounts[]	= 1000162505;
$arrAccounts[]	= 1000009837;
$arrAccounts[]	= 1000154860;
$arrAccounts[]	= 1000158526;
$arrAccounts[]	= 1000159203;
$arrAccounts[]	= 1000160066;
$arrAccounts[]	= 1000160628;
$arrAccounts[]	= 1000161212;
$arrAccounts[]	= 1000161222;
$arrAccounts[]	= 1000161920;
$arrAccounts[]	= 1000162398;
$arrAccounts[]	= 1000160964;
$arrAccounts[]	= 1000155866;
$arrAccounts[]	= 1000161994;
$arrAccounts[]	= 1000007914;
$arrAccounts[]	= 1000159032;
$arrAccounts[]	= 1000162746;
$arrAccounts[]	= 1000162522;
$arrAccounts[]	= 1000156073;
$arrAccounts[]	= 1000160189;
$arrAccounts[]	= 1000161654;
$arrAccounts[]	= 1000156720;
$arrAccounts[]	= 1000162295;
$arrAccounts[]	= 1000010069;
$arrAccounts[]	= 1000157074;
$arrAccounts[]	= 1000158810;
$arrAccounts[]	= 1000161344;
$arrAccounts[]	= 1000162475;
$arrAccounts[]	= 1000161792;
$arrAccounts[]	= 1000161381;
$arrAccounts[]	= 1000162718;
$arrAccounts[]	= 1000160834;
$arrAccounts[]	= 1000161461;
$arrAccounts[]	= 1000008900;
$arrAccounts[]	= 1000162712;
$arrAccounts[]	= 1000010420;
$arrAccounts[]	= 1000159546;
$arrAccounts[]	= 1000157566;
$arrAccounts[]	= 1000160092;
$arrAccounts[]	= 1000158287;
$arrAccounts[]	= 1000160682;
$arrAccounts[]	= 1000157536;
$arrAccounts[]	= 1000160860;
$arrAccounts[]	= 1000161541;
$arrAccounts[]	= 1000157133;
$arrAccounts[]	= 1000157758;
$arrAccounts[]	= 1000161181;
$arrAccounts[]	= 1000162275;
$arrAccounts[]	= 1000161964;
$arrAccounts[]	= 1000161086;
$arrAccounts[]	= 1000161857;
$arrAccounts[]	= 1000159462;
$arrAccounts[]	= 1000160891;
$arrAccounts[]	= 1000160304;
$arrAccounts[]	= 1000159875;


/*
// Get latest invoice run
$selInvoiceRun = new StatementSelect("Invoice", "InvoiceRun", "1", "CreatedOn DESC", 1);
$selInvoiceRun->Execute();
$arrInvoiceRun = $selInvoiceRun->Fetch();
$strInvoiceRun = $arrInvoiceRun['InvoiceRun'];
*/

// Specify the InvoiceRun
$strInvoiceRun = '45f4cb0c0a135';

$arrInvoices = Array();

// Get list of invoices
$selInvoices = new StatementSelect("Invoice", "Id", "Account = <Account> AND InvoiceRun = '$strInvoiceRun'");
foreach ($arrAccounts as $intAccount)
{
	$selInvoices->Execute(Array('Account' => $intAccount));
	$arrInvoice = $selInvoices->Fetch();
	$arrInvoices[] = $arrInvoice['Id'];
}

// reprint
$bolResponse = $appBilling->Reprint($arrInvoices);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>
