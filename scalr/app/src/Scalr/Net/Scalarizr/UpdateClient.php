<?php
    class Scalr_Net_Scalarizr_UpdateClient
    {
        private $dbServer,
            $port,
            $timeout,
            $cryptoTool,
            $isVPC = false;


        public function __construct(DBServer $dbServer, $port = 8008, $timeout = 5) {
            $this->dbServer = $dbServer;
            $this->port = $port;
            $this->timeout = $timeout;

            if ($this->dbServer->farmId)
                if (DBFarm::LoadByID($this->dbServer->farmId)->GetSetting(DBFarm::SETTING_EC2_VPC_ID))
                    $this->isVPC = true;

            $this->cryptoTool = Scalr_Messaging_CryptoTool::getInstance();
        }

        public function configure($repo, $schedule)
        {
            $params = new stdClass();
            $params->schedule = $schedule;
            $params->repository = $repo;

            return $this->request("configure", $params)->result;
        }

        public function getStatus()
        {
            $r = new stdClass();
            return $this->request("status", $r)->result;
        }

        public function updateScalarizr($force = false)
        {
            $r = new stdClass();
            $r->force = $force;
            return $this->request("update", $r);
        }

        public function restartScalarizr($force = false)
        {
            $r = new stdClass();
            $r->force = $force;
            return $this->request("restart", $r);
        }

        public function executeCmd($cmd) {
            $r = new stdClass();
            $r->command = $cmd;
            return $this->request("execute", $r);
        }

        public function putFile($path, $contents)
        {
            $r = new stdClass();
            $r->name = $path;
            $r->content = base64_encode($contents);
            $r->makedirs = true;
            return $this->request("put_file", $r);
        }

        private function request($method, $params = null)
        {
            $requestObj = new stdClass();
            $requestObj->id = microtime(true);
            $requestObj->method = $method;
            $requestObj->params = $params;

            $jsonRequest = json_encode($requestObj);

            $dt = new DateTime('now', new DateTimeZone("UTC"));
            $timestamp = $dt->format("D d M Y H:i:s e");

            $canonical_string = $jsonRequest . $timestamp;
            $signature = base64_encode(hash_hmac('SHA1', $canonical_string, $this->dbServer->GetProperty(SERVER_PROPERTIES::SZR_KEY), 1));

            $request = new HttpRequest();
            $request->setMethod(HTTP_METH_POST);

            if (\Scalr::config('scalr.instances_connection_policy') == 'local')
                $requestHost = "{$this->dbServer->localIp}:{$this->port}";
            elseif (\Scalr::config('scalr.instances_connection_policy') == 'public')
                $requestHost = "{$this->dbServer->remoteIp}:{$this->port}";
            elseif (\Scalr::config('scalr.instances_connection_policy') == 'auto') {
                if ($this->dbServer->remoteIp)
                    $requestHost = "{$this->dbServer->remoteIp}:{$this->port}";
                else
                    $requestHost = "{$this->dbServer->localIp}:{$this->port}";
            }

            if ($this->isVPC) {
                $routerRole = $this->dbServer->GetFarmObject()->GetFarmRoleByBehavior(ROLE_BEHAVIORS::VPC_ROUTER);
                if ($routerRole) {
                    // No remote IP need to use proxy
                    if (!$this->dbServer->remoteIp) {
                        $requestHost = $routerRole->GetSetting(Scalr_Role_Behavior_Router::ROLE_VPC_IP) . ":80";
                        $request->addHeaders(array(
                            "X-Receiver-Host" =>  $this->dbServer->localIp,
                            "X-Receiver-Port" => $this->port
                        ));
                        // There is public IP, can use it
                    } else {
                        $requestHost = "{$this->dbServer->remoteIp}:{$this->port}";
                    }
                }
            }

            $request->setUrl($requestHost);
            $request->setOptions(array(
                'timeout'	=> $this->timeout,
                'connecttimeout' => $this->timeout
            ));

            $request->addHeaders(array(
                "Date" =>  $timestamp,
                "X-Signature" => $signature,
                "X-Server-Id" => $this->dbServer->serverId
            ));
            $request->setBody($jsonRequest);

            try {
                // Send request
                $request->send();

                if ($request->getResponseCode() == 200) {

                    $response = $request->getResponseData();
                    $jResponse = @json_decode($response['body']);

                    if ($jResponse->error)
                        throw new Exception("{$jResponse->error->message} ({$jResponse->error->code}): {$jResponse->error->data} ({$response['body']})");

                    return $jResponse;
                } else {
                    throw new Exception(sprintf("Unable to perform request to update client: %s", $request->getResponseCode()));
                }
            } catch(HttpException $e) {
                if (isset($e->innerException))
                    $msg = $e->innerException->getMessage();
                else
                    $msg = $e->getMessage();

                throw new Exception(sprintf("Unable to perform request to update client: %s", $msg));
            }
        }
    }