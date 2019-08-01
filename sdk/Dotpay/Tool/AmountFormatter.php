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
namespace Dotpay\Tool;

/**
 * Helper for formatting amount in currencies available in Dotpay
 */
class AmountFormatter
{
    /**
     * List of all supported currencies
     */
    public static $CURRENCY_PRECISION = [
        'EUR' => 2,
        'USD' => 2,
        'GBP' => 2,
        'JPY' => 0,
        'CZK' => 2,
        'SEK' => 2,
        'UAH' => 2,
        'RON' => 2,
        'PLN' => 2, 
        'NOK' => 2,
        'BGN' => 2,
        'CHF' => 2,
        'HRK' => 2,
        'HUF' => 2,
        'RUB' => 2
    ];
    
    /**
     * Default precision of formatter
     */
    const DEFAULT_PRECISION = 2;
    
    /**
     * Return a string with formated amount
     * @param float $amount Amount to format
     * @param string $currency Currency code
     * @param boolean $rounded Flag if amount should be rounded by round() function
     * @return string
     */
    public static function format($amount, $currency, $rounded = true)
    {
        if(isset(self::$CURRENCY_PRECISION[$currency])) {
            $precision = self::$CURRENCY_PRECISION[$currency];
        } else {
            $precision = self::DEFAULT_PRECISION;
        }
        
        if($amount === null) {
            $amount = 0.0;
        } else if($rounded) {
            $amount = round($amount, $precision);
        }
        
		$amount1 = number_format($amount, $precision);
        return self::fixAmountSeparator($amount1);

    }
	
	/**
     * Fix separators in the given amount
     * @param string $inputAmount Input amount
     * @param string $separator Separator which should be removed besides the last one
     * @return type
     */
    protected static function fixAmountSeparator($inputAmount, $separator = '.') {
        $amount = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $inputAmount));
        $part1 = str_replace($separator, '', substr($amount, 0, strrpos($amount, $separator)));
        $part2 = substr($amount, strrpos($amount, $separator));
        return $part1.$part2;
    }
}
