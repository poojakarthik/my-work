<!DOCTYPE html>
<html>
<?php

error_reporting(0);

// HEAD
$D	= new DOM_Factory();
$D->getDOMDocument()->formatOutput	= true;

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
<br/><br/>\n";

echo $D->getDOMDocument()->saveXML(
	$D->div(array('id'=>'portal-user-registration'),
		$D->h2('Customer System — First Time User'),
		$D->strong(array('id'=>'portal-user-registration-error', 'class'=>(DBO()->GeneralError->Message->Value ? '-invalid' : '')), ''.(string)DBO()->GeneralError->Message->Value),
		$D->form(array('action'=>'','method'=>'post', 'novalidate'=>true),
			//  User Credentials 
			$D->fieldset(array('id'=>'portal-user-registration-user'),
				$D->legend('Your Details'),

				//  Given Name 
				$D->label(array('for'=>'portal-user-registration-user-givenname'), 'Given Name'),
				$D->input(array('id'=>'portal-user-registration-user-givenname','name'=>'user-givenname','type'=>'text','maxlength'=>'50','placeholder'=>'e.g. John','required'=>true, 'autofocus'=>true, 'value'=>$_POST['user-givenname'])),
				$D->strong(array('id'=>'portal-user-registration-user-givenname-error', 'class'=>(DBO()->ValidationErrors->Fields->Value['user-givenname']) ? '-invalid' : ''),
					''.(string)DBO()->ValidationErrors->Fields->Value['user-givenname']
				),

				//  Family Name 
				$D->label(array('for'=>'portal-user-registration-user-familyname'), 'Family Name'),
				$D->input(array('id'=>'portal-user-registration-user-familyname','name'=>'user-familyname','type'=>'text','maxlength'=>'50','placeholder'=>'e.g. Doe','required'=>true, 'value'=>$_POST['user-familyname'])),
				$D->strong(array('id'=>'portal-user-registration-user-familyname-error', 'class'=>(DBO()->ValidationErrors->Fields->Value['user-familyname']) ? '-invalid' : ''),
					''.(string)DBO()->ValidationErrors->Fields->Value['user-familyname']
				),

				//  Email Address 
				$D->label(array('for'=>'portal-user-registration-user-email'), 'Email Address'),
				$D->input(array('id'=>'portal-user-registration-user-email','name'=>'user-email','type'=>'email','maxlength'=>'256','required'=>true, 'value'=>$_POST['user-email'])),
				$D->strong(array('id'=>'portal-user-registration-user-email-error', 'class'=>(DBO()->ValidationErrors->Fields->Value['user-email']) ? '-invalid' : ''),
					''.(string)DBO()->ValidationErrors->Fields->Value['user-email']
				),
				$D->small('If you need to recover your password, a new one will be sent to this address.'),

				//  Username 
				$D->label(array('for'=>'portal-user-registration-user-username'), 'Username'),
				$D->input(array('id'=>'portal-user-registration-user-username','name'=>'user-username','type'=>'text','maxlength'=>'30','placeholder'=>'How you\'ll log in','pattern'=>'[0-9a-zA-Z_-]{3,30}','required'=>true, 'value'=>$_POST['user-username'])),
				$D->strong(array('id'=>'portal-user-registration-user-username-error', 'class'=>(DBO()->ValidationErrors->Fields->Value['user-username']) ? '-invalid' : ''),
					''.(string)DBO()->ValidationErrors->Fields->Value['user-username']
				),
				$D->small('Your username can only contain numbers, letters, the hyphen and underscore. There must be no spaces and must be between 3 and 30 characters.'),

				//  Password 
				$D->label(array('for'=>'portal-user-registration-user-password'), 'Password'),
				$D->input(array('id'=>'portal-user-registration-user-password','name'=>'user-password','type'=>'password','maxlength'=>'40','pattern'=>'.{6,40}','required'=>true)),
				$D->strong(array('id'=>'portal-user-registration-user-password-error', 'class'=>(DBO()->ValidationErrors->Fields->Value['user-password']) ? '-invalid' : ''),
					''.(string)DBO()->ValidationErrors->Fields->Value['user-password']
				),
				$D->small('Your password must be between 6 and 40 characters.'),

				//  Password Confirmation 
				$D->label(array('for'=>'portal-user-registration-user-password-confirm'), 'Confirm Password'),
				$D->input(array('id'=>'portal-user-registration-user-password-confirm','name'=>'user-password-confirm','type'=>'password','maxlength'=>'40','pattern'=>'.{6,40}','required'=>true))
			),
			
			//  Account Verification Details 
			$D->fieldset(array('id'=>'portal-user-registration-account'),
				$D->legend('Your Account\'s Details'),

				$D->small(array('class'=>'portal-user-registration-instructions'), 'These details can be found on your bill.'),

				$D->strong(array('id'=>'portal-user-registration-account-error', 'class'=>(DBO()->ValidationErrors->Fields->Value['account']) ? '-invalid' : ''),
					''.(string)DBO()->ValidationErrors->Fields->Value['account']
				),

				//  Account Number 
				$D->label(array('for'=>'portal-user-registration-account-number'), 'Account Number'),
				$D->input(array('id'=>'portal-user-registration-account-number','name'=>'account-number','type'=>'text','placeholder'=>'Account Number','pattern'=>'[0-9]+','required'=>true, 'value'=>$_POST['account-number'])),

				//  Account Name 
				$D->label(array('for'=>'portal-user-registration-account-name'), 'Account Name'),
				$D->input(array('id'=>'portal-user-registration-account-name','name'=>'account-name','type'=>'text','placeholder'=>'Who your bill is addressed to','required'=>true, 'value'=>$_POST['account-name'])),

				//  Recent Invoice Number 
				$D->label(array('for'=>'portal-user-registration-account-invoice-number'), 'Recent Invoice Number'),
				$D->input(array('id'=>'portal-user-registration-account-invoice-number','name'=>'account-invoice-number','type'=>'text','placeholder'=>'One of your 3 most recent Invoices','pattern'=>'[0-9]+','required'=>true, 'value'=>$_POST['account-invoice-number']))
			),

			//  Buttons 
			$D->fieldset(array('id'=>'portal-user-registration-buttons'),
				$D->button(array('id'=>'portal-user-registration-submit','type'=>'submit'), 'Create my Login'),
				$D->button(array('id'=>'portal-user-registration-cancel','type'=>'button'), 'Cancel')
			)
		)
	)
);
echo "\n";
echo $D->getDOMDocument()->saveXML(
	$D->p(
		'If you have already activated your account, ',
		$D->a(array('href'=>Href()->ResetPassword()), 'click here to reset your password'),
		'.'
	)
);

// Close the pageBody
CommonLayout::ClosePageBody(NULL);
?>

	</body>
</html>
