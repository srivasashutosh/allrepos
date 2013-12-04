<?php
namespace Scalr\Tests\Fixtures;

class DbMock1
{
    private $storage;

    private $selectQueries = 0;
    private $deleteQueries = 0;
    private $insertQueries = 0;

    public function Execute($query, array $pars)
    {
        if (count($pars) == 3) {
            $this->deleteQueries++;
            //DELETE
            //$this->id, $key, $group
            if (isset($this->storage[$pars[0]][$pars[2]][$pars[1]])) {
                unset($this->storage[$pars[0]][$pars[2]][$pars[1]]);
            }
        } else {
            $this->insertQueries++;
            //INSERT
            //$this->id, $key, $value, $group, $value
            $this->storage[$pars[0]][$pars[3]][$pars[1]] = $pars[2];
        }
    }

    public function GetOne($query, array $pars)
    {
        $this->selectQueries++;
        //$this->id, $key, $group
        return isset($this->storage[$pars[0]][$pars[2]][$pars[1]]) ? $this->storage[$pars[0]][$pars[2]][$pars[1]] : false;
    }

    public function GetAssoc($query, array $pars, $forcearray = false, $first2col = false)
    {
        $this->selectQueries++;
        $ret = array();
        for ($i = 2; $i < count($pars); $i++) {
            if (isset($this->storage[$pars[0]][$pars[1]][$pars[$i]])) {
                $ret[$pars[$i]] = $this->storage[$pars[0]][$pars[1]][$pars[$i]];
            }
        }
        return $ret;
    }

    public function getCountInsert()
    {
        return $this->insertQueries;
    }

    public function getCountDelete()
    {
        return $this->deleteQueries;
    }

    public function getCountSelect()
    {
        return $this->selectQueries;
    }
}
