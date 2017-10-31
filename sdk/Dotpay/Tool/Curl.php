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
namespace Dotpay\Tool;

use Dotpay\Exception\ExtensionNotFoundException;

/**
 * Tool for a support of cURL library
 */
class Curl
{
    /**
     * @var resource A cURL resource object
     */
    private $curl;
    
    /**
     * @var mixed informations returned by cURL after execution a command
     */
    private $info;
    
    /**
     * @var boolean A flag which inform if the curl object is active
     */
    private $active = false;
    
    /**
     * Initialize the tool
     * @throws ExtensionNotFoundException Thrown when the cURL library is not installed as a PHP extension
     */
    public function __construct()
    {
        if ($this->checkExtension() == false) {
            throw new ExtensionNotFoundException('curl');
        }
        $this->curl = curl_init();
        if ($this->curl !== null) {
            $this->active = true;
        }
    }
    
    /**
     * Uninitialize the tool
     */
    public function __destruct()
    {
        if ($this->active) {
            $this->close();
            $this->active = false;
        }
    }
    
    /**
     * Add a new cURL option to the configuration of the current cURL instance
     * @param int $option A cURL option constant
     * @param mixed $value A value which is set
     * @return Curl
     */
    public function addOption($option, $value)
    {
        curl_setopt($this->curl, $option, $value);
        return $this;
    }
    
    /**
     * Perform a cURL session and returns a result
     * @return mixed
     */
    public function exec()
    {
        $response = curl_exec($this->curl);
        $this->info = curl_getinfo($this->curl);
        return $response;
    }
    
    /**
     * Return a string containing the last error for the current session
     * @return string
     */
    public function error()
    {
        return curl_error($this->curl);
    }
    
    /**
     * Return informations about the last operation
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }
    
    /**
     * Close the cURL session
     * @return Curl
     */
    public function close()
    {
        curl_close($this->curl);
        $this->curl = null;
        $this->active = false;
        return $this;
    }
    
    /**
     * Reset the cURL instance
     * @return Curl
     */
    public function reset()
    {
        curl_close($this->curl);
        $this->curl = curl_init();
        return $this;
    }
    
    /**
     * Check if the cURL extension for PHP is installed
     * @codeCoverageIgnore
     * @return boolean
     */
    protected function checkExtension()
    {
        return extension_loaded('curl');
    }
}
