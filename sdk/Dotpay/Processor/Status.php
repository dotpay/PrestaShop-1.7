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
namespace Dotpay\Processor;

use Dotpay\Exception\FunctionNotFoundException;

/**
 * Processor of checking status of an order
 */
class Status {
    /**
     * @var int Order doesn't exist
     */
    private static $NOT_EXISTS = -1;
    
    /**
     * @var int An error with payment has been occured
     */
    private static $ERROR = 0;
    
    /**
     * @var int Shop is still waiting for confirmation of payment
     */
    private static $PENDING = 1;
    
    /**
     * @var int Order has been paid successfully
     */
    private static $SUCCESS = 2;
    
    /**
     * @var int Order has been paid before
     */
    private static $TOO_MANY = 3;
    
    /**
     * @var int Status of the order is different than ERROR or PENDING
     */
    private static $OTHER_STATUS = 4;
    
    /**
     * @var int Status code
     */
    private $code = -1000;
    
    /**
     * @var string Order status from a shop
     */
    private $status = "";
    
    /**
     * @var string An additional message which can be displayed on a shop site
     */
    private $message = NULL;
    
    /**
     * Return a status code
     * @return string
     */
    public function getCode() {
        return $this->code;
    }
    
    /**
     * Set a status code as order wasn't found
     * @return Status
     */
    public function codeNotExist() {
        $this->code = self::$NOT_EXISTS;
        return $this;
    }
    
    /**
     * Set a status code as an error
     * @return Status
     */
    public function codeError() {
        $this->code = self::$ERROR;
        return $this;
    }
    
    /**
     * Set a status code as a pending
     * @return Status
     */
    public function codePending() {
        $this->code = self::$PENDING;
        return $this;
    }
    
    /**
     * Set a status code as a success
     * @return Status
     */
    public function codeSuccess() {
        $this->code = self::$SUCCESS;
        return $this;
    }
    
    /**
     * Set a status code as too many payments
     * @return Status
     */
    public function codeTooMany() {
        $this->code = self::$TOO_MANY;
        return $this;
    }
    
    /**
     * Set a status code as other status
     * @return Status
     */
    public function codeOtherStatus() {
        $this->code = self::$OTHER_STATUS;
        return $this;
    }
    
    /**
     * Return an order status description
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Set the given order status description
     * @param string $status Order status description
     * @return Status
     */
    public function setStatus($status) {
        $this->status = (string)$status;
        return $this;
    }
    
    /**
     * Return an additional message
     * @return string|null
     */
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * Set an additional message
     * @param string $message An additional message
     * @return Status
     */
    public function setMessage($message) {
        $this->message = (string)$message;
        return $this;
    }
    
    /**
     * Return a string which contains JSON data with the current status information
     * @return string
     * @throws FunctionNotFoundException Thrown when function json_encode() isn't found.
     */
    public function getJson() {
        $data = [
            'code' => $this->getCode(),
            'status' => $this->getStatus()
        ];
        if($this->getMessage() !== NULL) {
            $data['message'] = $this->getMessage();
        }
        if (function_exists('json_encode')) {
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            throw new FunctionNotFoundException('json_encode');
        }
    }
}