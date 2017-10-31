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

use \DateTime;
use Dotpay\Loader\Loader;
use Dotpay\Channel\Channel;
use Dotpay\Model\Configuration;
use Dotpay\Model\Redirect;
use Dotpay\Model\Payer;
use Dotpay\Model\PaymentMethod;
use Dotpay\Tool\Curl;
use Dotpay\Resource\RegisterOrder\Result;
use Dotpay\Exception\FunctionNotFoundException;
use Dotpay\Exception\Resource\PaymentNotCreatedException;
use Dotpay\Exception\Resource\InstructionNotFoundException;

/**
 * Provide an interface to use Register Method to create payments
 */
class RegisterOrder extends Resource
{
    /**
     * Subaddress of the Retister API location
     */
    const TARGET = "payment_api/v1/register_order/";
    
    /**
     * @var Loader Instance of SDK Loader
     */
    private $loader;
    
    /**
     * Initialize the resource
     * @param Configuration $config Configuration of Dotpay payments
     * @param Curl $curl Tool for using the cURL library
     */
    public function __construct(Configuration $config, Curl $curl)
    {
        parent::__construct($config, $curl);
        $this->loader = Loader::load();
    }
    
    /**
     * Create a new payment using Register order method
     * @param Channel $channel Data of channel which should be used to realizing the operation
     * @return Result
     * @throws PaymentNotCreatedException Thrown when payment is not created
     */
    public function create(Channel $channel)
    {
         if (function_exists('json_encode')) {
            $data2send = json_encode($this->getDataStructure($channel), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            throw new FunctionNotFoundException('json_encode');
        }
        $resultArray = $this->postData($this->config->getPaymentUrl() . self::TARGET, $data2send);
        $info = $this->curl->getInfo();
        if ((int) $info['http_code'] !== 201) {
            throw new PaymentNotCreatedException();
        }
        $config = $this->config;
        $operation = $this->loader->get('Operation', [$resultArray['operation']['type'], $resultArray['operation']['number']]);
        $operation->setUrl($resultArray['operation']['href'])
                ->setDateTime(new DateTime($resultArray['operation']['creation_datetime']))
                ->setStatus($resultArray['operation']['status'])
                ->setAmount($resultArray['operation']['amount'])
                ->setCurrency($resultArray['operation']['currency'])
                ->setOriginalAmount($resultArray['operation']['original_amount'])
                ->setOriginalCurrency($resultArray['operation']['original_currency'])
                ->setAccountId($resultArray['operation']['account_id'])
                ->setDescription($resultArray['operation']['description'])
                ->setPayer(new Payer(
                    $resultArray['operation']['payer']['email'], $resultArray['operation']['payer']['first_name'], $resultArray['operation']['payer']['last_name']
                ))
                ->setPaymentMethod(new PaymentMethod(
                    $resultArray['operation']['payment_method']['channel_id']
                )
        );

        $result = new Result(
                $resultArray['info']['status_url'], $operation
        );
        if (isset($resultArray['redirect'])) {
            $result->setRedirect(
                    new Redirect(
                    $resultArray['redirect']['url'], $resultArray['redirect']['data'], $resultArray['redirect']['method'], $resultArray['redirect']['encoding']
                    )
            );
        }
        switch ($channel->getGroup()) {
            case $channel::CASH_GROUP:
            case $channel::TRANSFER_GROUP:
                return $this->processCashAndTransfer($resultArray, $result, $channel);
            default:
                return $result;
        }
    }
    
    /**
     * Process cash and transfer payments
     * @param array $resultArray Informations which are given from Dotpay server after realizing the payment
     * @param Result $resultObject Structure which contains selected result's informations
     * @param Channel $channel Data of channel which should be used to realizing the operation
     * @return Result
     * @throws InstructionNotFoundException Thrown when an instruction of finishing payment is not found for cash and transfer payments
     */
    private function processCashAndTransfer(array $resultArray, Result $resultObject, Channel $channel)
    {
        if (isset($resultArray['instruction'])) {
            $isCash = ($channel->getGroup() == $channel::CASH_GROUP);
            $instruction = $this->loader->get('Instruction');
            $instruction->setOrderId($channel->getTransaction()->getPayment()->getOrder()->getId())
                    ->setNumber($resultArray['operation']['number'])
                    ->setChannel($resultArray['operation']['payment_method']['channel_id'])
                    ->setIsCash($isCash)
                    ->setHash($this->getHashFromResultArray($resultArray));
            if (isset($resultArray['instruction']) && isset($resultArray['instruction']['recipient']) && isset($resultArray['instruction']['recipient']['bank_account_number'])) {
                $instruction->setBankAccount($resultArray['instruction']['recipient']['bank_account_number']);
            }
            $resultObject->setInstruction($instruction);
        } else {
            throw new InstructionNotFoundException($resultArray['operation']['number']);
        }
        return $resultObject;
    }
    
    /**
     * Return a hash of payment based on payment's results
     * @param array $payment Details of payment
     * @return string
     */
    private function getHashFromResultArray(array $payment)
    {
        $parts = explode('/', $payment['instruction']['instruction_url']);
        return (string)$parts[count($parts) - 2];
    }
    
    /**
     * Return a data structure for Register Order method
     * @param Channel $channel Data of channel which should be used to realizing the operation
     * @return array
     */
    private function getDataStructure(Channel $channel)
    {
        $result = [
            'order' => [
                'amount' => $channel->getTransaction()->getPayment()->getOrder()->getAmount(),
                'currency' => $channel->getTransaction()->getPayment()->getOrder()->getCurrency(),
                'description' => $channel->getTransaction()->getPayment()->getDescription(),
                'control' => $channel->getTransaction()->getPayment()->getOrder()->getId()
            ],
            'seller' => [
                'account_id' => $channel->getTransaction()->getPayment()->getSeller()->getId(),
                'url' => $channel->getTransaction()->getBackUrl(),
                'urlc' => $channel->getTransaction()->getConfirmUrl(),
            ],
            'payer' => [
                'first_name' => $channel->getTransaction()->getPayment()->getCustomer()->getFirstName(),
                'last_name' => $channel->getTransaction()->getPayment()->getCustomer()->getLastName(),
                'email' => $channel->getTransaction()->getPayment()->getCustomer()->getEmail(),
                
            ],
            'payment_method' => [
                'channel_id' => $channel->getChannelId()
            ],
            'request_context' => [
                'ip' => $_SERVER['REMOTE_ADDR']
            ]
        ];

			
			if (!empty($channel->getTransaction()->getPayment()->getCustomer()->getBuildingNumber()))
				{
					$building_numberRO = $channel->getTransaction()->getPayment()->getCustomer()->getBuildingNumber();
				}else{
					$building_numberRO = " "; //this field may not be blank in register order.
				}
		
		
if (!empty($channel->getTransaction()->getPayment()->getCustomer()->getStreet()) && !empty($channel->getTransaction()->getPayment()->getCustomer()->getPostCode()) && !empty($channel->getTransaction()->getPayment()->getCustomer()->getCity()) && !empty($channel->getTransaction()->getPayment()->getCustomer()->getCountry())){
	$result['payer']['address'] = [
		'street' => $channel->getTransaction()->getPayment()->getCustomer()->getStreet(),
		'building_number' => $building_numberRO,
		'postcode' => $channel->getTransaction()->getPayment()->getCustomer()->getPostCode(),
		'city' => $channel->getTransaction()->getPayment()->getCustomer()->getCity(),
		'country' => $channel->getTransaction()->getPayment()->getCustomer()->getCountry(),
	];
}
return $result;
	
    }
}
