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
 * Represent a structure of informations about one payment channel
 */
class OneChannel
{
    /**
     * @var array Informations about the one payment channel
     */
    private $data = [];
    
    /**
     * Initialize the object with the given data
     * @param array $data Informations about the one payment channel
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Return channel identifier
     * @return int
     */
    public function getId()
    {
        return (int)$this->get('id');
    }
    
    /**
     * Return a name of the channel
     * @return string
     */
    public function getName()
    {
        return (string)$this->get('name');
    }
    
    /**
     * Return the url where is located an image with a logo of the channel
     * @return string
     */
    public function getLogo()
    {
        return (string)$this->get('logo');
    }
    
    /**
     * Return a group of the channel
     * @return string
     */
    public function getGroup()
    {
        return (string)$this->get('group');
    }
    
    /**
     * Return a name of a channel's group
     * @return string
     */
    public function getGroupName()
    {
        return (string)$this->get('group_name');
    }
    
    /**
     * Return a short name of the channel
     * @return string
     */
    public function getShortName()
    {
        return (string)$this->get('short_name');
    }
    
    /**
     * Check if the channel is disabled
     * @return boolean
     */
    public function isDisabled()
    {
        return ($this->get('is_disable') !== "False");
    }
    
    /**
     * Check if the channel is not online
     * @return boolean
     */
    public function isNotOnline()
    {
        return ($this->get('is_not_online') !== "False");
    }
    
    /**
     * Return an array with list of fields which are needed on a payment form
     * @return array
     */
    public function getFormNames()
    {
        return $this->get('form_names');
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
