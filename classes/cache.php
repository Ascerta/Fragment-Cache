<?php 
 include_once 'exception.php';
abstract class Cache  {
 
/**
 * Kohana Cache provides a common interface to a variety of caching engines. Tags are
 * supported where available natively to the cache system. Kohana Cache supports multiple 
 * instances of cache engines through a grouped singleton pattern.
 * 
 * ### Supported cache engines
 * 
 * *  [APC](http://php.net/manual/en/book.apc.php)
 * *  [eAccelerator](http://eaccelerator.net/)
 * *  File
 * *  [Memcache](http://memcached.org/)
 * *  [Memcached-tags](http://code.google.com/p/memcached-tags/)
 * *  [SQLite](http://www.sqlite.org/)
 * *  [Xcache](http://xcache.lighttpd.net/)
 * 
 * ### Introduction to caching
 * 
 * Caching should be implemented with consideration. Generally, caching the result of resources
 * is faster than reprocessing them. Choosing what, how and when to cache is vital. PHP APC is
 * presently one of the fastest caching systems available, closely followed by Memcache. SQLite
 * and File caching are two of the slowest cache methods, however usually faster than reprocessing
 * a complex set of instructions.
 * 
 * Caching engines that use memory are considerably faster than the file based alternatives. But
 * memory is limited whereas disk space is plentiful. If caching large datasets it is best to use
 * file caching.
 * 
 * ### Configuration settings
 * 
 * Kohana Cache uses configuration groups to create cache instances. A configuration group can
 * use any supported driver, with successive groups using the same driver type if required.
 * 
 * #### Configuration example
 * 
 * Below is an example of a _memcache_ server configuration.
 * 
 *     return array(
 *          'default'       => array(                      // Default group
 *                  'driver'         => 'memcache',        // using Memcache driver
 *                  'servers'        => array(             // Available server definitions
 *                         array(
 *                              'host'       => 'localhost',
 *                              'port'       => 11211,
 *                              'persistent' => FALSE
 *                         )
 *                  ),
 *                  'compression'    => FALSE,             // Use compression?
 *           ),
 *     )
 * 
 * In cases where only one cache group is required, if the group is named `default` there is
 * no need to pass the group name when instantiating a cache instance.
 * 
 * #### General cache group configuration settings
 * 
 * Below are the settings available to all types of cache driver.
 * 
 * Name           | Required | Description
 * -------------- | -------- | ---------------------------------------------------------------
 * driver         | __YES__  | (_string_) The driver type to use
 * 
 * Details of the settings specific to each driver are available within the drivers documentation.
 * 
 * ### System requirements
 * 
 * *  Kohana 3.0.x
 * *  PHP 5.2.4 or greater
 * 
 * @package    Kohana
 * @category   Cache
 * @version    2.0
 * @author     Kohana Team
 * @copyright  (c) 2009-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */


	const DEFAULT_EXPIRE = 3600;

	/**
	 * @var   string     default driver to use
	 */
	public static $default = 'file';

	/**
	 * @var   Kohana_Cache instances
	 */
	public static $instances = array();

	/**
	 * Creates a singleton of a Kohana Cache group. If no group is supplied
	 * the __default__ cache group is used.
	 * 
	 *     // Create an instance of the default group
	 *     $default_group = Cache::instance();
	 * 
	 *     // Create an instance of a group
	 *     $foo_group = Cache::instance('foo');
	 * 
	 *     // Access an instantiated group directly
	 *     $foo_group = Cache::$instances['default'];
	 *
	 * @param   string   the name of the cache group to use [Optional]
	 * @return  Kohana_Cache
	 * @throws  Kohana_Cache_Exception
	 */
	public static function instance($group = NULL)
	{
		

		// Create a new cache type instance
		$cache_class = 'Cache_File';
                $config = array(
           'file'   => array(                          // File driver group
                   'driver'         => 'file',         // using File driver
                   'cache_dir'     => ABSPATH . 'wp-content/plugins/block_cache/cache/kohana_cache', // Cache location
            ),
      );
		Cache::$instances[$group] = new $cache_class($config);

		// Return the instance
		return Cache::$instances[$group];
	}

	/**
	 * @var  Kohana_Config
	 */
	protected $_config;

	/**
	 * Ensures singleton pattern is observed, loads the default expiry
	 * 
	 * @param  array     configuration
	 */
	protected function __construct(array $config)
	{
		$this->_config = $config;
	}

	/**
	 * Overload the __clone() method to prevent cloning
	 *
	 * @return  void
	 * @throws  Kohana_Cache_Exception
	 */
	public function __clone()
	{
		throw new Kohana_Cache_Exception('Cloning of Kohana_Cache objects is forbidden');
	}

	/**
	 * Retrieve a cached value entry by id.
	 * 
	 *     // Retrieve cache entry from default group
	 *     $data = Cache::instance()->get('foo');
	 * 
	 *     // Retrieve cache entry from default group and return 'bar' if miss
	 *     $data = Cache::instance()->get('foo', 'bar');
	 * 
	 *     // Retrieve cache entry from memcache group
	 *     $data = Cache::instance('memcache')->get('foo');
	 *
	 * @param   string   id of cache to entry
	 * @param   string   default value to return if cache miss
	 * @return  mixed
	 * @throws  Kohana_Cache_Exception
	 */
	abstract public function get($id, $default = NULL);

	/**
	 * Set a value to cache with id and lifetime
	 * 
	 *     $data = 'bar';
	 * 
	 *     // Set 'bar' to 'foo' in default group, using default expiry
	 *     Cache::instance()->set('foo', $data);
	 * 
	 *     // Set 'bar' to 'foo' in default group for 30 seconds
	 *     Cache::instance()->set('foo', $data, 30);
	 * 
	 *     // Set 'bar' to 'foo' in memcache group for 10 minutes
	 *     if (Cache::instance('memcache')->set('foo', $data, 600))
	 *     {
	 *          // Cache was set successfully
	 *          return
	 *     }
	 *
	 * @param   string   id of cache entry
	 * @param   string   data to set to cache
	 * @param   integer  lifetime in seconds
	 * @return  boolean
	 */
	abstract public function set($id, $data, $lifetime = 3600);

	/**
	 * Delete a cache entry based on id
	 * 
	 *     // Delete 'foo' entry from the default group
	 *     Cache::instance()->delete('foo');
	 * 
	 *     // Delete 'foo' entry from the memcache group
	 *     Cache::instance('memcache')->delete('foo')
	 *
	 * @param   string   id to remove from cache
	 * @return  boolean
	 */
	abstract public function delete($id);

	/**
	 * Delete all cache entries.
	 * 
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 * 
	 *     // Delete all cache entries in the default group
	 *     Cache::instance()->delete_all();
	 * 
	 *     // Delete all cache entries in the memcache group
	 *     Cache::instance('memcache')->delete_all();
	 *
	 * @return  boolean
	 */
	abstract public function delete_all();

	/**
	 * Replaces troublesome characters with underscores.
	 *
	 *     // Sanitize a cache id
	 *     $id = $this->_sanitize_id($id);
	 * 
	 * @param   string   id of cache to sanitize
	 * @return  string
	 */
	protected function _sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array('/', '\\', ' '), '_', $id);
	}

// End Kohana_Cache
    
    
    
}