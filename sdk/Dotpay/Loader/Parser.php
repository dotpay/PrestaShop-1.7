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

use Dotpay\Loader\Xml\ObjectNode;
use Dotpay\Loader\Xml\Param;
use Dotpay\Exception\Loader\XmlNotFoundException;
use \SimpleXMLElement;

/**
 * Parser which parse and store XML files with a dependency structure
 */
class Parser
{
    /**
     * @var SimpleXMLElement An object which represents XML preparsed file
     */
    private $xml;
    
    /**
     * @var array An array of ObjectNode elements which represent parsed object nodes
     */
    private $objects = [];
    
    /**
     * Initialize the parser object
     * @param string $fileName Name of an XML file with dependency structure
     * @throws XmlNotFoundException Thrown when XML file is not found
     */
    public function __construct($fileName)
    {
        if (!file_exists($fileName)) {
            throw new XmlNotFoundException($fileName);
        }
        $this->xml = new SimpleXMLElement(file_get_contents($fileName));
    }
    
    /**
     * Return an array with ObjectNode elements created after parsing the XML file
     * @return array
     */
    public function getObjects()
    {
        if (empty($this->objects)) {
            $this->parse();
        }
        return $this->objects;
    }
    
    /**
     * Parse the XML file and build a list of ObjectNode elements
     */
    private function parse()
    {
        foreach ($this->xml->object as $xmlObject) {
            $params = [];
            foreach ($xmlObject->param as $xmlParam) {
                $params[] = new Param($xmlParam['class'], $xmlParam['name'], $xmlParam['value']);
            }
            $this->objects[(string)$xmlObject['class']] = new ObjectNode($xmlObject['class'], $params, $xmlObject['alias'], $xmlObject['alwaysNew']);
        }
    }
}
