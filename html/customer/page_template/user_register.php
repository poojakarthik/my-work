<!DOCTYPE html>
<html>
<?php

error_reporting(0);

// HEAD
$D	= new DOM_Factory();

$aScript	= explode('.php', $_SERVER['REQUEST_URI'], 2);
$sBaseDir = substr($aScript[0], 0,strrpos($aScript[0], "/") + 1);

if ($_SERVER['HTTPS']) {
	$sBaseDir	= "https://{$_SERVER['SERVER_NAME']}{$sBaseDir}";
} else {
	$sBaseDir	= "http://{$_SERVER['SERVER_NAME']}{$sBaseDir}";
}

echo $D->getDOMDocument()->saveXML($D->head(
	$D->meta(array('charset'=>'utf-8')),
	$D->title('Flex Systems Login'),
	$D->base(array('href'=>$sBaseDir)),
	$D->link(array('rel'=>'stylesheet', 'href'=>'css.php?v='.md5_file(TEMPLATE_BASE_DIR."css/default.css")))
));

?>
	<body>
<?php

// Load the common layout for this app
require_once dirname(__FILE__) . "/../layout_template/common_layout.php";

CommonLayout::OpenPageBody(NULL, FALSE, FALSE, array(0=>"Console",1=>"ResetPassword",2=>"SetupAccount"), "");

	print "
	<br/><br/>";

$sUsernameComment	= 'Your username can only contain numbers, letters, the hyphen and underscore. There must be no spaces and must be between 3 and 30 characters.';

?>
	<div id='portal-user-registration'>
		<h2>Customer System &mdash; First Time User</h2>
		<form action='' method='post'>
			<!-- User Credentials -->
			<fieldset id='portal-user-registration-user'>
				<legend>Your Details</legend>

				<!-- Username -->
				<label for='portal-user-registration-user-username'>Username</label>
				<input id='portal-user-registration-user-username' name='user-username' type='text' maxlength='30' placeholder='How you&apos;ll log in' pattern='[0-9a-zA-Z_-]{3,30}' required autofocus />
				<small><?=$sUsernameComment?></small>

				<!-- Password -->
				<label for='portal-user-registration-user-password'>Password</label>
				<input id='portal-user-registration-user-password' name='user-password' type='password' maxlength='40' pattern='.{6,40}' required />
				<small>Your password must be between 6 and 40 characters</small>

				<!-- Password Confirmation -->
				<label for='portal-user-registration-user-password-confirm'>Confirm Password</label>
				<input id='portal-user-registration-user-password-confirm' name='user-password-confirm' type='password' maxlength='40' pattern='.{6,40}' required />

				<!-- Email Address -->
				<label for='portal-user-registration-user-email'>Email</label>
				<input id='portal-user-registration-user-email' name='user-email' type='email' maxlength='256' required />
				<small>If you need to recover your password, a new one will be sent to this address</small>
			</fieldset>
			
			<!-- Account Verification Details -->
			<fieldset id='portal-user-registration-account'>
				<legend>Your Account&apos;s Details</legend>

				<small class='portal-user-registration-instructions'>These details can be found on your bill.</small>

				<!-- Account Number -->
				<label for='portal-user-registration-account-number'>Account Number</label>
				<input id='portal-user-registration-account-number' name='account-number' type='text' placeholder='Account Number' pattern='[0-9]+' required />

				<!-- Account Name -->
				<label for='portal-user-registration-account-name'>Account Name</label>
				<input id='portal-user-registration-account-name' name='account-name' type='text' placeholder='Who your bill is addressed to' required />

				<!-- Recent Invoice Number -->
				<label for='portal-user-registration-account-invoice-number'>Recent Invoice Number</label>
				<input id='portal-user-registration-account-invoice-number' name='account-invoice-number' type='text' placeholder='One of your 3 most recent Invoices' pattern='[0-9]+' required />
			</fieldset>

			<!-- Buttons -->
			<fieldset id='portal-user-registration-buttons'>
				<button id='portal-user-registration-submit' type='submit'>Create my Login</button>
				<button id='portal-user-registration-cancel' type='button'>Cancel</button>
			</fieldset>
		</form>
<?php		
	print "
		<p>
			If you have already activated your account, <A HREF=\"" . Href()->ResetPassword() . "\">click here to retrieve your password</A>.
		</p>
	<div><br/>";

// Close the pageBody
CommonLayout::ClosePageBody(NULL);
?>

	</body>
</html>
