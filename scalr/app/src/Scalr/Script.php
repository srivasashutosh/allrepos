<?php

class Scalr_Script extends Scalr_Model
{
    protected $dbTableName = 'scripts';
    protected $dbPrimaryKey = 'id';
    protected $dbMessageKeyNotFound = 'Script #%s not found in database';

    protected $dbPropertyMap = array(
        'id'            => 'id',
        'name'          => 'name',
        'description'   => 'description',
        'dtadded'       => array('property' => 'dtAdded', 'createSql' => 'NOW()'),
        'issync'        => 'isSync',
        'clientid'      => 'accountId'
    );

    public
        $id,
        $name,
        $description,
        $dtAdded,
        $isSync,
        $accountId;

    const TARGET_ALL = 'all';
    const TARGET_FARM = 'farm';
    const TARGET_ROLE = 'role';
    const TARGET_INSTANCE = 'instance';
    const TARGET_ROLES = 'roles';
    const TARGET_BEHAVIORS = 'behaviors';

    public function getCustomVariables($template)
    {
        $text = preg_replace('/(\\\%)/si', '$$scalr$$', $template);
        preg_match_all("/\%([^\%\s]+)\%/si", $text, $matches);
        return $matches[1];
    }

    public function getLatestRevision()
    {
        return $this->db->GetOne("SELECT MAX(revision) FROM script_revisions WHERE scriptid = ? GROUP BY scriptid", array(
            $this->id
        ));
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $revisions = $this->db->GetAll("SELECT id, revision, script, dtcreated as dtCreated, variables FROM script_revisions WHERE scriptid=? ORDER BY revision DESC", array(
            $this->id
        ));

        foreach ($revisions as $index => $rev) {
            $revisions[$index]['dtCreated'] = Scalr_Util_DateTime::convertTz($rev['dtCreated']);
            $revisions[$index]['variables'] = unserialize($revisions[$index]['variables']);
        }

        return $revisions;
    }


    public function getRevision($version = null)
    {
        if (!$version)
            $revision = $this->db->GetRow("SELECT id, revision, script, dtcreated as dtCreated, variables FROM script_revisions WHERE scriptid=? ORDER BY revision DESC", array(
                $this->id
            ));
        else
            $revision = $this->db->GetRow("SELECT id, revision, script, dtcreated as dtCreated, variables FROM script_revisions WHERE scriptid=? AND revision = ?", array(
                $this->id,
                $version
            ));

        $revision['variables'] = unserialize($revision['variables']);
        return $revision;
    }

    public function setRevision($script, $version = null)
    {
        if ($version == null)
            $version = $this->getLatestRevision() + 1;

        $variables = array();
        $builtin = array_keys(Scalr_Scripting_Manager::getScriptingBuiltinVariables());
        foreach ((array)$this->getCustomVariables($script) as $var) {
            if (! in_array($var, $builtin))
                $variables[$var] = ucwords(str_replace("_", " ", $var));
        }

        $variables = serialize($variables);
        $this->db->Execute('INSERT INTO `script_revisions` SET
                scriptid = ?,
                revision = ?,
                script = ?,
                variables = ?,
                dtcreated = NOW()
            ON DUPLICATE KEY UPDATE
                script = ?,
                variables = ?,
                dtcreated = NOW()
            ', array(
            $this->id,
            $version,
            $script,
            $variables,
            $script,
            $variables
        ));
    }

    public function fork($newName, $accountId)
    {
        $script = new self();
        $script->name = $newName;
        $script->description = $this->description;
        $script->isSync = $this->isSync;
        $script->accountId = $accountId;
        $script->save();

        $latestRev = $this->getRevision();
        $script->setRevision($latestRev['script']);
        return $script;
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::delete()
     */
    public function delete($id = null)
    {
        // TODO: rewrite shortcuts

        // Check template usage
        $rolesCount = $this->db->GetOne("SELECT COUNT(*) FROM farm_role_scripts WHERE scriptid=? AND event_name NOT LIKE 'CustomEvent-%'",
            array($this->id)
        );

        if ($rolesCount > 0)
            throw new Scalr_Exception_Core(sprintf('Script "%s" being used and can\'t be deleted', $this->name));

        parent::delete();

        $id = !is_null($id) ? $id : $this->id;
        $this->db->Execute("DELETE FROM farm_role_scripts WHERE scriptid=?", array($id));
        $this->db->Execute("DELETE FROM script_revisions WHERE scriptid=?", array($id));
    }
}
