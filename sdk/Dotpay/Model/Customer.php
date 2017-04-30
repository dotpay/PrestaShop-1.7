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
namespace Dotpay\Model;

use Dotpay\Validator\Name;
use Dotpay\Validator\Street;
use Dotpay\Validator\BNumber;
use Dotpay\Validator\PostCode;
use Dotpay\Validator\Phone;
use Dotpay\Exception\BadParameter\LanguageException;
use Dotpay\Exception\BadParameter\StreetException;
use Dotpay\Exception\BadParameter\BNumberException;
use Dotpay\Exception\BadParameter\PostCodeException;
use Dotpay\Exception\BadParameter\CityException;
use Dotpay\Exception\BadParameter\CountryException;
use Dotpay\Exception\BadParameter\PhoneException;

/**
 * Informations about a bank acount of payer
 */
class Customer extends Payer
{
    /**
     * All available languages which are supported by Dotpay
     */
    public static $LANGUAGES = array(
        'pl',
        'en',
        'de',
        'it',
        'fr',
        'es',
        'cz',
        'ru',
        'bg'
    );
    
    /**
     * @var int|null Id of the customer in a shop
     */
    private $id = null;
    
    /**
     * @var string Street name of the customer
     */
    private $street = '';
    
    /**
     * @var string Building number of the customer
     */
    private $buildingNumber = '';
    
    /**
     * @var string Post code of the customer
     */
    private $postCode = '';
    
    /**
     * @var string City of the customer
     */
    private $city = '';
    
    /**
     * @var string Country of the customer
     */
    private $country = '';
    
    /**
     * @var string Phone number of the customer
     */
    private $phone = '';
    
    /**
     * @var string Language used by the customer
     */
    private $language = '';
    
    /**
     * Return an id of the customer in a shop
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Return a street name of the customer
     * @return string
     */
    public function getStreet()
    {
        $this->extractBnFromStreet();
        return $this->street;
    }
    
    /**
     * Return a building number of the customer
     * @return string
     */
    public function getBuildingNumber()
    {
        $this->extractBnFromStreet();
        return $this->buildingNumber;
    }
    
    /**
     * Return a post code of the customer
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }
    
    /**
     * Return a city of the customer
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Return a country of the customer
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * Return a phone number of the customer
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * Return a language used by the customer
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * Set an id of the customer in a shop
     * @param string $id Id of the customer in a shop
     * @return Customer
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Set a street name of the customer
     * @param string $street Street name of the customer
     * @return Customer
     * @throws StreetException Thrown when the given street is incorrect
     */
    public function setStreet($street)
    {
        if (!Street::validate($street)) {
            throw new StreetException($street);
        }
        $this->street = (string)$street;
        return $this;
    }
    
    /**
     * Set a building number of the customer
     * @param string $buildingNumber Building number of the customer
     * @return Customer
     * @throws BNumberException Thrown when the given building number is incorrect
     */
    public function setBuildingNumber($buildingNumber)
    {
        if (!BNumber::validate($buildingNumber)) {
            throw new BNumberException($buildingNumber);
        }
        $this->buildingNumber = (string)$buildingNumber;
        return $this;
    }
    
    /**
     * Set a post code of the customer
     * @param string $postCode Post code of the customer
     * @return Customer
     * @throws PostCodeException Thrown when the given post code is incorrect
     */
    public function setPostCode($postCode)
    {
        if (!PostCode::validate($postCode)) {
            throw new PostCodeException($postCode);
        }
        $this->postCode = (string)$postCode;
        return $this;
    }
    
    /**
     * Set a city of the customer
     * @param string $city City of the customer
     * @return Customer
     * @throws CityException Thrown when the given city is incorrect
     */
    public function setCity($city)
    {
        if (!Name::validate($city)) {
            throw new CityException($city);
        }
        $this->city = (string)$city;
        return $this;
    }
    
    /**
     * Set a country of the customer
     * @param string $country Country of the customer
     * @return Customer
     * @throws CountryException Thrown when the given country is incorrect
     */
    public function setCountry($country)
    {
        if (!Name::validate($country)) {
            throw new CountryException($country);
        }
        $this->country = (string)$country;
        return $this;
    }
    
    /**
     * Set a phone number of the customer
     * @param string $phone Phone number of the customer
     * @return Customer
     * @throws PhoneException Thrown when the given phone number is incorrect
     */
    public function setPhone($phone)
    {
        if (!Phone::validate($phone)) {
            throw new PhoneException($phone);
        }
        $this->phone = (string)trim($phone);
        return $this;
    }
    
    /**
     * Set a language used by the customer
     * @param string $language Language used by the customer
     * @return Customer
     * @throws LanguageException Thrown when the given language is incorrect
     */
    public function setLanguage($language)
    {
        if (!in_array($language, self::$LANGUAGES)) {
            throw new LanguageException($language);
        }
        $this->language = (string)trim($language);
        return $this;
    }
    
    /**
     * Try to extract a building number from the street name if it's an empty field
     */
    private function extractBnFromStreet()
    {
        if (empty($this->buildingNumber) && !empty($this->street)) {
            preg_match("/\s[\w\d\/_\-]{0,30}$/", $this->street, $matches);
            if (count($matches)>0) {
                $this->setBuildingNumber(trim($matches[0]));
                $this->setStreet(str_replace($matches[0], '', $this->street));
            }
        }
    }
}
