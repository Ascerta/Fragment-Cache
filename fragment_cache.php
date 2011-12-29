<?php
/**
 * @package Block_Cache
 * @version 0.1
 */
/*
Plugin Name: Block Cache
Plugin URI: http:// /
Description: Provides block or fragment level caching of pages. For example cahcing the output of tag clouds or expensive DB queries that aren't user specific.
Author: Andrew Spratley
Version: 0.1
Author URI: http://www.ascerta.co.uk/
*/
require 'classes/file.php';
   function start_cache_block($key)
                {
                   $cache =  Cache_File::instance('file');
                 $cached_data = $cache->get($key);
                 if(!$cached_data)
                 {
                    //if not in cache start buffering
                        ob_start();
                        return false;
                 }
                    //if in cache dump cached string
                    else {
                        echo $cached_data;
                         echo '<!--From Cache-->';
                        return true;
                    }
                }
                function end_cache_block($key, $time = 3600){
    $contents = ob_get_contents();
    if($contents !== false)
    {
        //cache this key with time
        ob_end_clean();
        $cache =  Cache_File::instance('file');
        
        $cache->set($key, $contents, $time);
        echo $contents;
        
    }
                
                }

?>
