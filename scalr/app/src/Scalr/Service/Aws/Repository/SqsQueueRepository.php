<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Sqs\DataType\QueueData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * SqsQueueRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 */
class SqsQueueRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Sqs\\DataType\\QueueData';

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractRepository::getReflectionClassName()
     */
    public function getReflectionClassName()
    {
        return self::$reflectionClassName;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractRepository::getIdentifier()
     */
    public function getIdentifier()
    {
        return 'queueName';
    }

    /**
     * Finds one element in entity manager by id
     *
     * @param    string      $id  A queue name
     * @return   QueueData   Returns QueueData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}