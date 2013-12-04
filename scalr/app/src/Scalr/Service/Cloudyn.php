<?php

namespace Scalr\Service;

/**
 * Cloudyn API
 *
 * @author   Vitaliy Demidov <vitaliy@scalr.com>
 * @since    14.11.2012
 */
class Cloudyn
{

    /**
     * Environment
     *
     * @var string
     */
    private $environment;

    /**
     * API url
     */
    const URL = 'https://%s.cloudyn.com:8443';

    /**
     * CONSOLE URL
     */
    const CONSOLE_URL = 'https://%s.cloudyn.com/dashboard/token/%s';

    /**
     * Environment settings
     *
     * @var array
     */
    private static $envSettings = array(
        //ENV  => array(API-subdomain, CONSOLE-subdomain),
        'ITG'     => array('itg1', 'itg-web1'),
         'INERNAL' => array('dev2', 'dev-web1'),
        'DEV'     => array('dev-api', 'dev-web2'),
        'QA'      => array('qa-api', 'itg-web1'),
        'PROD'    => array('api', 'app'),
    );

    /**
     * (Default) Returns the JSON array
     */
    const OUT_JSON = 'JSON';

    /**
     * Returns comma-separated list of values,
     */
    const OUT_CSV = 'CSV';

    /**
     * Returns tab-separated list of values
     */
    const OUT_TSV = 'TSV';

    /**
     * Returns human readable an HTML table
     */
    const OUT_HTML = 'HTML';

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * Auth token
     *
     * @var string
     */
    private $token;

    /**
     * The ID of the request to which the response belongs
     *
     * @var int
     */
    private $rqid = 0;

    /**
     * @var \HttpRequest
     */
    private $request;

    /**
     * @var \HttpMessage
     */
    private $message;

    /**
     * Constructor
     *
     * @param   string     $email    optional An user email.
     * @param   string     $password optional An user password.
     * @param   string     $env      optional An environment name (DEV|QA|PROD) (PROD by default)
     */
    public function __construct ($email = null, $password = null, $env = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->environment = isset($env) && isset(self::$envSettings[$env]) ? $env : 'PROD';
    }

    /**
     * Countries service query
     *
     * The system keeps two values for each country: the short name and the long name.
     * The former is used throughout the system.
     * The long name is presented to the customer during the registration process.
     * The API returns both names.
     *
     * @return  array Returns an associative array of the countries which looks like array(shortname => longname)
     * @throws  CloudynException
     */
    public function countries ()
    {
        $obj = $this->call('countries', array(), '/user', 'GET');
        $countries = array();
        foreach ($obj->data as $v) {
            $countries[$v->s] = $v->l;
        }
        return $countries;
    }

    /**
     * Check Status action
     *
     * This is a diagnostic request making a simple DB call
     * and returning either an OK or Error.
     *
     * @return  bool Returns true if status is OK or false otherwise
     */
    public function checkStatus ()
    {
        $obj = $this->call('checkstatus', array(), '/user', 'GET');
        return $obj->data[0]->result == 'OK' ? true : false;
    }

    /**
     * Get version acction
     *
     * This is diagnostic request to get the API version
     *
     * @return  string Returns the version.Something similar to: "@(#)BUILD:1.0.0.338 on: 21-Aug-2012 11:40@"
     * @throws  CloudynException
     */
    public function getVersion ()
    {
        $obj = $this->call('getversion', array(), '/user', 'GET');
        return $obj->version;
    }

