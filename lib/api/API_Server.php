<?php
class API_Server {
	static private $_routes = array(
      	// https://telcoblue.yellowbilling.com.au.bne-feprod-01.ybs.net.au:some-password@api.telcoblue.yellowbilling.com.au:8080/accounts/1000154811/invoices/3010863613.pdf
		'/^\/accounts\/(?P<account_id>\d+)\/invoices\/(?P<invoice_id>\d+)\.pdf$/' => 'API_Server_RequestHandler_Account_Invoice_PDF'
	);

	static public function process() {
		$request = new API_Server_Request($_SERVER);
		$response = new API_Server_Response();
		// TODO: Get URL and resolve against $routes

		$handlerFound = false;
		$urlMatches = array();
		foreach (self::$_routes as $urlRegex=>$handlerClassName) {
			if (preg_match($urlRegex, $request->path, $urlMatches)) {
				// If the URL is valid(Matches one of our regex's), only then do we extract the 'Named Subpatterns'
				$request->saveRegexMatchedNamedSubpatternsAsProperties($urlMatches);
				$handlerFound = true;
				break;
			}
		}
		if (!$handlerFound) {
			return $response->send(API_Response::STATUS_CODE_NOT_FOUND);
		}

		$httpMethod = $request->getHTTPMethod();
		if (!method_exists($handlerClassName, $httpMethod)) {
			return $response->send(API_Response::STATUS_CODE_METHOD_NOT_ALLOWED);
		}
		call_user_func(array($handlerClassName, $httpMethod), $request, $response);
	}
}