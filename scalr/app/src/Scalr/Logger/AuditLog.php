<?php
namespace Scalr\Logger;

use Scalr\Logger\AuditLog\KeyValueRecord;
use Scalr\Logger\AuditLog\Exception\AuditLogException;
use Scalr\Logger\AuditLog\AuditLogTags;
use Scalr\DependencyInjection\Container;
use Scalr\Logger\AuditLog\LogRecord;
use Scalr\Logger\AuditLog\Documents\AbstractAuditLogDocument;
use \Mongo;
use \MongoCursor;
use \MongoCollection;
use \ReflectionClass;

/**
 * Audit log service.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.10.2012
 */
class AuditLog implements AuditLogInterface
{
    /**
     * User instance
     *
     * @var \Scalr_Account_User
     */
    private $user;

    /**
     * Logger storage
     *
     * @var \Scalr\Logger\LoggerStorageInterface
     */
    private $storage;

    /**
     * Miscellaneous options.
     *
     * @var array
     */
    private $options;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Gets object mapping.
     *
     * This method is used for comparison the object's class with Document Objects, and then logger
     * calls an appropriated createFromNNN method for the found Document to create a new one.
     *
     * @return array Returns associative array that contains mapping for the objects and
     *                       evaluates appropriate documents.
     *                       This array looks like array(ObjectClassName => DocumentObjectName).
     */
    public static function getObjectMapping()
    {
        $map = array(
            'DBFarm'  => 'Farm',
        );
        return $map;
    }