    /**
     * Get Customer Delivery Lists action
     *
     * Delivery lists are the feature of the alerts,
     * and yet because of the time constraints is handled at the User API layer.
     *
     * @return  object
     * @throws  CloudynException
     */
    public function getDeliveryLists ()
    {
        return $this->call('getdeliverylists', array(), '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Add delivery list action
     *
     * A new delivery list must have a unique name and only one delivery list may be default
     *
     * @param   string     $notificationListName A list name must be unique.
     * @param   string     $recipientList        The comma separated list of emails
     * @param   string     $description          optional A free description.
     * @param   bool       $isDefault            optional Is it a default list.
     *                                           There may be only one default list for a customer.
     * @return  object
     * @throws  CloudynException
     */
    public function addDeliveryList ($notificationListName, $recipientList, $description = '', $isDefault = false)
    {
        $options = array(
            'notificationlistname' => (string) $notificationListName,
            'description'          => (string) $description,
            'recipientlist'        => (string) $recipientList,
            'isdefault'            => $isDefault ? 'True' : 'False',
        );
        return $this->call('getdeliverylists', $options, '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Update delivery list action
     *
     * Most of the query parameters are optional.
     * They need not be returned (although they may) if the user did not change them.
     *
     * @param   int        $notificationListId   Internal delivery list ID.
     * @param   string     $notificationListName optional A list name which must be unique.
     * @param   string     $recipientList        optional The comma separated list of emails.
     * @param   string     $description          optional A free description.
     * @return  object
     * @throws  CloudynException
     */
    public function updateDeliveryList ($notificationListId, $notificationListName = null, $recipientList = null,
                                        $description = null)
    {
        $options = array(
            'notificationlistid'   => (int) $notificationListId,
        );
        if ($notificationListName !== null) {
            $options['notificationlistname'] = (string) $notificationListName;
        }
        if ($recipientList !== null) {
            $options['recipientlist'] = (string) $recipientList;
        }
        if ($description !== null) {
            $options['description'] = (string) $description;
        }

        return $this->call('updatedeliverylist', $options, '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Delete Delivery List action
     *
     * @param   int        $notificationlistid An notification list ID.
     * @return  object
     * @throws  CloudynException
     */
    public function deleteDeliveryList ($notificationlistid)
    {
        $options = array(
            'notificationlistid' => (int) $notificationlistid,
        );
        return $this->call('deletedeliverylist', $options, '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * GetAccounts action
     *
     * In the future the API will return only the accounts allowed for the connecting user.
     * In the future we’ll also add the field to identify the account provider.
     * Currently all accounts are assumed to be AWS.
     *
     * @return  object
     * @throws  CloudynException
     */
    public function getAccounts ()
    {
        return $this->call('getaccounts', array(), '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Delete Account action
     *
     * After the account is deleted the UI should update its account list.
     *
     * @param   string   $accountid Internal account ID as returned by “Get Customer Accounts”
     * @return  object
     * @throws  CloudynException
     */
    public function deleteAccount ($accountid)
    {
        $options = array(
            'accountid' => (string) $accountid,
        );
        return $this->call('deleteaccount', $options, '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Add Account action
     *
     * Add AWS Account call is allowed for all users.
     * Admin users get automatic access to all accounts.
     * Non-admin users get automatic access to all accounts added by them,
     * and may be granted access to additional accounts by admin users.
     * Currently this query supports only one set of credentials.
     * In the future there may be multiple sets of credentials for one account.
     * Currently the only supported accounts are of Amazon AWS.
     * However, since in the future we’ll support other vendors, there’s a mandatory field ‘vendor’.

     * @param   string     $accountname   An account name.
     * @param   string     $accesskey     AWS access key.
     * @param   string     $secretkey     AWS secret key.
     * @param   string     $vendor        optional Account vendor (from the drop-down). Today only AWS is supported
     * @param   string     $accountstatus optional “active” or “disabled”
     * @return  object
     * @throws  CloudynException
     */
    public function addAccount ($accountname, $accesskey, $secretkey, $vendor = 'AWS', $accountstatus = 'active')
    {
        $options = array(
            'accountname'   => (string) $accountname,
            'accesskey'     => (string) $accesskey,
            'secretkey'     => (string) $secretkey,
            'vendor'        => (string) $vendor,
            'accountstatus' => (string) $accountstatus,
        );
        $obj = $this->call('addaccount', $options, '/user;token=' . $this->getToken(), 'GET');

        return $obj;
    }

    /**
     * Register customer
     *
     * When the registering customer belongs to a managing customer, this is the attachment mechanism.
     * It will be possible to carry out a user login with either a second password, attached during the registration,
     * or with a password of one of parent customer admin accounts.
     *
     * @param   string     $email          User email string(50)
     * @param   string     $password       User chosen password string(50)
     * @param   string     $firstname      User first name.
     * @param   string     $lastname       User last name.
     * @param   string     $companyname    The name of the user company (AKA Customer name).
     *                                     Will serve as bases for the CustomerID generation.
     * @param   string     $parentemail    One of the emails of the parent customer account.
     * @param   string     $parentpassword
     * @param   string     $phone          optional User phone number. (Length 25)
     * @param   string     $ipaddr         optional The IP from which the user logs in String(50)
     * @param   string     $country        optional User Country.
     * @param   bool       $allowmail      optional Whether the user allows us to send him periodic emails.
     * @return  object
     * @throws  CloudynException
     */
    public function registerCustomer ($email, $password, $firstname, $lastname, $companyname,
                              $parentemail, $parentpassword, $phone = null, $ipaddr = null,
                              $country = null, $allowmail = true)
    {
        $options = array(
            'email'          => (string) $email,
            'password'       => (string) $password,
            'firstname'      => (string) $firstname,
            'lastname'       => (string) $lastname,
            'companyname'    => (string) $companyname,
            'parentemail'    => (string) $parentemail,
            'parentpassword' => (string) $parentpassword,
            'country'        => $country === null ? 'US' : (string) $country,
            'allowmail'      => $allowmail ? 'True' : 'False',
        );
        if ($phone !== null) {
            $options['phone'] = (string) $phone;
        }
        if ($ipaddr !== null) {
            $options['ipaddr'] = (string) $ipaddr;
        }
        $obj = $this->call('register', $options, '/user', 'GET');

        return $obj;
    }

    /**
     * Get customer details action
     *
     * @return  object  Returns information about registered customer
     * @throws  CloudynException
     */
    public function getCustomer ()
    {
        return $this->call('customerdetails', array(), '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Get Managed Customers action.
     *
     * Get Managed Customers call returns a list of all the customers managed by the logged-in account.
     * For each one of them you get the managing login id, through which you may log in.
     *
     * @return  object Returns list of all the customers managed by the logged-in account.
     * @throws  CloudynException
     */
    public function getManagedCustomers()
    {
        return $this->call('getmanagedcustomers', array(), '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Get Users action.
     *
     * Get Users lists all the users for a specific customer (in case of Scalr there are three).
     * For a managed customer, you’ll get just the regular users,
     * but you will also get the managing login ID were appropriate.
     *
     * @return  object Returns all the users for a specific customer.
     * @throws  CloudynException
     */
    public function getUsers()
    {
        return $this->call('getusers', array(), '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Deletes Customer
     *
     * @param   string    $password  A customer's password.
     * @return  object
     */
    public function deleteCustomer ($password)
    {
        $options = array(
            'password' => (string) $password,
        );
        return $this->call('deletecustomer', $options, '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Login action
     *
     * @return  object Returns response
     * @throws  CloudynException
     */
    public function login ()
    {
        $options = array(
            'email'    => $this->getEmail(),
            'password' => $this->getPassword(),
        );
        $obj = $this->call('login', $options, '/user', 'GET');
        if (!empty($obj->token)) {
            $this->setToken($obj->token);
        }
        return $obj;
    }

    /**
     * Validate Session action.
     *
     * Validates the session and extends the token validity.
     *
     * @return  object
     * @throws  CloudynException
     */
    public function validateSession ()
    {
        return $this->call('validatesession', array(), '/user;token=' . $this->getToken(), 'GET');
    }

    /**
     * Logout action
     *
     * @return  object  Returns information about registered customer
     * @throws  CloudynException
     */
    public function logout ()
    {
        $obj = $this->call('logout', array(), '/user;token=' . $this->getToken(), 'GET');
        if ($obj->status == 'ok') {
            $this->setToken(null);
        }
        return $obj;
    }

    /**
     * Gets metrics (This action hasn't been documented yet.)
     *
     * @param  string $accountid  optional An accountid
     * @return object Returns object that contain metrics
     * @throws CloudynException
     */
    public function welcome ($accountid = null)
    {
        $options = array();
        if ($accountid !== null) {
            $options['accountid'] = (string) $accountid;
        }
        $obj = $this->call('welcome', $options, '/dataBundle/welcome;token=' . $this->getToken(), 'GET');
        foreach ($obj->elements as $n => $a) {
            if ($a->status != 'SUCCESSFUL') continue;
            $data = array();
            foreach ($a->content->data as $v) {
                $d = new \stdClass();
                foreach ($a->content->columns as $i => $col) {
                    switch ($col->type) {
                        case 'DATETIME':
                            $value = new \DateTime(null, new \DateTimeZone('UTC'));
                            //Converts from Java Epoc
                            $value->setTimestamp(substr($v[$i], 0, -3));
                            break;
                        case 'DECIMAL':
                        case 'BIGINT':
                        case 'INT':
                            $value = $v[$i] - 0;
                            break;
                        default:
                            $value = $v[$i];
                    }
                    $d->{$col->name} = $value;
                    unset($value);
                }
                $data[] = $d;
            }
            $obj->elements[$n]->content->compound = $data;
            unset($data);
        }
        return $obj;
    }

    /**
     * Makes api request
     *
     * @param   string     $qid     The id of the query.
     * @param   array      $options optional Query options for the request.
     * @param   string     $path    optional Uri path for the request (/user by default)
     * @param   string     $method  optional Http method (GET by default)
     * @return  object     Returns object that is an response data.
     * @throws  CloudynException
     */
    public function call ($qid, array $options = array(), $path = '/user', $method = 'GET')
    {
        $options['qid'] = (string) $qid;
        $options['out'] = self::OUT_JSON;
        if (!isset($options['rqid'])) {
            $options['rqid'] = $this->getRequestId();
        }
        if (!isset($options['apiversion'])) {
            $options['apiversion'] = '0.4';
        }
        $this->request = $this->createNewRequest();
        $this->request->setUrl($this->getUrl() . $path);
        $this->request->setMethod(constant('HTTP_METH_' . strtoupper($method)));
        $this->request->setOptions(array(
            'redirect'  => 10,
            'useragent' => 'Scalr Client (http://scalr.com)',
        ));
        $this->request->addQueryData($options);
        //This line is very necessary or HttpResponce will add stored cookies
        $this->request->resetCookies();
        $this->message = $this->tryCall($this->request);
        $json = $this->message->getBody();
        $json = preg_replace('#^[^\{\[]+|[^\}\]]+$#', '', trim($json));
        $obj = json_decode($json);
        if (isset($obj->status) && $obj->status != 'ok' && isset($obj->message)) {
            throw new CloudynException('Cloudyn error. ' . $obj->message);
        }
        return $obj;
    }

    /**
     * Tries to send request on several attempts.
     *
     * @param    \HttpRequest    $httpRequest
     * @param    int             $attempts     Attempts count.
     * @param    int             $interval     An sleep interval between an attempts in microseconds.
     * @throws   CloudynException
     * @returns  \HttpMessage    Returns HttpMessage if success.
     */
    protected function tryCall ($httpRequest, $attempts = 3, $interval = 200)
    {
        try {
            $message = $httpRequest->send();
        } catch (\HttpException $e) {
            if (--$attempts > 0) {
                usleep($interval);
                $message = $this->tryCall($httpRequest, $attempts, $interval * 2);
            } else {
                throw new CloudynException('The intertubes seem to be clogged again. Re-trying to connect to Cloudyn.');
            }
        }
        return $message;
    }

    /**
     * Gets an auto-incremented rqid
     *
     * @return  int  Returns an auto-incremented rqid
     */
    public function getRequestId ()
    {
        return ++$this->rqid;
    }

    /**
     * Gets Email
     *
     * @return string Returns an email
     */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
     * Gets Password
     *
     * @return string Returns the password
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     * Sets an authentication token
     *
     * @param   string    $token  An authentication token
     * @return  Cloudyn
     */
    public function setToken ($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Gets an authentication token value.
     *
     * This value is set after successfully log in operation.
     *
     * @return string Returns an authentication token value.
     */
    public function getToken ()
    {
        return $this->token;
    }

    /**
     * Gets an API url
     *
     * @return string Returns API url
     */
    public function getUrl ()
    {
        return sprintf(self::URL, self::$envSettings[$this->environment][0]);
    }

    /**
     * Gets an Cloudyn Console URL
     *
     * @return string Returns Cloudyn Console URL
     */
    public function getConsoleUrl ()
    {
        return sprintf(self::CONSOLE_URL, self::$envSettings[$this->environment][1], $this->getToken());
    }

    /**
     * Gets a new HttpRequest obejct
     *
     * @return \HttpRequest Returns HttpRequest obejct
     */
    public function createNewRequest ()
    {
        return new \HttpRequest();
    }

    /**
     * Gets the latest request
     *
     * @return \HttpRequest Returns the http request object for the latest request.
     */
    public function getRequest ()
    {
        return $this->request;
    }

    /**
     * Gets the latest response message
     *
     * @return \HttpMessage Returns the latest http response.
     */
    public function getMessage ()
    {
        return $this->message;
    }
}