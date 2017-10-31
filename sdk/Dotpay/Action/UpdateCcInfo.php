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
namespace Dotpay\Action;

use Dotpay\Model\CreditCard;

/**
 * Action which is executed during updating informations about credit card
 */
class UpdateCcInfo extends Action {
    /**
     * Return a credit card which is set as an argument for a callback.
     * @return CreditCard
     */
    public function getCreditCard() {
        return $this->getOneArgument();
    }
    
    /**
     * Set a credit card which can be passed to callback function.
     * @param CreditCard $cc Credit card object
     * @return UpdateCcInfo
     */
    public function setCreditCard(CreditCard $cc) {
        return $this->setOneArgument($cc);
    }
}