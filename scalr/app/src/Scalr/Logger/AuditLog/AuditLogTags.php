<?php
namespace Scalr\Logger\AuditLog;

/**
 * AuditLogTags
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.11.2012
 */
class AuditLogTags
{
    //Note. Tag value must be provided in lower case.

    const TAG_CREATE = 'create';

    const TAG_REMOVE = 'remove';

    const TAG_UPDATE = 'update';

    const TAG_TERMINATE = 'terminate';

    const TAG_START = 'start';

    const TAG_STOP = 'stop';

    const TAG_PAUSE = 'pause';

    const TAG_TEST = 'test';


    /**
     * Array of the tags
     *
     * @var array
     */
    private $tags = array();

    /**
     * Constructor
     *
     * @param   string     $tag,... unlimited optional A tag value
     */
    public function __construct($tag = null)
    {
        call_user_func_array(array($this, 'add'), ($tag !== null && is_array($tag) ? $tag : func_get_args()));
    }

    /**
     * Adds tags
     *
     * @param   string       $tag,... unlimited A tag value
     * @return  AuditLogTags Returns AuditLogTags
     * @throws  \InvalidArgumentException
     */
    public function add($tag = null)
    {
        $args = func_get_args();
        $available = self::getAvailableTags();
        foreach ($args as $v) {
            if (empty($v)) continue;
            if (!in_array($v, $available)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid tag "%s"! Allowed: %s', $v, join(', ', $available)
                ));
            }
            $this->tags[] = $v;
        }
        array_unique($this->tags);
        return $this;
    }

    /**
     * Removes tags
     *
     * @param   string       $tag,... unlimited A tag value
     * @return  AuditLogTags Returns AuditLogTags
     * @throws  \InvalidArgumentException
     */
    public function remove($tag = null)
    {
        $args = func_get_args();
        $available = self::getAvailableTags();
        $t = array();
        foreach ($args as $v) {
            if (empty($v)) continue;
            if (!in_array($v, $available)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid tag "%s"! Allowed: %s', $v, join(', ', $available)
                ));
            }
            $t[] = $v;
        }
        $this->tags = array_diff($this->tags, $t);
        return $this;
    }

    /**
     * Gets tags
     *
     * @return array Returns tags
     */
    public function get()
    {
        return array_values($this->tags);
    }

    public function __isset($tag)
    {
        return in_array($tag, $this->tags);
    }

    public function __unset($tag)
    {
        $this->remove($tag);
    }

    public function __toString()
    {
        return join(',', $this->tags);
    }

    /**
     * Gets an available tags.
     *
     * @return array Returns array of the available tags.
     */
    public static function getAvailableTags()
    {
        static $avail = null;
        if (!isset($avail)) {
            $avail = array();
            $refl = new \ReflectionClass(__CLASS__);
            /* @var $refConst  */
            foreach ($refl->getConstants() as $name => $value) {
                if (preg_match('/^TAG_/', $name)) {
                    $avail[] = $value;
                }
            }
        }
        return $avail;
    }
}