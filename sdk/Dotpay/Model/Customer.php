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

use Dotpay\Validator\Street;
use Dotpay\Validator\BNumber;
use Dotpay\Validator\PostCode;
use Dotpay\Validator\Phone;
use Dotpay\Validator\City;
use Dotpay\Validator\Country;
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
        'cs',
        'hu',
        'ru'
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
	 * checks and crops the size of a string
	 * the $special parameter means an estimate of how many urlencode characters can be used in a given field
	 * e.q. 'ż' (1 char) -> '%C5%BC' (6 chars)
	 * replacing removing double or more special characters that appear side by side by space from: firstname, lastname, city, street, p_info...
	 */	
	public function encoded_substrParams($string, $from, $to, $special=0)
		{	
			$string2 = preg_replace('/(\s{2,}|\.{2,}|@{2,}|\-{2,}|\/{3,} | \'{2,}|\"{2,}|_{2,})/', ' ', $string);
			$s = html_entity_decode($string2, ENT_QUOTES, 'UTF-8');
			$sub = mb_substr($s, $from, $to,'UTF-8');
			$sum = strlen(urlencode($sub));
			
			if($sum  > $to)
				{    
					$newsize = $to - $special; 		
					$sub = mb_substr($s, $from, $newsize,'UTF-8');
				} 	
			return trim($sub);
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
	 * prepare data for the postcode so that it would be consistent with the validation
	 */
	public function NewPostcode($value)
		{
			$NewPostcode1 = preg_replace('/[^\d\w\s\-]/','',$value);
			return $this->encoded_substrParams($NewPostcode1,0,20,6);
		}  

    /**
     * Return a post code of the customer
     * @return string
     */
    public function getPostCode()
    {
        return $this->NewPostcode($this->postCode);
    }
    
   

	/**
	 * prepare data for the city so that it would be consistent with the validation
	 */
	public function NewCity($value)
		{
			$NewCity1 = preg_replace('/[^\p{L}0-9\.\s\-\/_,]/u',' ',$value);
			return $this->encoded_substrParams($NewCity1,0,50,24);
		}

    /**
     * Return a city of the customer
     * @return string
     */
    public function getCity()
    {
        return $this->NewCity($this->city);
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
	 * prepare data for the phone so that it would be consistent with the validation
	 */
	public function NewPhone($value)
		{
			$NewPhone1 = preg_replace('/[^\+\s0-9\-_]/','',$value);
			return $this->encoded_substrParams($NewPhone1,0,20,6);
		}
	
	/**
     * Return a phone number of the customer
     * @return string
     */
    public function getPhone()
    {
        return $this->NewPhone($this->phone);
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
        if (!Street::validate($this->NewStreet($street))) {
            throw new StreetException($street);
        }
        $this->street = (string)$this->NewStreet($street);
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
        if (!BNumber::validate($this->NewStreet_n1($buildingNumber))) {
            throw new BNumberException($buildingNumber);
        }
        $this->buildingNumber = ((string)$this->NewStreet_n1($buildingNumber) !== null) ? (string)$this->NewStreet_n1($buildingNumber) : ' ';
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
        if (!PostCode::validate($this->NewPostcode($postCode))) {
            throw new PostCodeException($postCode);
        }
        $this->postCode = (string)$this->NewPostcode($postCode);
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
        if (!City::validate($this->NewCity($city))) {
            throw new CityException($city);
        }
        $this->city = (string)$this->NewCity($city);
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
        if (!Country::validate($country)) {
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
        if (!Phone::validate($this->NewPhone($phone))) {
            throw new PhoneException($phone);
        }
        $this->phone = (string)trim($this->NewPhone($phone));
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
	 * prepare data for the street so that it would be consistent with the validation
	 */
	public function NewStreet($value)
		{	
			$NewStreet1 = preg_replace('/[^\p{L}0-9\.\s\-\/_,]/u',' ',$value);
			return $this->encoded_substrParams($NewStreet1,0,100,50);
		}
	
	/**
	 * prepare data for the street_n1 so that it would be consistent with the validation
	 */
	public function NewStreet_n1($value)
		{
			$NewStreet_n1a = preg_replace('/[^\p{L}0-9\s\-_\/]/u',' ',$value);
			return $this->encoded_substrParams($NewStreet_n1a,0,30,24);
		}
	
	/**
     * Try to extract a building number from the street name if it's an empty field
     */
    private function extractBnFromStreet()
    {	
		$Street1 = $this->NewStreet($this->street);
		$Street_n1 = $this->NewStreet_n1($this->buildingNumber);
		
        if (empty($Street_n1) && !empty($Street1)) {
			preg_match("/\s[\p{L}0-9\s\-_\/]{1,15}$/u", $Street1, $matches);
            if (count($matches)>0) {
                $this->setBuildingNumber(trim($matches[0]));
                $this->setStreet(str_replace($matches[0], '', $Street1));
            } else {
			    $this->setStreet(trim($Street1));
			}
        }
    }
}
