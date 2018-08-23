<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@dotpay.pl so we can send you a copy immediately.
 *
 * @author    Dotpay Team <tech@dotpay.pl>
 * @copyright Dotpay sp. z o.o.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Dotpay\Loader;

use \ReflectionClass;
use Dotpay\Loader\Xml\Param;
use Dotpay\Loader\Xml\ObjectNode;
use Dotpay\Exception\DotpayException;
use Dotpay\Exception\Loader\ObjectNotFoundException;
use Dotpay\Exception\Loader\ParamNotFoundException;
use Dotpay\Exception\Loader\EmptyObjectNameException;

/**
 * Loader of class instances based on dependency structure defined in an XML configuration file
 */
class Loader
{
    /**
     * @var Loader|null An instance of the Loader class
     */
    private static $instance = null;
    
    /**
     * @var array List of ObjectNode elements which can contain instantiated objects.
     * Keys are class names.
     */
    private $objects = [];
    
    /**
     * @var array List of ObjectNode elements which can contain instantiated objects.
     * Keys are aliases.
     */
    private $aliases = [];
    
    /**
     * Load the loader with given configuration
     * @param Parser|null $current The current parsed XML file with dependency structure
     * @param Parser|null $default The default parsed XML file with dependency structure
     * @return Loader
     */
    public static function load(Parser $current = null, Parser $default = null)
    {
        if (empty(self::$instance)) {
            self::$instance = new Loader($current, $default);
        } elseif ($current !== null || $default !== null) {
            self::$instance->initialize($current, $default);
        }
        return self::$instance;
    }
    
    /**
     * Unload data stored in the Loader
     */
    public static function unload()
    {
        unset(self::$instance->objects);
        unset(self::$instance->aliases);
    }
    
    /**
     * Initialize the loader obect during creating an instance
     * @param Parser|null $current The current parsed XML file with dependency structure
     * @param Parser|null $default The default parsed XML file with dependency structure
     */
    private function __construct(Parser $current = null, Parser $default = null)
    {
        $this->initialize($current, $default);
    }
    
    /**
     * Initialize the loader object with the given data
     * @param Parser|null $current The current parsed XML file with dependency structure
     * @param Parser|null $default The default parsed XML file with dependency structure
     */
    private function initialize(Parser $current = null, Parser $default = null)
    {
        if ($current !== null) {
            $this->updateObjects($current->getObjects());
        }
        if ($default !== null) {
            $this->updateObjects($default->getObjects());
        }
    }
    
    /**
     * Get the object which is identified by the name and initialized with the given set of params
     * @param string $name A class name or an alias of an object which is returned
     * @param array $params Set of params which is used to an initialization
     * @return object|null
     */
    public function get($name, array $params = [])
    {
        $xmlObject = $this->findObject($name);
        $arguments = [];
        if (empty($params)) {
            foreach ($xmlObject->getParams() as $param) {
                $className = $param->getClassName();
                if (!empty($className)) {
                    $arguments[] = $this->get($param->getClassName());
                } else  {
                    $arguments[] = $param->getStoredValue();
                }
            }
        } else {
            $arguments = $params;
        }
        return $this->getObjectInstance($xmlObject, $arguments);
    }
    
    /**
     * Set the given object with the given name and alias.
     * Return the ObjectNode which represents the given object
     * @param string $className Class name of the given object
     * @param object $object The object which is set
     * @param string|null $alias Alias of the given object
     * @return ObjectNode
     */
    public function set($className, $object, $alias = null)
    {
        $newObject = new ObjectNode
        ($className, [], $alias);
        $newObject->setStoredInstance([], $object);
        $this->objects[$className] = $newObject;
        if ($alias !== null) {
            $this->aliases[$alias] = $newObject;
        }
        return $newObject;
    }
    
    /**
     * Return the object which represents an object which name and alias is given and which is initialized by using the given parameter list
     * @param string $className The class name of the object
     * @param array $params The arameter list
     * @param string|null $alias The alias of the object
     * @return ObjectNode
     */
    public function object($className, array $params = [], $alias = null)
    {
        $normalizedParams = [];
        foreach ($params as $key => $value) {
            $paramName = is_string($key)?$key:null;
            $objParam = new Param(null, $paramName, null);
            $objParam->setStoredValue($value);
            $normalizedParams[] = $objParam;
        }
        $newObject = new ObjectNode($className, $normalizedParams, $alias);
        $this->objects[$className] = $newObject;
        if ($alias !== null) {
            $this->aliases[$alias] = $newObject;
        }
        return $newObject;
    }
    
    /**
     * Set a value of the parameter which is identified by class name and parameter name.
     * Return the ObjectNode whose value has been set.
     * @param string $name A name of the parameter, which is composed of a class name and a parameter name, separated by ":" character
     * @param mixed $value A value of the parameter
     * @return ObjectNode
     * @throws ParamNotFoundException
     */
    public function parameter($name, $value)
    {
        if (!strpos($name, ':')) {
            throw new ParamNotFoundException($name);
        }
        list($className, $paramName) = explode(':', $name);
        $object = $this->findObject($className);
        return $object->setParamVal($paramName, $value);
    }

    /**
     * Return an object which class name or alias is given
     * @param string $name A class name or an alias of an object
     * @return ObjectNode
     * @throws EmptyObjectNameException Thrown when object name is empty
     * @throws ObjectNotFoundException Thrown when object with the given name is not found
     */
    private function findObject($name)
    {
        if (empty($name)) {
            throw new EmptyObjectNameException();
        }
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        } elseif (isset($this->objects[$name])) {
            return $this->objects[$name];
        } else {
            throw new ObjectNotFoundException($name);
        }
    }
    
    /**
     * Return an instance of object which is represented by the given ObjectNode, instantiated using the given param list
     * @param ObjectNode $object The ObjectNode instance which describes returned object
     * @param array $params The param list
     * @return object|null
     */
    private function getObjectInstance(ObjectNode $object, array $params = [])
    {
        $storedInstance = $object->getStoredInstance($params);
        if (empty($storedInstance) || $object->getAlwaysNew() == true) {
            $reflection = new ReflectionClass($object->getClass());
            $instance = $reflection->newInstanceArgs($params);
            $object->setStoredInstance($params, $instance);
            unset($reflection);
            return $instance;
        }
        return $storedInstance;
    }
    
    /**
     * Update objects which are stored in the loader
     * @param array $objects A set of new objects
     */
    private function updateObjects(array $objects)
    {
        foreach ($objects as $object) {
            //removing alias where object is overwritten
            try {
                $oldObject = $this->findObject($object->getClass());
                if ($this->findObject($oldObject->getAlias())) {
                    unset($this->aliases[$oldObject->getAlias()]);
                }
            } catch (DotpayException $ex) {
            }
            //removing object where alias is overwritten
            try {
                $oldObject = $this->findObject($object->getAlias());
                if ($this->findObject($oldObject->getClass())) {
                    unset($this->objects[$oldObject->getClass()]);
                }
            } catch (DotpayException $ex) {
            }
            $this->objects[$object->getClass()] = $object;
            if (!empty($object->getAlias())) {
                $this->aliases[$object->getAlias()] = $object;
            }
        }
    }
}
