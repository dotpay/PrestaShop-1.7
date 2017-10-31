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

/**
 * Informations about a card brand
 */
class CardBrand
{
    /**
     * @var int|null Id of the brand in the database
     */
    private $id = null;
    
    /**
     * @var string Name of the brand of a credit card
     */
    private $name;
    
    /**
     * @var string|null Code name of the brand of a credit card
     */
    private $codeName = null;
    
    /**
     * @var string Url to the logo of the credit card brand
     */
    private $image;
    
    /**
     * Initialize the model
     * @param string $name Name of the brand of a credit card
     * @param string $image Url to the logo of the credit card brand
     * @param string|null $codeName Code name of the brand of a credit card
     */
    public function __construct($name, $image, $codeName = null)
    {
        $this->setName($name);
        $this->setImage($image);
        $this->setCodeName($codeName);
    }
    
    /**
     * Return a name of the brand of a credit card
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Return a code name of the brand of a credit card
     * @return string|null
     */
    public function getCodeName()
    {
        return $this->codeName;
    }
    
    /**
     * Return a url to the logo of the credit card brand
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
    
    /**
     * Set a code name of the brand of a credit card
     * @param string|null $name Name of the brand of a credit card
     * @return CardBrand
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Set a code name of the brand of a credit card
     * @param string|null $codeName Code name of the brand of a credit card
     * @return CardBrand
     */
    public function setCodeName($codeName)
    {
        $this->codeName = $codeName;
        return $this;
    }
    
    /**
     * Set a url to the logo of the credit card brand
     * @param string|null $image Url to the logo of the credit card brand
     * @return CardBrand
     * @throws UrlException Thrown when the given url address is incorrect
     */
    public function setImage($image)
    {
        if (!Url::validate($image)) {
            throw new UrlException($image);
        }
        $this->image = (string)trim($image);
        return $this;
    }
}
