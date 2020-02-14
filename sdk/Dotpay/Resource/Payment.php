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
namespace Dotpay\Resource;

use Dotpay\Model\Payment as ModelPayment;
use Dotpay\Resource\Channel\Info;
use Dotpay\Model\Configuration;
use Dotpay\Resource\Channel\OneChannel;
use Dotpay\Exception\Resource\ApiException;
use Dotpay\Exception\BadReturn\TypeNotCompatibleException;
use Dotpay\Exception\Resource\Account\NotFoundException;

/**
 * Allow to use informations about channels which are enabled for details of payment
 */
class Payment extends Resource
{
    /**
     * @var array List of channels lists which are gotten from server for specific orders
     */
    private $buffer;

    /**
     * Return the Info structure which contains list of channels for the given payment details
     * @param ModelPayment $payment Payment details
     * @return Info
     * @throws TypeNotCompatibleException Thrown when a response from Dotpay server is in incompatible type
     * @throws ApiException Thrown when is reported an Api Error
     */
    public function getChannelInfo(ModelPayment $payment)
    {
        $id = $payment->getIdentifier();
        if (!isset($this->buffer[$id])) {
            $content = $this->getContent($this->getUrl($payment));
            if (!is_array($content)) {
                throw new TypeNotCompatibleException(gettype($content));
            }
            if (isset($content['error_code'])) {
                $exception = new ApiException($content['detail']);
                throw $exception->setApiCode($content['error_code']);
            }
            $this->buffer[$id] = new Info($content['channels'], $content['forms']);
            unset($content);
        }
        return $this->buffer[$id];
    }

    /**
     * Clear the buffer of past requests
     * @return Payment
     */
    public function clearBuffer()
    {
        unset($this->buffer);
        $this->buffer = [];
        return $this;
    }



    
    /**
     * Check if the seller with the given id exists in Dotpay system
     * @param int $id Seller id, $more_view ['error_code' - present error code,'receiver' - present name of the Dotpay account]
     * @return boolean, string, array
     * @throws TypeNotCompatibleException Thrown when a response from Dotpay server is in incompatible type
     */
    public function checkSeller($id,$more_view)
    {

        $url = $this->config->getPaymentUrl().
               'payment_api/channels/'.
               '?id='.$id.
               '&amount=301'.
               '&currency=PLN'.
               '&format=json';

        $content = $this->getContent($url);
        if (!is_array($content))
        {
            throw new TypeNotCompatibleException(gettype($content));

        } else {


            if($more_view == 'check')
            {
                if (isset($content['error_code'])) 
               {
                   unset($content);
                   return false;
               }else {
                unset($content);
                return true;
               }

            }

            else if($more_view == 'error_code') 
            {   
                if(isset($content['error_code'])) 
                {
                    return (array('error_code' => $content['error_code'],'detail' => $content['detail']));
                    unset($content);
                }else {
                    unset($content);
                    return false;
                }
   
           } 
           
           else if($more_view == 'receiver')
           {   
               if(isset($content['payment_details']['receiver'])) 
               {
                   return $content['payment_details']['receiver'];
                   unset($content);
               }else {
                   unset($content);
                   return false;
               }

            
          } else {

                   unset($content);
                   return false;
   
           }


        }


    }


    /**
     * Return an url to Dotpay API for the given payment details
     * @param ModelPayment $payment Payment details
     * @return string
     */
    private function getUrl(ModelPayment $payment)
    {
        $lang = $payment->getCustomer()->getLanguage();
        if (!$lang) {
            $lang = 'en';
        }
        if ($payment->getSeller()) {
            $id = $payment->getSeller()->getId();
        } else {
            throw new NotFoundException();
        }
        return $this->config->getPaymentUrl().'payment_api/channels/'.
               '?id='.$id.
               '&amount='.$payment->getOrder()->getAmount().
               '&currency='.$payment->getOrder()->getCurrency().
               '&lang='.$lang.
               '&format=json';
    }


    /**
     * Checks whether a given channel is available for a specific order (amount and currency)
     * @return bool
     */
    public function checkChannel(ModelPayment $payment, $chid)
    {
        $ischannel = false;  
        $content = $this->getContent($this->getUrl($payment));      
          foreach($content['channels'] as $kan){
            if($kan['id'] == $chid && $kan['is_disable'] == 'False') { 
                $ischannel =  true;
                break; 
            }
        }
        unset($content);
        return $ischannel;
    }




}
