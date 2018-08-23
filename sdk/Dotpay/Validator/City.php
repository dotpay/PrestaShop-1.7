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
namespace Dotpay\Validator;

/**
 * The validator checks if the given city is correct
 */
class City implements IValidate {
    /**
     * Validate the given value if it's correct
     * @param mixed $value The given value
     * @return boolean
     */
    public static function validate($value1) {
		$value = trim($value1);
		return (bool) preg_match('/^[\p{L}0-9\.\s\-\/\'_,]{1,50}$/u', $value) || empty($value);
    }
}

