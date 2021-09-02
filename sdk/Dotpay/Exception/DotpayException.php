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
 * @copyright PayPro S.A.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Dotpay\Exception;

/**
 * AN error with Dotpay SDK occured
 */
class DotpayException extends \RuntimeException
{
    /**
     * Message of error thrown by the exception.
     */
    const MESSAGE = 'An error with Dotpay translations has been occured. Details: %1';

    /**
     * Initialize locale exception.
     *
     * @param string     $message  Details of exception
     * @param int        $code     Code of exception
     * @param \Exception $previous The previous exception used for the exception chaining
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct(str_replace('%1', $message, static::MESSAGE), $code, $previous);
    }

}
