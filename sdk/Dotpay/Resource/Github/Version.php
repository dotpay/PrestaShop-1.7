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
namespace Dotpay\Resource\Github;

use \DateTime;
use Dotpay\Validator\Url;
use Dotpay\Exception\Resource\NotFoundException;
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
     * @var DateTime Date and time when the version has been created
     */
    private $created;
    
    /**
     * @var DateTime Date and time when the version has been published
     */
    private $published;
    
    /**
     * @var array List of assets associated with the version
     */
    private $assets = [];
    
    /**
     * Set basic informations about the version
     * @param string $number Number of the version
     */
    public function __construct($number)
    {
        $this->setNumber($number);
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
     * Return a list of assets associated with the version
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }
    
    /**
     * Return an asset which matches to the crteria
     * @param string $name Name of parameter
     * @param string $value Value of parameter
     * @return Asset
     * @throws NotFoundException Thrown when none asset matches the given criteria
     */
    public function findAsset($name, $value)
    {
        $fnName = 'get'.ucfirst($name);
        foreach($this->assets as $asset) {
            if ($asset->$fnName() == $value) {
                return $asset;
            }
        }
        throw new NotFoundException($name.' => '.$value);
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
    
    /**
     * Add an asset to the version
     * @param Asset $asset Asset object
     */
    public function addAsset(Asset $asset)
    {
        $this->assets[] = $asset;
    }
}
