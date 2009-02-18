<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// error.php (I DON'T THINK THIS IS ACTUALLY USED)
//----------------------------------------------------------------------------//
/**
 * error
 *
 * HTML Template for the HTML Error object
 *
 * HTML Template for the HTML Error object
 *
 * @file		error.php
 * @language	PHP
 * @package		ui_app
 * @author		Jared 'flame' Herbohn
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateError
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateConsole
 *
 * HTML Template class for the HTML Console object
 *
 * HTML Template class for the HTML Console object
 *
 *
 *
 * @package	ui_app
 * @class	HtmlTemplateConsole
 * @extends	HtmlTemplate
 */
class HtmlTemplateConsole extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{	
		?>
		
            <ul id="QuickList" class="Right"></ul>
            <h1>Employee Console</h1>
            <p>

			Welcome, <?php
			//debug (AuthenticatedUser()->_arrUser,1 );
			if (AuthenticatedUser()->_arrUser['FirstName'])
			{
				echo AuthenticatedUser()->_arrUser['FirstName'];
			} 
			else
			{
				echo ucfirst(AuthenticatedUser()->_arrUser['UserName']);
			}
			?> .
			You are currently logged into your Employee Account.
		</p>
            <div class="TinySeperator"></div>
            <h2>Menu</h2>
            <div class="SmallSeperator"></div>
            <table border="0" cellpadding="3" cellspacing="0">
              <tr>
                <td>
                  <a href="account_add.php">

                    <img src="img/template/contact_add.png" title="Add Customer" class="MenuIcon" />
                  </a>
                </td>
                <td><strong>
						Add Customer
					</strong><br />
					Add a new Customer to the system.
				</td>
              </tr>
              <tr>

                <td>
                  <a href="contact_verify.php">
                    <img src="img/template/contact_retrieve.png" title="Find Customer" class="MenuIcon" />
                  </a>
                </td>
                <td><strong>
						Find Customer
					</strong><br />
					Find a Customer and access their account.
				</td>

              </tr>
			  
              <tr>

                <td>
                  <a href="">
                    <img src="img/template/contact_retrieve.png" title="Find Customer" class="MenuIcon" />
                  </a>
                </td>
                <td><strong>
						Account Details
					</strong><br />
					Go to the <a href="../ui/flex.php/Account/Overview/?Account.Id=1000160841">account details</a> page of 1000160841<br />
					Go to the <a href="invoices_and_payments.php?Account.Id=1000160841">invoices and payments</a> page of 1000160841
				</td>

              </tr>

              <tr>
                <td>
                  <a href="#" onclick="return ModalDisplay ('#modalContent-recentCustomers')">
                    <img src="img/template/history.png" title="Recent Customers" class="MenuIcon" />
                  </a>
                </td>
                <td><strong>
						Recent Customers
					</strong><br />

					View recently accesed Customers.
				</td>
              </tr>
              <tr>
                <td>
                  <a href="../ui/flex.php/Plan/AvailablePlans/">
                    <img src="img/template/plans.png" title="View Plan Details" class="MenuIcon" />
                  </a>
                </td>

                <td><strong>
						View Available Plans
					</strong><br />
					View details of available Plans.
				</td>
              </tr>
              <tr>
                <td>
                  <a href="console_admin.php">
                    <img src="img/template/admin_console.png" title="Administrative Console" class="MenuIcon" />

                  </a>
                </td>
                <td><strong>
							Administrative Console
						</strong><br />
						Additional Administrative Options.
					</td>
              </tr>
              <tr>
                <td>

                  <a href="user_manual">
                    <img src="img/template/pdf.png" title="User Manual" class="MenuIcon" />
                  </a>
                </td>
                <td><strong>
						User Manual
					</strong><br />
					Need Help? Check the Manual.
				</td>
              </tr>

              <tr>
                <td>
                  <a href="logout.php" onclick="debug( Vixen.Logout()); return false;">
                    <img src="img/template/logout.png" title="Logout" class="MenuIcon" />
                  </a>
                </td>

                <td><strong>
						Logout
					</strong><br />
					Logout of the system.
				</td>
              </tr>
            </table>
			<?php
	}
}

?>
