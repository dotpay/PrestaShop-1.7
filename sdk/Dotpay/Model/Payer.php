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

use Dotpay\Validator\Name;
use Dotpay\Validator\Email;
use Dotpay\Exception\BadParameter\FirstnameException;
use Dotpay\Exception\BadParameter\LastnameException;
use Dotpay\Exception\BadParameter\EmailException;

/**
 * Informations about a payer
 */
class Payer
{
    /**
     * @var string Email address of the payer
     */
    private $email;

    /**
     * @var string First name of the payer
     */
    private $firstName = '';

    /**
     * @var string Last name of the payer
     */
    private $lastName = '';

    /**
     * Initialize the model
     * @param string $email Email address of the payer
     * @param string $firstName First name of the payer
     * @param string $lastName Last name of the payer
     */
    public function __construct($email, $firstName = '', $lastName = '')
    {
        if (!empty($email))
            $this->setEmail($email);
        if (!empty($firstName))
            $this->setFirstName($firstName);
        if(!empty($lastName))
            $this->setLastName($lastName);
    }

    /**
     * Return an email address of the payer
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


		/**
	 * prepare data for the firstname and lastname so that it would be consistent with the validation
	 */
	public function NewPersonName($value)
		{
			$NewPersonName1 = preg_replace('/[^\p{L}0-9\s\-_]/u',' ',$value);
			return $this->encoded_substrParams($NewPersonName1,0,50,24);
		}


    /**
     * Return a first name of the payer
     * @return string
     */
    public function getFirstName()
    {
		return $this->NewPersonName($this->firstName);
    }

    /**
     * Return a last name of the payer
     * @return string
     */
    public function getLastName()
    {
		return $this->NewPersonName($this->lastName);
    }

    /**
     * Set the email address of the payer
     * @param type $email Email address of the payer
     * @return Payer
     * @throws EmailException Thrown when the given email address is incorrect
     */
    public function setEmail($email)
    {
        if (!Email::validate($email)) {
            throw new EmailException("Incorect email: ".$email);
        }
        $this->email = (string)trim($email);
        return $this;
    }

    /**
     * Set the first name of the payer
     * @param string $firstName First name of the payer
     * @return Payer
     * @throws FirstnameException Thrown when the given first name is incorrect
     */
    public function setFirstName($firstName)
    {
        if (!Name::validate($firstName)) {
            throw new FirstnameException("Incorect first name: ".$firstName);
        }
        $this->firstName = (string)$firstName;
        return $this;
    }

    /**
     * Set the last name of the payer
     * @param type $lastName Last name of the payer
     * @return Payer
     * @throws LastnameException Thrown when the given last name is incorrect
     */
    public function setLastName($lastName)
    {
        if (!Name::validate($lastName)) {
            throw new LastnameException("Incorect last name: ".$lastName);
        }
        $this->lastName = (string)$lastName;
        return $this;
    }
}
