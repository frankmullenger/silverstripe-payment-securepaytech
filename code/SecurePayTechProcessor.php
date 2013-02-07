<?php

class SecurePayTechProcessor extends PaymentProcessor {

	public function capture($data) {

		parent::capture($data);

		//Redirect to a form that the customer can submit
		$confirmURL = Director::absoluteURL(Controller::join_links(
			$this->link(),
			'confirm',
			$this->methodName,
			$this->payment->ID,
			'?ref=' . $data['Reference']
		));
		
		Controller::curr()->redirect($confirmURL);
		return;
	}
	
	public function confirm($request) {

		// Reconstruct the payment object
		$this->payment = Payment::get()->byID($request->param('OtherID'));

		// Reconstruct the gateway object
		$methodName = $request->param('ID');
		$this->gateway = PaymentFactory::get_gateway($methodName);
		
		$config = Config::inst()->get('SecurePayTechGateway', PaymentGateway::get_environment());
		$merchantID = $config['merchant_id'];
		
		$returnURL = Director::absoluteURL(Controller::join_links(
			$this->link(),
			'complete',
			$methodName,
			$this->payment->ID
		));
		
		$cancelURL = Director::absoluteURL(Controller::join_links(
			$this->link(),
			'cancel',
			$methodName,
			$this->payment->ID
		));
		
		$ref = $request->getVar('ref');

		$payload = array(
			'amount' => number_format($this->payment->Amount->Amount, 2, '.', ''),
			'merchantID' => $config['merchant_id'], 
			'returnURL' => $returnURL,
			'cancelURL' => $cancelURL,
			'orderReference' => $ref
		);

		$form = $this->ConfirmForm();
		$form->loadDataFrom($payload);
		
		$content = $this->customise(array(
      'Content' => '',
      'Amount' => $payload['amount'],
      'Form' => ''
    ))->renderWith('SecurePayTechConfirmation');
		
		return $this->customise(array(
      'Content' => $content,
      'Form' => $form
    ))->renderWith('Page');
	}
	
	public function ConfirmForm() {
		
		$config = Config::inst()->get('SecurePayTechGateway', PaymentGateway::get_environment());

		$fields = FieldList::create(
			HiddenField::create('amount', 'Amount (NZD)'),	
			HiddenField::create('merchantID'),	
			HiddenField::create('orderReference'),	
			HiddenField::create('returnURL'),	
			HiddenField::create('cancelURL')	
		);
		$actions = FieldList::create(
			FormAction::create('proceed', 'Proceed to Pay')	
		);

    $form = Form::create($this, 'ConfirmForm', $fields, $actions);
    $form->setFormAction($config['url']);
    return $form;
	}

	public function complete($request) {
		
		SS_Log::log(new Exception(print_r($request, true)), SS_Log::NOTICE);
		
		// Reconstruct the payment object
		$this->payment = Payment::get()->byID($request->param('OtherID'));

		// Reconstruct the gateway object
		$methodName = $request->param('ID');
		$this->gateway = PaymentFactory::get_gateway($methodName);

		// Query the gateway for the payment result
		$result = $this->gateway->getResponse($request);
		$this->payment->updateStatus($result);

		// Do redirection
		$this->doRedirect();
	}
	
	public function cancel($request) {
		// Reconstruct the payment object
		$this->payment = Payment::get()->byID($request->param('OtherID'));

		// Reconstruct the gateway object
		$methodName = $request->param('ID');
		$this->gateway = PaymentFactory::get_gateway($methodName);

		// Query the gateway for the payment result
		// $result = $this->gateway->getResponse($request);
		$this->payment->updateStatus(new PaymentGateway_Failure(null, 'Payment was cancelled.'));

		// Do redirection
		$this->doRedirect();
	}

}
