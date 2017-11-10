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

use Dotpay\Loader\Loader;
use Dotpay\Model\Configuration;
use Dotpay\Tool\Curl;
use Dotpay\Model\Account;
use Dotpay\Model\Operation;
use Dotpay\Model\Payer;
use Dotpay\Model\PaymentMethod;
use Dotpay\Model\Payout;
use Dotpay\Model\Seller as SellerModel;
use Dotpay\Model\Refund;
use Dotpay\Validator\OpNumber;
use Dotpay\Exception\FunctionNotFoundException;
use Dotpay\Exception\BadParameter\OperationNumberException;
use Dotpay\Exception\Resource\UnauthorizedException;
use Dotpay\Exception\Resource\ApiException;
use Dotpay\Exception\BadReturn\TypeNotCompatibleException;
use Dotpay\Exception\Resource\NotFoundException as ResourceNotFoundException;
use Dotpay\Exception\Resource\Account\NotFoundException as AccountNotFoundException;
use Dotpay\Exception\Resource\Operation\NotFoundException as OperationNotFoundException;

/**
 * Allow to use Seller API
 */
class Seller extends Resource
{
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
        $this->curl->addOption(CURLOPT_USERPWD, $this->config->getUsername().':'.$this->config->getPassword());
        $this->loader = Loader::load();
    }
    
    /**
     * Check if the given username and password are correct
     * @return boolean
     */
    public function isAccountRight()
    {
        if (!$this->config->isGoodApiData()) {
            return false;
        }
        try {
            $this->getDataFromApi('payments/');
        } catch (UnauthorizedException $e) {
            return false;
        }
        return true;
    }
    
    /**
     * Check if the given normal seller pin is correct
     * @return boolean
     */
    public function checkPin()
    {
        return $this->checkIdAndPin($this->config->getId(), $this->config->getPin());
    }
    
    /**
     * Check if the given seller pin for foreign currencies is correct
     * @return boolean
     */
    public function checkFccPin()
    {
        return $this->checkIdAndPin($this->config->getFccId(), $this->config->getFccPin());
    }
    
    /**
     * Return an account of seller who has the given seller id
     * @param int $id Seller id
     * @return Account
     * @throws AccountNotFoundException Thrown when seller account is not found
     */
    public function getAccount($id)
    {
        try {
            $response = $this->getDataFromApi('accounts/'.$id.'/?format=json');
        } catch (ResourceNotFoundException $ex) {
            throw new AccountNotFoundException($id);
        }
        $account = new Account($response['id']);
        $account->setStatus($response['status'])
                ->setName($response['name'])
                ->setMcc($response['mcc_code'])
                ->setUrlc($response['config']['urlc'])
                ->setBlockExternalUrlc($response['config']['block_external_urlc'])
                ->setPin($response['config']['pin']);
        return $account;
    }
    
    /**
     * Return an Operation which number is given
     * @param string $number Number of a operation
     * @return Operation
     * @throws OperationNumberException Thrown when the given operation number is incorrect
     * @throws OperationNotFoundException Thrown when searched operation is not found
     */
    public function getOperationByNumber($number)
    {
        if (!OpNumber::validate($number)) {
            throw new OperationNumberException($number);
        }
        try {
            $response = $this->getDataFromApi('operations/'.$number.'/?format=json');
            return $this->getWrapedOperation($response);
        } catch (ResourceNotFoundException $ex) {
            throw new OperationNotFoundException($number);
        }
    }
    
    /**
     * Return an Operation which identifier is given
     * @param mixed $id Operation identifier
     * @return Operation
     * @throws OperationNotFoundException Thrown when searched operation is not found
     */
    public function getOperationById($id)
    {
        try {
            foreach ($this->getPaginateDataFromApi('operations/?control='.$id.'&format=json') as $operation) {
                if ($operation['control'] === $id) {
                    return $this->getWrapedOperation($operation);
                }
            }
        } catch (ResourceNotFoundException $ex) {
            throw new OperationNotFoundException($id);
        }
        throw new OperationNotFoundException($id);
    }
    
    /**
     * Realize a payout for the given seller using the given payout data
     * @param SellerModel $seller Seller data
     * @param Payout $payout Payout data
     * @return boolean
     * @throws AccountNotFoundException Thrown when seller account is not found
     */
    public function makePayout(SellerModel $seller, Payout $payout)
    {
        $data = $this->getDataForPayout($payout);
        try {
            $this->postData($this->getApiUrl('accounts/'.$seller->getId().'/payout/?format=json'), json_encode($data, 320));
            return true;
        } catch (ResourceNotFoundException $ex) {
            throw new AccountNotFoundException($seller->getId());
        }
    }
    
    /**
     * Realize a refund for the given model
     * @param Refund $refund Refund data
     * @return boolean
     * @throws OperationNotFoundException Thrown when payment for refund is not found
     */
    public function makeRefund(Refund $refund)
    {
        try {
            $refundDetails = [
                'amount' => $refund->getAmount(),
                'description' => $refund->getDescription(),
                'control' => $refund->getControl()
            ];
            if (function_exists('json_encode')) {
				$data2send = json_encode($refundDetails, 320);		
            } else {
                throw new FunctionNotFoundException('json_encode');
            }
            $this->postData($this->getApiUrl('payments/'.$refund->getPayment().'/refund/'), json_encode($data2send, 320));
            return true;
        } catch (ResourceNotFoundException $ex) {
            throw new OperationNotFoundException($refund->getPayment());
        }
    }
    
    /**
     * Return a data which can be used to creating a payout through Dotpay server
     * @param Payout $payout Payout data
     * @return array
     */
    private function getDataForPayout(Payout $payout)
    {
        $data = [
            'currency' => $payout->getCurrency(),
            'transfers' => []
        ];
        foreach ($payout->getTransfers() as $transfer) {
            $data['transfers'][] = [
                'amount' => $transfer->getAmount(),
                'control' => $transfer->getControl(),
                'description' => $transfer->getDescription(),
                'recipient' => [
                    'name' => $transfer->getRecipient()->getName(),
                    'account_number' => $transfer->getRecipient()->getNumber()
                ],
            ];
        }
        return $data;
    }
    
    /**
     * Return an Operation object which wraps the given input data
     * @param array $input Input data which should be wraped
     * @return Operation
     */
    private function getWrapedOperation(array $input)
    {
        $operation = $this->loader->get('Operation', [$input['type'], $input['number']]);
        $operation->setUrl($input['href'])
                      ->setDateTime(new \DateTime($input['creation_datetime']))
                      ->setStatus($input['status'])
                      ->setAmount($input['amount'])
                      ->setCurrency($input['currency'])
                      ->setOriginalAmount($input['original_amount'])
                      ->setOriginalCurrency($input['original_currency'])
                      ->setAccountId($input['account_id'])
                      ->setDescription($input['description'])
                      ->setControl($input['control'])
                      ->setPayer(new Payer($input['payer']['email'], $input['payer']['first_name'], $input['payer']['last_name']));
        if ($input['related_operation'] != null) {
            $operation->setRelatedOperation($input['related_operation']);
        }
        if (isset($input['payment_method'])) {
            if (isset($input['payment_method']['payer_bank_account'])) {
                $bank = $input['payment_method']['payer_bank_account'];
                $details = $this->loader->get('BankAccount', [$bank['name'], $bank['number']]);
                $type = PaymentMethod::BANK_ACCOUNT;
            } elseif (isset($input['payment_method']['credit_card'])) {
                $cc = $input['payment_method']['credit_card'];
                $ccFromDb = $this->loader->get('CreditCard');
                $details = $ccFromDb::getCreditCardByOrder($operation->getControl());
                if (!empty($details)) {
                    $details->setBrand($this->loader->get('CardBrand', [$cc['brand']['name'], $cc['brand']['logo'], $cc['brand']['codename']]))
                                ->setCardId($cc['id'])
                                ->setMask($cc['masked_number'])
                                ->setHref($cc['href']);
                    $type = PaymentMethod::CREDIT_CARD;
                }
            }
            if (isset($type)) {
                $operation->setPaymentMethod($this->loader->get('PaymentMethod', [$input['payment_method']['channel_id'], $details, $type]));
            }
        }
        return $operation;
    }
    
    /**
     * Check if the given seller id and pin are correct
     * @param int $id Seller id
     * @param string $pin Seller pin
     * @return boolean
     */
    private function checkIdAndPin($id, $pin)
    {
        try {
            $account = $this->getAccount($id);
        } catch (UnauthorizedException $e) {
            return false;
        }
        if ($account->getId() === (int)$id && $account->getPin() === $pin) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Return results when the target resource uses pagination mechanism
     * @param string $url Url address of the selected target
     * @return array
     * @throws TypeNotCompatibleException Thrown when a response from Dotpay server is in incompatible type
     * @throws ApiException Thrown when is reported an Api Error
     */
    private function getPaginateDataFromApi($url)
    {
        $this->curl->reset();
        $this->curl->addOption(CURLOPT_USERPWD, $this->config->getUsername().':'.$this->config->getPassword());
        $content = $this->getContent($this->getApiUrl($url), false);
        if (!is_array($content)) {
            throw new TypeNotCompatibleException(gettype($content));
        }
        $info = $this->curl->getInfo();
        if (isset($info['http_code']) && $info['http_code'] == 400) {
            reset($content);
            throw new ApiException($content[key($content)]);
        }
        if ($content['next'] === null) {
            return $content['results'];
        } else {
            return array_merge($content['results'], $this->getPaginateDataFromApi($content['next']));
        }
    }
    
    /**
     * Return a parsed response from the Seller Api
     * @param string $targetUrl Url of the target resource
     * @return array
     * @throws TypeNotCompatibleException Thrown when a response from Dotpay server is in incompatible type
     * @throws ApiException Thrown when is reported an Api Error
     */
    private function getDataFromApi($targetUrl)
    {
        if (!$this->config->isGoodApiData()) {
            throw new UnauthorizedException($this->getApiUrl($targetUrl));
        };
        $this->curl->addOption(CURLOPT_USERPWD, $this->config->getUsername().':'.$this->config->getPassword());
        $content = $this->getContent($this->getApiUrl($targetUrl));
        if (!is_array($content)) {
            throw new TypeNotCompatibleException(gettype($content));
        }
        $info = $this->curl->getInfo();
        if (isset($info['http_code']) && $info['http_code'] == 400) {
            reset($content);
            throw new ApiException($content[key($content)]);
        }
        unset($info);
        return $content;
    }
    
    /**
     * Return Api url for the given end point on the Dotpay server
     * @param string $end End point of the Api
     * @return string
     */
    private function getApiUrl($end)
    {
        return $this->config->getSellerUrl().'api/'.$end;
    }
}
