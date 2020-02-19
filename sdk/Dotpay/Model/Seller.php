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

use Dotpay\Validator\Id;
use Dotpay\Validator\Pin;
use Dotpay\Validator\Email;
use Dotpay\Validator\Username;
use Dotpay\Exception\BadParameter\IdException;
use Dotpay\Exception\BadParameter\PinException;
use Dotpay\Exception\BadParameter\UsernameException;
use Dotpay\Exception\BadParameter\EmailException;

/**
 * Informations about a seller
 */
class Seller
{
    /**
     * @var int|null Seller id
     */
    private $id = null;
    
    /**
     * @var string Seller pin
     */
    private $pin = '';
    
    /**
     * @var string Username of Dotpay seller dashboard
     */
    private $username = '';
    
    /**
     * @var string Password of Dotpay seller dashboard
     */
    private $password = '';
    
    /**
     * @var string Info about a shop name
     */
    private $info = '';
    
    /**
     * @var string Email of the seller
     */
    private $email = '';
    
    /**
     * Initialize the model
     * @param int $id Seller id
     * @param string $pin Seller pin
     */
    public function __construct($id, $pin)
    {
        $this->setId($id);
        $this->setPin($pin);
    }
    
    /**
     * Return a seller id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Return a seller pin
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }
    
    /**
     * Return a username of Dotpay seller dashboard
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Return a password of Dotpay seller dashboard
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Return an info about a shop name
     * @return string
     */
    public function getInfo()
    {
        return Notification::NewShopName($this->info);
    }
    
    /**
     * Return an email of the seller
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Check if an username and a password are given
     * @return boolean
     */
    public function hasAccessToApi()
    {
        return (!empty($this->username) && !empty($this->password));
    }
    
    /**
     * Set a seller id
     * @param int $id Seller id
     * @return Seller
     * @throws IdException Thrown when the given seller id is incorrect
     */
    public function setId($id)
    {
        if (!Id::validate($id)) {
            throw new IdException($id);
        }
        $this->id = (int)$id;
        return $this;
    }
    
    /**
     * Set a seller pin
     * @param string $pin Seller pin
     * @return Seller
     * @throws PinException Thrown when the given seller pin is incorrect
     */
    public function setPin($pin)
    {
        if (!Pin::validate($pin)) {
            throw new PinException($pin);
        }
        $this->pin = (string)trim($pin);
        return $this;
    }
    
    /**
     * Set a username of Dotpay seller dashboard
     * @param string $username Username of Dotpay seller dashboard
     * @return Seller
     * @throws UsernameException Thrown when the given seller username is incorrect
     */
    public function setUsername($username)
    {
        if (!Username::validate($username)) {
            throw new UsernameException($username);
        }
        $this->username = (string)trim($username);
        return $this;
    }
    
    /**
     * Set a password of Dotpay seller dashboard
     * @param string $password Password of Dotpay seller dashboard
     * @return Seller
     */
    public function setPassword($password)
    {
        $this->password = (string)trim($password);
        return $this;
    }
    
    /**
     * Set an info about a shop name
     * @param string $info Info about a shop name
     * @return Seller
     */
    public function setInfo($info)
    {
        $this->info = (string)$info;
        return $this;
    }
    
    /**
     * Set an email of the seller
     * @param string $email Email of the seller
     * @return Seller
     * @throws EmailException Thrown when the given seller email address is incorrect
     */
    public function setEmail($email)
    {
        if (!Email::validate($email)) {
            throw new EmailException($email);
        }
        $this->email = (string)trim($email);
        return $this;
    }
}
