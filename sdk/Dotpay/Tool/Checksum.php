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
 * @copyright Dotpay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Dotpay\Tool;

use \DirectoryIterator;
use Dotpay\Exception\FileNotFoundException;

class Checksum {
    public static function getForDir($dir) {
        $dirIter = new DirectoryIterator($dir);
        $concat = '';
        foreach ($dirIter as $fileinfo) {
            $filename = $fileinfo->getFilename();
            if (!$fileinfo->isDot() && substr($filename, 0, 1) != '.') {
                if ($fileinfo->getType() == 'dir') {
                    $concat .= self::getForDir($fileinfo->getPathname());
                } else {
                    $concat .= self::getForFile($fileinfo->getPathname());
                }
            }
        }
        return sha1($concat);
    }
    
    public static function getForFile($filename) {
        if (file_exists($filename)) {
            return sha1_file($filename);
        } else {
            throw new FileNotFoundException($filename);
        }
    }
}