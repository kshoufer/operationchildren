<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002 Active Fish Group                                 |
// +----------------------------------------------------------------------+
// | Authors: Kelvin Jones <kelvin@kelvinjones.co.uk>                     |
// +----------------------------------------------------------------------+
//
// $Id vlib.vlibTemplateCache.3.0.1 23/06/2002 $

/**
 * Class uses all of vlibTemplate's functionality but caches the template files.
 * It creates an identical tree structure to your filesystem but with cached files.
 *
 * @author Kelvin Jones <kelvin@kelvinjones.co.uk>
 * @since 22/02/2002
 * @version 3.0.1
 * @package vLIB
 * @access public
 */

class vlibTemplateCache extends vlibTemplate {

/*-----------------------------------------------------------------------------\
|     DO NOT TOUCH ANYTHING IN THIS CLASS, IT MAY NOT WORK OTHERWISE           |
\-----------------------------------------------------------------------------*/

    var $_cache = 1;     // tells vlibTemplate that we're caching
    var $_cachefile;     // full path to current cache file (even if it doesn't yet exist)
    var $_cacheexists;   // has this file been cached before
    var $_cachefilelocked; // is this file currently locked whilst writing
    var $_cachefiledir;  // dir of current cache file


    /**
     * FUNCTION: clearCache
     * will unset a file, and set $this->_cacheexists to 0.
     *
     * @access public
     * @return boolean
     */
    function clearCache() {
        $file = $this->_getFilename();
        if(is_file($file)) {
            unlink($this->_getFilename());
        }
        return true;
    }

    /**
     * FUNCTION: recache
     * alias for clearCache().
     *
     * @access public
     * @return boolean
     */
    function recache() {
        return $this->clearCache();
    }

    /**
     * FUNCTION: setCacheLifeTime
     * sets the lifetime of the cached file
     *
     * @param int $int number of seconds to set lifetime to
     * @access public
     * @return boolean
     */
    function setCacheLifeTime($int = null) {
        if ($int == null || !is_int($int)) return false;
        ($int == 0) and $int = 60;// sets it to 1 minute if 0 is passed
        $this->OPTIONS['CACHE_LIFETIME'] = $int;
        return true;
    }

    /**
     * FUNCTION: setCacheExtension
     * sets the extention of the cache file
     *
     * @param str $str name of new cache extention
     * @access public
     * @return boolean
     */
    function setCacheExtension($str = null) {
        if ($str == null || !ereg('^[a-z0-9]+$', strtolower($str))) return false;
        $this->OPTIONS['CACHE_EXTENSION'] = strtolower($str);
        return true;
    }


/*----------------------------------------\
          Private Functions
-----------------------------------------*/

    /**
     * FUNCTION: _checkCache
     * checks if there's a cache, if there is then it will read the cache file as the template.
     */
    function _checkCache () {
        $file = $this->_getFilename();

        if (file_exists($file)) {
            $this->_cacheexists = 1;
            $this->_cachefile = $file;

            // if it's expired
            if ((filectime ($this->_cachefile) + $this->OPTIONS['CACHE_LIFETIME']) < date ( "U")) {
                $this->_cacheexists = 0;
                $this->clearCache();
                return false; // so that we know to recache
            }
            else {
                $fh = fopen ($this->_cachefile, "r");
                $cached_file = fread ($fh, filesize ($this->_cachefile));
                fclose ($fh);
                // and set the variable in vlibTemplate
                $this->_tmplfilep = $cached_file;
                return true;
            }

        } else {
            $this->_cacheexists = 0;
            $this->_cachefile = $file;
        }
    }

    /**
     * FUNCTION: _getFilename
     * gets the full pathname for the cached file
     *
     */
    function _getFilename($str = null) {
        $_phpself = $GLOBALS['HTTP_SERVER_VARS']['PHP_SELF'];
        $_pathinfo = $GLOBALS['HTTP_SERVER_VARS']['PATH_INFO'];
        $_self = (!empty($_pathinfo)) ? $_pathinfo : $_phpself; // $PHP_SELF bug fix on Win32 CGI and IIS.

        $file = $GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'] . $_self;
        $file = eregi_replace('^(.*)\.[a-z0-9]+$', "\\1", $file); // removes extension
        $file = ereg_replace('\.|:', '_', $file);
        $file.= '.'. $this->OPTIONS['CACHE_EXTENSION'];
        return $this->OPTIONS['CACHE_DIRECTORY'].'/'.$file;
    }

    /**
     * FUNCTION: _createCache
     * creates the cached file
     *
     */
    function _createCache() {
        $cache_file = $this->_cachefile;
        if(!$this->_prepareDirs($cache_file)) return false; // prepare all of the directories

        $f = fopen ($cache_file, "w");
        if (!$f) vlibTemplateError::raiseError('VT_ERROR_NO_CACHE_WRITE',KILL,$cache_file);
        fputs ($f, $this->_tmplfilep); // write the parsed string from vlibTemplate
        fclose ($f);
        touch ($cache_file);
        return true;
    }

    /**
     * FUNCTION: _prepareDirs
     * prepares the directory structure
     *
     */
    function _prepareDirs($file) {
        if(empty($file)) die('no filename'); //doe error in future
        $filepath = dirname($file);
        $dirs = split('[\\/]', $filepath);
        $currpath;
        foreach ($dirs as $dir) {
            $currpath .= $dir .'/';
            $type = @filetype($currpath);

            ($type=='link') and $type = 'dir';
            if ($type != 'dir' && $type != false && !empty($type)) {
                vlibTemplateError::raiseError('VT_ERROR_WRONG_CACHE_TYPE',KILL,'directory: '.$currpath.', type: '.$type);
            }
            if ($type == 'dir') {
                continue;
            }
            else {
                $s = @mkdir($currpath, 0775);
                if (!$s) vlibTemplateError::raiseError('VT_ERROR_CACHE_MKDIR_FAILURE',KILL,'directory: '.$currpath);
            }
        }
        return true;
    }

} // -- end vlibTemplateCache class
?>
