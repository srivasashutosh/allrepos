<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

/**
 * Personality
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.12.2012
 */
class Personality
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $contents;

    /**
     * Convenient constructor
     *
     * @param   string     $path      Path
     * @param   string     $contents  Contents
     */
    public function __construct($path, $contents)
    {
        $this->path = $path;
        $this->contents = $contents;
    }
}