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
// $Id vlib.vlibTemplate.3.0.1 23/06/2002 $

// check to avoid multiple including of class
if (!defined('vlibTemplateClassLoaded')) {
    define('vlibTemplateClassLoaded', 1);

    include_once (dirname(__FILE__).'/vlibTemplate/error.php');

    /**
     * vlibTemplate is a class used to seperate PHP and HTML.
     * For instructions on how to use vlibTemplate, see the
     * vlibTemplate.html file, located in the 'docs' directory.
     *
     * @since 07/03/2002
     * @author Kelvin Jones <kelvin@kelvinjones.co.uk>
     * @package vLIB
     * @version 3.0.1
     * @access public
     * @see vlibTemplate.html
     */

    class vlibTemplate {

    /*-----------------------------------------------------------------------------\
    |                                 ATTENTION                                    |
    |  Do not touch the following variables. vlibTemplate will not work otherwise. |
    \-----------------------------------------------------------------------------*/

        var $OPTIONS = array(
                        'MAX_INCLUDES'          =>   10,
                        'TEMPLATE_DIR'          => null,
                        'GLOBAL_VARS'           => null,
                        'GLOBAL_CONTEXT_VARS'   => null,
                        'LOOP_CONTEXT_VARS'     => null,
                        'SET_LOOP_VAR'          => null,
                        'DEFAULT_ESCAPE'        => null,
                        'STRICT'                => null,
                        'CASELESS'              => null,
                        'UNKNOWNS'              => null,
                        'TIME_PARSE'            => null,
                        'INCLUDE_PATHS'         => array(),
                        'CACHE_DIRECTORY'       => null,
                        'CACHE_LIFETIME'        => null,
                        'CACHE_EXTENSION'       => null
                             );

        /** open and close tags used for escaping */
        var $ESCAPE_TAGS = array(
                            'html' => array('open' => 'htmlspecialchars(',
                                            'close'=> ', ENT_QUOTES)'),
                            'url' => array('open' => 'urlencode(',
                                            'close'=> ')'),
                            'sq' => array('open' => 'addcslashes(',
                                            'close'=> ", \"'\")"),
                            'dq' => array('open' => 'addcslashes(',
                                            'close'=> ", '\"')")
                                );

        /** open and close tags used for formatting */
        var $FORMAT_TAGS = array(
                            'strtoupper' => array('open' => 'strtoupper(',
                                            'close'=> ')'),
                            'uc'         => array('open' => 'strtoupper(',
                                            'close'=> ')'),
                            'strtolower' => array('open' => 'strtolower(',
                                            'close'=> ')'),
                            'lc'         => array('open' => 'strtolower(',
                                            'close'=> ')'),
                            'ucfirst'    => array('open' => 'ucfirst(',
                                            'close'=> ')'),
                            'lcucfirst'    => array('open' => 'ucfirst(strtolower(',
                                            'close'=> '))'),
                            'ucwords'    => array('open' => 'ucwords(',
                                            'close'=> ')'),
                            'lcucwords'    => array('open' => 'ucwords(strtolower(',
                                            'close'=> '))')
                                );

    //-----------------------------------------------------------------------------\\

        /** root directory of vlibTemplate automagically filled in */
        var $VLIBTEMPLATE_ROOT = null;

        /** contains current directory used when doing recursive include */
        var $_currentincludedir = array();

        /** current depth of includes */
        var $_includedepth = 0;

        /** full path to tmpl file */
        var $_tmplfilename = null;

        /** file data before it's parsed */
        var $_tmplfile = null;

        /** parsed version of file, ready for eval()ing */
        var $_tmplfilep = null;

        /** eval()ed version ready for printing or whatever */
        var $_tmploutput = null;

        /** array for variables to be kept */
        var $_vars = array();

        /** array where loop variables are kept */
        var $_arrvars = array();

        /** array which holds the current namespace during parse */
        var $_namespace = array();

        /** variable is set to true once the template is parsed, to save re-parsing everything */
        var $_parsed = false;

        /** array holds all unknowns vars */
        var $_unknowns = array();

        /** microtime when template parsing began */
        var $_firstparsetime = null;

        /** total time taken to parse template */
        var $_totalparsetime = null;

        /** name of current loop being passed in */
        var $_currloopname = null;

        /** rows with the above loop */
        var $_currloop = array();
    /*-----------------------------------------------------------------------------\
    |                           public functions                                   |
    \-----------------------------------------------------------------------------*/

        /**
         * FUNCTION: newTemplate
         *
         * Usually called by the class constructor.
         * Stores the filename in $this->_tmplfilename.
         * Raises an error if the template file is not found.
         *
         * @param string $tmplfile full path to template file
         * @return boolean true
         * @access public
         */
        function newTemplate ($tmplfile) {           #takes template file and includes any files needed
            if (!$tfile = $this->_fileSearch($tmplfile)) vlibTemplateError::raiseError('VT_ERROR_NOFILE',KILL,$tmplfile);
            $this->_tmplfilename = $tfile;
            return true;
        }

        /**
         * FUNCTION: setVar
         *
         * Sets variables to be used by the template
         * If $k is an array, then it will treat it as an associative array
         * using the keys as variable names and the values as variable values.
         *
         * @param mixed $k key to define variable name
         * @param mixed $v variable to assign to $k
         * @return boolean true/false
         * @access public
         */
        function setVar ($k,$v=null) {
            if(is_array($k)){
                foreach($k as $key => $value){
                    ($this->OPTIONS['CASELESS']) and $key = strtolower($key);
                    $key = trim($key);
                    if (!empty($value) && !is_array($value) && eregi('^[a-z_]+[a-z0-9_]*$', $key)) {
                        $this->_vars[$key] = $value;
                    }
                }
            }else{
                if (eregi('^[a-z_]+[a-z0-9_]*$', $k) && $v != null && !is_array($v)) {
                    ($this->OPTIONS['CASELESS']) and $k = strtolower($k);
                    $k = trim($k);
                    $this->_vars[$k] = $v;
                } else {
                    return false;
                }
            }
            return true;
        }

        /**
         * FUNCTION: unsetVar
         *
         * Unsets a variable which has already been set
         * Parse in all vars wanted for deletion in seperate parametres
         *
         * @param string var name to remove use: vlibTemplate::unsetVar(var[, var..])
         * @return boolean true/false returns true unless called with 0 params
         * @access public
         */
        function unsetVar () {
            $num_args = func_num_args();
            if ($num_args < 1)  return false;

            for ($i = 0; $i < $num_args; $i++) {
                $var = func_get_arg($i);
                ($this->OPTIONS['CASELESS']) and $var = strtolower($var);
                if (!eregi('^[a-z_]+[a-z0-9_]*$', $var)) continue;
                unset($this->_vars[$var]);
            }
            return true;
        }

        /**
         * FUNCTION: getVars
         *
         * Gets all vars currently set in global namespace.
         *
         * @return array
         * @access public
         */
        function getVars () {
            if (empty($this->_vars)) return false;
            return $this->_vars;
        }

        /**
         * FUNCTION: getVar
         *
         * Gets a single var from the global namespace
         *
         * @return var
         * @access public
         */
        function getVar ($var) {
            ($this->OPTIONS['CASELESS']) and $var = strtolower($var);
            if (empty($var) || !isset($this->_vars[$var])) return false;
            return $this->_vars[$var];
        }

        /**
         * FUNCTION: setContextVars
         *
         * sets the GLOBAL_CONTEXT_VARS
         *
         * @return true
         * @access public
         */
        function setContextVars () {
            $_phpself = $GLOBALS['HTTP_SERVER_VARS']['PHP_SELF'];
            $_pathinfo = $GLOBALS['HTTP_SERVER_VARS']['PATH_INFO'];

            // the following fixes bug of $PHP_SELF on Win32 CGI and IIS.
            $_self = (!empty($_pathinfo)) ? $_pathinfo : $_phpself;

            $_qs   = $GLOBALS['HTTP_SERVER_VARS']['QUERY_STRING'];
            $this->setvar('__SELF__', $_self);
            $this->setvar('__REQUEST_URI__', $_self.'?'.$_qs);
            return true;
        }

        /**
         * FUNCTION: setLoop
         *
         * Builds the loop construct for use with <TMPL_LOOP>.
         *
         * @param string $k string to define loop name
         * @param array $v array to assign to $k
         * @return boolean true/false
         * @access public
         */
        function setLoop ($k,$v) {
            if(is_array($v) && eregi('^[a-z_]+[a-z0-9_]*$', $k)){
                $k = trim($k);
                ($this->OPTIONS['CASELESS']) and $k = strtolower($k);
                $this->_arrvars[$k] = array();
                ($this->OPTIONS['SET_LOOP_VAR'] && !empty($v)) and $this->setvar($k, 1);
                if ($this->_debug && !in_array($k, $this->_debugallloops)) array_push($this->_debugallloops, $k);
                if (($this->_arrvars[$k] = $this->_arrayBuild($v)) == false)  {
                    vlibTemplateError::raiseError('VT_WARNING_INVALID_ARR',WARNING,$k);
                }
            }
            return true;
        }

        /**
         * FUNCTION: newLoop
         *
         * Sets the name for the curent loop in the 3 step loop process.
         *
         * @param string $name string to define loop name
         * @return boolean true/false
         * @access public
         */
        function newLoop ($loopname) {
            if(eregi('^[a-z_]+[a-z0-9_]*$', $loopname)){
                $this->_currloopname = trim($loopname);
                return true;
            }
            else {
                return false;
            }
        }

        /**
         * FUNCTION: addRow
         *
         * Adds a row to the current loop in the 3 step loop process.
         *
         * @param array $row loop row to add to current loop
         * @return boolean true/false
         * @access public
         */
        function addRow ($row) {
            if ($this->_currloopname == null) {
                vlibTemplateError::raiseError('VT_WARNING_LOOP_NOT_SET',WARNING);
                return false;
            }

            if(is_array($row)){
                $this->_currloop[] = $row;
                return true;
            }
            else {
                return false;
            }
        }

        /**
         * FUNCTION: addLoop
         *
         * Completes the 3 step loop process. This assigns the rows and resets
         * the variables used.
         *
         * @return boolean true/false
         * @access public
         */
        function addLoop () {
            if ($this->_currloopname == null) {
                vlibTemplateError::raiseError('VT_WARNING_LOOP_NOT_SET',WARNING);
                return false;
            }

            $this->setLoop($this->_currloopname, $this->_currloop);
            unset($this->_currloopname, $this->_currloop);
            return true;
        }

        /**
         * FUNCTION: unsetLoop
         *
         * Unsets a loop which has already been set.
         * Can only unset top level loops.
         *
         * @param string loop to remove use: vlibTemplate::unsetLoop(loop[, loop..])
         * @return boolean true/false returns true unless called with 0 params
         * @access public
         */
        function unsetLoop () {
            $num_args = func_num_args();
            if ($num_args < 1) return false;

            for ($i = 0; $i < $num_args; $i++) {
                $var = func_get_arg($i);
                ($this->OPTIONS['CASELESS']) and $var = strtolower($var);
                if (!eregi('^[a-z_]+[a-z0-9_]*$', $var)) continue;
                unset($this->_arrvars[$var]);
            }
            return true;
        }


        /**
         * FUNCTION: reset
         *
         * Resets the vlibTemplate object. After using vlibTemplate::reset() you must
         * use vlibTemplate::newTemplate(tmpl) to reuse, not passing in the options array.
         *
         * @return boolean true
         * @access public
         */
        function reset () {
            $this->clearVars();
            $this->clearLoops();
            $this->_tmplfilename = null;
            $this->_tmplfile = null;
            $this->_tmplfilep = null;
            $this->_tmploutput = null;
            $this->_parsed = false;
            $this->_unknowns = array();
            $this->_firstparsetime = null;
            $this->_totalparsetime = null;
            return true;
        }

        /**
         * FUNCTION: clearVars
         *
         * Unsets all variables in the template
         *
         * @return boolean true
         * @access public
         */
        function clearVars () {
            $this->_vars = array();
            return true;
        }

        /**
         * FUNCTION: clearLoops
         *
         * Unsets all loops in the template
         *
         * @return boolean true
         * @access public
         */
        function clearLoops () {
            $this->_arrvars = array();
            return true;
        }

        /**
         * FUNCTION: clearAll
         *
         * Unsets all variables and loops set using setVar/Loop()
         *
         * @return boolean true
         * @access public
         */
        function clearAll () {
            $this->clearVars();
            $this->clearLoops();
            return true;
        }

        /**
         * FUNCTION: unknownsExist
         *
         * Returns true if unknowns were found after parsing.
         * Function MUST be called AFTER one of the parsing functions to have any relevance.
         *
         * @return boolean true/false
         * @access public
         */
        function unknownsExist () {
            return (!empty($this->_unknowns));
        }

        /**
         * FUNCTION: unknowns
         *
         * Alias for unknownsExist.
         *
         * @access public
         */
        function unknowns () {
            return $this->unknownsExist();
        }

        /**
         * FUNCTION: getUnknowns
         *
         * Returns an array of all unknown vars found when parsing.
         * This function is only relevant after parsing a document.
         *
         * @return array
         * @access public
         */
        function getUnknowns () {
            return $this->_unknowns;
        }

        /**
         * FUNCTION: setUnknowns
         *
         * Sets how you want to handle variables that were found in the
         * template but not set in vlibTemplate using vlibTemplate::setVar().
         *
         * @param  string $arg ignore, remove, print, leave or comment
         * @return boolean
         * @access public
         */
        function setUnknowns ($arg) {
            $arg = trim($arg);
            if (eregi('^(ignore|remove|print|leave|comment)$', $arg)) {
                $this->OPTIONS['UNKNOWNS'] = strtolower($arg);
                return true;
            }
            return false;
        }

        /**
         * FUNCTION: setPath
         *
         * function sets the paths to use when including files.
         * Use of this function: vlibTemplate::setPath(string path [, string path, ..]);
         * i.e. if $tmpl is your template object do: $tmpl->setPath('/web/htdocs/templates','/web/htdocs/www');
         * with as many paths as you like.
         * if this function is called without any arguments, it will just delete any previously set paths.
         *
         * @param string path (mulitple)
         * @return bool success
         * @access public
         */
        function setPath () {
            $num_args = func_num_args();
            if ($num_args < 1) {
                $this->OPTIONS['INCLUDE_PATHS'] = array();
                return true;
            }

            for ($i = 0; $i < $num_args; $i++) {
                $thispath = func_get_arg($i);
                array_push($this->OPTIONS['INCLUDE_PATHS'], realpath($thispath));
            }
            return true;
        }

        /**
         * FUNCTION: getParseTime
         *
         * After using one of the parse functions, this will allow you
         * access the time taken to parse the template.
         * see OPTION 'TIME_PARSE'.
         *
         * @return float time taken to parse template
         * @access public
         */
        function getParseTime () {
            if ($this->OPTIONS['TIME_PARSE'] && $this->_parsed) {
                return $this->_totalparsetime;
            }
            return false;
        }


        /**
         * FUNCTION: fastPrint
         *
         * Identical to parse() except that it doesn't use output buffering thus
         * printing the output directly. Produces absolute minimal difference
         * except possibly if parsing a huge template.
         *
         * @access public
         * @return boolean true/false
         */
        function fastPrint () {
            if (!$this->_parsed) {
                if ($this->OPTIONS['TIME_PARSE']) $this->_firstparsetime = $this->_getMicroTime();
                $this->_intParse();
                $this->_parsed = true;
            }
            $success = @eval($this->_tmplfilep);
            if ($this->_debug) echo $this->doDebug();
            if (!$success) vlibTemplateError::raiseError('VT_ERROR_PARSE', FATAL);
            if ($this->_firstparsetime) {
                if ($this->OPTIONS['TIME_PARSE']) $this->_totalparsetime = ($this->_getMicroTime() - $this->_firstparsetime);
            }
            return true;
        }


        /**
         * FUNCTION: pparse
         *
         * Calls parse, and then prints out $this->_tmploutput
         *
         * @access public
         * @return boolean true/false
         */
        function pparse () {
            if (!$this->_parsed) $this->_parse();
            print($this->_tmploutput);
            return true;
        }

        /**
         * FUNCTION: pprint
         *
         * Alias for pparse()
         *
         * @access public
         */
        function pprint () {
            return $this->pparse();
        }


        /**
         * FUNCTION: grab
         *
         * Returns the parsed output, ready for printing, passing to mail() ...etc.
         * Invokes $this->_parse() if template has not yet been parsed.
         *
         * @access public
         * @return boolean true/false
         */
        function grab () {
            if (!$this->_parsed) $this->_parse();
            return $this->_tmploutput;
        }

    /*-----------------------------------------------------------------------------\
    |                           private functions                                  |
    \-----------------------------------------------------------------------------*/

        /**
         * FUNCTION: vlibTemplate
         *
         * vlibTemplate constructor.
         * if $tmplfile has been passed to it, it will send to $this->newTemplate()
         *
         * @param string $tmplfile full path to template file
         * @param array $options see above
         * @return boolean true/false
         * @access private
         */
        function vlibTemplate ($tmplfile=null, $options=null) {
            $this->VLIBTEMPLATE_ROOT = dirname(realpath(__FILE__)); // sets the template root dir

            if (is_file($this->VLIBTEMPLATE_ROOT.'/ini.php')) { // load ini file if it exists
                require ($this->VLIBTEMPLATE_ROOT.'/ini.php');
                $config = $VLIB_PARAMS['vlibTemplate'];
                foreach ($config as $name => $val) {
                    $this->OPTIONS[$name] = $val;
                }
            }
            if (is_array($options)) {
                foreach($options as $key => $val) {
                    if (strtolower($key) == 'path') {
                        $this->setPath($val);
                    }
                    else {
                        $this->_setOption(strtoupper($key), strtolower($val));
                    }
                }
            }

            if($tmplfile) $this->newTemplate($tmplfile);
            return true;
        }


        /**
         * FUNCTION: _intPreParse
         *
         * Function parses a file and runs the debugger if class initialised with vlibTemplateDebug.
         * Also does <tmpl_includes>s and calls itself to parse/debug each include.
         *
         * @param string $tmplfile path to template file to parse
         * @param string $tagtype either tmpl_include | tmpl_phpinclude
         * @param string $wholetag parsed by preg_replace within function
         * @param string $var parsed by preg_replace within function
         * @access private
         * @return string template with all tmpl_include files included
         */
        function _intPreParse ($tmplfile, $tagtype='include', $wholetag=null, $var=null) {

            if ($tagtype == 'comment' && empty($var)) {
                return $wholetag;
            }
            elseif ($tag == 'comment' && !empty($var)) {
                return;
            }

            if ($this->_includedepth > $this->OPTIONS['MAX_INCLUDES'] || $tmplfile == false) {
                return;
            }
            elseif (strtolower($tagtype) == 'phpinclude') {
                return "<?php include('$tmplfile'); ?>\n\r";
            }
            else {
                $this->_includedepth++;
                if ($this->_debug) array_push ($this->_debugIncludedfiles, $tmplfile);
            }

            array_push($this->_currentincludedir, dirname($tmplfile));
            $incl_file = fread($fp = fopen($tmplfile, 'r'), filesize($tmplfile));
            fclose($fp);

            if ($this->_debug) $this->doDebugWarnings(file($tmplfile), $tmplfile);

            $incl_file = preg_replace("/(<|<\/|{|{\/|<!--|<!--\/){1}\s*tmpl_(include|phpinclude|comment)\s*(((?:name|file)\s*=\s*)?[\"\']([A-Z0-9_\.\/ -]+)[\"\'])?\s*(?:>|\/>|}|-->){1}/ixSe"
                                ,"\$this->_intPreParse(\$this->_fileSearch('\\5'), '\\2', '\\0','\\5');"
                                ,$incl_file);

            $this->_includedepth--;

            array_pop($this->_currentincludedir);
            return $incl_file;
        }

        /**
         * FUNCTION: _fileSearch
         *
         * Searches for all possible instances of file { $file }
         *
         * @param string $file path of file we're looking for
         * @access private
         * @return mixed fullpath to file or boolean false
         */
        function _fileSearch ($file) {
            $filename = basename($file);
            $filepath = dirname($file);

            // check fullpath first
            $fullpath = $filepath.'/'.$filename;
            if (is_file($fullpath)) return $fullpath;

            // check for relative path for current directory
            if (!empty($this->_currentincludedir)) {
                $currdir = $this->_currentincludedir[(count($this->_currentincludedir) -1)];
                $relativepath = realpath($currdir.'/'.$filepath.'/'.$filename);
                if (is_file($relativepath)) {
                    array_push ($this->_currentincludedir, dirname($relativepath));
                    return $relativepath;
                }
            }

            // check for relative path for all additional given paths
            if (!empty($this->OPTIONS['INCLUDE_PATHS'])) {
                foreach ($this->OPTIONS['INCLUDE_PATHS'] as $currdir) {
                    $relativepath = realpath($currdir.'/'.$filepath.'/'.$filename);
                    if (is_file($relativepath)) {
                        return $relativepath;
                    }
                }
            }

            // check path from TEMPLATE_DIR
            if (!empty($this->OPTIONS['TEMPLATE_DIR'])) {
                $fullpath = realpath($this->OPTIONS['TEMPLATE_DIR'].'/'.$filepath.'/'.$filename);
                if (is_file($fullpath)) return $fullpath;
            }

            // check relative path from executing php script
            $fullpath = realpath($filepath.'/'.$filename);
            if (is_file($fullpath)) return $fullpath;

            // check path from template file
            if (!empty($this->VLIBTEMPLATE_ROOT)) {
                $fullpath = realpath($this->VLIBTEMPLATE_ROOT.'/'.$filepath.'/'.$filename);
                if (is_file($fullpath)) return $fullpath;
            }

            return false; // found nothing
        }



        /**
         * FUNCTION: _arrayBuild
         *
         * Modifies the array $arr to add Template variables, __FIRST__, __LAST__ ..etc
         * if $this->OPTIONS['LOOP_CONTEXT_VARS'] is true.
         * Used by $this->setloop().
         *
         * @param array $arr
         * @return array new look array
         * @access private
         */
        function _arrayBuild ($arr) {
            if (is_array($arr) && !empty($arr)) { // $arr is an array so let's continue
                $arr = array_values($arr); // to prevent problems w/ non sequential arrays
                for ($i = 0; $i < count($arr); $i++) {
                    if(!is_array($arr[$i])) {
                       return false;
                    }
                    if ($this->OPTIONS['LOOP_CONTEXT_VARS']) {
                        if ($i == 0) $arr[$i]['__FIRST__'] = true;
                        if (($i + 1) == count($arr)) $arr[$i]['__LAST__'] = true;
                        if ($i != 0 and (($i + 1) < count($arr))) $arr[$i]['__INNER__'] = true;
                        if (is_int(($i+1) / 2))  $arr[$i]['__EVEN__'] = true;
                        if (!is_int(($i+1) / 2))  $arr[$i]['__ODD__'] = true;
                        $arr[$i]['__ROWNUM__'] = ($i + 1);
                    }

                    $localCount = count($arr[$i]);
                    $index = 0;
                    foreach ($arr[$i] as $k => $v) {
                        unset($arr[$i][$k]);
                        if ($this->OPTIONS['CASELESS']) $k = strtolower($k);
                        if (ereg('^[0-9]+$', $k)) $k = '_'.$k;

                        if (is_array($v)) {
                            if (($arr[$i][$k] = $this->_arrayBuild($v)) == false) return false;
                            if ($this->_debug && !in_array($k.' (inner)', $this->_debugallloops)) array_push($this->_debugallloops, $k.' (inner)');
                        }
                        else { // reinsert the var
                            $arr[$i][$k] = $v;
                        }
                        
                        $index++;
                        
                        if($index >= $localCount)
                        {
                        	break;
                        }
                    }
                }
                return $arr;
            }
            elseif (empty($arr)) {
                return true;
            }
        }

        /**
         * FUNCTION: _parseIf
         * returns a string used for parsing in tmpl_if statements.
         *
         * @param string $varname
         * @param string $namespace current namespace
         * @access private
         * @return string used for eval'ing
         */
        function _parseIf ($varname, $namespace) {

            if(isset($namespace)) $namespace = substr($namespace, 0, -1);
            if (count($this->_namespace) == 0 || $namespace == 'global') return "\$this->_vars['$varname']";

            $retstr = "\$this->_arrvars";
            $numnamespaces = count($this->_namespace);
            for ($i=0; $i < $numnamespaces; $i++) {
                if ($this->_namespace[$i] == $namespace || (($i + 1) == $numnamespaces && !empty($namespace))) {
                    $retstr .= "['".$namespace."'][\$_".$i."]";
                    break 1;
                } else {
                    $retstr .= "['".$this->_namespace[$i]."'][\$_".$i."]";
                }
            }

            if ($this->OPTIONS['GLOBAL_VARS'] && empty($namespace)) {
                return "((".$retstr."['".$varname."'] != null) ? ".$retstr."['".$varname."'] : \$this->_vars['$varname'])";
            } else {
                return $retstr."['".$varname."']";
            }
        }


        /**
         * FUNCTION: _parseLoop
         * returns a string used for parsing in tmpl_loop statements.
         *
         * @param string $varname
         * @access private
         * @return string used for eval'ing
         */
        function _parseLoop ($varname) {
            array_push($this->_namespace, $varname);

            $tempvar = count($this->_namespace) - 1;
            $retstr = "for (\$_".$tempvar."=0 ; \$_".$tempvar." < count(\$this->_arrvars";

            for ($i=0; $i < count($this->_namespace); $i++) {
                $retstr .= "['".$this->_namespace[$i]."']";
                if ($this->_namespace[$i] != $varname) $retstr .= "[\$_".$i."]";
            }

            return $retstr."); \$_".$tempvar."++) {";
        }

        /**
         * FUNCTION: _parseVar
         *
         * returns a string used for parsing in tmpl_var statements.
         *
         * @param string $wholetag
         * @param string $tag
         * @param string $varname
         * @param string $escape
         * @param string $format
         * @param string $namespace
         * @access private
         * @return string used for eval'ing
         */
        function _parseVar ($wholetag, $tag, $varname, $escape, $format, $namespace) {
            // get the var name
            if(isset($namespace)) $namespace = substr($namespace, 0, -1);
            $wholetag = stripslashes($wholetag);

            // if it's a global
            if (count($this->_namespace) == 0 || $namespace == 'global') {
                $var1 = "\$this->_vars['$varname']";
            }
            else {
                // build a loop var
                $var1build = "\$this->_arrvars";
                $numnamespaces = count($this->_namespace);
                for ($i=0; $i < $numnamespaces; $i++) {
                    if ($this->_namespace[$i] == $namespace || (($i + 1) == $numnamespaces && !empty($namespace))) {
                        $var1build .= "['".$namespace."'][\$_".$i."]";
                        break 1;
                    } else {
                        $var1build .= "['".$this->_namespace[$i]."'][\$_".$i."]";
                    }
                }
                $var1 = $var1build . "['$varname']";

                // use of global vars if not a global itself
                if ($this->OPTIONS['GLOBAL_VARS'] && empty($namespace)) {
                    $var2 = "\$this->_vars['$varname']";
                }

            }

            $beforevar = ''; // what we put before a var when printing
            $aftervar  = ''; // and what we put after (4 formatting and escaping).

            if (!empty($escape)&& isset($this->ESCAPE_TAGS[$escape])) {
                $beforevar .= $this->ESCAPE_TAGS[$escape]['open'];
                $aftervar   = $this->ESCAPE_TAGS[$escape]['close'] . $aftervar;
            }

            if (!empty($format)&& isset($this->FORMAT_TAGS[$format])) {
                $beforevar .= $this->FORMAT_TAGS[$format]['open'];
                $aftervar   = $this->FORMAT_TAGS[$format]['close'] . $aftervar;
            }

            // build return values
            $retstr  = 'if ('.$var1.' !== null) { ';
            $retstr .= 'print('.$beforevar.$var1.$aftervar.'); ';
            $retstr .= '}';

            if ($var2) {
                $retstr .= ' elseif ('.$var2.' !== null) { ';
                $retstr .= 'print('.$beforevar.$var2.$aftervar.'); ';
                $retstr .= '}';
            }

            switch (strtolower($this->OPTIONS['UNKNOWNS'])) {
                case 'comment':
                    $comment = addcslashes('<!-- unknown variable '.ereg_replace('<!--|-->', '', $wholetag).'//-->', '"');
                    $retstr .= ' else { print("'.$comment.'"); $this->_setUnknown("'.$varname.'"); }';
                    return $retstr;
                break;
                case 'leave': // prints without htmlescaping
                    $retstr .= ' else { print("'.addcslashes($wholetag, '"').'"); $this->_setUnknown("'.$varname.'"); }';
                    return $retstr;
                break;
                case 'print': // print html escaped tag
                    $retstr .= ' else { print("'.htmlspecialchars($wholetag, ENT_QUOTES).'"); $this->_setUnknown("'.$varname.'"); }';
                    return $retstr;
                break;

                case 'ignore': // different 2 remove as it does not note the unknown at all
                    return $retstr;
                break;
                case 'remove': // remove tag but still notes it. (default handler)
                default:
                    $retstr .= ' else { $this->_setUnknown("'.$varname.'"); }';
                    return $retstr;
                break;
            }
        }


        /**
         * FUNCTION: _parseTag
         * takes values from preg_replace in $this->_intparse() and determines
         * the replace string
         *
         * @param array $args array of all matches found by preg_replace
         * @access private
         * @return string replace values
         */
        function _parseTag ($args) {
            $wholetag = $args[0];
            $openclose = $args[1];
            $tag = strtolower($args[2]);
            ($args[9] == "\n") ? $newline = "\n\r" : $newline=null;

            // if it's a closing tag return
            if(preg_match("/^<\/|{\/|<!--\/$/s", $openclose) || ereg("endloop|endif|endunless|endcomment", $tag)) {
                if ($tag == 'loop' || $tag == 'endloop') array_pop($this->_namespace);

                if ($tag == 'comment' || $tag == 'endcomment') {
                    return "<?php */ ?>$newline";
                }
                else {
                    return "<?php } ?>$newline";
                }
            }

            for($i=3; $i < 8; $i=($i+2)) {
                if (empty($args[$i]) && empty($args[($i+1)])) break;
                (empty($args[$i])) ? $key = 'name' : $key = strtolower($args[$i]);
                $$key = $args[($i+1)];
            }

            ($this->OPTIONS['CASELESS']) ? $var = strtolower($name) : $var = $name; // strtolower if caseless

            // used for debugging
            if ($this->_debug && !empty($var)) {
                if (eregi("^global\.([a-z_]+[_a-z0-9]*)$", $var, $matches)) $var2 = $matches[1];
                (empty($this->_debugTemplatevars[$tag])) and $this->_debugTemplatevars[$tag] = array();
                (!isset($var2)) and $var2 = $var;
                (!in_array($var2, $this->_debugTemplatevars[$tag])) and array_push($this->_debugTemplatevars[$tag], $var2);
            }

            if (eregi("^([a-z_]+[_a-z0-9]*(\.)+)?([a-z_]+[_a-z0-9]*)$", $var, $matches)) {
                $var = $matches[3];
                $namespace = $matches[1];
            }

            // return correct string (tag dependent)
            switch ($tag) {
                case 'var':
                    if (empty($escape) &&
                        (!empty($this->OPTIONS['DEFAULT_ESCAPE'])
                            && strtolower($this->OPTIONS['DEFAULT_ESCAPE']) != 'none')) {
                        $escape = strtolower($this->OPTIONS['DEFAULT_ESCAPE']);
                    }
                    return '<?php '.$this->_parseVar ($wholetag, $tag, $var, $escape, $format, $namespace)." ?>$newline";
                break;

                case 'if':
                    return "<?php if (". $this->_parseIf($var, $namespace) ." != false) { ?>$newline";
                break;

                case 'unless':
                      return "<?php if (". $this->_parseIf($var, $namespace) ." == false) { ?>$newline";
                break;

                case 'elseif':
                      return "<?php } elseif (". $this->_parseIf($var, $namespace) ." != false) { ?>$newline";
                break;

                case 'loop':
                      return "<?php ". $this->_parseLoop($var) ."?>$newline";
                break;

                case 'else':
                    return "<?php } else { ?>$newline";
                break;
                case 'comment':
                    if (empty($var)) { // full open/close style comment
                        return "<?php /* ?>$newline";
                    }
                    else { // just ignore tag if it was a one line comment
                        return;
                    }
                break;
                default:
                    if ($this->OPTIONS['STRICT']) vlibTemplateError::raiseError('VT_ERROR_INVALID_TAG', KILL, htmlspecialchars($wholetag, ENT_QUOTES));
                break;
            }// end switch

        } // << end function getretvals


        /**
         * FUNCTION: _intParse
         *
         * Parses $this->_tmplfile into correct format for eval() to work
         * Called by $this->_parse(), or $this->fastPrint, this replaces all <tmpl_*> references
         * with their correct php representation, i.e. <tmpl_var title> becomes $this->vars['title']
         * Sets final parsed file to $this->tmplfilep.
         *
         * @access private
         * @return boolean true/false
         */
        function _intParse () {
            if ($this->OPTIONS['GLOBAL_CONTEXT_VARS']) $this->setContextVars();

            if($this->_cache) { // if class instantiated with vlibTemplateCache, check if cache exists
                if ($this->_checkCache()) return true;
            }
            $this->_tmplfile = $this->_intPreParse($this->_tmplfilename);
            $file = '?>'.$this->_tmplfile.'<?php return true;';


            $regex = "/(<|<\/|{|{\/|<!--|<!--\/){1}\s*tmpl_([\w]+)\s*(?:(?:(name|format|escape)\s*=\s*)?[\"\']?([A-Z0-9_\.]*)[\"\']?)?\s*(?:(?:(name|format|escape)\s*=\s*)?[\"\']?([A-Z0-9_\.]*)[\"\']?)?\s*(?:(?:(name|format|escape)\s*=\s*)?[\"\']?([A-Z0-9_\.]*)[\"\']?)?\s*(?:>|\/>|}|-->){1}([\n])?/i";

            if (function_exists('preg_replace_callback')) {
                $file = preg_replace_callback($regex ,array(&$this, '_parseTag'),$file);
            } else {
                $file = preg_replace($regex.'e',"\$this->_parseTag(array('\\0','\\1','\\2','\\3','\\4','\\5','\\6','\\7','\\8','\\9'));",$file);
            }

//			print_r($this->_vars);
//			print("<!--".$file."-->");

            $this->_tmplfilep = $file;
            if($this->_cache) {
                $this->_createCache(); // creates the cached file
            }
            return true;
        }

        /**
         * FUNCTION: _parse
         *
         * Calls _intParse, and eval()s $this->tmplfilep
         * and outputs the results to $this->tmploutput
         *
         * @access private
         * @return boolean true/false
         */
        function _parse () {
            if (!$this->_parsed) {
                if ($this->OPTIONS['TIME_PARSE']) $this->_firstparsetime = $this->_getMicroTime();
                $this->_intParse();
                $this->_parsed = true;

                if ($this->OPTIONS['TIME_PARSE']) $this->_totalparsetime = ($this->_getMicroTime() - $this->_firstparsetime);
                if ($this->OPTIONS['TIME_PARSE'] && $this->OPTIONS['GLOBAL_CONTEXT_VARS']) $this->setVar('__PARSE_TIME__', $this->getParseTime());
            }

            ob_start();
                $success = @eval($this->_tmplfilep);
                if ($this->_debug) $this->doDebug();
                if (!$success) vlibTemplateError::raiseError('VT_ERROR_PARSE', FATAL);
                $this->_tmploutput .= ob_get_contents();
            ob_end_clean();

            return true;
        }



        /**
         * FUNCTION: _setOption
         *
         * Sets one or more of the boolean options 1/0, that control certain actions in the template.
         * Use of this function:
         * either: vlibTemplate::_setOptions(string option_name, bool option_val [, string option_name, bool option_val ..]);
         * or      vlibTemplate::_setOptions(array);
         *          with an associative array where the key is the option_name
         *          and the value is the option_value.
         *
         * @param mixed (mulitple)
         * @return bool true/false
         * @access private
         */
        function _setOption () {
            $numargs = func_num_args();
            if ($numargs < 1) {
                vlibTemplateError::raiseError('VT_ERROR_WRONG_NO_PARAMS', null, '_setOption()');
                return false;
            }

            if ($numargs == 1) {
                $options = func_get_arg(1);
                if (is_array($options)) {
                    foreach ($options as $k => $v) {
                        if ($v != null) {
                            if(in_array($k, array_keys($this->OPTIONS))) $this->OPTIONS[$k] = $v;
                        } else {
                            continue;
                        }
                    }
                } else {
                    vlibTemplateError::raiseError('VT_ERROR_WRONG_NO_PARAMS', null, '_setOption()');
                    return false;
                }
            }
            elseif (is_int($numargs / 2)) {
                for ($i = 0; $i < $numargs; $i=($i+2)) {
                    $k  = func_get_arg($i);
                    $v = func_get_arg(($i+1));

                    if ($v != null) {
                        if(in_array($k, array_keys($this->OPTIONS))) $this->OPTIONS[$k] = $v;
                    }
                }
            } else {
                vlibTemplateError::raiseError('VT_ERROR_WRONG_NO_PARAMS', null, '_setOption()');
                return false;
            }
            return true;
        }

        /**
         * FUNCTION: _setUnknown
         *
         * Used during parsing, this function sets an unknown var checking to see if it
         * has been previously set.
         *
         * @param string var
         * @access private
         */
        function _setUnknown ($var) {
            if (!in_array($var, $this->_unknowns)) array_push($this->_unknowns, $var);
        }

        /**
         * FUNCTION: _getMicrotime
         * Returns microtime as a float number
         *
         * @return float microtime
         * @access private
        */
        function _getMicrotime () {
            list($bigsec, $smallsec) = explode(" ",microtime());
            return ((float)$bigsec + (float)$smallsec);
        }


    /*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    The following functions have no use and are included just so that if the user
    is making use of vlibTemplateCache functions, this doesn't crash when changed to
    vlibTemplate if the user is quickly bypassing the vlibTemplateCache class.
    - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
        function clearCache()        {vlibTemplateError::raiseError('VT_WARNING_NOT_CACHE_OBJ', WARNING, 'clearCache()');}
        function recache()           {vlibTemplateError::raiseError('VT_WARNING_NOT_CACHE_OBJ', WARNING, 'recache()');}
        function setCacheLifeTime()  {vlibTemplateError::raiseError('VT_WARNING_NOT_CACHE_OBJ', WARNING, 'setCacheLifeTime()');}
        function setCacheExtension() {vlibTemplateError::raiseError('VT_WARNING_NOT_CACHE_OBJ', WARNING, 'setCacheExtension()');}
    }

    include_once (dirname(__FILE__).'/vlibTemplate/debug.php');
    include_once (dirname(__FILE__).'/vlibTemplate/cache.php');

} // << end if(!defined())..
?>
