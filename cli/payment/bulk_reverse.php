<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import and process payments
//----------------------------------------------------------------------------//
require_once('../../flex.require.php');
$arrConfig = LoadApplication();



$arrPayments	= Array();
$arrPayments[]	= 82278;
$arrPayments[]	= 82279;
$arrPayments[]	= 82280;
$arrPayments[]	= 82281;
$arrPayments[]	= 82282;
$arrPayments[]	= 82283;
$arrPayments[]	= 82284;
$arrPayments[]	= 82285;
$arrPayments[]	= 82286;
$arrPayments[]	= 82287;
$arrPayments[]	= 82288;
$arrPayments[]	= 82289;
$arrPayments[]	= 82290;
$arrPayments[]	= 82291;
$arrPayments[]	= 82292;
$arrPayments[]	= 82293;
$arrPayments[]	= 82294;
$arrPayments[]	= 82295;
$arrPayments[]	= 82296;
$arrPayments[]	= 82297;
$arrPayments[]	= 82298;
$arrPayments[]	= 82299;
$arrPayments[]	= 82300;
$arrPayments[]	= 82301;
$arrPayments[]	= 82302;
$arrPayments[]	= 82303;
$arrPayments[]	= 82304;
$arrPayments[]	= 82305;
$arrPayments[]	= 82306;
$arrPayments[]	= 82307;
$arrPayments[]	= 82308;
$arrPayments[]	= 82309;
$arrPayments[]	= 82310;
$arrPayments[]	= 82311;
$arrPayments[]	= 82312;
$arrPayments[]	= 82313;
$arrPayments[]	= 82314;
$arrPayments[]	= 82315;
$arrPayments[]	= 82316;
$arrPayments[]	= 82317;
$arrPayments[]	= 82318;
$arrPayments[]	= 82319;
$arrPayments[]	= 82320;
$arrPayments[]	= 82321;
$arrPayments[]	= 82322;
$arrPayments[]	= 82323;
$arrPayments[]	= 82324;
$arrPayments[]	= 82325;
$arrPayments[]	= 82326;
$arrPayments[]	= 82327;
$arrPayments[]	= 82328;
$arrPayments[]	= 82329;
$arrPayments[]	= 82330;
$arrPayments[]	= 82331;
$arrPayments[]	= 82332;
$arrPayments[]	= 82333;
$arrPayments[]	= 82334;
$arrPayments[]	= 82335;
$arrPayments[]	= 82336;
$arrPayments[]	= 82337;
$arrPayments[]	= 82338;
$arrPayments[]	= 82339;
$arrPayments[]	= 82340;
$arrPayments[]	= 82341;
$arrPayments[]	= 82342;
$arrPayments[]	= 82343;
$arrPayments[]	= 82344;
$arrPayments[]	= 82345;
$arrPayments[]	= 82346;
$arrPayments[]	= 82347;
$arrPayments[]	= 82348;
$arrPayments[]	= 82349;
$arrPayments[]	= 82350;
$arrPayments[]	= 82351;
$arrPayments[]	= 82352;
$arrPayments[]	= 82353;
$arrPayments[]	= 82354;
$arrPayments[]	= 82355;
$arrPayments[]	= 82356;
$arrPayments[]	= 82357;
$arrPayments[]	= 82358;
$arrPayments[]	= 82359;
$arrPayments[]	= 82360;
$arrPayments[]	= 82361;
$arrPayments[]	= 82362;
$arrPayments[]	= 82363;
$arrPayments[]	= 82364;
$arrPayments[]	= 82365;
$arrPayments[]	= 82366;
$arrPayments[]	= 82367;
$arrPayments[]	= 82368;
$arrPayments[]	= 82369;
$arrPayments[]	= 82370;
$arrPayments[]	= 82371;
$arrPayments[]	= 82372;
$arrPayments[]	= 82373;
$arrPayments[]	= 82374;
$arrPayments[]	= 82375;
$arrPayments[]	= 82376;
$arrPayments[]	= 82377;
$arrPayments[]	= 82378;
$arrPayments[]	= 82379;
$arrPayments[]	= 82380;
$arrPayments[]	= 82381;
$arrPayments[]	= 82382;
$arrPayments[]	= 82383;
$arrPayments[]	= 82384;
$arrPayments[]	= 82385;
$arrPayments[]	= 82386;
$arrPayments[]	= 82387;
$arrPayments[]	= 82388;
$arrPayments[]	= 82389;
$arrPayments[]	= 82390;
$arrPayments[]	= 82391;
$arrPayments[]	= 82392;
$arrPayments[]	= 82393;
$arrPayments[]	= 82394;
$arrPayments[]	= 82395;
$arrPayments[]	= 82396;
$arrPayments[]	= 82397;
$arrPayments[]	= 82398;
$arrPayments[]	= 82399;
$arrPayments[]	= 82400;
$arrPayments[]	= 82401;
$arrPayments[]	= 82402;
$arrPayments[]	= 82403;
$arrPayments[]	= 82404;
$arrPayments[]	= 82405;
$arrPayments[]	= 82406;
$arrPayments[]	= 82407;
$arrPayments[]	= 82408;
$arrPayments[]	= 82409;
$arrPayments[]	= 82410;
$arrPayments[]	= 82411;
$arrPayments[]	= 82412;
$arrPayments[]	= 82413;
$arrPayments[]	= 82414;
$arrPayments[]	= 82415;
$arrPayments[]	= 82416;
$arrPayments[]	= 82417;
$arrPayments[]	= 82418;
$arrPayments[]	= 82419;
$arrPayments[]	= 82420;
$arrPayments[]	= 82421;
$arrPayments[]	= 82422;
$arrPayments[]	= 82423;
$arrPayments[]	= 82424;
$arrPayments[]	= 82425;
$arrPayments[]	= 82426;
$arrPayments[]	= 82427;
$arrPayments[]	= 82428;
$arrPayments[]	= 82429;
$arrPayments[]	= 82430;
$arrPayments[]	= 82431;
$arrPayments[]	= 82432;
$arrPayments[]	= 82433;
$arrPayments[]	= 82434;
$arrPayments[]	= 82435;
$arrPayments[]	= 82436;
$arrPayments[]	= 82437;
$arrPayments[]	= 82438;
$arrPayments[]	= 82439;
$arrPayments[]	= 82440;
$arrPayments[]	= 82441;
$arrPayments[]	= 82442;
$arrPayments[]	= 82443;
$arrPayments[]	= 82444;
$arrPayments[]	= 82445;
$arrPayments[]	= 82446;
$arrPayments[]	= 82447;
$arrPayments[]	= 82448;
$arrPayments[]	= 82449;
$arrPayments[]	= 82450;
$arrPayments[]	= 82451;
$arrPayments[]	= 82452;
$arrPayments[]	= 82453;
$arrPayments[]	= 82454;
$arrPayments[]	= 82455;
$arrPayments[]	= 82456;
$arrPayments[]	= 82457;
$arrPayments[]	= 82458;
$arrPayments[]	= 82459;
$arrPayments[]	= 82460;
$arrPayments[]	= 82461;
$arrPayments[]	= 82462;
$arrPayments[]	= 82463;
$arrPayments[]	= 82464;
$arrPayments[]	= 82465;
$arrPayments[]	= 82466;
$arrPayments[]	= 82467;
$arrPayments[]	= 82468;
$arrPayments[]	= 82469;
$arrPayments[]	= 82470;
$arrPayments[]	= 82471;
$arrPayments[]	= 82472;
$arrPayments[]	= 82473;
$arrPayments[]	= 82474;
$arrPayments[]	= 82475;
$arrPayments[]	= 82476;
$arrPayments[]	= 82477;
$arrPayments[]	= 82478;
$arrPayments[]	= 82479;
$arrPayments[]	= 82480;
$arrPayments[]	= 82481;
$arrPayments[]	= 82482;
$arrPayments[]	= 82483;
$arrPayments[]	= 82484;
$arrPayments[]	= 82485;
$arrPayments[]	= 82486;
$arrPayments[]	= 82487;
$arrPayments[]	= 82488;
$arrPayments[]	= 82489;
$arrPayments[]	= 82490;
$arrPayments[]	= 82491;
$arrPayments[]	= 82492;
$arrPayments[]	= 82493;
$arrPayments[]	= 82494;
$arrPayments[]	= 82495;





// Application entry point - create an instance of the application object
$appPayment = new ApplicationPayment($arrConfig);

// Execute the application
foreach ($arrPayments as $intPaymentId)
{
	CliEcho(($appPayment->ReversePayment($intPaymentId)) ? "Reversed Payment #$intPaymentId\n" : "FAILED!\n");
}

// finished
echo("\n-- End of Payments --\n");
echo "</pre>";
die();

?>
