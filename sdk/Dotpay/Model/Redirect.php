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
namespace Dotpay\Model;

use Dotpay\Validator\Url;
use Dotpay\Exception\BadParameter\UrlException;
use Dotpay\Exception\BadParameter\MethodException;

/**
 * Informations about a redirect
 */
class Redirect
{
    /**
     * List of allowed HTTP methods
     */
    const ALLOWED_METHODS = [
        'get', 'post', 'put', 'delete'
    ];
    
    /**
     * @var string Target of the redirect
     */
    private $url = '';
    
    /**
     * @var array Data to send during the redirect.
     * Keys of the array are names of values.
     */
    private $data = [];
    
    /**
     * @var string Name of used HTTP method
     */
    private $method = 'post';
    
    /**
     * @var string Encoding type
     */
    private $encoding = 'utf-8';
    
    /**
     * Initialize the model
     * @param string $url Target of the redirect
     * @param array $data Data to send during the redirect
     * @param string $method Name of used HTTP method
     * @param string $encoding Encoding type
     */
    public function __construct($url, array $data, $method = 'post', $encoding = 'utf-8')
    {
        $this->setUrl($url);
        $this->setData($data);
        $this->setMethod($method);
        $this->setEncoding($encoding);
    }
    
    /**
     * Return a target of the redirect
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Return a data to send during the redirect
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Return a name of used HTTP method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Return an encoding type
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
    
    /**
     * Set a target of the redirect
     * @param string $url Target of the redirect
     * @return Redirect
     * @throws UrlException Thrown when the given url is incorrect
     */
    public function setUrl($url)
    {
        if (!Url::validate($url)) {
            throw new UrlException($url);
        }
        $this->url = (string)trim($url);
        return $this;
    }
    
    /**
     * Set a data to send during the redirect
     * @param array $data Data to send during the redirect
     * @return Redirect
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Set a name of used HTTP method
     * @param string $method Name of used HTTP method
     * @return Redirect
     * @throws MethodException
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        if (array_search($method, self::ALLOWED_METHODS) === false) {
            throw new MethodException($method);
        }
        $this->method = (string)trim($method);
        return $this;
    }
    
    /**
     * Set an encoding type
     * @param string $encoding Encoding type
     * @return Redirect
     */
    public function setEncoding($encoding)
    {
        $this->encoding = (string)$encoding;
        return $this;
    }
}
