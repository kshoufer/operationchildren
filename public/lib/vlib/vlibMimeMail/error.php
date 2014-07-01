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
// $Id vlib.vlibMimeMailError.3.0.1 23/06/2002 $

define('FATAL', E_USER_ERROR);
define('WARNING', E_USER_WARNING);
define('NOTICE', E_USER_NOTICE);

/**
 * Class is used by vlibMimeMail.
 * It handles all of the error reporting for vlibMimeMail.
 *
 * @author Kelvin Jones <kelvin@kelvinjones.co.uk>
 * @since 22/04/2002
 * @version 3.0.1
 * @package vLIB
 * @access private
 */

class vlibMimeMailError {

/*-----------------------------------------------------------------------------\
|     DO NOT TOUCH ANYTHING IN THIS CLASS IT MAY NOT WORK OTHERWISE            |
\-----------------------------------------------------------------------------*/

    function raiseError ($code, $level = null, $extra=null) {
        if (!($level & error_reporting())) return; // binary AND checks for reporting level

        $error_codes = array(
                        'VM_ERROR_INVALID_ERROR_CODE'   => 'vlibMimeMail error: Invalid error raised.',
                        'VM_ERROR_NOFILE'               => 'vlibMimeMail error: Attachment ('.$extra.') file not found.',
                        'VM_ERROR_BADEMAIL'             => 'vlibMimeMail error: Email address ('.$extra.') not valid.',
                        'VM_ERROR_NOBODY'               => 'vlibMimeMail error: Tried to send a message with no body.'
                            );

        $error_levels = array(
                        'VM_ERROR_INVALID_ERROR_CODE'   => FATAL,
                        'VM_ERROR_NOFILE'               => FATAL,
                        'VM_ERROR_BADEMAIL'             => FATAL,
                        'VM_ERROR_NOBODY'               => FATAL
                            );

        if ($level === null) $level = $error_levels[$code];

        if ($msg = $error_codes[$code]) {
            trigger_error($msg, $level);
        } else {
            trigger_error($error_codes['VM_ERROR_INVALID_ERROR_CODE'], $error_levels['VM_ERROR_INVALID_ERROR_CODE']);
        }
        return;
    }
}
?>
