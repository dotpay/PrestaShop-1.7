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
namespace Dotpay\Model;

use Dotpay\Channel\Channel;
use Dotpay\Channel\Dotpay;
use Dotpay\Html\Container\Script;
use Dotpay\Html\PlainText;

use \ArrayAccess;
use \Countable;
use \Iterator;

/**
 * Model of payment channels list
 */
class ChannelList implements ArrayAccess, Countable, Iterator
{
    /**
     * @var int Pointer to the current channel
     */
    private $pointer;
    
    /**
     * @var array List of channels
     */
    private $channels = [];
    
    /**
     * Initialize the list
     */
    public function __construct()
    {
        $this->rewind();
    }
    
    /**
     * Check if the given offset points on a channel on the list
     * @param int $offset Position of a channel on a list
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->channels[$offset]);
    }
    
    /**
     * Return a channel which is pointed by the given offset
     * @param int $offset Position of a channel on a list
     * @return Channel
     */
    public function offsetGet($offset)
    {
        return $this->channels[$offset];
    }
    
    /**
     * Set the given channel on a place which is pointed by the given offset
     * @param int $offset Position of a channel on a list
     * @param Channel $value The given channel to set
     */
    public function offsetSet($offset, $value)
    {
        $this->channels[$offset] = $value;
    }
    
    /**
     * Remove a channel which is located in a place pointed by the given offset
     * @param int $offset Position of a channel on a list
     */
    public function offsetUnset($offset)
    {
        unset($this->channels[$offset]);
    }
    
    /**
     * Return a number of all channels which are on the list
     * @return int
     */
    public function count()
    {
        return count($this->channels);
    }
    
    /**
     * Return the current channel
     * @return Channel
     */
    public function current()
    {
        return $this->channels[$this->key()];
    }
    
    /**
     * Return a key of the current channel
     * @return int
     */
    public function key()
    {
        return $this->pointer;
    }
    
    /**
     * Set the pointer on a next channel on the list which is enabled
     */
    public function next()
    {
        do {
            ++$this->pointer;
        } while ($this->valid() && !$this->current()->isEnabled());
    }
    
    /**
     * Rewind the channel pointer to the first place with enabled channel
     */
    public function rewind()
    {
        $this->pointer = -1;
        $this->next();
    }
    
    /**
     * Check if the current pointer is valid
     * @return boolean
     */
    public function valid()
    {
        return isset($this->channels[$this->key()]);
    }
    
    /**
     * Add a new payment channel to the list
     * @param Channel $channel A payment channel object
     * @return ChannelList
     */
    public function addChannel(Channel $channel)
    {
        $this->channels[] = $channel;
        return $this;
    }
    
    /**
     * Return an array of ids of channels which are on the list
     * @return array
     */
    public function getChannelIds()
    {
        $ids = [];
        foreach ($this as $channel) {
            if (!empty($channel->getChannelId())) {
                $ids[] = $channel->getChannelId();
            }
        }
        return $ids;
    }
    
    /**
     * Return a Script object which contains a script with Dotpay widget configuration
     * @return Script
     */
    public function getWidgetScript()
    {
        foreach ($this as $channel) {
            if ($channel instanceof Dotpay) {
                return $channel->getScript($this->getChannelIds());
            }
        }
        return new Script(new PlainText());
    }
}