    /**
     * Constructor
     *
     * @param   \Scalr_Account_User    $user    A Scalr_Account_User instance.
     * @param   LoggerStorageInterface $storage A database storage provider.
     * @param   array                  $options A required options array.
     *                                          It should look like array('option' => value).
     * @throws  \InvalidArgumentException
     */
    public function __construct(\Scalr_Account_User $user, LoggerStorageInterface $storage, array $options = array())
    {
        $this->user = $user;
        $this->storage = $storage;
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
     * {@inheritdoc}
     * @see Scalr\Logger.AuditLogInterface::getUser()
     * @return \Scalr_Account_User Returns user instance
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.AuditLogInterface::find()
     */
    public function find(array $criteria, array $order, $limit)
    {
        return $this->storage->find($criteria, $order, $limit);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.AuditLogInterface::log()
     */
    public function log($message, $tags, $object = null, $objectBefore = null)
    {
        if (!$this->isEnabled()) return true;

        $user = $this->getUser();

        if (!$this->getContainer()->initialized('request') ||
            !($this->getContainer()->request instanceof \Scalr_UI_Request)) {
            $ip = '127.0.0.1';
            $envid = 0;
        } else {
            /* @var $request \Scalr_UI_Request */
            $request = $this->getContainer()->request;
            $ip = $request->getClientIp();
            if ($request->getEnvironment() === null) {
                $envid = 0;
            } else {
                $envid = $request->getEnvironment()->id;
            }
        }

        if (!($tags instanceof AuditLogTags)) {
            $tags = new AuditLogTags(!empty($tags) && is_array($tags) ? $tags : null);
        }

        $record = new LogRecord();
        $record
            ->setEnvid($envid)
            ->setUserid($user->getId())
            ->setEmail($user->getEmail())
            ->setAccountid($user->getAccountId())
            ->setIp(ip2long($ip))
            ->setTime(new \DateTime(null, new \DateTimeZone('UTC')))
            ->setMessage($message)
            ->setTags($tags)
        ;

        if ($object !== null || $objectBefore !== null) {
            $classes = array();
            if ($object !== null) {
                if (!($object instanceof AbstractAuditLogDocument))
                    $object = $this->getObjectDocument($object);
                $classes[0] = get_class($object);
            }
            if ($objectBefore !== null) {
                if (!($objectBefore instanceof AbstractAuditLogDocument))
                    $objectBefore = $this->getObjectDocument($objectBefore);
                $classes[1] = get_class($objectBefore);
            }
            if (count($classes) != 2) {
                //Empty state has been before or will be after the change.
                list($index, $dataClass) = each($classes);
                if ($index == 0) {
                    $objectBefore = new $dataClass;
                } else {
                    $object = new $dataClass;
                }
            }

            $keyValueRecord = $this->getKeyValueRecord($objectBefore, $object);
            if ($keyValueRecord == null) {
                //No changes found.
                return true;
            }
            $record->setData($keyValueRecord);
        }

        try {
            $res = $this->storage->write($record);
        } catch (\Exception $e) {
            error_log(sprintf(
                'AuditLog::log() failed. %s %s.',
                get_class($e), $e->getMessage()
            ));
            $res = true;
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.AuditLogInterface::getKeyValueRecord()
     */
    public function getKeyValueRecord(AbstractAuditLogDocument $objectBefore, AbstractAuditLogDocument $object)
    {
        $class = get_class($object);
        if (get_class($objectBefore) !== $class) {
            throw new AuditLogException(sprintf(
                'Two states need to have the same object type, but "%s" and "%s" have been given.',
                get_class($objectBefore), $class
            ));
        }
        $record = new KeyValueRecord($class);
        $allprops = array_merge(array_keys((array)$objectBefore), array_keys((array)$object));
        $docProps = $class::getDocumentProperties();
        $hasChanges = false;
        $aIds = array();
        foreach ($allprops as $propname) {
            $v = array();
            $v[0] = isset($objectBefore[$propname]) ? (string)$objectBefore[$propname] : null;
            $v[1] = isset($object[$propname]) ? (string)$object[$propname] : null;
            if ($v[0] !== $v[1]) {
                $hasChanges = true;
                $record->setState($propname, $v[0], $v[1]);
            } else if (!empty($docProps[$propname]['idx'])) {
                $aIds[$propname] = $v;
            }
        }
        if ($hasChanges || !empty($aIds)) {
            foreach ($aIds as $propname => $v) {
                $record->setState($propname, $v[0], $v[1]);
            }
        }
        return $hasChanges ? $record : null;
    }

    /**
     * Gets document that is associated with given object.
     *
     * @param  object   $object  An object. This object needs to be supported with document.
     * @return AbstractAuditLogDocument Returns appropriate document.
     * @throws AuditLogException
     */
    private function getObjectDocument($object)
    {
        $mapping = self::getObjectMapping();
        $class = get_class($object);
        if (!isset($mapping[$class])) {
            throw new AuditLogException(sprintf(
                'Can not find appropriate document for the object "%s". '
              . 'You must update %s::getObjectMapping() method.', $class, __CLASS__
            ));
        }
        $documentClass = __NAMESPACE__ . '\\AuditLog\\Documents\\' . $mapping[$class] . 'Document';
        $basename = preg_replace('#^(.+\\\\)?([^\\\\]+)$#', '\\2', $class);
        if (!is_callable($documentClass . '::createFrom' . $basename)) {
            throw new AuditLogException(sprintf(
                'Cannot find method %s to obtain document. ', $documentClass . '::createFrom' . $basename
            ));
        }
        return call_user_func($documentClass . '::createFrom' . $basename, $object);
    }

    /**
     * Sets container
     * @param     Container    $container DI Container
     * @return    AuditLog
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Gets container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger\AuditLog.AuditLogInterface::getDefaultOptions()
     */
    public function getDefaultOptions()
    {
        return array('enabled' => true);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger\AuditLog.AuditLogInterface::getRequiredOptions()
     */
    public function getRequiredOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.AuditLogInterface::isEnabled()
     */
    public function isEnabled()
    {
        return $this->options['enabled'] ? true : false;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.AuditLogInterface::getStorage()
     */
    public function getStorage()
    {
        return $this->storage;
    }
}