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
	private $invoiceCDRCredits				= NULL;
	private $interimInvoiceDeliveryMethodId	= NULL;
	
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
				throw new Exception_Database("Failed to retrieve all Customer Groups: ". $selCustomerGroups->Error());
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

	public static function getForConstantName($strConstantName)
	{
		$arrCustomerGroups	= self::listAll();
		foreach ($arrCustomerGroups as $intCustomerGroupId=>$objCustomerGroup)
		{
			if ($objCustomerGroup->getConstantName() === $strConstantName || $objCustomerGroup->getConstantName() === strstr($strConstantName, 'CUSTOMER_GROUP_'))
			{
				return $objCustomerGroup;
			}
		}
		return null;
	}

	public function getConstantName()
	{
		return self::_makeConstantName($this->internalName);
	}
	
	public function getPaymentMethods()
	{
		return Customer_Group_Payment_Method::getForCustomerGroup($this->Id);
	}
	
	public function setDefaultAccountClassId($iAccountClassId)
	{
		$oQuery 	= new Query();
		$mResult	= $oQuery->Execute("	UPDATE	CustomerGroup
											SET		default_account_class_id = {$iAccountClassId}
											WHERE	Id = {$this->Id}");
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to update customer group default account class. ".$oQuery->Error());
		}
		
		return true;
	}
	
	public function toArray()
	{
		$aMe		= array();
		$aColumns	= self::getColumns();
		foreach ($aColumns as $sColumn)
		{
			$aMe[$sColumn]	= $this->{self::tidyName($sColumn)};
		}
		return $aMe;
	}
	
	public static function getDefaultAccountClassForCustomerGroup($iCustomerGroupId)
	{
		$oQuery 	= new Query();
		$mResult	= $oQuery->Execute("SELECT	default_account_class_id
										FROM	CustomerGroup
										WHERE	Id = {$iCustomerGroupId}");
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to get Customer Group default Account Class. ".$oQuery->Error());
		}
		
		$aRow = $mResult->fetch_assoc();
		if (!$aRow)
		{
			throw new Exception("Failed to get Customer Group default Account Class. Invalid Customer Group Id supplied '{$iCustomerGroupId}'");
		}
		
		return Account_Class::getForId($aRow['default_account_class_id']);
	}
	
	public static function getForDefaultAccountClassId($iAccountClassId)
	{
		$oQuery 	= new Query();
		$mResult	= $oQuery->Execute("SELECT	*
										FROM	CustomerGroup
										WHERE	default_account_class_id = {$iAccountClassId}");
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to get Customer Group default Account Class. ".$oQuery->Error());
		}
		
		$aCustomerGroups = array();
		while ($aRow = $mResult->fetch_assoc())
		{
			$aCustomerGroups[$aRow['Id']] = new Customer_Group($aRow);
		}
		return $aCustomerGroups;
	}
	
	private static function _makeConstantName($sInternalName)
	{
		return 'CUSTOMER_GROUP_'.strtoupper(str_replace(' ', '_', $sInternalName));
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
						"cooling_off_period",
						"invoice_cdr_credits",
						"interim_invoice_delivery_method_id",
						"default_account_class_id"
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

	public static function getForBaseURL($sBaseURL) {
		// TODO: We should probably make a full URL as well
		$mResult	= Query::run("
			SELECT		*
			FROM		CustomerGroup
			WHERE		flex_url LIKE <sBaseURL>
						OR flex_url LIKE CONCAT('http://', <sBaseURL>)
						OR flex_url LIKE CONCAT('https://', <sBaseURL>)
		", array('sBaseURL'=>trim((string)$sBaseURL)));

		$aMatches	= array();
		while ($aMatch = $mResult->fetch_assoc()) {
			$aMatches[$aMatch['Id']]	= new self($aMatch);
		}
		return $aMatches;
	}
	
}

?>
