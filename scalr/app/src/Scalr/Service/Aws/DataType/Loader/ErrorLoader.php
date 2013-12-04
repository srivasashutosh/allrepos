<?php
namespace Scalr\Service\Aws\DataType\Loader;

use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\LoaderException;
use Scalr\Service\Aws\LoaderInterface;

/**
 * Error Loader.
 *
 * Loads ErrorData.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.10.2012
 */
class ErrorLoader implements LoaderInterface
{

    /**
     * ErrorData object
     *
     * @var ErrorData
     */
    private $result;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.LoaderInterface::getResult()
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.LoaderInterface::load()
     * @return ErrorData Returns ErrorData object
     */
    public function load($xml)
    {
        $this->result = new ErrorData();
        /* @var $simpleXmlElement \SimpleXmlElement */
        $simpleXmlElement = simplexml_load_string($xml);
        switch (true) {
            case !empty($simpleXmlElement->Error) :
                $error = $simpleXmlElement->Error;
                break;

            case !empty($simpleXmlElement->Errors->Error) :
                //Workaround for the EC2 service
                foreach ($simpleXmlElement->Errors->Error as $v) {
                    $error = $v;
                    break;
                }
                break;

            case isset($simpleXmlElement->Code) && isset($simpleXmlElement->Message) :
                //Workaround for the S3 service
                $error = $simpleXmlElement;
                break;

            case !empty($simpleXmlElement->Fault) || !empty($simpleXmlElement->Body->Fault) :
                $src = !empty($simpleXmlElement->Fault) ? $simpleXmlElement->Fault : $simpleXmlElement->Body->Fault;
                $error = new \stdClass();
                $m = null;
                if (isset($src->faultcode) && preg_match('/^aws:([^\.]+)\.(.+?)(Fault)?$/', (string)$src->faultcode, $m)) {
                    $error->Type = $m[1];
                }
                $error->Code = isset($src->faultcode) ? (isset($m[2]) ? $m[2] : (string)$src->faultcode) : '';
                $error->Message = isset($src->faultstring) ? (string) $src->faultstring : '';
                break;

            default:
                $error = null;
        }
        if (empty($error)) {
            if (strpos($xml, 'Service Unavailable') !== false) {
                throw new ClientException('Service Unavailable');
            } else {
                throw new LoaderException('Unexpected XML for the ErrorResponse: ' . $xml);
            }
        }
        /* @var $error \SimpleXmlElement */
        $this->result->type = isset($error->Type) ? (string)$error->Type : null;
        $this->result->code = (string)$error->Code;
        $this->result->message = (string)$error->Message;
        if (isset($simpleXmlElement->RequestId)) {
            $this->result->requestId = (string)$simpleXmlElement->RequestId;
        }
        return $this->result;
    }
}