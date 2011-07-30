<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

// no namespace



/**
 * Callback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return Callback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof Callback) ? $callback : new Callback($callback, $m);
}



/**
 * Debug::dump shortcut.
 */
function dump($var)
{
	foreach (func_get_args() as $arg) Debug::dump($arg);
	return $var;
}
