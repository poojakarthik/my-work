<?php

//----------------------------------------------------------------------------//
// Customer_Group
//----------------------------------------------------------------------------//
/**
 * Customer_Group
 *
 * Models a single customer group.  Also includes other customer group functionality
 *
 * Models a single customer group.  Also includes other customer group functionality
 *
 * @class	Customer_Group
 */
class Customer_Group 
{
	private $id								= NULL;
	private $internalName					= NULL;
	private $externalName					= NULL;
	private $outboundEmail					= NULL;
	private $flexUrl						= NULL;
	private $emailDomain					= NULL;
	private $customerPrimaryColor			= NULL;
	private $customerSecondaryColor			= NULL;
	private $customerLogo					= NULL;
	private $customerLogoType				= NULL;
	private $customerBreadcrumbMenuColor	= NULL;
	private $customerExitUrl				= NULL;
	private $billPayBillerCode				= NULL;
	private $abn							= NULL;
	private $acn							= NULL;
	private $businessPhone					= NULL;
	private $businessFax					= NULL;
	private $businessWeb					= NULL;
	private $businessContactEmail			= NULL;
	private $businessInfoEmail				= NULL;
	private $customerServicePhone			= NULL;
	private $customerServiceEmail			= NULL;
	private $customerServiceContactName		= NULL;
	private $businessPayableName			= NULL;
	private $businessPayableAddress			= NULL;
	private $creditCardPaymentPhone			= NULL;
	private $faultsPhone					= NULL;
	private $coolingOffPeriod				= NULL;
	
	private $value							= NULL;
	private $name							= NULL;
	private $description					= NULL;
	private $constant						= NULL;
	
	
	protected $arrProperties = array();

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @param		array	$arrProperties 	Optional.  Associative array defining a customer group with keys for each field of the CustomerGroup table
	 * @return		void
	 * @constructor
	 */
	private function __construct($arrProperties=NULL)
	{
		if ($arrProperties)
		{
			$this->init($arrProperties);
		}
	}

	public static function getAll($bolForceRefresh=FALSE)
	{
		return self::listAll($bolForceRefresh);
	}

	public static function listAll($bolForceRefresh=FALSE)
	{
		static $arrCustomerGroups;
		if (!isset($arrCustomerGroups) || $bolForceRefresh)
		{
			$arrCustomerGroups = array();
			
			$selCustomerGroups = new StatementSelect("CustomerGroup", self::getColumns(), "", "internal_name ASC");
			if (($outcome = $selCustomerGroups->Execute()) === FALSE)
			{
				throw new Exception("Failed to retrieve all Customer Groups: ". $selCustomerGroups->Error());
			}
	
			while ($arrCustomerGroup = $selCustomerGroups->Fetch())
			{
				$arrCustomerGroups[$arrCustomerGroup['Id']] = new self($arrCustomerGroup);
			}
		}
		return $arrCustomerGroups;
	}

	public static function getForId($id)
	{
		$instances = self::listAll();
		$id = intval($id);
		return (array_key_exists($id, $instances)) ? $instances[$id] : NULL;
	}
	
	//------------------------------------------------------------------------//
	// getColumns
	//------------------------------------------------------------------------//
	/**
	 * getColumns()
	 *
	 * Returns array defining the columns of the CustomerGroup table
	 * 
	 * Returns array defining the columns of the CustomerGroup table
	 *
	 * @return		array	
	 * @method
	 */
	protected static function getColumns()
	{
		return array(
						"Id",
						"internal_name",
						"external_name",
						"outbound_email",
						"flex_url",
						"email_domain",
						"customer_primary_color",
						"customer_secondary_color",
						"customer_logo",
						"customer_logo_type",
						"customer_breadcrumb_menu_color",
						"customer_exit_url",
						"bill_pay_biller_code",
						"abn",
						"acn",
						"business_phone",
						"business_fax",
						"business_web",
						"business_contact_email",
						"business_info_email",
						"customer_service_phone",
						"customer_service_email",
						"customer_service_contact_name",
						"business_payable_name",
						"business_payable_address",
						"credit_card_payment_phone",
						"faults_phone",
						"cooling_off_period"
					);
	}

	//------------------------------------------------------------------------//
	// init
	//------------------------------------------------------------------------//
	/**
	 * init()
	 *
	 * Initialises the Customer_Group object
	 * 
	 * Initialises the Customer_Group object
	 *
	 * @param		array	$arrProperties		assoc array modelling record of CustomerGroup table
	 * @return		void
	 * @method
	 */
	private function init($arrProperties)
	{
		foreach($arrProperties as $name => $value)
		{
			$this->{self::tidyName($name)} = $value;
		}

		// Constant Group stuff
		$this->name			= $this->internalName;
		$this->description	= $this->internalName;
		$this->constant		= "CUSTOMER_GROUP_" . strtoupper(str_replace(" ", "_", $this->internalName));
		$this->value		= $this->id;
	}

	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * accessor method
	 * 
	 * accessor method
	 *
	 * @param	string	$strName	name of property to get. in either of the formats xxxYyyZzz or xxx_yyy_zzz 
	 * @return	void
	 * @method
	 */
	public function __get($strName)
	{
		if ($strName[0] === '_')
		{
			// Don't allow access to data attributes that start with '_'
			return NULL;
		}
		if (property_exists($this, $strName) || (($strName = self::tidyName($strName)) && property_exists($this, $strName)))
		{
			return $this->{$strName};
		}
		return NULL;
	}
	
	//------------------------------------------------------------------------//
	// tidyName
	//------------------------------------------------------------------------//
	/**
	 * tidyName()
	 *
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * 
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * If the string is already in the xxxYxxZzz format, then it will not be changed
	 *
	 * @param	string	$strName
	 * @return	string
	 * @method
	 */
	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}

	
}

?>
