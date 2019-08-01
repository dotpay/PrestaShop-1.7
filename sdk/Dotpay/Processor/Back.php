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

use Dotpay\Exception\Payment\BadDataFormatException;
use Dotpay\Exception\Payment\BlockedAccountException;
use Dotpay\Exception\Payment\DisabledChannelException;
use Dotpay\Exception\Payment\HashNotEqualException;
use Dotpay\Exception\Payment\HighAmountException;
use Dotpay\Exception\Payment\UrlcInvalidException;
use Dotpay\Exception\Payment\InactiveAccountException;
use Dotpay\Exception\Payment\LowAmountException;
use Dotpay\Exception\Payment\ExpiredException;
use Dotpay\Exception\Payment\UnknowChannelException;
use Dotpay\Exception\Payment\UnrecognizedException;

/**
 * Processor of service of back after making a payment
 */
class Back {
    private $errorCode = null;
    public function __construct($errorCode) {
        if (!empty($errorCode)) {
            $this->errorCode = strtoupper($errorCode);
        }
    }
    
    /**
     * Execute the processor for making all activities
     * @return boolean
     * @throws ExpiredException Thrown when payment has been expired
     * @throws UnknowChannelException Thrown when the given channel is unknown
     * @throws DisabledChannelException Thrown when selected channel payment is disabled
     * @throws BlockedAccountException Thrown when seller account is disabled
     * @throws InactiveAccountException Thrown when seller account is inactive
     * @throws LowAmountException Thrown when amount is too low
     * @throws HighAmountException Thrown when amount is too high
     * @throws BadDataFormatException Thrown when format of request data is bad
     * @throws HashNotEqualException Thrown when request has been modified during transmission
     * @throws UrlcInvalidException Thrown when SSL ptotocol is required
     * @throws UnrecognizedException Thrown when unrecognized error occured
     */
    public function execute() {
        if ($this->errorCode === null) {
            return true;
        }
        switch ($this->errorCode) {
            case 'PAYMENT_EXPIRED':
                throw new ExpiredException();
            case 'UNKNOWN_CHANNEL':
                throw new UnknowChannelException();
            case 'DISABLED_CHANNEL':
                throw new DisabledChannelException();
            case 'BLOCKED_ACCOUNT':
                throw new BlockedAccountException();
            case 'INACTIVE_SELLER':
                throw new InactiveAccountException();
            case 'AMOUNT_TOO_LOW':
                throw new LowAmountException();
            case 'AMOUNT_TOO_HIGH':
                throw new HighAmountException();
            case 'BAD_DATA_FORMAT':
                throw new BadDataFormatException();
            case 'HASH_NOT_EQUAL_CHK':
                throw new HashNotEqualException();
            case 'URLC_INVALID':
                throw new UrlcInvalidException();     
            default:
                throw new UnrecognizedException();
        }
    }
}
