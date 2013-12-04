<?php


class Chargify
{
  	public function __construct($api_key, $domain)
  	{
    	$this->apiKey = $api_key;
    	$this->domain  = $domain;
  	}


    public function createComponentUsage($subscriptionId, $componentId, $quantity, $memo)
    {
        $rObject = new stdClass();
        $rObject->usage = new stdClass();
        $rObject->usage->quantity = $quantity;
        $rObject->usage->memo = $memo;

        $r = $this->sendRequest("/subscriptions/{$subscriptionId}/components/{$componentId}/usages.json", 'POST', json_encode($rObject));

        if ($r->getResponseCode() == 200) {
            return json_decode($r->getResponseBody(), true);
        }
        else {
            if ($r->getResponseCode() == 422) {
                $response = json_decode($r->getResponseBody(), true);
                throw new Exception($response['errors'][0]);
            }

            throw new Exception("Cannot create component usage. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
        }
    }

  	public function setComponentValue($subscriptionId, $componentId, $value)
  	{
  		$rObject = new stdClass();
  		$rObject->component = new stdClass();
  		$rObject->component->allocated_quantity = $value;

  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}/components/{$componentId}.json", 'PUT', json_encode($rObject));

	  	if ($r->getResponseCode() == 200) {
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else {
	  		if ($r->getResponseCode() == 422) {
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot set component value. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

	public function reactivateSubscription($subscriptionId)
	{
  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}/reactivate.json", 'PUT');

	  	if ($r->getResponseCode() == 200) {
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else {
	  		if ($r->getResponseCode() == 422) {
		  		$response = json_decode($r->getResponseBody(), true);

				if ($response[0][1])
					throw new Exception($response[0][1]);
				else
		  			throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot reactivate subscription. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
	}

	public function cancelSubscription($subscriptionId, $reason = 'Canceling the subscription via the API. Requested by customer.')
	{
		$rObject = new stdClass();
  		$rObject->subscription = new stdClass();
  		$rObject->subscription->cancellation_message = $reason;
  		$rObject->subscription->cancel_at_end_of_period = true;

  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}.json", 'PUT', json_encode($rObject));
  		if ($r->getResponseCode() == 200) {
	    	return true;
	  	}
	  	else {
	  		if ($r->getResponseCode() == 422) {
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot cancel subscription. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

  	public function applyCoupon($subscriptionId, $couponCode)
  	{
  		$rObject = new stdClass();
  		$rObject->code = $couponCode;

  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}/add_coupon.json", 'POST', json_encode($rObject));

	  	if ($r->getResponseCode() == 200) {
	    	return true;
	  	}
	  	else {
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response[0][1]);
		  		exit();
	  		}

	  		throw new Exception("Cannot apply coupon to subscription. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

	public function getSubscriptionByCustomerId($clientId)
  	{
  		$r = $this->sendRequest("/customers/{$clientId}/subscriptions.json", 'GET');

  		if ($r->getResponseCode() == 200)
	  	{
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get statements. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

  	public function getCustomerById($customerId)
  	{
  		$r = $this->sendRequest("/customers/{$customerId}.json", 'GET');

	  	if ($r->getResponseCode() == 200)
	  	{
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get statements. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

	public function createSubscription($scalrAccountId, $package, $ccNumber, $ccExpMonth, $ccExpYear, $ccCvv, $clientInfo = false, $postalCode = false)
	{
		$rObject = new stdClass();
  		$rObject->subscription = new stdClass();
  		$rObject->subscription->product_handle = $package;

  		if ($ccNumber) {
	  		$rObject->subscription->credit_card_attributes = new stdClass();
	  		$rObject->subscription->credit_card_attributes->full_number = preg_replace("/[^0-9]+/", "", $ccNumber);
	  		$rObject->subscription->credit_card_attributes->expiration_month = $ccExpMonth;
	  		$rObject->subscription->credit_card_attributes->expiration_year = $ccExpYear;
	  		$rObject->subscription->credit_card_attributes->cvv = $ccCvv;
  		}

  		if ($postalCode)
  			$rObject->subscription->credit_card_attributes->billing_zip = $postalCode;

  		if (!$clientInfo) {
  			$rObject->subscription->customer_reference = $scalrAccountId;
  		} else {
  			$rObject->subscription->customer_attributes = new stdClass();
  			$rObject->subscription->customer_attributes->email = $clientInfo['email'];
  			$rObject->subscription->customer_attributes->first_name = $clientInfo['first_name'];
  			$rObject->subscription->customer_attributes->last_name = $clientInfo['last_name'];
  			$rObject->subscription->customer_attributes->organization = $clientInfo['org'];
  			$rObject->subscription->customer_attributes->reference = $scalrAccountId;
  		}

	  	$r = $this->sendRequest('/subscriptions.json', 'POST', json_encode($rObject));

	  	if ($r->getResponseCode() == 201) {
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else {
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot create subscription. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
	}

  	public function getStatements($subscriptionId)
  	{
  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}/statements.json?per_page=500", 'GET');

	  	if ($r->getResponseCode() == 200)
	  	{
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get statements. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

  	public function updateSubscription($subscriptionId, $ccNumber, $ccCvv, $ccExpMonth, $ccExpYear, $fName, $lName, $postalCode = false)
  	{
  		$rObject = new stdClass();
  		$rObject->subscription = new stdClass();
  		$rObject->subscription->credit_card_attributes = new stdClass();
  		$rObject->subscription->credit_card_attributes->full_number = preg_replace("/[^0-9]+/", "", $ccNumber);
  		$rObject->subscription->credit_card_attributes->expiration_month = $ccExpMonth;
  		$rObject->subscription->credit_card_attributes->expiration_year = $ccExpYear;
  		$rObject->subscription->credit_card_attributes->cvv = $ccCvv;
		$rObject->subscription->credit_card_attributes->first_name = $fName;
		$rObject->subscription->credit_card_attributes->last_name = $lName;

		if ($postalCode)
			$rObject->subscription->credit_card_attributes->billing_zip = $postalCode;


  		$data = json_encode($rObject);

  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}.json", 'PUT', $data);

  		if ($r->getResponseCode() == 200)
	  	{

	  		return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get update subscription. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

  	public function getCouponDetails($couponCode)
  	{
  		$r = $this->sendRequest("/coupons/find.json?code={$couponCode}", 'GET');
  		if ($r->getResponseCode() == 200)
	  	{
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get coupon info. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

	public function getSubscription($subscriptionId)
  	{
  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}.json", 'GET');

	  	if ($r->getResponseCode() == 200)
	  	{
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get subscription info. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

  	public function getSubscriptionComponents($subscriptionId)
  	{
  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}/components.json", 'GET');

	  	if ($r->getResponseCode() == 200)
	  	{
	  		$result = json_decode($r->getResponseBody(), true);
	  		$retval = array();
	  		foreach ($result as $r) {
	  			$retval[$r['component']['component_id']] = $r['component'];
	  		}

	  		return $retval;
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot get subscription info. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}

	public function upgradeSubscription($subscriptionId, $package, $includeTrial = false) {

		if (!$includeTrial)
		{
		  	$rObject = new stdClass();
	  		$rObject->migration = new stdClass();
	  		$rObject->migration->product_handle = $package;

		  	$r = $this->sendRequest("/subscriptions/{$subscriptionId}/migrations.json", 'POST', json_encode($rObject));
		}
		else {
			$rObject = new stdClass();
	  		$rObject->subscription = new stdClass();
	  		$rObject->subscription->product_handle = $package;

	  		$r = $this->sendRequest("/subscriptions/{$subscriptionId}.json", 'PUT', json_encode($rObject));
		}

		if ($r->getResponseCode() == 200)
	  	{
	    	return json_decode($r->getResponseBody(), true);
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$response = json_decode($r->getResponseBody(), true);
		  		throw new Exception($response['errors'][0]);
	  		}

	  		throw new Exception("Cannot upgrade subscription. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
	}

  /*
  public function retrieveAllCustomersXML($page_num = 1)
  {
    return $this->sendRequest('/customers.xml?page=' . $page_num);
  }

  public function retrieveCustomerXMLByID($id)
  {
    return $this->sendRequest('/customers/' . $id . '.xml');
  }

  public function retrieveCustomerXMLByReference($ref)
  {
    return $this->sendRequest('/customers/lookup.xml?reference=' . $ref);
  }

  public function retrieveSubscriptionsXMLByCustomerID($id)
  {
    return $this->sendRequest('/customers/' . $id . '/subscriptions.xml');
  }

  public function retrieveAllProductsXML()
  {
    return $this->sendRequest('/products.xml');
  }

  public function createCustomerAndSubscription($post_xml)
  {
    $xml = $this->sendRequest('/subscriptions.xml', $post_xml);

    $tree = new SimpleXMLElement($xml);

    if(isset($tree->error)) { return $tree; }
    else { $subscription = new ChargifySubscription($tree); }

    return $subscription;
  }

  public function getAllCustomers()
  {
    $xml = $this->retrieveAllCustomersXML();

    $all_customers = new SimpleXMLElement($xml);

    $customer_objects = array();

    foreach($all_customers as $customer)
    {
      $temp_customer = new ChargifyCustomer($customer);
      array_push($customer_objects, $temp_customer);
    }

    return $customer_objects;
  }


  public function getCustomerByID($id)
  {
    $xml = $this->retrieveCustomerXMLByID($id);

    $customer_xml_node = new SimpleXMLElement($xml);

    $customer = new ChargifyCustomer($customer_xml_node);

    return $customer;
  }

  public function getCustomerByReference($ref)
  {
    $xml = $this->retrieveCustomerXMLByReference($ref);

    $customer_xml_node = new SimpleXMLElement($xml);

    $customer = new ChargifyCustomer($customer_xml_node);

    return $customer;
  }

  public function getSubscriptionsByCustomerID($id)
  {
    $xml = $this->retrieveSubscriptionsXMLByCustomerID($id);

    $subscriptions = new SimpleXMLElement($xml);

    $subscription_objects = array();

    foreach($subscriptions as $subscription)
    {
      $temp_sub = new ChargifySubscription($subscription);

      array_push($subscription_objects, $temp_sub);
    }

    return $subscription_objects;
  }

  public function getAllProducts()
  {
    $xml = $this->retrieveAllProductsXML();

    $all_products = new SimpleXMLElement($xml);

    $product_objects = array();

    foreach($all_products as $product)
    {
      $temp_product = new ChargifyProduct($product);
      array_push($product_objects, $temp_product);
    }

    return $product_objects;
  }
  */


	/**
   *
   * @param integer $subscription_id
   * @return ChargifySubscription
   */
  	public function getCustomerSubscriptionByCId($client_id)
  	{
  		$r = $this->sendRequest("/customers/{$client_id}/subscriptions.xml", 'GET');

	  	if ($r->getResponseCode() == 200)
	  	{
	  		$xml_node = new SimpleXMLElement($r->getResponseBody());
	    	$subscription = new ChargifySubscription($xml_node);
	    	return $subscription;
	  	}
	  	else
	  	{
	  		if ($r->getResponseCode() == 422)
	  		{
		  		$xml_node = new SimpleXMLElement($r->getResponseBody());
		  		if ($xml_node->error)
		  			return (string)$xml_node->error;
	  		}

	  		throw new Exception("Cannot get subscription info. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
	  	}
  	}


/*	public function listComponents($product_family_id) {

  	$r = $this->sendRequest("/product_families/{$product_family_id}/components.xml", false);

  	if ($r->getResponseCode() == 200)
  	{
    	var_dump($r->getResponseBody());

  		return true;
  	}
  	else
  	{
  		if ($r->getResponseCode() == 422)
  		{
	  		$xml_node = new SimpleXMLElement($r->getResponseBody());
	  		if ($xml_node->error)
	  			return (string)$xml_node->error;
  		}

  		var_dump($r->getResponseBody());

  		throw new Exception("Cannot create usage. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
  	}

  }

  public function createMeteredUsage($subscription_id, $component_id, $quantity, $memo) {

  	$post_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <usage>
          <quantity>{$quantity}</quantity>
          <memo>{$memo}</memo>
        </usage>
  	";

  	var_dump("/subscriptions/{$subscription_id}/components/{$component_id}/usages.xml");
  	var_dump($post_xml);

  	$r = $this->sendRequest("/subscriptions/{$subscription_id}/components/{$component_id}/usages.xml", $post_xml);

  	if ($r->getResponseCode() == 200)
  	{
    	var_dump($r->getResponseBody());

  		return true;
  	}
  	else
  	{
  		if ($r->getResponseCode() == 422)
  		{
	  		$xml_node = new SimpleXMLElement($r->getResponseBody());
	  		if ($xml_node->error)
	  			return (string)$xml_node->error;
  		}

  		var_dump($r->getResponseBody());

  		throw new Exception("Cannot create usage. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
  	}

  }
  */

  /**
   * @return ChargifySubscription
   * @param unknown_type $scalr_client_id
   * @param unknown_type $product_code
   * @param unknown_type $cc_number
   * @param unknown_type $cc_exp_month
   * @param unknown_type $cc_exp_year
   * @param unknown_type $cc_cvv
   */

  /**
   * @return ChargifyCustomer
   */
  public function createCustomer($scalr_client_id, $email, $first_name, $last_name, $org = "") {

  	$post_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<customer>
		<email>{$email}</email>
		<first_name>{$first_name}</first_name>
		<last_name>{$last_name}</last_name>
		<organization>{$org}</organization>
		<reference>{$scalr_client_id}</reference>
	</customer>";

  	$r = $this->sendRequest('/customers.xml', 'POST', $post_xml);

  	if ($r->getResponseCode() == 201)
  	{
    	$xml_node = new SimpleXMLElement($r->getResponseBody());
    	$customer = new ChargifyCustomer($xml_node);
    	return $customer;
  	}
  	else
  	{
  		if ($r->getResponseCode() == 422)
  		{
	  		$xml_node = new SimpleXMLElement($r->getResponseBody());
	  		if ($xml_node->error)
	  			return (string)$xml_node->error;
  		}

  		throw new Exception("Cannot add new client. Response code: ".$r->getResponseCode().". Response body: ".$r->getResponseBody());
  	}
  }

	protected function sendRequest($uri, $method, $data = null)
	{
	  	try
	  	{
		  	$request = new HttpRequest("https://{$this->domain}.chargify.com{$uri}");
		  	$request->setHeaders(array('Content-Type' => 'application/json'));
		  	$request->setOptions(array(
		  		'httpauth'	=> "{$this->apiKey}:x",
		  		'timeout'	=> 45,
		  		'connecttimeout' => 45
		  	));

		  	$request->setMethod(constant("HTTP_METH_{$method}"));

		  	if ($method == 'POST' && $data)
		  		$request->setBody($data);

		  	if ($method == 'PUT') {
		  		$request->addPutData($data);
		  	}
	  	}
	  	catch(Exception $e)
	  	{
			//TODO:
	  		throw $e;
	  	}

	  	$request->send();

	  	if ($request->getResponseCode() == 500)
	  		throw new Exception("Unable to proceed your request at the moment. Please try again later.");

	  	if ($request->getResponseCode() == 404)
	  		throw new Exception("Unable to proceed your request. Please contact billing@scalr.net to get help.");

	  	return $request;
	}
}
?>