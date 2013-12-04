<?php

class Scalr_Scripting_Manager
{
    private static $BUILTIN_VARIABLES_LOADED = false;
    private static $BUILTIN_VARIABLES = array(
        "image_id" 		=> 1,
        "role_name" 	=> 1,
        "isdbmaster" 	=> 1,
        "farm_id"		=> 1,
        "farm_name"		=> 1,
        "behaviors"		=> 1,
        "server_id"		=> 1,
        "env_id"		=> 1,
        "env_name"		=> 1,
        "farm_role_id"  => 1,
        "event_name"	=> 1,
        "cloud_location"=> 1,

        //TODO: Remove this vars
        "ami_id" 		=> 1,
        "instance_index"=> 1,
        "region" 		=> 1,
        "avail_zone" 	=> 1,
        "external_ip" 	=> 1,
        "internal_ip" 	=> 1,
        "instance_id" 	=> 1
    );

    public static function getScriptingBuiltinVariables()
    {
        foreach (self::$BUILTIN_VARIABLES as $k=>$v)
            self::$BUILTIN_VARIABLES["event_{$k}"] = $v;

        if (!self::$BUILTIN_VARIABLES_LOADED)
        {
            $ReflectEVENT_TYPE = new ReflectionClass("EVENT_TYPE");
            $event_types = $ReflectEVENT_TYPE->getConstants();
            foreach ($event_types as $event_type)
            {
                if (class_exists("{$event_type}Event"))
                {
                    $ReflectClass = new ReflectionClass("{$event_type}Event");
                    $retval = $ReflectClass->getMethod("GetScriptingVars")->invoke(null);
                    if (!empty($retval))
                    {
                        foreach ($retval as $k=>$v)
                        {
                            if (!self::$BUILTIN_VARIABLES[$k])
                            {
                                self::$BUILTIN_VARIABLES[$k] = array(
                                "PropName"	=> $v,
                                "EventName" => "{$event_type}"
                                );
                            }
                            else
                            {
                                if (!is_array(self::$BUILTIN_VARIABLES[$k]['EventName']))
                                    $events = array(self::$BUILTIN_VARIABLES[$k]['EventName']);
                                else
                                    $events = self::$BUILTIN_VARIABLES[$k]['EventName'];

                                $events[] = $event_type;

                                self::$BUILTIN_VARIABLES[$k] = array(
                                "PropName"	=> $v,
                                "EventName" => $events
                                );
                            }
                        }
                    }
                }
            }

            self::$BUILTIN_VARIABLES_LOADED = true;
        }

        return self::$BUILTIN_VARIABLES;
    }


