<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of API_Client_Request
 *
 * @author JanVanDerBreggen
 */
class API_Client_Request extends API_Request {

	protected $oCurlHandler;
	protected $sURL;
	protected $cURL;

	private function __construct($sQueryString, $sRequestMethod, $sData = NULL)
	{
		$this->cURL = new CURL();
		$this->oCurlHandler = curl_init();
		$this->sURL = API_URL.$sQueryString;
 
		$this->cURL->setOption(CURLOPT_URL, $this->sURL);
		$this->cURL ->setOption( CURLOPT_HEADER, 1);
		$this->cURL->setOption(CURLOPT_RETURNTRANSFER, TRUE);
		$this->cURL->setOption(CURLOPT_SSL_VERIFYPEER, FALSE);
		$this->cURL->setOption(CURLOPT_FOLLOWLOCATION, 1);
		$this->cURL->setOption(CURLOPT_TIMEOUT, 40);
		//set the server port as configured in the flex.cfg file
		$this->cURL->setOption(CURLOPT_PORT, API_URL_SERVER_PORT);
		
		if ($sData !== NULL)
			$sData = "data = ".urlencode(json_encode($sData));
		if ($sRequestMethod === API_request::HTTP_METHOD_PUT)
		{					
			$fh = fopen(FILES_BASE_PATH.'temp/'.'tmp_putvars.txt', 'w') or die("can't open file");
			fwrite($fh,trim($sData) );
			fclose($fh);
			$fh = fopen(FILES_BASE_PATH.'temp/'.'tmp_putvars.txt', 'rw') or die("can't open file");
			$this->cURL->setOption(CURLOPT_PUT, true);
			$this->cURL->setOption(CURLOPT_INFILE, $fh);
			$this->cURL->setOption( CURLOPT_INFILESIZE, strlen($sData));
			//if we don't add the follow header, for some reason PHP will generate the response with status code 100 (continue)
			//this could be similar to what is described in a user comment on http://php.net/manual/en/function.curl-setopt.php about POST requests with over 1024 bytes
			$this->cURL->setOption(CURLOPT_HTTPHEADER,array("Expect:"));

		}
		else if ($sRequestMethod === API_request::HTTP_METHOD_POST)
		{			
			$this->cURL->setOption(CURLOPT_POST,1);
			$this->cURL->setOption(CURLOPT_POSTFIELDS,$sData);
		}
		else if ($sRequestMethod === API_Request::HTTP_METHOD_PATCH)
		{
			$this->cURL->setOption(CURLOPT_HTTPHEADER,array('OVERRIDE_METHOD: Patch'));
			$this->cURL->setOption(CURLOPT_POST,1);
			$this->cURL->setOption(CURLOPT_POSTFIELDS,$sData);
		}		
	}
	
	public function send()
	{
		
		$sResponse = $this->cURL->execute($this->sURL);		
		$oResponse = new API_Client_Response($sResponse);
		return $oResponse;
	}

	public function create($sURL, $sRequestMethod, $sData = NULL)
	{
		return new self($sURL, $sRequestMethod, $sData);
	}



	
}
?>
