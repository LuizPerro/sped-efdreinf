<?php

namespace NFePHP\EFDReinf;

/**
 * Class efdReinf Event constructor
 *
 * @category  NFePHP
 * @package   NFePHP\EFDReinf
 * @copyright NFePHP Copyright (c) 2017
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-esocial for the canonical source repository
 */

use NFePHP\EFDReinf\Exception\EventsException;

class Event
{
    /**
     * Relationship between the name of the event and its respective class
     * @var array
     */
    private static $available = [
        'evtInfoContri' => Factories\EvtInfoContri::class,
    ];
    
    /**
     * Relationship between the code of the event and its respective name
     * @var array
     */
    private static $aliases = [
        'r1000' => 'evtInfoContri',
        
    ];
    
    /**
     * Call classes to build XML eSocial Event
     * @param string $name
     * @param array $arguments [config, std, certificate, $date]
     * @return object
     * @throws NFePHP\EFDReinf\Exception\EventsException
     */
    public static function __callStatic($name, $arguments)
    {
        $name = str_replace('-', '', strtolower($name));
        $realname = $name;
        if (substr($name, 0, 1) == 's') {
            if (!array_key_exists($name, self::$aliases)) {
                //este evento não foi localizado
                throw EventsException::wrongArgument(1000, $name);
            }
            $realname = self::$aliases[$name];
        }
        if (!array_key_exists($realname, self::$available)) {
            //este evento não foi localizado
            throw EventsException::wrongArgument(1000, $name);
        }
        $className = self::$available[$realname];
        if (empty($arguments[0])) {
            throw EventsException::wrongArgument(1001);
        }
        if (empty($arguments[1])) {
            throw EventsException::wrongArgument(1002, $name);
        }
        if (count($arguments) > 2 && count($arguments) < 4) {
            return new $className($arguments[0], $arguments[1], $arguments[2]);
        }
        if (count($arguments) > 3) {
            return new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
        }
        return new $className($arguments[0], $arguments[1]);
    }
}
