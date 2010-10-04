<?php
class Email_Template_Logic_Ebill extends Email_Template_Logic
{
	protected $_aVariables	=	array(
									'CustomerGroup'	=>	array(
															'external_name',
															'customer_service_phone'
														),
									'Account'		=>	array(
															'id'
														),
									'Invoice'		=>	array(
															'created_on'
														),
									'Contact'		=>	array(
															'first_name'
														)
								);
	public function getEmail($iCustomerGroup, $iAccount, $sInvoiceCreatedOn, $sContactName)
	{
		$aData	=	array(
						'CustomerGroup'	=>	Customer_Group::getForId($iCustomerGroup)->toArray(),
						'Account'		=>	array(
												'id'	=> $iAccount
											),
						'Invoice'		=>	array(
												'created_on'	=> date('F jS, Y', strtotime($sInvoiceCreatedOn))
											),
						'Contact'		=>	array(
												'first_name'	=> $sContactName
											)
					);
		$oEmail	= $this->_generateEmail($aData);
		return $oEmail;
	}
}
?>