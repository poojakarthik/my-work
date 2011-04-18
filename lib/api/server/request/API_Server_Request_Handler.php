<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Request_Handler
 *
 * @author JanVanDerBreggen
 */
abstract class API_Server_Request_Handler {

	protected $oRequest;
	protected $oResponse;
	protected $aParameters;

    abstract public static function handleRequest(API_Server_Request $oRequest, API_Server_Response $oResponse);

	abstract public static function create($oRequest, $oResponse);

	abstract public static function getQueryRegex();


	protected function __construct($oRequest, $oResponse)
	{
		$this->oRequest		= $oRequest;
		$this->oResponse	= $oResponse;
		$this->aParameters	= $this->oRequest->getParameters();
	}
}
?>
