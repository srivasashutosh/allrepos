<?php

namespace Scalr\System\Config;

/**
 * Yaml config parser.
 *
 * This class depends on Pecl yaml package. (php -m | grep yaml)
 * This is extended parser. Addidtional directives are supported.
 *
 * 1)First addition
 * --
 * imports:
 *   - { resource: parameters.ini }
 *   - { resource: to-override.yml }
 *
 * Imports directive allows you to import configurations from another files one by one.
 * It accepts ini configuration files as well as yaml files.
 *
 * 2)Second addition
 * --
 * # ./to-override.yml content
 * # If the imported file contains following parameters dataset
 * # then all entries %one% or %two% from to-parse.yml document
 * # in this case will be replaced with its values.
 * parameters:
 *     one: "1"
 *     two: "2"
 * ===
 * # ./to-parse.yml content
 * scalr:
 *    option1: %one%
 *    option2: %two%
 * ===
 *
 * Basic usage:
 *
 * $config = \Scalr\System\Config\Yaml::load('/app/etc/config.yml');
 * echo $config->get('scalr.connections.mysql.name');
 * echo $config['scalr']['connections']['mysql']['name'];
 * echo $config->toArray();
 * echo $config->toJson();
 * $s = serialize($config);
 * $un = unserialize($s);
 * echo $un->get('scalr.connections.mysql.name');
 *
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    29.05.2013
 *
 * @method   \Scalr\System\Config\Yaml load()
 *           load(string $file)
 *           Loads yaml document from the specified file
 *
 * @method   \Scalr\System\Config\Yaml parse()
 *           parse(string $content)
 *           Parses yaml document from the specified string
 */
class Yaml implements \ArrayAccess
{
    /**
     * Data
     *
     * @var array
     */
    private $data;

    /**
     * Dot notation access to data (2D list)
     *
     * @var array
     */
    private $index;

    /**
     * Parameters
     *
     * @var array
     */
    private $parameters;

    /**
     * The list of the files to import.
     *
     * This array looks like array(path => modified)
     *
     * @var array
     */
    private $imports;

    /**
     * Real path to yaml document
     *
     * @var string
     */
    private $path;

    /**
     * The timestamp when config was modified last time.
     *
     * @var int
     */
    private $modified;

