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
// $Id vlib.vlibMimeMail.3.0.1 23/06/2002 $

// check to avoid multiple including of class
if (!defined('vlibMimeMailClassLoaded')) {
    define('vlibMimeMailClassLoaded', 1);

    // get the functionality of the vlibCommon class.
    include_once(dirname(__FILE__).'/vlibCommon/common.php');

    // get error reporting functionality.
    include_once(dirname(__FILE__).'/vlibMimeMail/error.php');

    /**
     * vlibMimeMail Class is used send mime encoded mail messages.
     * For instructions on how to use vlibMimeMail, see the
     * vlibMimeMail.html file, located in the 'docs' directory.
     *
     * @since 22/04/2002
     * @author Kelvin Jones <kelvin@kelvinjones.co.uk>
     * @package vLIB
     * @version 3.0.1
     * @access public
     * @see vlibMimeMail.html
     */


    class vlibMimeMail {

    /*-----------------------------------------------------------------------------\
    |                                 ATTENTION                                    |
    |  Do not touch the following variables. vlibTemplate will not work otherwise. |
    \-----------------------------------------------------------------------------*/

        /** lists To addresses */
        var $sendto = array();

        /** lists Cc addresses */
        var $sendcc = array();

        /** lists Bcc addresses */
        var $sendbcc = array();

        /** paths of attached files */
        var $attachments = array();

        /** attachment mime-types */
        var $mimetypes = array();

        /** attachment dispositions */
        var $dispositions = array();

        /** list of message headers */
        var $xheaders = array();

        /** message priorities */
        var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );

        /** character set of plain text message */
        var $charset = "us-ascii";
        var $ctencoding = "7bit";

        /** character set of html message */
        var $htmlcharset = "us-ascii";
        var $htmlctencoding = "7bit";

        /** Request receipt?? */
        var $receipt = 0;

        /** Do we need to encode when we send?? */
        var $doencoding = 0;

    /*-----------------------------------------------------------------------------\
    |                           public functions                                   |
    \-----------------------------------------------------------------------------*/


        /**
         * FUNCTION: autoCheck
         *
         * Sets auto checking to the value of $bool.
         *
         * @param bool $bool true/false
         * @access public
         */
        function autoCheck ($bool) {
            $this->checkAddress = ($bool);
        }


        /**
         * FUNCTION: to
         *
         * Sets the To header for the message.
         *
         * @param string $toEmail email address
         * @param string $toName Name of recipient [optional]
         * @access public
         */
        function to ($toEmail, $toName=null) {
            if (!$toEmail) return false;
            if ($this->checkAddress && !$this->validateEmail($toEmail)) vlibMimeMailError::raiseError('VM_ERROR_BADEMAIL', FATAL, 'To: '.$toEmail);

            $this->sendto[] = ($toName == null) ? $toEmail : $toName.' <'.$toEmail.'>';
        }


        /**
         * FUNCTION: cc
         *
         * Sets the Cc header for the message.
         *
         * @param string $ccEmail email address
         * @param string $ccName Name of recipient [optional]
         * @access public
         */
        function cc ($ccEmail, $ccName=null) {
            if (!$ccEmail) return false;
            if ($this->checkAddress && !$this->validateEmail($ccEmail)) vlibMimeMailError::raiseError('VM_ERROR_BADEMAIL', FATAL, 'Cc: '.$ccEmail);

            $this->sendcc[] = ($ccName == null) ? $ccEmail : $ccName.' <'.$ccEmail.'>';
        }


        /**
         * FUNCTION: bcc
         *
         * Sets the Bcc header for the message.
         *
         * @param string $bccEmail email address
         * @param string $bccName Name of recipient [optional]
         * @access public
         */
        function bcc ($bccEmail, $bccName=null) {
            if (!$bccEmail) return false;
            if ($this->checkAddress && !$this->validateEmail($bccEmail)) vlibMimeMailError::raiseError('VM_ERROR_BADEMAIL', FATAL, 'Bcc: '.$bccEmail);

            $this->sendbcc[] = ($bccName == null) ? $bccEmail : $bccName.' <'.$bccEmail.'>';
        }


        /**
         * FUNCTION: from
         *
         * Sets the From header for the message.
         *
         * @param string $fromEmail email address
         * @param string $fromName Name of recipient [optional]
         * @access public
         */
        function from ($fromEmail, $fromName=null) {
            if (!$fromEmail) return false;
            if ($this->checkAddress && !$this->validateEmail($fromEmail)) vlibMimeMailError::raiseError('VM_ERROR_BADEMAIL', FATAL, 'From: '.$fromEmail);

            $this->xheaders['From'] = ($fromName == null) ? $fromEmail : $fromName.' <'.$fromEmail.'>';
        }


        /**
         * FUNCTION: replyTo
         *
         * Sets the ReplyTo header for the message.
         *
         * @param string $replytoEmail email address
         * @param string $replytoName Name of recipient [optional]
         * @access public
         */
        function replyTo ($replytoEmail, $replytoName=null) {
            if (!$replytoEmail) return false;
            if ($this->checkAddress && !$this->validateEmail($replytoEmail)) vlibMimeMailError::raiseError('VM_ERROR_BADEMAIL', FATAL, 'Reply-To: '.$replytoEmail);

            $this->xheaders['Reply-To'] = ($replytoName == null) ? $replytoEmail : $replytoName.' <'.$replytoEmail.'>';
        }


        /**
         * FUNCTION: subject
         *
         * Sets the subject for this message.
         *
         * @param string $subject
         * @access public
         */
        function subject ($subject) {
            $this->xheaders['Subject'] = strtr($subject, "\r\n", "  ");
        }


        /**
         * FUNCTION: body
         *
         * Sets the Body of the message.
         * If you're sending a mail with special characters, be sure to define the
         * charset.
         *  i.e. $mail->body('ce message est en français.', 'iso-8859-1');
         *
         * @param string $body plain text as the body
         * @param string $charset
         * @access public
         */
        function body ($body, $charset="") {
            $this->body = $body;

            if($charset != "") {
                $this->charset = strtolower($charset);
                if($this->charset != "us-ascii") $this->ctencoding = "8bit";
            }
        }


        /**
         * FUNCTION: htmlBody
         *
         * Sets the HTML Body of the message.
         * You can you the body() function or htmlBody or both just to be certain that
         * the user will be able to see the stuff you're sending.
         *
         * If you're sending a mail with special characters, be sure to define the
         * charset.
         *  i.e. $mail->body('ce message est en français.', 'iso-8859-1');
         *
         * @param string $htmlbody html text as the body
         * @param string $charset
         * @access public
         */
        function htmlBody ($htmlbody, $charset="") {
            $this->htmlbody = $htmlbody;

            if($charset != "") {
                $this->htmlcharset = strtolower($charset);
                if($this->htmlcharset != "us-ascii") $this->htmlctencoding = "8bit";
            }
        }


        /**
         * FUNCTION: attach
         *
         * Attach a file to the message. Defaults the disposition to 'attachment',
         * you can also use 'inline' which the client will try to show in the message.
         * Mime-types can be handled by vlibMimeMail by the list found in
         * vlibCommon/mime_types.php.
         *
         * @param string $filename full path of the file to attach
         * @param string $disposition inline or attachment
         * @param string $mimetype MIME-type of the file. defaults to 'application/octet-stream'
         * @access public
         */
        function attach ($filename, $disposition = "attachment", $mimetype = null) {
            if($mimetype == null) $mimetype = vlibCommon::getMimeType($filename);
            $this->attachments[] = $filename;
            $this->mimetypes[] = $mimetype;
            $this->dispositions[] = $disposition;
        }


        /**
         * FUNCTION: organization
         *
         * Sets the Organization header for the message.
         *
         * @param string $org organization name
         * @access public
         */
        function organization ($org) {
            if(!empty($org)) $this->xheaders['Organization'] = $org;
        }


        /**
         * FUNCTION: receipt
         *
         * To request a receipt you must call this function with a true value.
         * And you must call $this->from() or $this->replyTo() before sending the mail.
         *
         * @param bool $bool true/false
         * @access public
         */
        function receipt ($bool=true) {
            $this->receipt = ($bool);
        }


        /**
         * FUNCTION: priority
         *
         * Sets the Priority header for the message.
         * usage: $mail->priority(1); // highest setting
         * view documentation for priority levels.
         *
         * @param string $priority
         * @access public
         */
        function priority ($priority) {
            if(!is_int($priority)) $priority = settype($priority, 'integer');
            if(!isset($this->priorities[$priority-1])) return false;

            $this->xheaders["X-Priority"] = $this->priorities[$priority-1];
            return true;
        }


        /**
         * FUNCTION: validateEmail
         *
         * Validates an email address and return true or false.
         *
         * @param string $address email address
         * @return bool true/false
         * @access public
         */
        function validateEmail ($address) {
            return ereg("^([_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9_-]+(\.[_A-Za-z0-9-]+)+)$",$address);
        }


        /**
         * FUNCTION: send
         *
         * Sends the mail.
         *
         * @return boolean true on success, false on failure
         * @access public
         */
        function send () {
            $this->_buildMail();
            return @mail($this->xheaders['To'], $this->xheaders['Subject'], $this->fullBody, $this->headers);
        }

        /**
         * FUNCTION: get
         *
         * Returns the whole e-mail, headers and message. Can be used to display the
         * message in pain text or for debugging.
         *
         * @return string message
         * @access public
         */
        function get () {
            $this->_buildMail();
            $mail = "To: ".$this->xheaders['To']."\n";
            $mail = "Subject: ".$this->xheaders['Subject']."\n";
            $mail .= $this->headers . "\n";
            $mail .= $this->fullBody;
            return $mail;
        }



    /*-----------------------------------------------------------------------------\
    |                           private functions                                  |
    \-----------------------------------------------------------------------------*/


        /**
         * FUNCTION: vlibMimeMail [contsructor]
         *
         * Enables auto checking by default.
         * Also creates the unique boundary id for this message.
         *
         * @access private
         */
        function vlibMimeMail () {
            $this->autoCheck(true);
            $this->boundary = "--" . md5(uniqid("vlibseediganoo"));
        }


        /**
         * FUNCTION: _buildMail
         *
         * Proccesses all headers and attachments ready for sending.
         *
         * @access private
         */
        function _buildMail () {

            // build the headers
            $this->headers = "";
            $this->xheaders['To']  = implode(", ", $this->sendto);
            if (!empty($this->xheaders['Cc']))  $this->xheaders['Cc']  = implode(", ", $this->sendcc);
            if (!empty($this->xheaders['Bcc'])) $this->xheaders['Bcc'] = implode(", ", $this->sendbcc);

            if($this->receipt) {
                if(isset($this->xheaders["Reply-To"])) {
                    $this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
                }
                else {
                    $this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
                }
            }

            if($this->charset != "") {
                $this->xheaders["Mime-Version"] = "1.0";
                $this->xheaders["Content-Type"] = "text/plain; charset=".$this->charset;
                $this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
            }
            $this->xheaders["X-Mailer"] = "vlibMimeMail";

            // setup the body ready for sending
            $this->_setBody();

            reset($this->xheaders);
            while(list($head, $value) = each($this->xheaders)) {
                if (!ereg('^(Subject|To)$', $head)) $this->headers .= $head.": $value\n";
            }
        }


        /**
         * FUNCTION: _setBody
         *
         * sets the body to be used in a mail.
         *
         * @access private
         */
        function _setBody () {
            // do we need to encode??
            (empty($this->htmlbody) && empty($this->attachments)) ? $encode = false : $encode = true;

            // if yes...
            if ($encode) {
                $this->xheaders['Content-Type'] = "multipart/mixed;\n boundary=\"".$this->boundary.'"';

                $this->fullBody = "This is a multi-part message in MIME format.\n\n--".$this->boundary;

                if (!empty($this->body)) {
                    $this->fullBody .= "\nContent-Type: text/plain; charset=".$this->charset."\nContent-Transfer-Encoding: ".$this->ctencoding."\n\n".$this->body."\n\n--".$this->boundary;
                }
                if (!empty($this->htmlbody)) {
                    $this->fullBody .= "\nContent-Type: text/html; charset=".$this->charset."\nContent-Transfer-Encoding: ".$this->ctencoding."\n\n".$this->htmlbody."\n\n--".$this->boundary;
                }
                if (!empty($this->attachments)) {
                    $this->_build_attachments();
                }
                $this->fullBody .= '--'; // ends the last boundary
            }
            // else we just send plain text.
            else {
                if (!empty($this->body)) {
                    $this->fullBody = $this->body;
                }
                else {
                    vlibMimeMailError::raiseError('VM_ERROR_NOBODY', FATAL);
                }
            }
        }


        /**
         * FUNCTION: _build_attachments
         *
         * Checks and encodes all attachments.
         *
         * @access private
         */
        function _build_attachments () {

            $sep = chr(13).chr(10);
            $ata = array();
            $k=0;

            // for each attached file, do...
            for ($i=0; $i < count( $this->attachments); $i++) {
                $filename = $this->attachments[$i];
                $basename = basename($filename);
                $ctype = $this->mimetypes[$i]; // content-type
                $disposition = $this->dispositions[$i];
                if(!file_exists($filename)) {
                    vlibMimeMailError::raiseError('VM_ERROR_NOFILE', FATAL, $filename);
                }
                $subhdr = "Content-type: ".$ctype.";\n name=\"".$basename."\"\nContent-Transfer-Encoding: base64\nContent-Disposition: ".$disposition.";\n  filename=\"".$basename."\"\n";
                $ata[$k++] = $subhdr;
                // non encoded line length
                $linesz= filesize($filename)+1;
                $fp = fopen($filename, 'r');
                $ata[$k++] = chunk_split(base64_encode(fread($fp, $linesz))) . "\n\n--".$this->boundary;
                fclose($fp);
            }
            $this->fullBody .= "\n".implode($sep, $ata);
        }

    } // class vlibMimeMail
} // << end if(!defined())..
?>
