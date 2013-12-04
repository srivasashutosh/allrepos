<?php
namespace Scalr\Service\Aws\Client;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ErrorData;
use \stdClass;

/**
 * Amazon SOAP client.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.02.2013
 */
class SoapClient extends \SoapClient implements ClientInterface
{

    const CONNECTION_TIMEOUT= 15;

    const WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const WSUNS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const WSSEPFX = 'wsse';
    const WSUPFX = 'wsu';

    const SIGN_TPL = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><ds:SignedInfo><ds:SignatureMethod/></ds:SignedInfo></ds:Signature>';
    const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';

    const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
    const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';

    const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
    const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

    protected $sigNode = null;
    protected $canonicalMethod = self::EXC_C14N;

    /**
     * Base url for API requests
     *
     * @var string
     */
    protected $url;

    /**
     * AWS Access Key Id
     *
     * @var string
     */
    protected $awsAccessKeyId;

    /**
     * Secret Access Key
     *
     * @var string
     */
    protected $secretAccessKey;

    /**
     * x.509 certificate
     *
     * @var string
     */
    protected $certificate;

    /**
     * private key
     *
     * @var string
     */
    protected $privateKey;

    /**
     * WSDL local path or uri
     *
     * @var string
     */
    protected $wsdl;

    /**
     * AWS API Version
     *
     * @var string
     */
    protected $apiVersion;

    /**
     * Aws instance
     *
     * @var \Scalr\Service\Aws
     */
    private $aws;

    /**
     * Sets aws instance
     *
     * @param   \Scalr\Service\Aws $aws AWS intance
     * @return  SoapClient
     */
    public function setAws(\Scalr\Service\Aws $aws = null)
    {
        $this->aws = $aws;
        return $this;
    }

    /**
     * Gets AWS instance
     * @return  \Scalr\Service\Aws Returns an AWS intance
     */
    public function getAws()
    {
        return $this->aws;
    }

