<?php
class API_Request {

	//not sure if this is useful
	const TOKEN_DELIMITER = '/';

	//add whatever conventions apply to API Requests that both client and server should know about.
	const HTTP_METHOD_GET		= 'get';
	const HTTP_METHOD_PUT		= 'put';
	const HTTP_METHOD_PATCH		= 'patch';
	const HTTP_METHOD_POST		= 'post';
}