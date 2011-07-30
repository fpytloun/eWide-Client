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



/**
 * The Nette Framework.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
final class Framework
{

	/**#@+ Nette Framework version identification */
	const NAME = 'Nette Framework';

	const VERSION = '1.0-dev';

	const REVISION = '3fe360f released on 2010-08-25';
	/**#@-*/



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Nette Framework promotion.
	 * @return void
	 */
	public static function promo()
	{
		echo '<a href="http://nette.org" title="Nette Framework - The Most Innovative PHP Framework"><img ',
			'src="http://files.nette.org/icons/nette-powered.gif" alt="Powered by Nette Framework" width="80" height="15" /></a>';
	}

}