    private static function makeSeed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }

    public static function generateEventName($prefix)
    {
        mt_srand(self::makeSeed());
        return "{$prefix}-" . date("YmdHis") . '-' . mt_rand(100000,999999);
    }

    public static function extendMessage(Scalr_Messaging_Msg $message, Event $event, DBServer $eventServer, DBServer $targetServer, $noScripts = false)
    {
        $db = \Scalr::getDb();

        $retval = array();

        if (!$noScripts) {
            try {
                $scripts = self::getEventScriptList($event, $eventServer, $targetServer);
                if (count($scripts) > 0) {
                    foreach ($scripts as $script) {
                        $itm = new stdClass();
                        // Script
                        $itm->asynchronous = ($script['issync'] == 1) ? '0' : '1';
                        $itm->timeout = $script['timeout'];
                        $itm->name = $script['name'];
                        $itm->body = $script['body'];

                        $retval[] = $itm;
                    }
                }
            } catch (Exception $e) {}
        }

        $message->scripts = $retval;
        $message->eventId = $event->GetEventID();
        $message->globalVariables = array();

        //Global variables
        try {

            /** System variables **/
            if ($targetServer)
                $variables = $targetServer->GetScriptingVars();
            else
                $variables = array();

            if ($event) {
                if ($eventServer)
                    foreach ($eventServer->GetScriptingVars() as $k => $v) {
                        $variables["event_{$k}"] = $v;
                    }

                foreach ($event->GetScriptingVars() as $k=>$v)
                    $variables[$k] = $event->{$v};

                if (isset($event->params) && is_array($event->params))
                    foreach ($event->params as $k=>$v)
                        $variables[$k] = $v;


                $variables['event_name'] = $event->GetName();
            }
            foreach ($variables as $name => $value) {
                $message->globalVariables[] = (object)array('name' => "SCALR_".strtoupper($name), 'value' => $value);
            }

            // Add custom variables
            $globalVariables = new Scalr_Scripting_GlobalVariables($eventServer->envId);
            $vars = $globalVariables->listVariables($eventServer->roleId, $eventServer->farmId, $eventServer->farmRoleId);
            foreach ($vars as $k => $v) {
                $message->globalVariables[] = (object)array('name' => $k, 'value' => $v);
            }
        } catch (Exception $e) {}

        return $message;
    }

    public static function prepareScript($scriptSettings, DBServer $targetServer, Event $event = null)
    {
        $db = \Scalr::getDb();

        //$scriptSettings['version'] = (int)$scriptSettings['version'];

        if ($scriptSettings['version'] == 'latest' || (int)$scriptSettings['version'] == -1) {
            $version = (int)$db->GetOne("SELECT MAX(revision) FROM script_revisions WHERE scriptid=?",
                array($scriptSettings['scriptid'])
            );
        }
        else
            $version = (int)$scriptSettings['version'];

        $template = $db->GetRow("SELECT name,id FROM scripts WHERE id=?",
            array($scriptSettings['scriptid'])
        );
        $template['timeout'] = $scriptSettings['timeout'];
        $template['issync'] = $scriptSettings['issync'];

        $revisionInfo = $db->GetRow("SELECT script, variables FROM script_revisions WHERE scriptid=? AND revision=?", array(
            $template['id'], $version
        ));

        $template['body'] = $revisionInfo['script'];

        if (!$template['body'])
            return false;

        $scriptParams = (array)unserialize($revisionInfo['variables']);
        foreach ($scriptParams as &$val)
            $val = "";

        $params = array_merge($scriptParams, $targetServer->GetScriptingVars(), (array)unserialize($scriptSettings['params']));

        if ($event) {
            $eventServer = $event->DBServer;
            foreach ($eventServer->GetScriptingVars() as $k => $v) {
                $params["event_{$k}"] = $v;
            }

            foreach ($event->GetScriptingVars() as $k=>$v)
                $params[$k] = $event->{$v};

            if (isset($event->params) && is_array($event->params))
                foreach ($event->params as $k=>$v)
                    $params[$k] = $v;

            $params['event_name'] = $event->GetName();
        }

        if ($event instanceof CustomEvent) {
            if (count($event->params) > 0)
                $params = array_merge($params, $event->params);
        }

        // Prepare keys array and array with values for replacement in script
        $keys = array_keys($params);
        $f = create_function('$item', 'return "%".$item."%";');
        $keys = array_map($f, $keys);
        $values = array_values($params);
        $script_contents = str_replace($keys, $values, $template['body']);
        $template['body'] = str_replace('\%', "%", $script_contents);

        // Parse and set variables from data bag
        //TODO: @param_name@

        // Generate script contents
        $template['name'] = preg_replace("/[^A-Za-z0-9]+/", "_", $template['name']);

        return $template;
    }

    public static function getEventScriptList(Event $event, DBServer $eventServer, DBServer $targetServer)
    {
        $db = \Scalr::getDb();

        $roleScripts = $db->GetAll("SELECT * FROM role_scripts WHERE (event_name=? OR event_name='*') AND role_id=? ORDER BY order_index ASC", array($event->GetName(), $eventServer->roleId));

        $scripts = $db->GetAll("SELECT * FROM farm_role_scripts WHERE (event_name=? OR event_name='*') AND farmid=? ORDER BY order_index ASC", array($event->GetName(), $eventServer->farmId));

        foreach ($roleScripts as $script) {

            $params = $db->GetOne("SELECT params FROM farm_role_scripting_params WHERE farm_role_id = ? AND `hash` = ? AND farm_role_script_id = '0'", array(
                $eventServer->farmRoleId,
                $script['hash']
            ));
            if ($params)
                $script['params'] = $params;

            $scripts[] = array(
             "id" => "r{$script['id']}",
             "scriptid" => $script['script_id'],
             "params" => $script['params'],
             "event_name" => $event->GetName(),
             "target" => $script['target'],
             "version" => $script['version'],
             "timeout" => $script['timeout'],
             "issync" => $script['issync'],
             "order_index" => $script['order_index'],
             "type"   => "role"
            );
        }

        $retval = array();
        foreach ($scripts as $scriptSettings) {
            $scriptSettings['order_index'] = (float)$scriptSettings['order_index'];

            // If target set to that instance only
            if ($scriptSettings['target'] == Scalr_Script::TARGET_INSTANCE && $eventServer->serverId != $targetServer->serverId)
                continue;

            // If target set to all instances in specific role
            if ($scriptSettings['target'] == Scalr_Script::TARGET_ROLE && $eventServer->farmRoleId != $targetServer->farmRoleId)
                continue;

            if ($scriptSettings['type'] != 'role') {
                // Validate that event was triggered on the same farmRoleId as script
                if ($eventServer->farmRoleId != $scriptSettings['farm_roleid'])
                    continue;

                // Validate that target server has the same farmRoleId as event server with target ROLE
                if ($scriptSettings['type'] != 'role' && $scriptSettings['target'] == Scalr_Script::TARGET_ROLE && $targetServer->farmRoleId != $scriptSettings['farm_roleid'])
                    continue;
            }

            if ($scriptSettings['target'] == Scalr_Script::TARGET_ROLES || $scriptSettings['target'] == Scalr_Script::TARGET_BEHAVIORS) {

                if ($scriptSettings['type'] != 'role')
                    $targets = $db->GetAll("SELECT * FROM farm_role_scripting_targets WHERE farm_role_script_id = ?", array($scriptSettings['id']));
                else
                    $targets = array();

                $execute = false;
                foreach ($targets as $target) {
                    switch ($target['target_type']) {
                        case "farmrole":
                            if ($targetServer->farmRoleId == $target['target'])
                                $execute = true;
                            break;
                        case "behavior":
                            if ($targetServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior($target['target']))
                                $execute = true;
                            break;
                    }
                }

                if (!$execute)
                    continue;
            }

            if ($scriptSettings['target'] == "" || $scriptSettings['id'] == "")
                continue;

            $script = self::prepareScript($scriptSettings, $targetServer, $event);

            if ($script) {
                while (true) {
                    $index = (string)$scriptSettings['order_index'];
                    if (!$retval[$index]) {
                        $retval[$index] = $script;
                        break;
                    }
                    else
                        $scriptSettings['order_index'] += 0.01;
                }
            }
        }

        @ksort($retval);

        return $retval;
    }
}