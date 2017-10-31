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
@ini_set('memory_limit', '256M');

/**
 * SDK class is not found
 */
class DotpayClassNotFoundException extends Exception {
    
}

/**
 * SDK class is not in found file
 */
class DotpayClassNotInFileException extends Exception {
    
}

/**
 * Load an SDK class
 * @param string $className Loader of SDK classes
 * @return boolean
 * @throws DotpayClassNotFoundException
 * @throws DotpayClassNotInFileException
 */
function dotpayApiLoader($className) {
    if(strpos($className, 'Dotpay\\') !== 0)
        return false;
    $path = __DIR__.'/'.str_replace('\\', '/', $className).'.php';
    if(!file_exists($path))
        throw new DotpayClassNotFoundException($className);
    include_once($path);
    if(!(class_exists($className) || interface_exists($className)))
        throw new DotpayClassNotInFileException($className.' in: '.$path);
}

spl_autoload_register('dotpayApiLoader');
