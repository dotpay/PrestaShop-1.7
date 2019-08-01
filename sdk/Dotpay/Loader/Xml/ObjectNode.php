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
namespace Dotpay\Loader\Xml;

use Dotpay\Exception\Loader\EmptyObjectNameException;

/**
 * ObjectNode node in XML file with Dependency Injection rules.
 * It represents created object.
 */
class ObjectNode
{
    /**
     * @var string Class name of the object
     */
    private $className;

    /**
     * @var array Array of Param object which are used during creation the object
     */
    private $parameters = [];

    /**
     * @var array Array of named prameters which contain Param objects
     */
    private $namedParameters = [];

    /**
     * @var string|null A short name of the object. It's an alias on the main class name.
     */
    private $alias;

    /**
     * @var boolean A flag if the instance should be always new
     */
    private $alwaysNew = false;

    /**
     * @var array Array of stored instances of the object for different sets of params used for an initialization
     */
    private $storedInstance = [];

    /**
     * Initialize the object
     * @param string $className Class name of the object
     * @param array $parameters Array of Param object which are used during creation the object
     * @param string|null $alias A short name of the object
     * @param boolean A flag if the instance should be always new
     * @throws EmptyObjectNameException Thrown when class name is empty
     */
    public function __construct($className, array $parameters = [], $alias = null, $alwaysNew = false)
    {
        if (empty($className)) {
            throw new EmptyObjectNameException();
        }
        $this->className = (string)$className;
        foreach ($parameters as $param) {
            if ($param instanceof Param) {
                $this->parameters[] = $param;
                if (!empty($param->getName())) {
                    $this->namedParameters[$param->getName()] = $param;
                }
            }
        }
        $this->alias = (string)$alias;
        $this->alwaysNew = (bool)$alwaysNew;
    }

    /**
     * Return a class name of the object
     * @return string
     */
    public function getClass()
    {
        return $this->className;
    }

    /**
     * Return an array of all Param objects for the object
     * @return array
     */
    public function getParams()
    {
        return $this->parameters;
    }

    /**
     * Return a value of a parameter which has the given name, if the parameter is in a set of named parameters
     * @param string $name Name of the parameter
     * @return mixed
     */
    public function getParamVal($name)
    {
        foreach ($this->namedParameters as $key => $value) {
            if ($name === $key) {
                return $value->getStoredValue();
            }
        }
        return null;
    }

    /**
     * Set a value to the parameter which has the given name, if the parameter is in a set of named parameters
     * @param string $name A name of the parameter
     * @param mixed $value A value to set
     * @return ObjectNode
     */
    public function setParamVal($name, $value)
    {
        foreach ($this->namedParameters as $key => $oldValue) {
            if ($name === $key) {
                $this->namedParameters[$name]->setStoredValue($value);
                break;
            }
        }
        return $this;
    }

    /**
     * Return an alias of the object
     * @return string|null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return a flag if the instance should be always new
     * @return boolean
     */
    public function getAlwaysNew()
    {
        return $this->alwaysNew;
    }

    /**
     * Return an object which was created with the given set of params and which is set as an one of instances inside the ObjectNode
     * @param array $params An array of params
     * @return object|null
     */
    public function getStoredInstance(array $params)
    {
        $paramId = sha1($this->getParamsId($params));
        return isset($this->storedInstance[$paramId])?$this->storedInstance[$paramId]:null;
    }

    /**
     * Set the instance of an object which was created with using the given set of params
     * @param array $params Params which were used to create the instance
     * @param object $instance An instance of a object which is the instance of the class represents by the ObjectNode
     * @return ObjectNode
     */
    public function setStoredInstance($params, $instance)
    {
        $paramId = sha1($this->getParamsId($params));
        $this->storedInstance[$paramId] = $instance;
        return $this;
    }

    /**
     * Return a string which contains XML representation of the ObjectNode
     * @return string
     */
    public function getXml()
    {
        $element = '<object';
        if (!empty($this->getClass())) {
            $element .= ' class=\''.$this->getClass().'\'';
        }
        if (!empty($this->getAlias())) {
            $element .= ' alias=\''.$this->getAlias().'\'';
        }
        $element .= '>';
        foreach ($this->getParams() as $param) {
            $element .= $param->getXml();
        }
        $element .= '</object>';
        return $element;
    }

    /**
     * Return an identificator of the given data. It's a substitute of full serialization.
     * @param mixed $input Input data
     * @return string
     */
    private function getParamsId($input) {
	switch(gettype($input)) {
		case 'object':
			return get_class($input);
		case 'array':
			$serialString = '';
			foreach ($input as $key => $value) {
				$serialString .= $key.$this->getParamsId($value);
                        }
			return $serialString;
		case 'resource':
			return get_resource_type($input);
		case 'unknown type':
			return 'unknown';
		case 'NULL':
			return 'null';
		default:
			return $input;
	}
    }
}
