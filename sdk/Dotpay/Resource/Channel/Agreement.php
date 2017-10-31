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
namespace Dotpay\Resource\Channel;

/**
 * Represent a structure of single agreement data
 */
class Agreement
{
    /**
     * @var array Data of the agreement
     */
    private $data = [];
    
    /**
     * Initialize the agreement structure
     * @param array $data Data of the agreement
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Return a type of the agreement
     * @return string
     */
    public function getType()
    {
        return (string)$this->get('type');
    }
    
    /**
     * Return a name of the agreement
     * @return string
     */
    public function getName()
    {
        return (string)$this->get('name');
    }
    
    /**
     * Return a label of the agreement
     * @return string
     */
    public function getLabel()
    {
        return (string)$this->get('label');
    }
    
    /**
     * Check if the agreement must be checked on a checkout page
     * @return boolean
     */
    public function getRequired()
    {
        return (bool)$this->get('required');
    }
    
    /**
     * Check if the agreement is cheecked by default
     * @return boolean
     */
    public function getDefault()
    {
        return (bool)$this->get('default');
    }
    
    /**
     * Return a descriotion text of the agreement
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->get('description_text');
    }
    
    /**
     * Return a descriotion text decorated by HTML of the agreement
     * @return string
     */
    public function getDescriptionHtml()
    {
        return (string)$this->get('description_html');
    }
    
    /**
     * Return a value which is saved under the given key
     * @param string $name Key of a value
     * @return mixed
     */
    protected function get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }
}
