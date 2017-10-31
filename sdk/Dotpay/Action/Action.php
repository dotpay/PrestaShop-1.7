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

/**
 * Action provides a functionality of some actions, executed in concrete times thanks to special interface.
 */
abstract class Action
{
    /**
     * @var callable A callable value to executed
     */
    protected $userFunc;

    /**
     * @var mixed If a callable value contains a function which needs only one argument, it can be passed from this value
     */
    protected $oneArgument;

    /**
     * Initialize an action object
     * @param callable $userFunc Initial callable value, which can be executed in a coorect time
     */
    public function __construct(callable $userFunc = null)
    {
        if ($userFunc !== null) {
            $this->setUserFunc($userFunc);
        }
    }

    /**
     * Return a callable value which is set.
     * @return callable
     */
    public function getUserFunc()
    {
        return $this->userFunc;
    }

    /**
     * Return one argument, which is set.
     * @return mixed
     */
    public function getOneArgument()
    {
        return $this->oneArgument;
    }

    /**
     * Set a callable value which will be executed.
     * @param callable $userFunc Callable value
     * @return Action
     */
    public function setUserFunc(callable $userFunc)
    {
        $this->userFunc = $userFunc;

        return $this;
    }

    /**
     * Set an one argument which will be passed to the function during its execution.
     * @param mixed $oneArgument Argument to passing to the function during execution
     * @return Action
     */
    public function setOneArgument($oneArgument)
    {
        $this->oneArgument = $oneArgument;

        return $this;
    }

    /**
     * Execute a callback and returns a result of it. It returns null if callback wasn't set.
     * @return mixed
     */
    public function execute()
    {
        $func = $this->userFunc;
        if (!is_callable($func)) {
            return null;
        }
        if ($this->oneArgument !== null) {
            return $func($this->getOneArgument());
        } else {
            return $func();
        }
    }
}
