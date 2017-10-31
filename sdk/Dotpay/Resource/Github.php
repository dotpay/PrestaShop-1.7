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
namespace Dotpay\Resource;

use Dotpay\Resource\Github\Version;
use Dotpay\Resource\Github\Asset;
use Dotpay\Exception\Resource\Github\VersionNotFoundException;
use Dotpay\Exception\BadReturn\TypeNotCompatibleException;
use Dotpay\Exception\Resource\NotFoundException;
use \DateTime;

/**
 * Allow to use Github Api for checking a version of this SDK and other projects
 */
class Github extends Resource
{
    /**
     * Basic url of the Github API
     */
    const githubUrl = 'https://api.github.com/';
    
    /**
     * Return the Version object with informations about the newest version of this SDK
     * @return Version
     */
    public function getSdkVersion()
    {
        return $this->getLatestProjectVersion('dotpay', 'phpSDK');
    }
    
    /**
     * Check if the current version is the same or newer than the version on Github
     * @return boolean
     */
    public function isSdkNewest()
    {
        $config = $this->config;
        $githubVersion = $this->getSdkVersion()->getNumber();
        $installedVersion = $config::SDK_VERSION;
        return version_compare($githubVersion, $installedVersion, '<=');
    }
    
    /**
     * Return details of version of the project which is developed on the given Github user account
     * @param string $username Username of Github user
     * @param string $project Name of a project
     * @return Version
     * @throws VersionNotFoundException Thrown when any latest version of the project is not found
     * @throws TypeNotCompatibleException Thrown when a response from Github server is in incompatible type
     */
    public function getLatestProjectVersion($username, $project)
    {
        try {
            $content = $this->getContent(self::githubUrl.'repos/'.$username.'/'.$project.'/releases/latest');
        } catch (NotFoundException $ex) {
            throw new VersionNotFoundException($project);
        }
        if (!is_array($content)) {
            throw new TypeNotCompatibleException(gettype($content));
        }
        $version = new Version($content['tag_name']);
        $version->setUrl($content['url'])
                ->setCreated(new DateTime($content['created_at']))
                ->setPublished(new DateTime($content['published_at']));
        foreach($content['assets'] as $rawAsset) {
            $asset = new Asset($rawAsset['name'], $rawAsset['url'], $rawAsset['content_type']);
            $asset->setUploader($rawAsset['uploader']['login'])
                  ->setSize($rawAsset['size'])
                  ->setDownloadUrl($rawAsset['browser_download_url']);
            $version->addAsset($asset);
        }
        return $version;
    }
    
    /**
     * Return a string which contain a header with Accept rule
     * @return string
     */
    protected function getAcceptHeader()
    {
        return 'Accept: application/vnd.github.v3+json';
    }
}
