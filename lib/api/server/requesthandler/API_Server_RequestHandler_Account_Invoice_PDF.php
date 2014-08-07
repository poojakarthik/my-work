<?php
class API_Server_RequestHandler_Account_Invoice_PDF implements API_Server_RequestHandler {
	static public function get(API_Server_Request $request, API_Server_Response $response) {
		$accountId = $request->url->account_id;
		$invoiceId = $request->url->invoice_id;
		try {
			$invoice = Invoice::getForId($invoiceId);
			if ($invoice->Account !== intval($accountId)) {
				throw new Exception('Invoice/Account mismatch');
			}
		} catch (Exception $exception) {
			$response->send(API_Response::STATUS_CODE_NOT_FOUND, array(
				'content-type' => 'text/plain'
			), sprintf('No Invoice #%s on Account #%s',
				$accountId,
				$invoiceId
			));
		}

		$response->send(
			API_Response::STATUS_CODE_OK,
			array(
				'Content-Type' => 'application/pdf'
			),
			GetPDFContent($invoice->Account, $iYear=null, $iMonth=null, $invoice->Id, $invoice->invoice_run_id)
		);
	}
	static public function post(API_Server_Request $request, API_Server_Response $response) {
		$accountId = $request->url->account_id;
		$invoiceId = $request->url->invoice_id;
		try {
			$invoice = Invoice::getForId($invoiceId);
			if ($invoice->Account !== intval($accountId)) {
				throw new Exception('Invoice/Account mismatch');
			}
		} catch (Exception $exception) {
			$response->send(API_Response::STATUS_CODE_NOT_FOUND, array(
				'content-type' => 'text/plain'
			), sprintf('No Invoice #%s on Account #%s',
				$accountId,
				$invoiceId
			));
		}

		$sXML = $request->getData();

		$response->send(
			API_Response::STATUS_CODE_OK,
			array(
				'Content-Type' => 'application/pdf'
			),
			generateInvoicePDF($sXML, $invoice->Id, null, $invoice->invoice_run_id, $invoice->Account)
		);
	}
}