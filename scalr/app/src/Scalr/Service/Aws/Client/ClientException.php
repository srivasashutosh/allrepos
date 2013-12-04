<?php
namespace Scalr\Service\Aws\Client;

use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\AwsException;

/**
 * ClientException
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     23.09.2012
 */
class ClientException extends AwsException
{
    /**
     * @var ErrorData
     */
    protected $errorData;

    public function __construct($message = null, $code = null, $previous = null)
    {
        if ($message instanceof ErrorData) {
            $this->errorData = $message;

            //Action is the AWS Action name
            $action = null;
            //We need to fetch Action name from the request if possible.
            if ($message->request instanceof \HttpRequest) {
                if ($message->request->getMethod() == HTTP_METH_POST) {
                    $postfields = $message->request->getPostFields();
                    if (!empty($postfields['Action'])) {
                        $action = $postfields['Action'];
                    }
                }
            }
            //Trying to fetch Action from the backtrace
            if ($action === null) {
                foreach (debug_backtrace() as $arr) {
                    if (empty($arr['class']) ||
                        !preg_match("/\\\\Service\\\\Aws\\\\.+Api$/", $arr['class']) ||
                        $arr['type'] !== '->') {
                        continue;
                    }
                    $action = ucfirst($arr['function']);
                    break;
                }
            }

            parent::__construct(
                sprintf(
                    'AWS Error.%s %s',
                    ($action ? sprintf(" Request %s failed.", $action) : ''),
                    $this->errorData->getMessage()
                ),
                $code,
                $previous
            );
        } else {
            parent::__construct($message, $code, $previous);
        }
    }

    /**
     * Gets ErrorData
     *
     * @return \Scalr\Service\Aws\DataType\ErrorData Returns ErrorData object
     */
    public function getErrorData()
    {
        return $this->errorData;
    }
}