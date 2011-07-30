<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Web
 */



/**
 * URI Syntax (RFC 3986).
 *
 * <pre>
 * http://user:password@nette.org:8042/en/manual.html?name=param#fragment
 * \__/^^^\__________________________/\_____________/^\________/^\______/
 *   |                 |                     |            |         |
 * scheme          authority               path         query    fragment
 * </pre>
 *
 * - authority:   [user[:password]@]host[:port]
 * - hostUri:     http://user:password@nette.org:8042
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Web
 *
 * @property   string $scheme
 * @property   string $user
 * @property   string $password
 * @property   string $host
 * @property   string $port
 * @property   string $path
 * @property   string $query
 * @property   string $fragment
 * @property-read string $absoluteUri
 * @property-read string $authority
 * @property-read string $hostUri
 */
class Uri extends FreezableObject
{
	/** @var array */
	public static $defaultPorts = array(
		'http' => 80,
		'https' => 443,
		'ftp' => 21,
		'news' => 119,
		'nntp' => 119,
	);

	/** @var string */
	private $scheme = '';

	/** @var string */
	private $user = '';

	/** @var string */
	private $pass = '';

	/** @var string */
	private $host = '';

	/** @var int */
	private $port = NULL;

	/** @var string */
	private $path = '';

	/** @var string */
	private $query = '';

	/** @var string */
	private $fragment = '';



	/**
	 * @param  string  URL
	 * @throws InvalidArgumentException
	 */
	public function __construct($uri = NULL)
	{
		if (is_string($uri)) {
			$parts = @parse_url($uri); // @ - is escalated to exception
			if ($parts === FALSE) {
				throw new InvalidArgumentException("Malformed or unsupported URI '$uri'.");
			}

			foreach ($parts as $key => $val) {
				$this->$key = $val;
			}

			if (!$this->port && isset(self::$defaultPorts[$this->scheme])) {
				$this->port = self::$defaultPorts[$this->scheme];
			}

		} elseif ($uri instanceof self) {
			foreach ($this as $key => $val) {
				$this->$key = $uri->$key;
			}
		}
	}



	/**
	 * Sets the scheme part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setScheme($value)
	{
		$this->updating();
		$this->scheme = (string) $value;
		return $this;
	}



	/**
	 * Returns the scheme part of URI.
	 * @return string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}



	/**
	 * Sets the user name part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setUser($value)
	{
		$this->updating();
		$this->user = (string) $value;
		return $this;
	}



	/**
	 * Returns the user name part of URI.
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}



	/**
	 * Sets the password part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setPassword($value)
	{
		$this->updating();
		$this->pass = (string) $value;
		return $this;
	}



	/**
	 * Returns the password part of URI.
	 * @return string
	 */
	public function getPassword()
	{
		return $this->pass;
	}



	/**
	 * Sets the host part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setHost($value)
	{
		$this->updating();
		$this->host = (string) $value;
		return $this;
	}



	/**
	 * Returns the host part of URI.
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}



	/**
	 * Sets the port part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setPort($value)
	{
		$this->updating();
		$this->port = (int) $value;
		return $this;
	}



	/**
	 * Returns the port part of URI.
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}



	/**
	 * Sets the path part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setPath($value)
	{
		$this->updating();
		$this->path = (string) $value;
		return $this;
	}



	/**
	 * Returns the path part of URI.
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}



	/**
	 * Sets the query part of URI.
	 * @param  string|array
	 * @return Uri  provides a fluent interface
	 */
	public function setQuery($value)
	{
		$this->updating();
		$this->query = (string) (is_array($value) ? http_build_query($value, '', '&') : $value);
		return $this;
	}



	/**
	 * Appends the query part of URI.
	 * @param  string|array
	 * @return void
	 */
	public function appendQuery($value)
	{
		$this->updating();
		$value = (string) (is_array($value) ? http_build_query($value, '', '&') : $value);
		$this->query .= ($this->query === '' || $value === '') ? $value : '&' . $value;
	}



	/**
	 * Returns the query part of URI.
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}



	/**
	 * Sets the fragment part of URI.
	 * @param  string
	 * @return Uri  provides a fluent interface
	 */
	public function setFragment($value)
	{
		$this->updating();
		$this->fragment = (string) $value;
		return $this;
	}



	/**
	 * Returns the fragment part of URI.
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}



	/**
	 * Returns the entire URI including query string and fragment.
	 * @return string
	 */
	public function getAbsoluteUri()
	{
		return $this->scheme . '://' . $this->getAuthority() . $this->path
			. ($this->query === '' ? '' : '?' . $this->query)
			. ($this->fragment === '' ? '' : '#' . $this->fragment);
	}



	/**
	 * Returns the [user[:pass]@]host[:port] part of URI.
	 * @return string
	 */
	public function getAuthority()
	{
		$authority = $this->host;
		if ($this->port && isset(self::$defaultPorts[$this->scheme]) && $this->port !== self::$defaultPorts[$this->scheme]) {
			$authority .= ':' . $this->port;
		}

		if ($this->user !== '' && $this->scheme !== 'http' && $this->scheme !== 'https') {
			$authority = $this->user . ($this->pass === '' ? '' : ':' . $this->pass) . '@' . $authority;
		}

		return $authority;
	}



	/**
	 * Returns the scheme and authority part of URI.
	 * @return string
	 */
	public function getHostUri()
	{
		return $this->scheme . '://' . $this->getAuthority();
	}



	/**
	 * URI comparsion (this object must be in canonical form).
	 * @param  string
	 * @return bool
	 */
	public function isEqual($uri)
	{
		// compare host + path
		$part = self::unescape(strtok($uri, '?#'), '%/');
		if (strncmp($part, '//', 2) === 0) { // absolute URI without scheme
			if ($part !== '//' . $this->getAuthority() . $this->path) return FALSE;

		} elseif (strncmp($part, '/', 1) === 0) { // absolute path
			if ($part !== $this->path) return FALSE;

		} else {
			if ($part !== $this->scheme . '://' . $this->getAuthority() . $this->path) return FALSE;
		}

		// compare query strings
		$part = preg_split('#[&;]#', self::unescape(strtr((string) strtok('?#'), '+', ' '), '%&;=+'));
		sort($part);
		$query = preg_split('#[&;]#', $this->query);
		sort($query);
		return $part === $query;
	}



	/**
	 * Transform to canonical form.
	 * @return void
	 */
	public function canonicalize()
	{
		$this->updating();
		$this->path = $this->path === '' ? '/' : self::unescape($this->path, '%/');
		$this->host = strtolower(rawurldecode($this->host));
		$this->query = self::unescape(strtr($this->query, '+', ' '), '%&;=+');
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getAbsoluteUri();
	}



	/**
	 * Similar to rawurldecode, but preserve reserved chars encoded.
	 * @param  string to decode
	 * @param  string reserved characters
	 * @return string
	 */
	public static function unescape($s, $reserved = '%;/?:@&=+$,')
	{
		// reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
		// within a path segment, the characters "/", ";", "=", "?" are reserved
		// within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
		preg_match_all('#(?<=%)[a-f0-9][a-f0-9]#i', $s, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		foreach (array_reverse($matches) as $match) {
			$ch = chr(hexdec($match[0][0]));
			if (strpos($reserved, $ch) === FALSE) {
				$s = substr_replace($s, $ch, $match[0][1] - 1, 3);
			}
		}
		return $s;
	}

}
