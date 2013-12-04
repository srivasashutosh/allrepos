<?php
namespace Scalr\Logger;

use Scalr\Logger\AuditLog\KeyValueRecord;
use Scalr\Logger\AuditLog\AuditLogTags;
use Scalr\Logger\AuditLog\Documents\AbstractAuditLogDocument;
use Scalr\Logger\AuditLog\LogRecord;
use \ADOConnection;

/**
 * Mysql logger storage
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    12.02.2013
 */
class MysqlLoggerStorage implements LoggerStorageInterface
{

    /**
     * Misc. options
     *
     * @var array
     */
    protected $options;

    /**
     * @var \ADODB_mysqli
     */
    private $db;

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
     * @return  ADOConnection Returns the instance of the ADOConnection
     */
    protected function getDb()
    {
        if ($this->db === null) {
            $this->db = NewADOConnection($this->options['dsn']);
            $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        }
        return $this->db;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::getDefaultOptions()
     */
    public function getDefaultOptions()
    {
        //two weeks
        return array(
            'dsn'      => 'mysql://localhost:3306/scalr',
            'lifetime' => 1209600,
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
        $date = date('Y-m-d H:i:s', time() - $this->options['lifetime']);
        //Removes records whose lifetime is ended
        $this->getDb()->Execute("
            DELETE `auditlog_data`
            FROM `auditlog_data`,`auditlog`
            WHERE `auditlog`.`id` = `auditlog_data`.`logid`
            AND `auditlog`.`time` < ?
        ", array($date));
        $this->getDb()->Execute("
            DELETE `auditlog_tags`
            FROM `auditlog_tags`,`auditlog`
            WHERE `auditlog`.`id` = `auditlog_tags`.`logid`
            AND `auditlog`.`time` < ?
        ", array($date));
        $this->getDb()->Execute("
            DELETE `auditlog` FROM `auditlog` WHERE `time` < ?
        ", array($date));
        //Removes test records
        $this->getDb()->Execute("
            DELETE FROM `auditlog`
            WHERE EXISTS (
                SELECT 1 FROM `auditlog_tags`
                WHERE `auditlog_tags`.`logid` = `auditlog`.`id`
                AND `auditlog_tags`.`tag` = ?
            )
        ", array(AuditLogTags::TAG_TEST));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::find()
     */
    public function find(array $criteria, array $order, $limit)
    {
        $orderAllowed = array('id', 'sessionid', 'accountid', 'userid', 'envid', 'ip', 'time', 'datatype');
        if (!empty($order)) {
            $sOrder = '';
            foreach ($order as $k => $v) {
                if (!in_array(strtolower($k), $orderAllowed)) continue;
                $sOrder .= ', l.`' . $k . '`' . ($v ? '' : ' DESC');
            }
            $sOrder = 'ORDER BY ' . ($sOrder == '' ? 'l.`time` DESC' : substr($sOrder, 2));
        } else {
            $sOrder = 'ORDER BY l.`time` DESC';
        }
        if ($limit) {
            if (is_array($limit)) {
                $sLimit = 'LIMIT ' . (isset($limit['start']) ? $limit['start'] - 0 : '0') . ', '
                        . (isset($limit['limit']) ? $limit['limit'] - 0 : '0');
            } else {
                $sLimit = 'LIMIT ' . ($limit - 0);
            }
        } else {
            $sLimit = '';
        }
        $built = $this->buildQuery($criteria);

        $rows = $this->getDb()->Execute("
            SELECT
                l.`id`, l.`sessionid`, l.`accountid`, l.`userid`, l.`email`,
                l.`envid`, l.`ip`, l.`time`, l.`message`, l.`datatype`
            FROM `auditlog` l
            LEFT JOIN `auditlog_tags` t ON t.`logid` = l.`id`
            LEFT JOIN `auditlog_data` d ON d.`logid` = l.`id`
            WHERE 1 " .  $built['where'] . "
            GROUP BY l.id
            {$sOrder}
            {$sLimit}
        ", $built['args'])->GetRows();

        $records = array();
        foreach ($rows as $record) {
            $trows = $this->getDb()->Execute("
                SELECT `tag` FROM `auditlog_tags` WHERE `logid` = ?
            ", $record['id'])->GetRows();
            if (!empty($trows)) {
                $tags = array();
                array_walk($trows, function($value, $key) use (&$tags) {
                    $tags[] = $value['tag'];
                });
            } else $tags = null;
            $record['tags'] = $tags;
            if (!empty($record['datatype'])) {
                $trows = $this->getDb()->Execute("
                    SELECT `key`, `old_value`, `new_value`
                    FROM `auditlog_data`
                    WHERE `logid` = ?
                ", $record['id'])->GetRows();
                if (!empty($trows)) {
                    $data = array();
                    array_walk($trows, function($value, $key) use (&$data) {
                        $data[$value['key']] = array(
                            'old_value' => $value['old_value'],
                            'new_value' => $value['new_value'],
                        );
                    });
                } else $data = null;
                $record['data'] = $data;
            } else {
                $record['data'] = null;
            }
            $records[] = $this->getLogRecord($record);
        }
        return $records;
    }

    /**
     * Builds query
     *
     * @param  array $criteria
     * @return array
     */
    private function buildQuery(array $criteria)
    {
        $allowed = array(
            'id' => 'l',
            'sessionid' => 'l',
            'accountid' => 'l',
            'userid' => 'l',
            'email' => 'l',
            'envid' => 'l',
            'ip' => 'l',
            'time' => 'l',
            'message' => 'l',
            'datatype' => 'l',
            'tag' => 't',
            'key' => 'd',
            'value' => 'd',
        );
        $built = array(
            'where' => array(),
            'args'  => array(),
        );
        $cmp = array(
            '$lt' => '<',
            '$gt' => '>',
            '$gte' => '>=',
            '$lte' => '<=',
        );
        foreach ($criteria as $k => $v) {
            if (!isset($allowed[$k])) {
                //to be compartible with mongodb query
                if ($k == 'tags') {
                    $k = 'tag';
                } else continue;
            };
            if (is_array($v)) {
                foreach ($v as $t => $vv) {
                    if (isset($cmp[$t])) {
                        $built['where'][] = " AND " . $allowed[$k]. ".`" . $k . "` " . $cmp[$t] . " ?";
                        $built['args'][] = (string) $v;
                    } else if ($t == '$in' || $t == '$nin') {
                        $tmp = '';
                        foreach ((array) $vv as $inVal) {
                            $tmp .= ', ?';
                            $built['args'][] = (string) $inVal;
                        }
                        if ($tmp != '') {
                            $built['where'][] = " AND " . $allowed[$k]. ".`" . $k . "` "
                              . ($t == '$in' ? 'IN' : 'NOT IN')
                              . "(" . substr($tmp, 1) . ")";
                        }
                    }
                }
            } else {
                $built['where'][] = " AND " . $allowed[$k]. ".`" . $k . "` = ?";
                $built['args'][] = (string) $v;
            }
        }
        if (!empty($built['where'])) {
            $built['where'] = join(" ", $built['where']);
        } else {
            $built['where'] = '1';
        }
        return $built;
    }

    /**
     * Gets an log record by the data from db
     *
     * @param    array      $data   Array
     * @return   LogRecord  Returns LogRecord object
     */
    private function getLogRecord ($data)
    {
        $record = new LogRecord($data['id']);
        $record
            ->setAccountid(isset($data['accountid']) ? $data['accountid'] : null)
            ->setSessionid(isset($data['sessionid']) ? $data['sessionid'] : null)
            ->setEmail(isset($data['email']) ? $data['email'] : null)
            ->setEnvid(isset($data['envid']) ? $data['envid'] : null)
            ->setIp(isset($data['ip']) ? $data['ip'] : null)
            ->setMessage(isset($data['message']) ? $data['message'] : null)
            ->setUserid(isset($data['userid']) ? $data['userid'] : null)
        ;
        if (isset($data['time'])) {
            $time = new \DateTime($data['time'], new \DateTimeZone('UTC'));
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
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::truncate()
     */
    public function truncate()
    {
        $this->getDb()->Execute("
            DELETE FROM `auditlog_data`
        ");
        $this->getDb()->Execute("
            DELETE FROM `auditlog_tags`
        ");
        $this->getDb()->Execute("
            DELETE FROM `auditlog`
        ");
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Logger.LoggerStorageInterface::write()
     */
    public function write(LogRecord $record)
    {
        $data = $record->getData();

        $this->getDb()->Execute('START TRANSACTION');

        $res = $this->getDb()->Execute("
            INSERT `auditlog`
            SET `id` = ?,
                `sessionid` = ?,
                `accountid` = ?,
                `userid` = ?,
                `email` = ?,
                `envid` = ?,
                `ip` = ?,
                `time` = ?,
                `message` = ?,
                `datatype` = ?
        ", array(
            $record->getUuid(),
            $record->getSessionid(),
            $record->getAccountid(),
            $record->getUserid(),
            $record->getEmail(),
            $record->getEnvid(),
            $record->getIp(),
            $record->getTime()->format('Y-m-d H:i:s'),
            $record->getMessage(),
            $record->getDatatype(),
        ));

        if ($res) {
            $stmt = '';
            $rt = array();
            foreach ($record->getTags()->get() as $v) {
                $stmt .= ",('" . $record->getUuid() . "', ?)";
                $rt[] = $v;
            }
            if ($stmt != '') {
                $res = $this->getDb()->Execute("
                    INSERT `auditlog_tags` (`logid`, `tag`)
                    VALUES " . substr($stmt, 1) . "
                ", $rt);
            }

            if ($data instanceof KeyValueRecord) {
                $diff = get_object_vars($data);
                if (!empty($diff)) {
                    $stmt = '';
                    $rt = array();
                    foreach ($diff as $k => $v) {
                        $stmt .= ", ('" . $record->getUuid() . "', ?, ?, ?)";
                        $rt[] = $k;
                        $rt[] = $v['old_value'];
                        $rt[] = $v['new_value'];
                    }
                    if ($stmt != '') {
                        $res = $this->getDb()->Execute("
                            INSERT `auditlog_data` (`logid`, `key`, `old_value`, `new_value`)
                            VALUES " . substr($stmt, 1) . "
                        ", $rt);
                    }
                }
            }
        }
        if ($res) {
            $this->getDb()->Execute('COMMIT');
        } else {
            $this->getDb()->Execute('ROLLBACK');
        }
        return $res ? true : false;
    }
}