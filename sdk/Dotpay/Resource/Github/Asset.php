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

use Dotpay\Validator\Url;
use Dotpay\Exception\BadParameter\UrlException;

/**
 * Represent an asset which is added to Github version
 */
class Asset
{
    /**
     * @var string Name of the asset
     */
    private $name;
    
    /**
     * @var string Url to the Github page where asseet is available
     */
    private $url;
    
    /**
     * @var string MIME type of the asset
     */
    private $mime;
    
    /**
     * @var string Uploader login
     */
    private $uploader = null;
    
    /**
     * @var int Size of the file
     */
    private $size = 0;
    
    /**
     * @var string Github url where the asset can be downloaded
     */
    private $downloadUrl = '';
    
    /**
     * Initialize the object
     * @param string $name Name of the asset
     * @param string $url Url to the Github page where asseet is available
     * @param string $mime MIME type of the asset
     */
    public function __construct($name, $url, $mime)
    {
        $this->setName($name);
        $this->setUrl($url);
        $this->setMime($mime);
    }
    
    /**
     * Return a name of the asset
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Return an url to the Github page where asseet is available
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }
    
    /**
     * Return a MIME type of the asset
     * @return string
     */
    public function getMime() {
        return $this->mime;
    }
    
    /**
     * Return an uploader login
     * @return string
     */
    public function getUploader() {
        return $this->uploader;
    }
    
    /**
     * Return a size of the file
     * @return int
     */
    public function getSize() {
        return $this->size;
    }
    
    /**
     * Return a Github url where the asset can be downloaded
     * @return string
     */
    public function getDownloadUrl() {
        return $this->downloadUrl;
    }
    
    /**
     * Set a name of the asset
     * @param string $name Name of the asset
     * @return Asset
     */
    public function setName($name) {
        $this->name = (string)$name;
        return $this;
    }
    
    /**
     * Set an url to the Github page where asseet is available
     * @param string $url Url to the Github page where asseet is available
     * @return Asset
     */
    public function setUrl($url) {
        if (!Url::validate($url)) {
            throw new UrlException($url);
        }
        $this->url = (string)$url;
        return $this;
    }
    
    /**
     * Set a MIME type of the asset
     * @param string $mime MIME type of the asset
     * @return Asset
     */
    public function setMime($mime) {
        $this->mime = (string)$mime;
        return $this;
    }
    
    /**
     * Set an uploader login
     * @param string $uploader Uploader login
     * @return Asset
     */
    public function setUploader($uploader) {
        $this->uploader = (string)$uploader;
        return $this;
    }
    
    /**
     * Set a size of the file
     * @param int $size Size of the file
     * @return Asset
     */
    public function setSize($size) {
        $this->size = (int)$size;
        return $this;
    }
    
    /**
     * Set a Github url where the asset can be downloaded
     * @param string $downloadUrl Github url where the asset can be downloaded
     * @return Asset
     */
    public function setDownloadUrl($downloadUrl) {
        if (!Url::validate($downloadUrl)) {
            throw new UrlException($downloadUrl);
        }
        $this->downloadUrl = (string)$downloadUrl;
        return $this;
    }
}