    /**
     * The timestamp when the last time additional dependent code is changed.
     *
     * @var array
     */
    private $extensionmodified;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->parameters = array();
        $this->data = array();
        $this->imports = array();
        $this->index = array();
        $this->extensionmodified = array();
    }

    /**
     * Gets the value of the specified parameter using the dot notation access.
     *
     * @param   string    $name The dot notation name of the parameter. Dot will be used as separator.
     * @return  mixed     Returns the value of the specified parameter or throws an exception if it isn't defined.
     * @throws  Exception\YamlException
     */
    public function get($name)
    {
        //Tries to use index for complete paths
        if (array_key_exists($name, $this->index)) {
            return $this->index[$name];
        }

        //If path is incomplete it will try to fetch
        $token = strtok($name, '.');
        $ptr =& $this->data;
        while ($token !== false) {
            if (!isset($ptr[$token])) {
                throw new Exception\YamlException(sprintf(
                    'Config parameter "%s" is not defined.', $name
                ));
            }
            $ptr =& $ptr[$token];
            $token = strtok('.');
        }

        return $ptr;
    }

    /**
     * Checks whether specified key is defined.
     *
     * @param   string    $name  Dot notation key
     * @return  boolean   Returns true if defined
     */
    public function defined($name)
    {
        if (array_key_exists($name, $this->index)) return true;

        //If path is incomplete it will try to fetch
        $token = strtok($name, '.');
        $ptr =& $this->data;
        while ($token !== false) {
            if (!isset($ptr[$token])) {
                return false;
            }
            $ptr =& $ptr[$token];
            $token = strtok('.');
        }

        return true;
    }

    /**
     * Sets the $key with $value
     *
     * @param   string     $name  Dot notation access key
     * @param   mixed      $value Value
     * @return  Yaml
     */
    protected function set($name, $value)
    {
        $c = $token = strtok($name, '.');
        $ptr =& $this->data;
        while ($token !== false) {
            if (!isset($ptr[$token])) {
                $ptr[$token] = array();
            } else if (!is_array($ptr[$token])) {
                $ptr[$token] = array();
                if (isset($this->index[$c])) {
                    unset($this->index[$c]);
                }
            }
            $t = strtok('.');
            if ($t === false) break;
            $ptr =& $ptr[$token];
            $token = $t;
            $c .= '.' . $token;
        }
        $ptr[$token] = $value;
        $this->index[$name] = $value;

        return $this;
    }

    /**
     * Creates dot notation fast access
     */
    private function createIndexes()
    {
        $this->index = array();
        if (!is_array($this->data)) return;
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->data));
        foreach ($it as $lkey => $lvalue) {
            $key = $lkey;
            for ($i = $it->getDepth() - 1; $i >= 0; --$i) {
                $key = $it->getSubIterator($i)->key() . '.' . $key;
            }
            $this->index[$key] = $lvalue;
        }
    }

    /**
     * Parses yaml file from the content
     *
     * @param   string    $content
     * @return  Yaml
     */
    protected function _parse($content)
    {
        $data = array();
        //This is necessary for supporting imports feature
        if (($m = preg_split('/^imports[\s]*\:/sm', $content, 2)) && !empty($m[1]) &&
            ($n = preg_split('/^[^\s]/sm', $m[1], 2)) && !empty($n[0])) {
            $arr = @yaml_parse('imports:' . $n[0]);
            if ($arr === false) {
                throw new Exception\YamlException('Could not parse yaml file.');
            }
            if (!empty($arr['imports']) && is_array($arr['imports'])) {
                foreach ($arr['imports'] as $v) {
                    if (!empty($v['resource']) && preg_match('/^(.).*\.(ini|ya?ml)$/i', $v['resource'], $m)) {
                        $location = realpath(
                            $m[1] == '/' ? $v['resource'] : dirname($this->path) . DIRECTORY_SEPARATOR . $v['resource']
                        );
                        if (strtolower($m[2]) == 'ini') {
                            $cfg = parse_ini_file($location, true);
                        } else {
                            $yaml = self::load($location);
                            $cfg = $yaml->toArray();
                            $this->imports = array_merge($this->imports, $yaml->getImports());
                            unset($yaml);
                        }
                        $this->appendImports($location);
                        if (!empty($cfg) && is_array($cfg)) {
                            if (isset($cfg['parameters']) && is_array($cfg['parameters'])) {
                                foreach ($cfg['parameters'] as $key => $value) {
                                    //For now, it only supports the string type of the parameters.
                                    $this->parameters[$key] = (string) $value;
                                }
                            }
                            $data[] = $cfg;
                        }
                    }
                }
            }
        }

        if (preg_match('/\.ini$/', $this->path)) {
            //Workaround for the INI config format
            $config = parse_ini_string($content, true);
        } else {
            if (!empty($this->parameters)) {
                //We have to replase %parameter-keys% with its values.
                //Multiline strings are supported
                $content = preg_replace(
                    array_map(function ($arr) {
                        return '/^( *)(.+)(' . preg_quote("%{$arr}%", '/') . ')/m';
                    }, array_keys($this->parameters)),
                    array_map(function ($val) {
                        return strpos($val, "\n") === false ? '\\1\\2 ' . $val :
                            ('\\1\\2 | ' . "\n" . '\\1  ' . preg_replace('/[\r\n]{1,2}/', "\n" . '\\$1  ', $val));
                    }, array_values($this->parameters)),
                    $content
                );
            }

            $config = yaml_parse($content);
            if ($config === false) {
                throw new Exception\YamlException('Could not parse yaml file.');
            }
            //imports is the reserved top level token.
            if (isset($config['imports'])) {
                unset($config['imports']);
            }
        }

        $data[] = $config;

        $this->data = call_user_func_array('array_replace_recursive', $data);

        $this->setModified();

        $this->createIndexes();

        return $this;
    }

    /**
     * Gets all imported files
     *
     * @param  bool  $includeSelf  Whether it should include self file
     * @return array Retuns the list of the all imported files
     */
    public function getImports($includeSelf = false)
    {
        $ret = $this->imports;
        if ($includeSelf) {
            $ret = array_merge($this->imports, array(
                $this->path => $this->modified,
            ), $this->extensionmodified);
        }
        return $ret;
    }

    /**
     * Appends path to the imports array
     *
     * @param   string   $path Path to the imported document
     * @return  Yaml
     */
    private function appendImports($path)
    {
        $this->imports[$path] = filemtime($path);

        return $this;
    }

    /**
     * Gets an real path of the parsed config file
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets timestamp when the parsed file was modified last time.
     *
     * @return  int
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Sets timestamp for the parsed file when it was modified last time.
     *
     * @return  Yaml
     */
    private function setModified()
    {
        if (!$this->path) return;
        $this->modified = filemtime($this->path);

        $this->extensionmodified = array();

        //Changes in the Extension cause changes in the interpretation of the config
        foreach (array(APPPATH . '/src/Scalr/System/Config/Extension.php',
                       APPPATH . '/src/Scalr/System/Config/Loader.php',
                       APPPATH . '/src/Scalr/System/Config/Yaml.php') as $path) {
            if (is_readable($path)) {
                $this->extensionmodified[realpath($path)] = filemtime($path);
            }
        }

        return $this;
    }

    /**
     * Magic call.
     *
     * It ensures that calls from the static content for load and parse methods will work.
     * For an instance you may want to use Yaml::load($file).
     *
     * @param   string     $name      A static method name.
     * @param   array      $arguments Arguments
     * @throws  \InvalidArgumentException
     */
    public function __call($name, $arguments)
    {
        if ($name == 'load') {
            if (!isset($arguments[0])) {
                throw new \InvalidArgumentException(sprintf('Path to the yaml file must be provided.'));
            }
            $this->path = @realpath($arguments[0]);
            return $this->_parse(file_get_contents($this->path));
        } else if ($name == 'parse') {
            if (!isset($arguments[0])) {
                throw new \InvalidArgumentException(sprintf('Content of the yaml document must be provided.'));
            }
            $this->path = '.';
            return $this->_parse($arguments[0]);
        }
        throw new \InvalidArgumentException(sprintf(
            'Could not find public method %s for the object "%s".', $name, get_class($this)
        ));
    }

    /**
     * Magic call static.
     *
     * It ensures that calls from the static content for load and parse methods will work.
     * For an instance you may want to use Yaml::load($file).
     *
     * @param   string     $name      A static method name.
     * @param   array      $arguments Arguments
     * @throws  \InvalidArgumentException
     */
    public static function __callStatic($name, $arguments)
    {
        $class = get_called_class();
        if ($name == 'load' || $name == 'parse') {
            $obj = new $class;
            return call_user_func_array(array($obj, $name), $arguments);
        }
        throw new \InvalidArgumentException(sprintf(
            'Could not find static method %s::%s.', $class, $name
        ));
    }

	/**
     * {@inheritdoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

	/**
     * {@inheritdoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

	/**
     * {@inheritdoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

	/**
     * {@inheritdoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Gets parsed data as array
     *
     * @return  array Returns parsed data as array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Gets parsed data as json string
     *
     * @param   int    $options  optional Bitmask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP,
     *                           JSON_HEX_APOS, JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES,
     *                           JSON_FORCE_OBJECT, JSON_UNESCAPED_UNICODE.
     * @return  string Returns parsed data as json string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->data, $options);
    }

    public function __sleep()
    {
        return array('data', 'parameters', 'imports', 'path', 'index', 'modified', 'extensionmodified');
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Tries to get parameter
     *
     * @param   string      $name Dot notation access name.
     * @return  mixed
     * @throws  Exception\YamlException
     */
    public function __invoke($name)
    {
        return $this->get($name);
    }

    /**
     * @throws Exception\YamlException
     */
    public function __set($name, $value)
    {
        throw new Exception\YamlException('You can not set a value directly to config object.');
    }
}