<?php
	
	//----------------------------------------------------------------------------//
	// employee.php
	//----------------------------------------------------------------------------//
	/**
	 * employee.php
	 *
	 * File containing Employee Class
	 *
	 * File containing Employee Class
	 *
	 * @file		employee.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Employee
	//----------------------------------------------------------------------------//
	/**
	 * Employee
	 *
	 * An Employee in the Database
	 *
	 * An Employee in the Database
	 *
	 *
	 * @prefix	emp
	 *
	 * @package		intranet_app
	 * @class		Employee
	 * @extends		dataObject
	 */
	
	class Employee extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Employee
		 *
		 * Constructor for a new Employee
		 *
		 * @param	Integer		$intId		The Id of the Employee being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Employee information and Store it ...
			$selEmployee = new StatementSelect('Employee', '*', 'Id = <Id>', null, 1);
			$selEmployee->useObLib(TRUE);
			$intCount = $selEmployee->Execute(Array('Id' => $intId));

			if ($intCount != 1)
			{
				//TODO!flame! DO NOT DIE IF EMPLOYEE DOES NOT EXIST !!!!!!!!!!!!!!!!!
				//throw new Exception ('Employee does not exist.');
				$selEmployee->FetchCleanOblib('Employee', $this);
			}
			else
			{
				$selEmployee->Fetch($this);
			}
			
			// Construct the object
			parent::__construct('Employee', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Update
		//------------------------------------------------------------------------//
		/**
		 * Update()
		 *
		 * Updates the Employee
		 *
		 * Updates the Employee
		 *
		 * @param	AuthenticatedEmployee		$aemAuthenticatedEmployee		The employee (admin) who is updating this employee
		 * @param	Array						$arrData						The data to update to
		 * @return	Void
		 *
		 * @method
		 */
		
		public function Update (AuthenticatedEmployee $aemAuthenticatedEmployee, $arrData)
		{
			// If we're not updating our personal profile, then we have to be updating 
			// the profile of someone else. In this case, we want to lock this method off
			// so that only Admin users can do this
			
			if ($aemAuthenticatedEmployee->Pull ('Id')->getValue () <> $this->Pull ('Id')->getValue ())
			{
				// If this person isn't an Admin user, then they shouldn't be in this Method, so throw an exception
				if (!HasPermission ($aemAuthenticatedEmployee->Pull ('Privileges')->getValue (), PERMISSIONG_ADMIN))
				{
					throw new Exception ('You do not have access to update this employee');
				}
			}
			
			$arrEmployee = Array (
				'FirstName'				=> $arrData ['FirstName'],
				'LastName'				=> $arrData ['LastName'],
				'Email'					=> $arrData ['Email'],
				'Extension'				=> $arrData ['Extension'],
				'Phone'					=> $arrData ['Phone'],
				'Mobile'				=> $arrData ['Mobile'],
				'UserName'				=> $arrData ['UserName']
			);
			
			if (!empty ($arrData ['PassWord']))
			{
				$arrEmployee ['PassWord'] = sha1 ($arrData ['PassWord']);
			}
			
			$updEmployee = new StatementUpdate ('Employee', 'Id = <Id>', $arrEmployee, 1);
			$updEmployee->Execute ($arrEmployee, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// ArchiveStatus
		//------------------------------------------------------------------------//
		/**
		 * ArchiveStatus()
		 *
		 * Update Employee Archive Status
		 *
		 * Update Employee Archive Status. If an Unarchive is being attempted, 
		 * this method will check that the UserName hasn't been 'snatched' by someone else.
		 * If it has been snatched, then it will throw an Exception
		 *
		 * @param	Boolean		$bolArchive		TRUE/FALSE:		Whether or not to Archive this Employee
		 * @return	Void
		 *
		 * @method
		 */
		
		public function ArchiveStatus ($bolArchive)
		{
			
			// If we want to Unarchive an Employee, we have to Ensure that there isn't an unarchive (active)
			// account with the same username. If there is, throw an Exception
			
			if ($bolArchive == FALSE)
			{
				$selEmployee = new StatementSelect ('Employee', 'count(*) AS length', 'UserName = <UserName> AND Archived = 0');
				$selEmployee->Execute (Array ('UserName' => $this->Pull ('UserName')->getValue ()));
				$arrUserNames = $selEmployee->Fetch ();
				
				if ($arrUserNames ['length'] <> 0)
				{
					throw new Exception ('UserName Obtained Elsewhere');
				}
			}
			
			// Set up an Archive SET clause
			$arrArchive = Array (
				"Archived"	=>	($bolArchive == TRUE) ? "1" : "0"
			);
			
			$updEmployee = new StatementUpdate ('Employee', 'Id = <Id>', $arrArchive, 1);
			$updEmployee->Execute ($arrArchive, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// PermissionList
		//------------------------------------------------------------------------//
		/**
		 * PermissionList()
		 *
		 * Get a list of Permissions this user has
		 *
		 * Get a list of Permissions this user has
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function PermissionList ()
		{
			$oblarrPermissions = new dataArray ('PermissionList', 'Permission');
			
			// Test each Permission
			foreach ($GLOBALS['Permissions'] AS $intKey => $intValue)
			{
				if (HasPermission ($this->Pull ('Privileges')->getValue (), $intKey))
				{
					$oblarrPermissions->Push (new Permission ($intKey));
				}
			}
			
			return $oblarrPermissions;
		}
		
		//------------------------------------------------------------------------//
		// PermissionsSet
		//------------------------------------------------------------------------//
		/**
		 * PermissionsSet()
		 *
		 * Get a list of Permissions this user has
		 *
		 * Get a list of Permissions this user has
		 *
		 * @param	AuthenticatedEmploee	$aemAuthenticatedEmployee		The person wishing to perform this act
		 * @param	Array					$arrSelectedPermissions			A list of Selected Permissions
		 *
		 * @method
		 */
		
		public function PermissionsSet (AuthenticatedEmployee $aemAuthenticatedEmployee, $arrSelectedPermissions)
		{
			// If we're not updating our personal profile, then we have to be updating 
			// the profile of someone else. In this case, we want to lock this method off
			// so that only Admin users can do this
			
			if ($aemAuthenticatedEmployee->Pull ('Id')->getValue () <> $this->Pull ('Id')->getValue ())
			{
				// If this person isn't an Admin user, then they shouldn't be in this Method, so throw an exception
				if (!HasPermission ($aemAuthenticatedEmployee->Pull ('Privileges')->getValue (), PERMISSIONG_ADMIN))
				{
					throw new Exception ('You do not have access to update this employee');
				}
			}
			
			// Count the Permission Values
			$intNewPermission = 0;
			
			// We have to check we have a list. No list means revert of Permission (set to 0)
			if ($arrSelectedPermissions)
			{
				// Loop through each of the Selected Values
				foreach ($arrSelectedPermissions AS $intPermission)
				{
					$intNewPermission = AddPermission ($intNewPermission, $intPermission);
				}
			}
			
			$arrPermission = Array (
				'Privileges'		=> $intNewPermission
			);
			
			$updEmployeePermission = new StatementUpdate ('Employee', 'Id = <Id>', $arrPermission, 1);
			$updEmployeePermission->Execute ($arrPermission, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>
