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
namespace Dotpay\Resource\Channel;

use Dotpay\Exception\Resource\Channel\NotFoundException;

/**
 * Represent a structure of information about payment channels which is downloaded from Dotpay
 */
class Info
{
    /**
     * @var array Data of channels
     */
    private $channels = [];

    /**
     * @var array Data of form's fields
     */
    private $forms = [];

    /**
     * Initialize informations about payment channels
     * @param array $channels Data of channels
     * @param array $forms Data of form's fields
     */
    public function __construct(array $channels, array $forms)
    {
        $this->channels = $channels;
        $this->forms = $forms;
    }

    /**
     * Return saved original data of channels
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * Return saved original data of forms
     * @return array
     */
    public function getForms()
    {
        return $this->forms;
    }


    /**
     * Return an array of channels wrapped in OneChannel classes
     * @param array $excludedChannels List of channel ids which should be excluded from the channel array
     * @return array
     */
    public function getChannelList($excludedChannels = [])
    {
        $channelList = [];
        foreach ($this->getChannels() as $channel) {
            if (isset($channel['id']) && in_array($channel['id'], $excludedChannels) === false) {
                $channelList[] = new OneChannel($channel);
            }
        }
        return $channelList;
    }

    /**
     * Return a structure of payment channel informations for the channel which has the given id
     * @param int $channelId Channel id
     * @return OneChannel
     * @throws NotFoundException Thrown when doesn't exist a payment channel with the given id
     */
    public function getChannelInfo($channelId)
    {
        foreach ($this->getChannels() as $channel) {
            if (isset($channel['id']) && $channel['id'] == $channelId) {
                return new OneChannel($channel);
            }
        }
        throw new NotFoundException($channelId);
    }

    /**
     * Return an array of Agreements object with agreements which are dedicated for the payment channel which has the given id
     * @param int $channelId Channel id
     * @return array
     */
    public function getAgreements($channelId)
    {
        $channelInfo = $this->getChannelInfo($channelId);
        $fields = $channelInfo->getFormNames();
        $agreements = [];
        if (array_search('agreement', $fields) !== false) {
            foreach ($this->getForms() as $form) {
                if (isset($form['form_name']) && $form['form_name'] == 'agreement' && isset($form['fields'])) {
                    foreach ($form['fields'] as $field) {
                        if ($field['required']) {
                            $agreements[] = new Agreement($field);
                        }
                    }
                }
            }
        }
        return $agreements;
    }
}
