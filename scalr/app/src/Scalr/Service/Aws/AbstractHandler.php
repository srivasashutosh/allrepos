<?php
namespace Scalr\Service\Aws;

use Scalr\Service\AwsException;
use Scalr\Service\Aws\ServiceInterface;
use Scalr\Service\Aws\AbstractServiceRelatedType;

/**
 * AbstractHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.10.2012
 */
abstract class AbstractHandler extends AbstractServiceRelatedType
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractServiceRelatedType::getServiceNames()
     */
    public function getServiceNames()
    {
        //This method is supposed to be overriden
        return array();
    }

    /**
     * Constructor
     *
     * @param  ServiceInterface $service,... unlimited optional services which is related to handler.
     */
    public function __construct($service)
    {
        $args = func_get_args();
        foreach ($args as $serv) {
            if (!($serv instanceof ServiceInterface)) {
                throw new AwsException(sprintf('Invalid service %s. It must be instance of ServiceInterface.', get_class($serv)));
            }
            //Regexp also supports mock classess.
            $sname = preg_replace('/^(.+\\\\|Mock_)([^\\\\]+?)(_.+)?$/', '\\2', get_class($serv));
            $fnset = 'set' . $sname;
            $this->$fnset($serv);
        }
    }
}