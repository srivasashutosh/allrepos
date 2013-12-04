<?php

class Scalr_Messaging_Service_ControlQueueHandler implements Scalr_Messaging_Service_QueueHandler
{

    /**
     * @var \ADODB_mysqli
     */
    private $db;

    private $logger;

    function __construct()
    {
        $this->db = \Scalr::getDb();
        $this->logger = Logger::getLogger(__CLASS__);
    }

    function accept($queue) {
        return $queue == "control";
    }

    function handle($queue, Scalr_Messaging_Msg $message, $rawMessage = null, $rawJsonMessage = null) {
        $this->logger->info(sprintf("Received message '%s' from server '%s'",
                $message->getName(), $message->getServerId()));
        try {
            $this->db->Execute("INSERT INTO messages SET
                messageid = ?,
                message = ?,
                json_message = ?,
                server_id = ?,
                dtlasthandleattempt = NOW(),
                type = ?,
                isszr = ?,
                ipaddress = ?
            ", array(
                $message->messageId,
                $rawMessage,
                $rawJsonMessage,
                $message->getServerId(),
                "in",
                1,
                $_SERVER['REMOTE_ADDR']
            ));
        } catch (Exception $e) {
            // Message may be already delivered.
            // urlopen issue on scalarizr side:
            // QueryEnvError: <urlopen error [Errno 4] Interrupted system call>
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                throw $e;
            }
        }
    }
}