    /**
     * Constructor
     *
     * @param    string    $awsAccessKeyId    AWS Access Key Id
     * @param    string    $secretAccessKey   AWS Secret Access Key
     * @param    string    $apiVersion        YYYY-MM-DD representation of AWS API version
     * @param    string    $url               host name for the all api requests
     * @param    string    $wsdl              WSDL local path or url
     */
    public function __construct($awsAccessKeyId, $secretAccessKey, $apiVersion, $url, $wsdl)
    {
        $this->awsAccessKeyId = $awsAccessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->setApiVersion($apiVersion);
        $this->setUrl($url);
        $this->wsdl = $wsdl;
        parent::__construct($this->wsdl, array(
            'connection_timeout' => self::CONNECTION_TIMEOUT,
            'trace'              => true,
            'exceptions'         => false,
            'user_agent'         => 'Scalr AWS Soap Client (http://scalr.com)'
        ));
        if (substr($url, -1) != '/') {
            $url = $url . "/";
        }
        $this->__setLocation('https://' . $url);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientInterface::getType()
     */
    public function getType()
    {
        return Aws::CLIENT_SOAP;
    }

    /**
     * Gets x.509 certificate
     *
     * @return  string Returns x.509 certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * Sets x.509 certificate
     *
     * @param   string $certificate x.509 certificate
     * @return  SoapClient
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;
        return $this;
    }

    /**
     * Gets private key that associated with x.509 certificate
     *
     * @return  string Returns private key that associated with x.509 certificate
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Sets private key that associated with x.509 certificate
     *
     * @param   string $pk private key that associated with x.509 certificate
     * @return  SoapClient
     */
    public function setPrivateKey($pk)
    {
        $this->privateKey = $pk;
        return $this;
    }

    /**
     * Sets Api Version
     *
     * @param     string    $apiVersion  YYYY-MM-DD representation of AWS API version
     */
    public function setApiVersion($apiVersion)
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $apiVersion, $m)) {
            $apiVersion = $m[1] . '-' . $m[2] . '-' . $m[3];
        } else if (!preg_match('/^[\d]{4}\-[\d]{2}\-[\d]{2}$/', $apiVersion)) {
            throw new QueryClientException(
                'Invalid API version ' . $apiVersion . '. '
              . 'You should have used following format YYYY-MM-DD.'
            );
        }
        $this->apiVersion = $apiVersion;
    }

    /**
     * Gets API Version date
     *
     * @return string Returns API Version Date in YYYY-MM-DD format
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Sets query url
     *
     * @param    string   $url  Base url for API requests
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Gets base url for API requests
     *
     * @return   string  Returns base url for API requests
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets expiration time for Expires option.
     *
     * @return   string   Returns expiration time form Expires option
     *                    that's used in AWS api requests.
     */
    protected function getExpirationTime()
    {
        return gmdate('c', time() + 3600);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Client.ClientInterface::call()
     */
    public function call($action, $options, $path = '/')
    {
        if (empty($options)) {
            $res = $this->$action();
        } else {
            //Options array must be compartible with wsdl schema definition.
            $res = $this->$action($options);
        }
        if ($this->getAws() && $this->getAws()->getDebug()) {
            echo "\n";
            echo $this->__getLastRequest() . "\n";
            echo $this->__getLastResponseHeaders() . "\n";
            echo $this->__getLastResponse() . "\n";
        }
        $dom = new \DOMDocument();
        $dom->loadXML($this->__getLastResponse());
        if (($nodeList = $dom->getElementsByTagName($action . 'Response')) && $nodeList->length) {
            $node = $nodeList->item(0);
        }
        $response = new SoapClientResponse(
            isset($node) ? $dom->saveXML($node) : $this->__getLastResponse(),
            $this->__getLastResponseHeaders(),
            $this->__getLastRequest()
        );
        unset($dom);
        return $response;
    }

    /**
     * {@inheritdoc}
     * @see SoapClient::__call()
     */
    public function __call($function_name, $arguments)
    {
        return parent::__call($function_name, $arguments);
    }

    protected function getRequestObjectByArray($options)
    {
        if (empty($options)) return null;
        $req = new stdClass();
        return $req;
    }

    /**
     * {@inheritdoc}
     * @see SoapClient::__doRequest()
     */
    public function __doRequest($request, $location, $action, $version, $one_way = null)
    {
        $certKey = openssl_get_privatekey($this->privateKey, '');

        $wsse = new \DOMDocument('1.0', 'UTF-8');
        $wsse->loadXML($request);

        $envelope = $wsse->documentElement;
        $ns = $envelope->namespaceURI;
        $prefix = $envelope->prefix;
        $xpath = new \DOMXPath($wsse);
        $xpath->registerNamespace('wssoap', $ns);
        $xpath->registerNamespace('wswsse', self::WSSENS);

        //Sets security header
        $setActor = null;
        $headers = $xpath->query('//wssoap:Envelope/wssoap:Header');
        $header = $headers->item(0);
        if (!$header) {
            $header = $wsse->createElementNS($ns, $prefix . ':Header');
            $envelope->insertBefore($header, $envelope->firstChild);
        }
        $secNodes = $xpath->query('./wswsse:Security', $header);
        $security = null;
        foreach ($secNodes as $node) {
            $actor = $node->getAttributeNS($ns, 'actor');
            if ($actor == $setActor) {
                $security = $node;
                break;
            }
        }
        if (!$security) {
            $security = $wsse->createElementNS(self::WSSENS, self::WSSEPFX . ':Security');
            $header->appendChild($security);
            $security->setAttributeNS($ns, $prefix . ':mustUnderstand', '1');
            if (!empty($setActor)) {
                $security->setAttributeNS($ns, $prefix . ':actor', $setActor);
            }
        }

        //Sets timestamp
        $timestamp = $wsse->createElementNS(self::WSUNS, self::WSUPFX . ':Timestamp');
        $security->insertBefore($timestamp, $security->firstChild);
        $currentTime = time();
        $created = $wsse->createElementNS(
            self::WSUNS, self::WSUPFX . ':Created', gmdate('Y-m-d\TH:i:s', $currentTime) . 'Z'
        );
        $timestamp->appendChild($created);
        $expire = $wsse->createElementNS(
            self::WSUNS, self::WSUPFX . ':Expires', gmdate('Y-m-d\TH:i:s', $currentTime + 3600) . 'Z'
        );
        $timestamp->appendChild($expire);

        //Signs document
        $arNodes = array();
        foreach ($security->childNodes as $node) {
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                $arNodes[] = $node;
            }
        }
        foreach ($envelope->childNodes as $node) {
            if ($node->namespaceURI == $ns && $node->localName == 'Body') {
                $arNodes[] = $node;
                break;
            }
        }
        $arOptions = array(
            'prefix'    => self::WSUPFX,
            'prefix_ns' => self::WSUNS
        );

        try {
            $sigdoc = new \DOMDocument();
            $sigdoc->loadXML(self::SIGN_TPL);
            $this->sigNode = $sigdoc->documentElement;
            $sigXpath = new \DOMXPath($this->sigNode->ownerDocument);
            $sigXpath->registerNamespace('secdsig', self::XMLDSIGNS);

            $query = './secdsig:SignedInfo';
            $nodeset = $sigXpath->query($query);
            if ($sinfo = $nodeset->item(0)) {
                $canonNode = $this->_createNewSignNode('CanonicalizationMethod');
                $sinfo->insertBefore($canonNode, $sinfo->firstChild);
                $canonNode->setAttribute('Algorithm', $this->canonicalMethod);
            }

            $nodeset = $sigXpath->query("./secdsig:SignedInfo");
            if ($sInfo = $nodeset->item(0)) {
                foreach ($arNodes as $node) {
                    $this->_addRefInternal($sInfo, $node, self::SHA1, null, $arOptions);
                }
            }

            $nodeset = $sigXpath->query("./secdsig:SignedInfo");
            if ($sInfo = $nodeset->item(0)) {
                $nodeset = $sigXpath->query("./secdsig:SignatureMethod", $sInfo);
                $sMethod = $nodeset->item(0);
                $sMethod->setAttribute('Algorithm', "http://www.w3.org/2000/09/xmldsig#rsa-sha1");
                $data = $this->_canonicalizeData($sInfo, $this->canonicalMethod);
                $algo = OPENSSL_ALGO_SHA1;
                if (!openssl_sign($data, $signature, $certKey, $algo)) {
                    throw new \Exception('Failure Signing Data: ' . openssl_error_string() . ' - ' . $algo);
                    return;
                }
                $sigValue = base64_encode($signature);
                $sigValueNode = $this->_createNewSignNode('SignatureValue', $sigValue);
                if ($infoSibling = $sInfo->nextSibling) {
                    $infoSibling->parentNode->insertBefore($sigValueNode, $infoSibling);
                } else {
                    $this->sigNode->appendChild($sigValueNode);
                }
            }
            $this->_insertSignature($security, $security->firstChild);
        } catch (\Exception $e) {
            throw new \Exception(
                "Unable to sign AWS API request. Please, check your X.509 certificate and private key. "
            );
        }

        $token = $wsse->createElementNS(self::WSSENS, self::WSSEPFX . ':BinarySecurityToken', self::_get509XCert($this->certificate, true));
        $security->insertBefore($token, $security->firstChild);
        $token->setAttribute('EncodingType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary');
        $token->setAttributeNS(self::WSUNS, self::WSUPFX . ':Id', self::_generateGuid());
        $token->setAttribute('ValueType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');

        $sigdoc = new \DOMDocument();
        $sigdoc->loadXML(self::SIGN_TPL);
        $this->sigNode = $sigdoc->documentElement;
        $sigXpath = new \DOMXPath($this->sigNode->ownerDocument);
        $sigXpath->registerNamespace('secdsig', self::XMLDSIGNS);

        if ($objDSig = $this->_locateSignature($wsse)) {
            $tokenURI = '#' . $token->getAttributeNS(self::WSUNS, "Id");
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $nodeset = $xpath->query("./secdsig:KeyInfo", $objDSig);
            $keyInfo = $nodeset->item(0);
            if (!$keyInfo) {
                $keyInfo = $this->_createNewSignNode('KeyInfo');
                $objDSig->appendChild($keyInfo);
            }
            $tokenRef = $wsse->createElementNS(self::WSSENS, self::WSSEPFX . ':SecurityTokenReference');
            $keyInfo->appendChild($tokenRef);
            $reference = $wsse->createElementNS(self::WSSENS, self::WSSEPFX . ':Reference');
            $reference->setAttribute("URI", $tokenURI);
            $tokenRef->appendChild($reference);
        } else {
            throw new \Exception('Unable to locate digital signature');
        }

        $retval = parent::__doRequest($wsse->saveXML(), $location, $action, $version, $one_way);
        if ($retval instanceOf \SoapFault && stristr($retval->faultstring, "Could not connect to host")) {
            throw new \Exception($retval->getMessage());
        }
        return $retval;
    }

    /**
     * This function inserts the signature element.
     *
     * The signature element will be appended to the element, unless $beforeNode is specified. If $beforeNode
     * is specified, the signature element will be inserted as the last element before $beforeNode.
     *
     * @param $node  The node the signature element should be inserted into.
     * @param $beforeNode  The node the signature element should be located before.
     */
    protected function _insertSignature($node, $beforeNode = NULL)
    {
        $document = $node->ownerDocument;
        $signatureElement = $document->importNode($this->sigNode, TRUE);
        if ($beforeNode == NULL) {
            $node->insertBefore($signatureElement);
        } else {
            $node->insertBefore($signatureElement, $beforeNode);
        }
    }

    protected static function _generateGuid($prefix = 'pfx')
    {
        $uuid = md5(uniqid(rand(), true));
        $guid = $prefix . substr($uuid, 0, 8) . "-" . substr($uuid, 8, 4) . "-" . substr($uuid, 12, 4) . "-" . substr($uuid, 16, 4) . "-" . substr($uuid, 20, 12);
        return $guid;
    }

    protected function _createNewSignNode($name, $value = NULL)
    {
        $doc = $this->sigNode->ownerDocument;
        if (!is_null($value)) {
            $node = $doc->createElementNS(self::XMLDSIGNS, 'ds:' . $name, $value);
        } else {
            $node = $doc->createElementNS(self::XMLDSIGNS, 'ds:' . $name);
        }
        return $node;
    }

    protected function _addRefInternal($sinfoNode, $node, $algorithm, $arTransforms = null, $options = null)
    {
        $prefix = null;
        $prefix_ns = null;
        $id_name = 'Id';
        $overwrite_id = true;
        $force_uri = false;
        if (is_array($options)) {
            $prefix = empty($options['prefix']) ? null : $options['prefix'];
            $prefix_ns = empty($options['prefix_ns']) ? null : $options['prefix_ns'];
            $id_name = empty($options['id_name']) ? 'Id' : $options['id_name'];
            $overwrite_id = !isset($options['overwrite']) ? true : (bool) $options['overwrite'];
            $force_uri = !isset($options['force_uri']) ? false : (bool) $options['force_uri'];
        }
        $attname = $id_name;
        if (!empty($prefix)) {
            $attname = $prefix . ':' . $attname;
        }
        $refNode = $this->_createNewSignNode('Reference');
        $sinfoNode->appendChild($refNode);
        if (!$node instanceof \DOMDocument) {
            $uri = null;
            if (!$overwrite_id) {
                $uri = $node->getAttributeNS($prefix_ns, $attname);
            }
            if (empty($uri)) {
                $uri = self::_generateGuid();
                $node->setAttributeNS($prefix_ns, $attname, $uri);
            }
            $refNode->setAttribute("URI", '#' . $uri);
        } elseif ($force_uri) {
            $refNode->setAttribute("URI", '');
        }
        $transNodes = $this->_createNewSignNode('Transforms');
        $refNode->appendChild($transNodes);
        if (is_array($arTransforms)) {
            foreach ($arTransforms as $transform) {
                $transNode = $this->_createNewSignNode('Transform');
                $transNodes->appendChild($transNode);
                if (is_array($transform) &&
                    (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116'])) &&
                    (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']))) {
                    $transNode->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
                    $XPathNode = $this->_createNewSignNode('XPath', $transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']);
                    $transNode->appendChild($XPathNode);
                    if (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'])) {
                        foreach ($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'] as $prefix => $namespace) {
                            $XPathNode->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:$prefix", $namespace);
                        }
                    }
                } else {
                    $transNode->setAttribute('Algorithm', $transform);
                }
            }
        } elseif (!empty($this->canonicalMethod)) {
            $transNode = $this->_createNewSignNode('Transform');
            $transNodes->appendChild($transNode);
            $transNode->setAttribute('Algorithm', $this->canonicalMethod);
        }
        $canonicalData = $this->_processTransforms($refNode, $node);
        $digValue = $this->_calculateDigest($algorithm, $canonicalData);
        $digestMethod = $this->_createNewSignNode('DigestMethod');
        $refNode->appendChild($digestMethod);
        $digestMethod->setAttribute('Algorithm', $algorithm);
        $digestValue = $this->_createNewSignNode('DigestValue', $digValue);
        $refNode->appendChild($digestValue);
    }

    protected function _processTransforms($refNode, $objData)
    {
        $data = $objData;
        $xpath = new \DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = './secdsig:Transforms/secdsig:Transform';
        $nodelist = $xpath->query($query, $refNode);
        $canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $arXPath = null;
        $prefixList = null;
        foreach ($nodelist as $transform) {
            $algorithm = $transform->getAttribute("Algorithm");
            switch ($algorithm) {
                case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'InclusiveNamespaces') {
                            if ($pfx = $node->getAttribute('PrefixList')) {
                                $arpfx = array();
                                $pfxlist = split(" ", $pfx);
                                foreach ($pfxlist as $pfx) {
                                    $val = trim($pfx);
                                    if (!empty($val)) {
                                        $arpfx[] = $val;
                                    }
                                }
                                if (count($arpfx) > 0) {
                                    $prefixList = $arpfx;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                    $canonicalMethod = $algorithm;
                    break;
                case 'http://www.w3.org/TR/1999/REC-xpath-19991116':
                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'XPath') {
                            $arXPath = array();
                            $arXPath['query'] = '(.//. | .//@* | .//namespace::*)[' . $node->nodeValue . ']';
                            $arXpath['namespaces'] = array();
                            $nslist = $xpath->query('./namespace::*', $node);
                            foreach ($nslist as $nsnode) {
                                if ($nsnode->localName != "xml") {
                                    $arXPath['namespaces'][$nsnode->localName] = $nsnode->nodeValue;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                    break;
            }
        }
        if ($data instanceof \DOMNode) {
            $data = $this->_canonicalizeData($objData, $canonicalMethod, $arXPath, $prefixList);
        }
        return $data;
    }

    protected function _calculateDigest($digestAlgorithm, $data)
    {
        switch ($digestAlgorithm) {
            case self::SHA1:
                $alg = 'sha1';
                break;
            case self::SHA256:
                $alg = 'sha256';
                break;
            case self::SHA512:
                $alg = 'sha512';
                break;
            case self::RIPEMD160:
                $alg = 'ripemd160';
                break;
            default:
                throw new \Exception("Cannot validate digest: Unsupported Algorith <$digestAlgorithm>");
        }
        if (function_exists('hash')) {
            return base64_encode(hash($alg, $data, true));
        } elseif (function_exists('mhash')) {
            $alg = "MHASH_" . strtoupper($alg);
            return base64_encode(mhash(constant($alg), $data));
        } elseif ($alg === 'sha1') {
            return base64_encode(sha1($data, true));
        } else {
            throw new \Exception('SoapClient is unable to calculate a digest. Maybe you need the mhash library?');
        }
    }

    protected function _canonicalizeData($node, $canonicalmethod, $arXPath = null, $prefixList = null)
    {
        $exclusive = false;
        $withComments = false;
        switch ($canonicalmethod) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                $exclusive = false;
                $withComments = false;
                break;
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                $withComments = true;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                $exclusive = true;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $exclusive = true;
                $withComments = true;
                break;
        }
        return $node->C14N($exclusive, $withComments, $arXPath, $prefixList);
    }

    protected static function _get509XCert($cert, $isPEMFormat = true)
    {
        $certs = self::_staticGet509XCerts($cert, $isPEMFormat);
        if (!empty($certs)) {
            return $certs[0];
        }
        return '';
    }

    protected static function _staticGet509XCerts($certs, $isPEMFormat = true)
    {
        if ($isPEMFormat) {
            $data = '';
            $certlist = array();
            $arCert = explode("\n", $certs);
            $inData = false;
            foreach ($arCert as $curData) {
                if (!$inData) {
                    if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
                        $inData = true;
                    }
                } else {
                    if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
                        $inData = false;
                        $certlist[] = $data;
                        $data = '';
                        continue;
                    }
                    $data .= trim($curData);
                }
            }
            return $certlist;
        } else {
            return array(
                $certs
            );
        }
    }

    protected function _locateSignature($objDoc)
    {
        if ($objDoc instanceof \DOMDocument) {
            $doc = $objDoc;
        } else {
            $doc = $objDoc->ownerDocument;
        }
        if ($doc) {
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $query = ".//secdsig:Signature";
            $nodeset = $xpath->query($query, $objDoc);
            $this->sigNode = $nodeset->item(0);
            return $this->sigNode;
        }
        return null;
    }
}