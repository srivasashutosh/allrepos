<?php
namespace Scalr\Tests\Fixtures;

class DiObject1
{
    private $data;

    public $region;

    public function __construct($region = null)
    {
        $this->region = $region;
        $this->data = str_repeat('y', 1024 * 1024);
    }
}