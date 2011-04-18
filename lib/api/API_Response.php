<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Response
 *
 * @author JanVanDerBreggen
 */
class API_Response {
   	const STATUS_CODE_OK					= 200;
	const STATUS_CODE_CREATED				= 201;
	const STATUS_CODE_ACCEPTED				= 202;
	const STATUS_CODE_BAD_REQUEST			= 400;
	const STATUS_CODE_NOT_FOUND				= 404;
	const STATUS_CODE_METHOD_NOT_ALLOWED	= 405;
	const STATUS_CODE_SERVER_ERROR			= 500;
	const STATUS_CODE_NOT_IMPLEMENTED		= 501;

	const CONTENT_TYPE_HTML = 'text/html';
	const CONTENT_TYPE_PDF	= 'application/pdf';
	const CONTENT_TYPE_JSON = 'application/json';


	protected static $aCodes = Array(
							100 => 'Continue',
							101 => 'Switching Protocols',
							200 => 'OK',
							201 => 'Created',
							202 => 'Accepted',
							203 => 'Non-Authoritative Information',
							204 => 'No Content',
							205 => 'Reset Content',
							206 => 'Partial Content',
							300 => 'Multiple Choices',
							301 => 'Moved Permanently',
							302 => 'Found',
							303 => 'See Other',
							304 => 'Not Modified',
							305 => 'Use Proxy',
							306 => '(Unused)',
							307 => 'Temporary Redirect',
							400 => 'Bad Request',
							401 => 'Unauthorized',
							402 => 'Payment Required',
							403 => 'Forbidden',
							404 => 'Not Found',
							405 => 'Method Not Allowed',
							406 => 'Not Acceptable',
							407 => 'Proxy Authentication Required',
							408 => 'Request Timeout',
							409 => 'Conflict',
							410 => 'Gone',
							411 => 'Length Required',
							412 => 'Precondition Failed',
							413 => 'Request Entity Too Large',
							414 => 'Request-URI Too Long',
							415 => 'Unsupported Media Type',
							416 => 'Requested Range Not Satisfiable',
							417 => 'Expectation Failed',
							500 => 'Internal Server Error',
							501 => 'Not Implemented',
							502 => 'Bad Gateway',
							503 => 'Service Unavailable',
							504 => 'Gateway Timeout',
							505 => 'HTTP Version Not Supported'
						);

	protected $iResponseStatus;
	protected $sBody;
	protected $sContentType;


	public function getStatusCodeMessage()
	{
		return (isset(self::$aCodes[$this->iResponseStatus])) ? self::$aCodes[$this->iResponseStatus] : '';
	}
}
?>
