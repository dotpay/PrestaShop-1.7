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
 * @copyright Dotpay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Dotpay\Resource\Github;

use \DateTime;
use Dotpay\Validator\Url;
use Dotpay\Exception\BadParameter\UrlException;

/**
 * Represent informations about the version of a software on Github server
 */
class Version
{
    /**
     * @var string Number of the version
     */
    private $number;
    
    /**
     * @var string Url where are available informations about the version
     */
    private $url;
    
    /**
     *
     * @var string Url address of a place where from it's possible do download a zip file with a software
     */
    private $zip;
    
    /**
     * @var DateTime Date and time when the version has been created
     */
    private $created;
    
    /**
     * @var DateTime Date and time when the version has been published
     */
    private $published;
    
    /**
     * Set basic informations about the version
     * @param string $number Number of the version
     * @param string $zip Url address of a place where from it's possible do download a zip file with a software
     */
    public function __construct($number, $zip)
    {
        $this->setNumber($number);
        $this->setZip($zip);
    }
    
    /**
     * Return a number of the version
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * Return an url where are available informations about the version
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Return an url address of a place where from it's possible do download a zip file with a software
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }
    
    /**
     * Return a date and time when the version has been created
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
     * Return a date and time when the version has been published
     * @return DateTime
     */
    public function getPublished()
    {
        return $this->published;
    }
    
    /**
     * Set a number of the version
     * @param string $number Number of the version
     * @return Version
     */
    public function setNumber($number)
    {
        $this->number = str_replace('v', '', $number);
        return $this;
    }
    
    /**
     * Set an url where are available informations about the version
     * @param string $url Url where are available informations about the version
     * @return Version
     * @throws UrlException
     */
    public function setUrl($url)
    {
        if (!Url::validate($url)) {
            throw new UrlException($url);
        }
        $this->url = $url;
        return $this;
    }
    
    /**
     * Set an url address of a place where from it's possible do download a zip file with a software
     * @param string $zip Url address of a place where from it's possible do download a zip file with a software
     * @return Version
     * @throws UrlException
     */
    public function setZip($zip)
    {
        if (!Url::validate($zip)) {
            throw new UrlException($zip);
        }
        $this->zip = $zip;
        return $this;
    }
    
    /**
     * Set a date and time when the version has been created
     * @param DateTime $created Date and time when the version has been created
     * @return Version
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
        return $this;
    }
    
    /**
     * Set a date and time when the version has been published
     * @param DateTime $published Date and time when the version has been published
     * @return Version
     */
    public function setPublished(DateTime $published)
    {
        $this->published = $published;
        return $this;
    }
}
