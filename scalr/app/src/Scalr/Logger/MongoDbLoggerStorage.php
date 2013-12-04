<?php
namespace Scalr\Logger;

use Scalr\Logger\AuditLog\KeyValueRecord;
use Scalr\Logger\AuditLog\AuditLogTags;
use Scalr\Logger\AuditLog\Documents\AbstractAuditLogDocument;
use Scalr\Logger\AuditLog\LogRecord;
use \Mongo;
use \MongoCursor;
use \MongoCollection;
use \MongoDate;

/**
 * MongoDb logger storage
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.11.2012
 */
class MongoDbLoggerStorage implements LoggerStorageInterface
{

    /**
     * Misc. options
     *
     * @var array
     */
    protected $options;

    /**
     * Mongo instance
     *
     * @var \MongoCollection
     */
    private $mongo;

    /**
     * Constructor
     *
     * @param  array $options optional An array of the required options
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = array())
    {
        $this->options = $this->getDefaultOptions();
        foreach ($this->getRequiredOptions() as $identifier) {
            if (!array_key_exists($identifier, $options)) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing required option. "%s" must be provided',
                    $identifier
                ));
            }
        }
        $this->options = array_replace_recursive($this->options, $options);
    }

    /**
     * Gets MongoDb Collection
     *
     * @return MongoCollection Returns the instance of the MongoDB Collection
     */
    protected function getMongo()
    {
        if ($this->mongo === null) {
            if (preg_match('#^(mongodb://.*)/(.*)/(.*)$#', $this->options['dsn'], $matches)) {
                $mongo = new Mongo($matches[1] . (!empty($matches[2]) ? '/' . $matches[2] : ''));
                $database = $matches[2];
                $collection = $matches[3];
                $this->mongo = $mongo->selectCollection($database, $collection);
            } else {
                throw new \RuntimeException(sprintf(
                    'Please check your configuration. '
                  . 'You are trying to use MongoDB with an invalid dsn "%s". '
                  . 'The expected format is "mongodb://user:pass@location/database/collection"', $this->options['dsn']
                ));
            }
        }
        return $this->mongo;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::getDefaultOptions()
     */
    public function getDefaultOptions()
    {
        //two weeks
        return array(
            'dsn'      => 'mongodb://' . Mongo::DEFAULT_HOST . ':' . Mongo::DEFAULT_PORT . '/db/auditlog',
            'lifetime' => 1209600,
            'safe'     => false,
            'fsync'    => false,
            'timeout'  => MongoCursor::$timeout,
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::getRequiredOptions()
     */
    public function getRequiredOptions()
    {
        return array('dsn');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::cleanup()
     */
    public function cleanup()
    {
        $this->getMongo()->remove(array(
            '$or' => array(
                'time' => array(
                    '$lt' => new MongoDate(time() - $this->options['lifetime']),
                ),
                'tags' => array(
                    '$in' => array(AuditLogTags::TAG_TEST),
                ),
            )
        ));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::find()
     */
    public function find(array $criteria, array $order, $limit)
    {
        $cursor = $this->getMongo()->find($this->buildQuery($criteria))->sort($order)->limit($limit);
        $records = array();
        foreach ($cursor as $record) {
            $records[] = $this->getLogRecord($record);
        }
        return $records;
    }

    /**
     * Gets an log record by the data from db
     *
     * @param    array      $data   Array
     * @return   LogRecord  Returns LogRecord object
     */
    private function getLogRecord ($data)
    {
        $record = new LogRecord($data['_id']);
        $record
            ->setAccountid(isset($data['accountid']) ? $data['accountid'] : null)
            ->setEmail(isset($data['email']) ? $data['email'] : null)
            ->setEnvid(isset($data['envid']) ? $data['envid'] : null)
            ->setIp(isset($data['ip']) ? $data['ip'] : null)
            ->setMessage(isset($data['message']) ? $data['message'] : null)
            ->setUserid(isset($data['userid']) ? $data['userid'] : null)
        ;
        if (isset($data['time'])) {
            $time = new \DateTime(null, new \DateTimeZone('UTC'));
            $time->setTimestamp($data['time']->sec);
            $record->setTime($time);
        }
        if (isset($data['datatype'])) {
            $kvr = new KeyValueRecord($data['datatype']);
            foreach ($data['data'] as $prop => $val) {
                $kvr->$prop = $val;
            }
            $record->setData($kvr);
        }
        if (!empty($data['tags']) && is_array($data['tags'])) {
            $record->setTags(new AuditLogTags($data['tags']));
        }

        return $record;
    }

    /**
     * Builds query
     *
     * @param  array $criteria
     * @return array
     */
    private function buildQuery(array $criteria)
    {
        return $criteria;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::truncate()
     */
    public function truncate()
    {
        $this->getMongo()->remove(array());
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::write()
     */
    public function write(LogRecord $record)
    {
        $data = $record->getData();

        $r = array(
            '_id'       => $record->getUuid(),
            'sessionid' => $record->getSessionid(),
            'accountid' => $record->getAccountid(),
            'userid'    => $record->getUserid(),
            'email'     => $record->getEmail(),
            'envid'     => $record->getEnvid(),
            'ip'        => $record->getIp(),
            'time'      => new MongoDate($record->getTime()->getTimestamp()),
            'message'   => $record->getMessage(),
            'tags'      => $record->getTags()->get(),
            'data'      => $data instanceof KeyValueRecord ? get_object_vars($data) : array(),
            'datatype'  => $record->getDatatype(),
        );

        return $this->getMongo()->insert($r, array(
            'safe'     => $this->options['safe'],
            'fsync'    => $this->options['fsync'],
            'timeout'  => $this->options['timeout'],
        ));
    }
}