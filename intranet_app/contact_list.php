<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2006 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//
	
	// call application loader
	require ('config/application_loader.php');
	
	// set page details
	$arrPage['PopUp']		= FALSE;
	$arrPage['Permission']	= PERMISSION_OPERATOR;
	$arrPage['Modules']		= MODULE_BASE | MODULE_SERVICE | MODULE_ACCOUNT | MODULE_CONTACT;
	
	// call application
	require ('config/application.php');
	
	$docDocumentation->Explain ("Account");
	$docDocumentation->Explain ("Service");
	
	// Stage Two: Validate the Account or Service and Choose a Contact
	if ($_POST ['Account'] || $_POST ['FNN'])
	{
		try
		{
			// The main aim in the following IF ... (ELSE IF ... ) ELSE ... statement 
			// is to get a Contact that we can use to Authenticate against. If you
			// gracefully exit out of this block, it can be assumed that the value of
			// the variable $actAccount is the Account that you wish to edit.
			
			// Also - we only want to attach the Account to $Style if it's what we searched by
			
			// Try the Account first
			if ($_POST ['Account'])
			{
				$actAccount = new Account ($_POST ['Account']);
				$Style->attachObject ($actAccount);
			}
			// Then try the Service FNN
			else if ($_POST ['FNN'])
			{
				$srvService = Services::UnarchivedFNN ($_POST ['FNN']);
				$actAccount = $srvService->getAccount ();
				
				$Style->attachObject ($srvService);
			}
			else
			{
				header ('Location: contact_list.php');
				exit;
			}
			
			$docDocumentation->Explain ("Contact");
			
			// BRANCH
			
			// If there is a Contact Specified, try Authenticating the follow:
			
			if ($_POST ['Contact'])
			{
				// We've reached the point where we want to do stage 3: verification
				
				// Push the Account
				// If it's already on there - it won't push it twice.
				if (!$_POST ['Account'])
				{
					$Style->attachObject ($actAccount);
				}
				
				// Explain the Invoice, CreditCard and Direct Debit
				$docDocumentation->Explain ("Invoice");
				$docDocumentation->Explain ("Credit Card");
				$docDocumentation->Explain ("Direct Debit");
				
				// Get the Contact and attach it to the Style object
				// If the Contact comes through invalid, this is handled in the TRY/CATCH
				$cntContact = new Contact ($_POST ['Contact']);
				$Style->attachObject ($cntContact);
				
				
				// If we have a verification proceedure (which is identified by the presence of the $_POST ['Fields'] array ...
				if ($_POST ['Fields'])
				{
					// RULES FOR SUCCESSFUL VERIFICATION
					
					// 1.	At least 3 Fields must be ticked and specified and Completely Correct
					//		in order to successfully validate.
					//
					//		Except:
					//		A.	If the Business Name and Trading Name fields are ticked, there must be 4
					//
					//
					// 2.	If the Business Name field is Ticked, the Trading Name Field must also
					//		be Ticked (and visa versa)
					
					
					// Innocent until proven guilty
					$bolValidated = TRUE;
					
					foreach ($_POST ['Fields'] AS $strEntityName => $strEntityValue)
					{
						switch ($strEntityName)
						{
							case 'Account-Id':
								if ($actAccount->Pull ('Id')->getValue () <> $_POST ['Values']['Account-Id'])
								{
									$bolValidated = false;
								}
								
								break;
								
							case 'Account-BusinessName':
								if (!isset ($_POST ['Fields']['Account-TradingName']))
								{
									$bolValidated = false;
								}
								
								break;
								
							case 'Account-TradingName':
								if (!isset ($_POST ['Fields']['Account-BusinessName']))
								{
									$bolValidated = false;
								}
								
								break;
								
							case 'Account-Address':
								break;
								
							case 'Account-ABN':
								$abnABN = new ABN ('ABN', $_POST ['Values']['Account-ABN']);
								if ($actAccount->Pull ('ABN')->getValue () <> $abnABN->getValue ())
								{
									$bolValidated = false;
								}
								
								break;
								
							case 'Account-ACN':
								$acnACN = new ACN ('ACN', $_POST ['Values']['Account-ACN']);
								if ($actAccount->Pull ('ACN')->getValue () <> $acnACN->getValue ())
								{
									$bolValidated = false;
								}
								
								break;
								
							case 'Contact-DOB':
								break;
								
							case 'Invoice-Amount':
								break;
								
							case 'CreditCard-CardNumber':
								break;
								
							case 'CreditCard-Expiration':
								break;
								
							case 'DirectDebit-BSB':
								break;
								
							default:
								$bolValidated = false;
								break;
						}
						
						if (!$bolValidated)
						{
							echo $strEntityName;
							break;
						}
					}
					
					if ($bolValidated)
					{
						// Record a request to view a Contact/Account in the Audit
						$athAuthentication->AuthenticatedEmployee ()->Audit ()->RecordContact ($cntContact);
						$athAuthentication->AuthenticatedEmployee ()->Save ();
						
						// Redirect to the Contact's Page
						header ("Location: contact_view.php?Id=" . $cntContact->Pull ('Id')->getValue ());
						exit;
					}
				}
				
				$Style->Output ('xsl/content/contact/3_verify.xsl');
			}
			else
			{
				// If we have reached this Branch, then we
				// have not selected a Contact from the List
				// Therefore, we have to pull Basic Information
				// about each Contact so we can select a Contact
				// based on their Name
				
				// Pull information about Contacts
				$cnsContacts = $Style->attachObject (new Contacts ());
				$cnsContacts->Constrain ('Account', '=', $actAccount->Pull ('Id')->getValue ());
				$cnsContacts->Order ('LastName', FALSE);
				$cnsContacts->Sample ();
				
				$Style->Output ('xsl/content/contact/2_contact.xsl');
			}
		}
		catch (Exception $e)
		{
			header ('Location: contact_list.php');
			exit;
		}
		
		exit;
	}
	
	// Stage One: Identify an Account or a Service
	else
	{
		$Style->Output ('xsl/content/contact/1_account.xsl');
	}
	
?>
