<?php
if (version_compare(PHP_VERSION, '5.0.0', '<') ) exit("Sorry, this version of PHPMailer will only run on PHP version 5 or greater!\n");


class PHPMailer {

  public $Priority          = 3;
  public $CharSet           = 'iso-8859-1';
  public $ContentType       = 'text/plain';
  public $Encoding          = '8bit';
  public $ErrorInfo         = '';
  public $From              = 'root@localhost';
  public $FromName          = 'Root User';
  public $Sender            = '';
  public $ReturnPath        = '';
  public $Subject           = '';
  public $Body              = '';
  public $AltBody           = '';
  protected $MIMEBody       = '';
  protected $MIMEHeader     = '';
  protected $mailHeader     = '';
  public $WordWrap          = 0;
  public $Mailer            = 'mail';
  public $Sendmail          = '/usr/sbin/sendmail';
  public $UseSendmailOptions    = true;
  public $PluginDir         = '';
  public $ConfirmReadingTo  = '';
  public $Hostname          = '';
  public $MessageID         = '';
  public $MessageDate       = '';
  public $Host          = 'localhost';
  public $Port          = 25;
  public $Helo          = '';
  public $SMTPSecure    = '';
  public $SMTPAuth      = false;
  public $Username      = '';
  public $Password      = '';
  public $AuthType      = '';
  public $Realm         = '';
  public $Workstation   = '';
  public $Timeout       = 10;
  public $SMTPDebug     = false;
  public $Debugoutput     = "echo";
  public $SMTPKeepAlive = false;
  public $SingleTo      = false;
  public $SingleToArray = array();
  public $LE              = "\n";
  public $DKIM_selector   = '';
  public $DKIM_identity   = '';
  public $DKIM_passphrase   = '';
  public $DKIM_domain     = '';
  public $DKIM_private    = '';
  public $action_function = '';
  public $Version         = '5.2.4';
  public $XMailer         = '';
  protected   $smtp           = null;
  protected   $to             = array();
  protected   $cc             = array();
  protected   $bcc            = array();
  protected   $ReplyTo        = array();
  protected   $all_recipients = array();
  protected   $attachment     = array();
  protected   $CustomHeader   = array();
  protected   $message_type   = '';
  protected   $boundary       = array();
  protected   $language       = array();
  protected   $error_count    = 0;
  protected   $sign_cert_file = '';
  protected   $sign_key_file  = '';
  protected   $sign_key_pass  = '';
  protected   $exceptions     = false;
  const STOP_MESSAGE  = 0;
  const STOP_CONTINUE = 1;
  const STOP_CRITICAL = 2;
  const CRLF = "\r\n";
  

  private function mail_passthru($to, $subject, $body, $header, $params) {
    if ( ini_get('safe_mode') || !($this->UseSendmailOptions) ) {
        $rt = @mail($to, $this->EncodeHeader($this->SecureHeader($subject)), $body, $header);
    } else {
        $rt = @mail($to, $this->EncodeHeader($this->SecureHeader($subject)), $body, $header, $params);
    }
    return $rt;
  }

  private function edebug($str) {
    if ($this->Debugoutput == "error_log") {
        error_log($str);
    } else {
        echo $str;
    }
  }

  public function __construct($exceptions = false) {
    $this->exceptions = ($exceptions == true);
  }

  public function IsHTML($ishtml = true) {
    if ($ishtml) {
      $this->ContentType = 'text/html';
    } else {
      $this->ContentType = 'text/plain';
    }
  }

  public function IsSMTP() {
    $this->Mailer = 'smtp';
  }

  public function IsMail() {
    $this->Mailer = 'mail';
  }

  public function IsSendmail() {
    if (!stristr(ini_get('sendmail_path'), 'sendmail')) {
      $this->Sendmail = '/var/qmail/bin/sendmail';
    }
    $this->Mailer = 'sendmail';
  }

  public function IsQmail() {
    if (stristr(ini_get('sendmail_path'), 'qmail')) {
      $this->Sendmail = '/var/qmail/bin/sendmail';
    }
    $this->Mailer = 'sendmail';
  }


  public function AddAddress($address, $name = '') {
    return $this->AddAnAddress('to', $address, $name);
  }

  public function AddCC($address, $name = '') {
    return $this->AddAnAddress('cc', $address, $name);
  }

  public function AddBCC($address, $name = '') {
    return $this->AddAnAddress('bcc', $address, $name);
  }

  public function AddReplyTo($address, $name = '') {
    return $this->AddAnAddress('Reply-To', $address, $name);
  }

  protected function AddAnAddress($kind, $address, $name = '') {
    if (!preg_match('/^(to|cc|bcc|Reply-To)$/', $kind)) {
      $this->SetError($this->Lang('Invalid recipient array').': '.$kind);
      if ($this->exceptions) {
        throw new phpmailerException('Invalid recipient array: ' . $kind);
      }
      if ($this->SMTPDebug) {
        $this->edebug($this->Lang('Invalid recipient array').': '.$kind);
      }
      return false;
    }
    $address = trim($address);
    $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
    if (!$this->ValidateAddress($address)) {
      $this->SetError($this->Lang('invalid_address').': '. $address);
      if ($this->exceptions) {
        throw new phpmailerException($this->Lang('invalid_address').': '.$address);
      }
      if ($this->SMTPDebug) {
        $this->edebug($this->Lang('invalid_address').': '.$address);
      }
      return false;
    }
    if ($kind != 'Reply-To') {
      if (!isset($this->all_recipients[strtolower($address)])) {
        array_push($this->$kind, array($address, $name));
        $this->all_recipients[strtolower($address)] = true;
        return true;
      }
    } else {
      if (!array_key_exists(strtolower($address), $this->ReplyTo)) {
        $this->ReplyTo[strtolower($address)] = array($address, $name);
      return true;
    }
  }
  return false;
}

  public function SetFrom($address, $name = '', $auto = 1) {
    $address = trim($address);
    $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
    if (!$this->ValidateAddress($address)) {
      $this->SetError($this->Lang('invalid_address').': '. $address);
      if ($this->exceptions) {
        throw new phpmailerException($this->Lang('invalid_address').': '.$address);
      }
      if ($this->SMTPDebug) {
        $this->edebug($this->Lang('invalid_address').': '.$address);
      }
      return false;
    }
    $this->From = $address;
    $this->FromName = $name;
    if ($auto) {
      if (empty($this->ReplyTo)) {
        $this->AddAnAddress('Reply-To', $address, $name);
      }
      if (empty($this->Sender)) {
        $this->Sender = $address;
      }
    }
    return true;
  }

  public static function ValidateAddress($address) {
        if ((defined('PCRE_VERSION')) && (version_compare(PCRE_VERSION, '8.0') >= 0)) {
          return preg_match('/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)((?>(?>(?>((?>(?>(?>\x0D\x0A)?[     ])+|(?>[        ]*\x0D\x0A)?[   ]+)?)(\((?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}|(?!(?:.*[a-f0-9][:\]]){7,})((?6)(?>:(?6)){0,5})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:|(?!(?:.*[a-f0-9]:){5,})(?8)?::(?>((?6)(?>:(?6)){0,3}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD', $address);
        } elseif (function_exists('filter_var')) { //Introduced in PHP 5.2
        if(filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE) {
          return false;
        } else {
          return true;
        }
    } else {
        return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address);
        }
  }

  public function Send() {
    try {
      if(!$this->PreSend()) return false;
      return $this->PostSend();
    } catch (phpmailerException $e) {
      $this->mailHeader = '';
      $this->SetError($e->getMessage());
      if ($this->exceptions) {
        throw $e;
      }
      return false;
    }
  }

  public function PreSend() {
    try {
      $this->mailHeader = "";
      if ((count($this->to) + count($this->cc) + count($this->bcc)) < 1) {
        throw new phpmailerException($this->Lang('provide_address'), self::STOP_CRITICAL);
      }

      // Set whether the message is multipart/alternative
      if(!empty($this->AltBody)) {
        $this->ContentType = 'multipart/alternative';
      }

      $this->error_count = 0; // reset errors
      $this->SetMessageType();
      //Refuse to send an empty message
      if (empty($this->Body)) {
        throw new phpmailerException($this->Lang('empty_message'), self::STOP_CRITICAL);
      }

      $this->MIMEHeader = $this->CreateHeader();
      $this->MIMEBody = $this->CreateBody();

      if ($this->Mailer == 'mail') {
        if (count($this->to) > 0) {
          $this->mailHeader .= $this->AddrAppend("To", $this->to);
        } else {
          $this->mailHeader .= $this->HeaderLine("To", "undisclosed-recipients:;");
        }
        $this->mailHeader .= $this->HeaderLine('Subject', $this->EncodeHeader($this->SecureHeader(trim($this->Subject))));
        // if(count($this->cc) > 0) {
            // $this->mailHeader .= $this->AddrAppend("Cc", $this->cc);
        // }
      }

      // digitally sign with DKIM if enabled
      if (!empty($this->DKIM_domain) && !empty($this->DKIM_private) && !empty($this->DKIM_selector) && !empty($this->DKIM_domain) && file_exists($this->DKIM_private)) {
        $header_dkim = $this->DKIM_Add($this->MIMEHeader, $this->EncodeHeader($this->SecureHeader($this->Subject)), $this->MIMEBody);
        $this->MIMEHeader = str_replace("\r\n", "\n", $header_dkim) . $this->MIMEHeader;
      }

      return true;

    } catch (phpmailerException $e) {
      $this->SetError($e->getMessage());
      if ($this->exceptions) {
        throw $e;
      }
      return false;
    }
  }

  /**
   * Actual Email transport function
   * Send the email via the selected mechanism
   * @throws phpmailerException
   * @return bool
   */
  public function PostSend() {
    try {
      // Choose the mailer and send through it
      switch($this->Mailer) {
        case 'sendmail':
          return $this->SendmailSend($this->MIMEHeader, $this->MIMEBody);
        case 'smtp':
          return $this->SmtpSend($this->MIMEHeader, $this->MIMEBody);
        case 'mail':
          return $this->MailSend($this->MIMEHeader, $this->MIMEBody);
        default:
          return $this->MailSend($this->MIMEHeader, $this->MIMEBody);
      }
    } catch (phpmailerException $e) {
      $this->SetError($e->getMessage());
      if ($this->exceptions) {
        throw $e;
      }
      if ($this->SMTPDebug) {
        $this->edebug($e->getMessage()."\n");
      }
    }
    return false;
  }

  /**
   * Sends mail using the $Sendmail program.
   * @param string $header The message headers
   * @param string $body The message body
   * @throws phpmailerException
   * @access protected
   * @return bool
   */
  protected function SendmailSend($header, $body) {
    if ($this->Sender != '') {
      $sendmail = sprintf("%s -oi -f%s -t", escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
    } else {
      $sendmail = sprintf("%s -oi -t", escapeshellcmd($this->Sendmail));
    }
    if ($this->SingleTo === true) {
      foreach ($this->SingleToArray as $val) {
        if(!@$mail = popen($sendmail, 'w')) {
          throw new phpmailerException($this->Lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
        }
        fputs($mail, "To: " . $val . "\n");
        fputs($mail, $header);
        fputs($mail, $body);
        $result = pclose($mail);
        // implement call back function if it exists
        $isSent = ($result == 0) ? 1 : 0;
        $this->doCallback($isSent, $val, $this->cc, $this->bcc, $this->Subject, $body);
        if($result != 0) {
          throw new phpmailerException($this->Lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
        }
      }
    } else {
      if(!@$mail = popen($sendmail, 'w')) {
        throw new phpmailerException($this->Lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
      }
      fputs($mail, $header);
      fputs($mail, $body);
      $result = pclose($mail);
      // implement call back function if it exists
      $isSent = ($result == 0) ? 1 : 0;
      $this->doCallback($isSent, $this->to, $this->cc, $this->bcc, $this->Subject, $body);
      if($result != 0) {
        throw new phpmailerException($this->Lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
      }
    }
    return true;
  }

  protected function MailSend($header, $body) {
    $toArr = array();
    foreach($this->to as $t) {
      $toArr[] = $this->AddrFormat($t);
    }
    $to = implode(', ', $toArr);

    if (empty($this->Sender)) {
      $params = "-oi ";
    } else {
      $params = sprintf("-oi -f%s", $this->Sender);
    }
    if ($this->Sender != '' and !ini_get('safe_mode')) {
      $old_from = ini_get('sendmail_from');
      ini_set('sendmail_from', $this->Sender);
    }
      $rt = false;
    if ($this->SingleTo === true && count($toArr) > 1) {
      foreach ($toArr as $val) {
        $rt = $this->mail_passthru($val, $this->Subject, $body, $header, $params);
        // implement call back function if it exists
        $isSent = ($rt == 1) ? 1 : 0;
        $this->doCallback($isSent, $val, $this->cc, $this->bcc, $this->Subject, $body);
      }
    } else {
      $rt = $this->mail_passthru($to, $this->Subject, $body, $header, $params);
      // implement call back function if it exists
      $isSent = ($rt == 1) ? 1 : 0;
      $this->doCallback($isSent, $to, $this->cc, $this->bcc, $this->Subject, $body);
    }
    if (isset($old_from)) {
      ini_set('sendmail_from', $old_from);
    }
    if(!$rt) {
      throw new phpmailerException($this->Lang('instantiate'), self::STOP_CRITICAL);
    }
    return true;
  }

  protected function SmtpSend($header, $body) {
    $bad_rcpt = array();

    if(!$this->SmtpConnect()) {
      throw new phpmailerException($this->Lang('smtp_connect_failed'), self::STOP_CRITICAL);
    }
    $smtp_from = ($this->Sender == '') ? $this->From : $this->Sender;
    if(!$this->smtp->Mail($smtp_from)) {
      $this->SetError($this->Lang('from_failed') . $smtp_from . " : " . implode(",",$this->smtp->getError())) ;
      throw new phpmailerException($this->ErrorInfo, self::STOP_CRITICAL);
    }

    // Attempt to send attach all recipients
    foreach($this->to as $to) {
      if (!$this->smtp->Recipient($to[0])) {
        $bad_rcpt[] = $to[0];
        // implement call back function if it exists
        $isSent = 0;
        $this->doCallback($isSent, $to[0], '', '', $this->Subject, $body);
      } else {
        // implement call back function if it exists
        $isSent = 1;
        $this->doCallback($isSent, $to[0], '', '', $this->Subject, $body);
      }
    }
    foreach($this->cc as $cc) {
      if (!$this->smtp->Recipient($cc[0])) {
        $bad_rcpt[] = $cc[0];
        // implement call back function if it exists
        $isSent = 0;
        $this->doCallback($isSent, '', $cc[0], '', $this->Subject, $body);
      } else {
        // implement call back function if it exists
        $isSent = 1;
        $this->doCallback($isSent, '', $cc[0], '', $this->Subject, $body);
      }
    }
    foreach($this->bcc as $bcc) {
      if (!$this->smtp->Recipient($bcc[0])) {
        $bad_rcpt[] = $bcc[0];
        // implement call back function if it exists
        $isSent = 0;
        $this->doCallback($isSent, '', '', $bcc[0], $this->Subject, $body);
      } else {
        // implement call back function if it exists
        $isSent = 1;
        $this->doCallback($isSent, '', '', $bcc[0], $this->Subject, $body);
      }
    }


    if (count($bad_rcpt) > 0 ) { //Create error message for any bad addresses
      $badaddresses = implode(', ', $bad_rcpt);
      throw new phpmailerException($this->Lang('recipients_failed') . $badaddresses);
    }
    if(!$this->smtp->Data($header . $body)) {
      throw new phpmailerException($this->Lang('data_not_accepted'), self::STOP_CRITICAL);
    }
    if($this->SMTPKeepAlive == true) {
      $this->smtp->Reset();
    } else {
        $this->smtp->Quit();
        $this->smtp->Close();
    }
    return true;
  }

  public function SmtpConnect() {
    if(is_null($this->smtp)) {
      $this->smtp = new SMTP;
    }

    $this->smtp->Timeout = $this->Timeout;
    $this->smtp->do_debug = $this->SMTPDebug;
    $hosts = explode(';', $this->Host);
    $index = 0;
    $connection = $this->smtp->Connected();

    // Retry while there is no connection
    try {
      while($index < count($hosts) && !$connection) {
        $hostinfo = array();
        if (preg_match('/^(.+):([0-9]+)$/', $hosts[$index], $hostinfo)) {
          $host = $hostinfo[1];
          $port = $hostinfo[2];
        } else {
          $host = $hosts[$index];
          $port = $this->Port;
        }

        $tls = ($this->SMTPSecure == 'tls');
        $ssl = ($this->SMTPSecure == 'ssl');

        if ($this->smtp->Connect(($ssl ? 'ssl://':'').$host, $port, $this->Timeout)) {

          $hello = ($this->Helo != '' ? $this->Helo : $this->ServerHostname());
          $this->smtp->Hello($hello);

          if ($tls) {
            if (!$this->smtp->StartTLS()) {
              throw new phpmailerException($this->Lang('connect_host'));
            }

            //We must resend HELO after tls negotiation
            $this->smtp->Hello($hello);
          }

          $connection = true;
          if ($this->SMTPAuth) {
            if (!$this->smtp->Authenticate($this->Username, $this->Password, $this->AuthType,
                                           $this->Realm, $this->Workstation)) {
              throw new phpmailerException($this->Lang('authenticate'));
            }
          }
        }
        $index++;
      if (!$connection) {
        throw new phpmailerException($this->Lang('connect_host'));
      }
      }
    } catch (phpmailerException $e) {
      $this->smtp->Reset();
      if ($this->exceptions) {
        throw $e;
      }
    }
    return true;
  }

  /**
   * Closes the active SMTP session if one exists.
   * @return void
   */
  public function SmtpClose() {
    if ($this->smtp !== null) {
      if($this->smtp->Connected()) {
        $this->smtp->Quit();
        $this->smtp->Close();
      }
    }
  }

  function SetLanguage($langcode = 'en', $lang_path = 'language/') {
    //Define full set of translatable strings
    $PHPMAILER_LANG = array(
      'authenticate'         => 'SMTP Error: Could not authenticate.',
      'connect_host'         => 'SMTP Error: Could not connect to SMTP host.',
      'data_not_accepted'    => 'SMTP Error: Data not accepted.',
      'empty_message'        => 'Message body empty',
      'encoding'             => 'Unknown encoding: ',
      'execute'              => 'Could not execute: ',
      'file_access'          => 'Could not access file: ',
      'file_open'            => 'File Error: Could not open file: ',
      'from_failed'          => 'The following From address failed: ',
      'instantiate'          => 'Could not instantiate mail function.',
      'invalid_address'      => 'Invalid address',
      'mailer_not_supported' => ' mailer is not supported.',
      'provide_address'      => 'You must provide at least one recipient email address.',
      'recipients_failed'    => 'SMTP Error: The following recipients failed: ',
      'signing'              => 'Signing Error: ',
      'smtp_connect_failed'  => 'SMTP Connect() failed.',
      'smtp_error'           => 'SMTP server error: ',
      'variable_set'         => 'Cannot set or reset variable: '
    );
    //Overwrite language-specific strings. This way we'll never have missing translations - no more "language string failed to load"!
    $l = true;
    if ($langcode != 'en') { //There is no English translation file
      $l = @include $lang_path.'phpmailer.lang-'.$langcode.'.php';
    }
    $this->language = $PHPMAILER_LANG;
    return ($l == true); //Returns false if language not found
  }


  public function GetTranslations() {
    return $this->language;
  }

  public function AddrAppend($type, $addr) {
    $addr_str = $type . ': ';
    $addresses = array();
    foreach ($addr as $a) {
      $addresses[] = $this->AddrFormat($a);
    }
    $addr_str .= implode(', ', $addresses);
    $addr_str .= $this->LE;

    return $addr_str;
  }

  public function AddrFormat($addr) {
    if (empty($addr[1])) {
      return $this->SecureHeader($addr[0]);
    } else {
      return $this->EncodeHeader($this->SecureHeader($addr[1]), 'phrase') . " <" . $this->SecureHeader($addr[0]) . ">";
    }
  }

  /**
   * Wraps message for use with mailers that do not
   * automatically perform wrapping and for quoted-printable.
   * Original written by philippe.
   * @param string $message The message to wrap
   * @param integer $length The line length to wrap to
   * @param boolean $qp_mode Whether to run in Quoted-Printable mode
   * @access public
   * @return string
   */
  public function WrapText($message, $length, $qp_mode = false) {
    $soft_break = ($qp_mode) ? sprintf(" =%s", $this->LE) : $this->LE;
    // If utf-8 encoding is used, we will need to make sure we don't
    // split multibyte characters when we wrap
    $is_utf8 = (strtolower($this->CharSet) == "utf-8");
    $lelen = strlen($this->LE);
    $crlflen = strlen(self::CRLF);

    $message = $this->FixEOL($message);
    if (substr($message, -$lelen) == $this->LE) {
      $message = substr($message, 0, -$lelen);
    }

    $line = explode($this->LE, $message);   // Magic. We know FixEOL uses $LE
    $message = '';
    for ($i = 0 ;$i < count($line); $i++) {
      $line_part = explode(' ', $line[$i]);
      $buf = '';
      for ($e = 0; $e<count($line_part); $e++) {
        $word = $line_part[$e];
        if ($qp_mode and (strlen($word) > $length)) {
          $space_left = $length - strlen($buf) - $crlflen;
          if ($e != 0) {
            if ($space_left > 20) {
              $len = $space_left;
              if ($is_utf8) {
                $len = $this->UTF8CharBoundary($word, $len);
              } elseif (substr($word, $len - 1, 1) == "=") {
                $len--;
              } elseif (substr($word, $len - 2, 1) == "=") {
                $len -= 2;
              }
              $part = substr($word, 0, $len);
              $word = substr($word, $len);
              $buf .= ' ' . $part;
              $message .= $buf . sprintf("=%s", self::CRLF);
            } else {
              $message .= $buf . $soft_break;
            }
            $buf = '';
          }
          while (strlen($word) > 0) {
            $len = $length;
            if ($is_utf8) {
              $len = $this->UTF8CharBoundary($word, $len);
            } elseif (substr($word, $len - 1, 1) == "=") {
              $len--;
            } elseif (substr($word, $len - 2, 1) == "=") {
              $len -= 2;
            }
            $part = substr($word, 0, $len);
            $word = substr($word, $len);

            if (strlen($word) > 0) {
              $message .= $part . sprintf("=%s", self::CRLF);
            } else {
              $buf = $part;
            }
          }
        } else {
          $buf_o = $buf;
          $buf .= ($e == 0) ? $word : (' ' . $word);

          if (strlen($buf) > $length and $buf_o != '') {
            $message .= $buf_o . $soft_break;
            $buf = $word;
          }
        }
      }
      $message .= $buf . self::CRLF;
    }

    return $message;
  }

  public function UTF8CharBoundary($encodedText, $maxLength) {
    $foundSplitPos = false;
    $lookBack = 3;
    while (!$foundSplitPos) {
      $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
      $encodedCharPos = strpos($lastChunk, "=");
      if ($encodedCharPos !== false) {
        // Found start of encoded character byte within $lookBack block.
        // Check the encoded byte value (the 2 chars after the '=')
        $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
        $dec = hexdec($hex);
        if ($dec < 128) { // Single byte character.
          // If the encoded char was found at pos 0, it will fit
          // otherwise reduce maxLength to start of the encoded char
          $maxLength = ($encodedCharPos == 0) ? $maxLength :
          $maxLength - ($lookBack - $encodedCharPos);
          $foundSplitPos = true;
        } elseif ($dec >= 192) { // First byte of a multi byte character
          // Reduce maxLength to split at start of character
          $maxLength = $maxLength - ($lookBack - $encodedCharPos);
          $foundSplitPos = true;
        } elseif ($dec < 192) { // Middle byte of a multi byte character, look further back
          $lookBack += 3;
        }
      } else {
        // No encoded character found
        $foundSplitPos = true;
      }
    }
    return $maxLength;
  }

  public function SetWordWrap() {
    if($this->WordWrap < 1) {
      return;
    }

    switch($this->message_type) {
      case 'alt':
      case 'alt_inline':
      case 'alt_attach':
      case 'alt_inline_attach':
        $this->AltBody = $this->WrapText($this->AltBody, $this->WordWrap);
        break;
      default:
        $this->Body = $this->WrapText($this->Body, $this->WordWrap);
        break;
    }
  }

  /**
   * Assembles message header.
   * @access public
   * @return string The assembled header
   */
  public function CreateHeader() {
    $result = '';

    // Set the boundaries
    $uniq_id = md5(uniqid(time()));
    $this->boundary[1] = 'b1_' . $uniq_id;
    $this->boundary[2] = 'b2_' . $uniq_id;
    $this->boundary[3] = 'b3_' . $uniq_id;

    if ($this->MessageDate == '') {
      $result .= $this->HeaderLine('Date', self::RFCDate());
    } else {
      $result .= $this->HeaderLine('Date', $this->MessageDate);
    }

    if ($this->ReturnPath) {
      $result .= $this->HeaderLine('Return-Path', trim($this->ReturnPath));
    } elseif ($this->Sender == '') {
      $result .= $this->HeaderLine('Return-Path', trim($this->From));
    } else {
      $result .= $this->HeaderLine('Return-Path', trim($this->Sender));
    }

    // To be created automatically by mail()
    if($this->Mailer != 'mail') {
      if ($this->SingleTo === true) {
        foreach($this->to as $t) {
          $this->SingleToArray[] = $this->AddrFormat($t);
        }
      } else {
        if(count($this->to) > 0) {
          $result .= $this->AddrAppend('To', $this->to);
        } elseif (count($this->cc) == 0) {
          $result .= $this->HeaderLine('To', 'undisclosed-recipients:;');
        }
      }
    }

    $from = array();
    $from[0][0] = trim($this->From);
    $from[0][1] = $this->FromName;
    $result .= $this->AddrAppend('From', $from);

    // sendmail and mail() extract Cc from the header before sending
    if(count($this->cc) > 0) {
      $result .= $this->AddrAppend('Cc', $this->cc);
    }

    // sendmail and mail() extract Bcc from the header before sending
    if((($this->Mailer == 'sendmail') || ($this->Mailer == 'mail')) && (count($this->bcc) > 0)) {
      $result .= $this->AddrAppend('Bcc', $this->bcc);
    }

    if(count($this->ReplyTo) > 0) {
      $result .= $this->AddrAppend('Reply-To', $this->ReplyTo);
    }

    // mail() sets the subject itself
    if($this->Mailer != 'mail') {
      $result .= $this->HeaderLine('Subject', $this->EncodeHeader($this->SecureHeader($this->Subject)));
    }

    if($this->MessageID != '') {
      $result .= $this->HeaderLine('Message-ID', $this->MessageID);
    } else {
      $result .= sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->ServerHostname(), $this->LE);
    }
    $result .= $this->HeaderLine('X-Priority', $this->Priority);
    if ($this->XMailer == '') {
        $result .= $this->HeaderLine('X-Mailer', 'PHPMailer '.$this->Version.' (http://code.google.com/a/apache-extras.org/p/phpmailer/)');
    } else {
      $myXmailer = trim($this->XMailer);
      if ($myXmailer) {
        $result .= $this->HeaderLine('X-Mailer', $myXmailer);
      }
    }

    if($this->ConfirmReadingTo != '') {
      $result .= $this->HeaderLine('Disposition-Notification-To', '<' . trim($this->ConfirmReadingTo) . '>');
    }

    // Add custom headers
    for($index = 0; $index < count($this->CustomHeader); $index++) {
      $result .= $this->HeaderLine(trim($this->CustomHeader[$index][0]), $this->EncodeHeader(trim($this->CustomHeader[$index][1])));
    }
    if (!$this->sign_key_file) {
      $result .= $this->HeaderLine('MIME-Version', '1.0');
      $result .= $this->GetMailMIME();
    }

    return $result;
  }

  /**
   * Returns the message MIME.
   * @access public
   * @return string
   */
  public function GetMailMIME() {
    $result = '';
    switch($this->message_type) {
      case 'inline':
        $result .= $this->HeaderLine('Content-Type', 'multipart/related;');
        $result .= $this->TextLine("\tboundary=\"" . $this->boundary[1] . '"');
        break;
      case 'attach':
      case 'inline_attach':
      case 'alt_attach':
      case 'alt_inline_attach':
        $result .= $this->HeaderLine('Content-Type', 'multipart/mixed;');
        $result .= $this->TextLine("\tboundary=\"" . $this->boundary[1] . '"');
        break;
      case 'alt':
      case 'alt_inline':
        $result .= $this->HeaderLine('Content-Type', 'multipart/alternative;');
        $result .= $this->TextLine("\tboundary=\"" . $this->boundary[1] . '"');
        break;
      default:
        // Catches case 'plain': and case '':
        $result .= $this->HeaderLine('Content-Transfer-Encoding', $this->Encoding);
        $result .= $this->TextLine('Content-Type: '.$this->ContentType.'; charset='.$this->CharSet);
        break;
    }

    if($this->Mailer != 'mail') {
      $result .= $this->LE;
    }

    return $result;
  }

  /**
   * Returns the MIME message (headers and body). Only really valid post PreSend().
   * @access public
   * @return string
   */
  public function GetSentMIMEMessage() {
    return $this->MIMEHeader . $this->mailHeader . self::CRLF . $this->MIMEBody;
  }

  public function CreateBody() {
    $body = '';

    if ($this->sign_key_file) {
      $body .= $this->GetMailMIME().$this->LE;
    }

    $this->SetWordWrap();

    switch($this->message_type) {
      case 'inline':
        $body .= $this->GetBoundary($this->boundary[1], '', '', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->AttachAll("inline", $this->boundary[1]);
        break;
      case 'attach':
        $body .= $this->GetBoundary($this->boundary[1], '', '', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->AttachAll("attachment", $this->boundary[1]);
        break;
      case 'inline_attach':
        $body .= $this->TextLine("--" . $this->boundary[1]);
        $body .= $this->HeaderLine('Content-Type', 'multipart/related;');
        $body .= $this->TextLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->GetBoundary($this->boundary[2], '', '', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->AttachAll("inline", $this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->AttachAll("attachment", $this->boundary[1]);
        break;
      case 'alt':
        $body .= $this->GetBoundary($this->boundary[1], '', 'text/plain', '');
        $body .= $this->EncodeString($this->AltBody, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->GetBoundary($this->boundary[1], '', 'text/html', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->EndBoundary($this->boundary[1]);
        break;
      case 'alt_inline':
        $body .= $this->GetBoundary($this->boundary[1], '', 'text/plain', '');
        $body .= $this->EncodeString($this->AltBody, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->TextLine("--" . $this->boundary[1]);
        $body .= $this->HeaderLine('Content-Type', 'multipart/related;');
        $body .= $this->TextLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->GetBoundary($this->boundary[2], '', 'text/html', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->AttachAll("inline", $this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->EndBoundary($this->boundary[1]);
        break;
      case 'alt_attach':
        $body .= $this->TextLine("--" . $this->boundary[1]);
        $body .= $this->HeaderLine('Content-Type', 'multipart/alternative;');
        $body .= $this->TextLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->GetBoundary($this->boundary[2], '', 'text/plain', '');
        $body .= $this->EncodeString($this->AltBody, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->GetBoundary($this->boundary[2], '', 'text/html', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->EndBoundary($this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->AttachAll("attachment", $this->boundary[1]);
        break;
      case 'alt_inline_attach':
        $body .= $this->TextLine("--" . $this->boundary[1]);
        $body .= $this->HeaderLine('Content-Type', 'multipart/alternative;');
        $body .= $this->TextLine("\tboundary=\"" . $this->boundary[2] . '"');
        $body .= $this->LE;
        $body .= $this->GetBoundary($this->boundary[2], '', 'text/plain', '');
        $body .= $this->EncodeString($this->AltBody, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->TextLine("--" . $this->boundary[2]);
        $body .= $this->HeaderLine('Content-Type', 'multipart/related;');
        $body .= $this->TextLine("\tboundary=\"" . $this->boundary[3] . '"');
        $body .= $this->LE;
        $body .= $this->GetBoundary($this->boundary[3], '', 'text/html', '');
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        $body .= $this->LE.$this->LE;
        $body .= $this->AttachAll("inline", $this->boundary[3]);
        $body .= $this->LE;
        $body .= $this->EndBoundary($this->boundary[2]);
        $body .= $this->LE;
        $body .= $this->AttachAll("attachment", $this->boundary[1]);
        break;
      default:
        // catch case 'plain' and case ''
        $body .= $this->EncodeString($this->Body, $this->Encoding);
        break;
    }

    if ($this->IsError()) {
      $body = '';
    } elseif ($this->sign_key_file) {
      try {
        $file = tempnam('', 'mail');
        file_put_contents($file, $body); //TODO check this worked
        $signed = tempnam("", "signed");
        if (@openssl_pkcs7_sign($file, $signed, "file://".$this->sign_cert_file, array("file://".$this->sign_key_file, $this->sign_key_pass), NULL)) {
          @unlink($file);
          $body = file_get_contents($signed);
          @unlink($signed);
        } else {
          @unlink($file);
          @unlink($signed);
          throw new phpmailerException($this->Lang("signing").openssl_error_string());
        }
      } catch (phpmailerException $e) {
        $body = '';
        if ($this->exceptions) {
          throw $e;
        }
      }
    }

    return $body;
  }

  protected function GetBoundary($boundary, $charSet, $contentType, $encoding) {
    $result = '';
    if($charSet == '') {
      $charSet = $this->CharSet;
    }
    if($contentType == '') {
      $contentType = $this->ContentType;
    }
    if($encoding == '') {
      $encoding = $this->Encoding;
    }
    $result .= $this->TextLine('--' . $boundary);
    $result .= sprintf("Content-Type: %s; charset=%s", $contentType, $charSet);
    $result .= $this->LE;
    $result .= $this->HeaderLine('Content-Transfer-Encoding', $encoding);
    $result .= $this->LE;

    return $result;
  }

  /**
   * Returns the end of a message boundary.
   * @access protected
   * @param string $boundary
   * @return string
   */
  protected function EndBoundary($boundary) {
    return $this->LE . '--' . $boundary . '--' . $this->LE;
  }

  /**
   * Sets the message type.
   * @access protected
   * @return void
   */
  protected function SetMessageType() {
    $this->message_type = array();
    if($this->AlternativeExists()) $this->message_type[] = "alt";
    if($this->InlineImageExists()) $this->message_type[] = "inline";
    if($this->AttachmentExists()) $this->message_type[] = "attach";
    $this->message_type = implode("_", $this->message_type);
    if($this->message_type == "") $this->message_type = "plain";
  }

  /**
   *  Returns a formatted header line.
   * @access public
   * @param string $name
   * @param string $value
   * @return string
   */
  public function HeaderLine($name, $value) {
    return $name . ': ' . $value . $this->LE;
  }

  /**
   * Returns a formatted mail line.
   * @access public
   * @param string $value
   * @return string
   */
  public function TextLine($value) {
    return $value . $this->LE;
  }

  public function AddAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
    try {
      if ( !@is_file($path) ) {
        throw new phpmailerException($this->Lang('file_access') . $path, self::STOP_CONTINUE);
      }
      $filename = basename($path);
      if ( $name == '' ) {
        $name = $filename;
      }

      $this->attachment[] = array(
        0 => $path,
        1 => $filename,
        2 => $name,
        3 => $encoding,
        4 => $type,
        5 => false,  // isStringAttachment
        6 => 'attachment',
        7 => 0
      );

    } catch (phpmailerException $e) {
      $this->SetError($e->getMessage());
      if ($this->exceptions) {
        throw $e;
      }
      if ($this->SMTPDebug) {
        $this->edebug($e->getMessage()."\n");
      }
      if ( $e->getCode() == self::STOP_CRITICAL ) {
        return false;
      }
    }
    return true;
  }

  /**
  * Return the current array of attachments
  * @return array
  */
  public function GetAttachments() {
    return $this->attachment;
  }

  /**
   * Attaches all fs, string, and binary attachments to the message.
   * Returns an empty string on failure.
   * @access protected
   * @param string $disposition_type
   * @param string $boundary
   * @return string
   */
  protected function AttachAll($disposition_type, $boundary) {
    // Return text of body
    $mime = array();
    $cidUniq = array();
    $incl = array();

    // Add all attachments
    foreach ($this->attachment as $attachment) {
      // CHECK IF IT IS A VALID DISPOSITION_FILTER
      if($attachment[6] == $disposition_type) {
        // Check for string attachment
        $string = '';
        $path = '';
        $bString = $attachment[5];
        if ($bString) {
          $string = $attachment[0];
        } else {
          $path = $attachment[0];
        }

        $inclhash = md5(serialize($attachment));
        if (in_array($inclhash, $incl)) { continue; }
        $incl[]      = $inclhash;
        $filename    = $attachment[1];
        $name        = $attachment[2];
        $encoding    = $attachment[3];
        $type        = $attachment[4];
        $disposition = $attachment[6];
        $cid         = $attachment[7];
        if ( $disposition == 'inline' && isset($cidUniq[$cid]) ) { continue; }
        $cidUniq[$cid] = true;

        $mime[] = sprintf("--%s%s", $boundary, $this->LE);
        $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $this->EncodeHeader($this->SecureHeader($name)), $this->LE);
        $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, $this->LE);

        if($disposition == 'inline') {
          $mime[] = sprintf("Content-ID: <%s>%s", $cid, $this->LE);
        }

        $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", $disposition, $this->EncodeHeader($this->SecureHeader($name)), $this->LE.$this->LE);

        // Encode as string attachment
        if($bString) {
          $mime[] = $this->EncodeString($string, $encoding);
          if($this->IsError()) {
            return '';
          }
          $mime[] = $this->LE.$this->LE;
        } else {
          $mime[] = $this->EncodeFile($path, $encoding);
          if($this->IsError()) {
            return '';
          }
          $mime[] = $this->LE.$this->LE;
        }
      }
    }

    $mime[] = sprintf("--%s--%s", $boundary, $this->LE);

    return implode("", $mime);
  }

  protected function EncodeFile($path, $encoding = 'base64') {
    try {
      if (!is_readable($path)) {
        throw new phpmailerException($this->Lang('file_open') . $path, self::STOP_CONTINUE);
      }
      //  if (!function_exists('get_magic_quotes')) {
      //    function get_magic_quotes() {
      //      return false;
      //    }
      //  }
      $magic_quotes = get_magic_quotes_runtime();
      if ($magic_quotes) {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
          set_magic_quotes_runtime(0);
        } else {
          ini_set('magic_quotes_runtime', 0); 
        }
      }
      $file_buffer  = file_get_contents($path);
      $file_buffer  = $this->EncodeString($file_buffer, $encoding);
      if ($magic_quotes) {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
          set_magic_quotes_runtime($magic_quotes);
        } else {
          ini_set('magic_quotes_runtime', $magic_quotes); 
        }
      }
      return $file_buffer;
    } catch (Exception $e) {
      $this->SetError($e->getMessage());
      return '';
    }
  }

  public function EncodeString($str, $encoding = 'base64') {
    $encoded = '';
    switch(strtolower($encoding)) {
      case 'base64':
        $encoded = chunk_split(base64_encode($str), 76, $this->LE);
        break;
      case '7bit':
      case '8bit':
        $encoded = $this->FixEOL($str);
        //Make sure it ends with a line break
        if (substr($encoded, -(strlen($this->LE))) != $this->LE)
          $encoded .= $this->LE;
        break;
      case 'binary':
        $encoded = $str;
        break;
      case 'quoted-printable':
        $encoded = $this->EncodeQP($str);
        break;
      default:
        $this->SetError($this->Lang('encoding') . $encoding);
        break;
    }
    return $encoded;
  }

  public function EncodeHeader($str, $position = 'text') {
    $x = 0;

    switch (strtolower($position)) {
      case 'phrase':
        if (!preg_match('/[\200-\377]/', $str)) {
          // Can't use addslashes as we don't know what value has magic_quotes_sybase
          $encoded = addcslashes($str, "\0..\37\177\\\"");
          if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
            return ($encoded);
          } else {
            return ("\"$encoded\"");
          }
        }
        $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
        break;
      case 'comment':
        $x = preg_match_all('/[()"]/', $str, $matches);
        // Fall-through
      case 'text':
      default:
        $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
        break;
    }

    if ($x == 0) {
      return ($str);
    }

    $maxlen = 75 - 7 - strlen($this->CharSet);
    // Try to select the encoding which should produce the shortest output
    if (strlen($str)/3 < $x) {
      $encoding = 'B';
      if (function_exists('mb_strlen') && $this->HasMultiBytes($str)) {
        // Use a custom function which correctly encodes and wraps long
        // multibyte strings without breaking lines within a character
        $encoded = $this->Base64EncodeWrapMB($str, "\n");
      } else {
        $encoded = base64_encode($str);
        $maxlen -= $maxlen % 4;
        $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
      }
    } else {
      $encoding = 'Q';
      $encoded = $this->EncodeQ($str, $position);
      $encoded = $this->WrapText($encoded, $maxlen, true);
      $encoded = str_replace('='.self::CRLF, "\n", trim($encoded));
    }

    $encoded = preg_replace('/^(.*)$/m', " =?".$this->CharSet."?$encoding?\\1?=", $encoded);
    $encoded = trim(str_replace("\n", $this->LE, $encoded));

    return $encoded;
  }

  public function HasMultiBytes($str) {
    if (function_exists('mb_strlen')) {
      return (strlen($str) > mb_strlen($str, $this->CharSet));
    } else { // Assume no multibytes (we can't handle without mbstring functions anyway)
      return false;
    }
  }

  public function Base64EncodeWrapMB($str, $lf=null) {
    $start = "=?".$this->CharSet."?B?";
    $end = "?=";
    $encoded = "";
    if ($lf === null) {
      $lf = $this->LE;
    }

    $mb_length = mb_strlen($str, $this->CharSet);
    // Each line must have length <= 75, including $start and $end
    $length = 75 - strlen($start) - strlen($end);
    // Average multi-byte ratio
    $ratio = $mb_length / strlen($str);
    // Base64 has a 4:3 ratio
    $offset = $avgLength = floor($length * $ratio * .75);

    for ($i = 0; $i < $mb_length; $i += $offset) {
      $lookBack = 0;

      do {
        $offset = $avgLength - $lookBack;
        $chunk = mb_substr($str, $i, $offset, $this->CharSet);
        $chunk = base64_encode($chunk);
        $lookBack++;
      }
      while (strlen($chunk) > $length);

      $encoded .= $chunk . $lf;
    }

    // Chomp the last linefeed
    $encoded = substr($encoded, 0, -strlen($lf));
    return $encoded;
  }

  public function EncodeQPphp( $input = '', $line_max = 76, $space_conv = false) {
    $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
    $lines = preg_split('/(?:\r\n|\r|\n)/', $input);
    $eol = "\r\n";
    $escape = '=';
    $output = '';
    while( list(, $line) = each($lines) ) {
      $linlen = strlen($line);
      $newline = '';
      for($i = 0; $i < $linlen; $i++) {
        $c = substr( $line, $i, 1 );
        $dec = ord( $c );
        if ( ( $i == 0 ) && ( $dec == 46 ) ) { // convert first point in the line into =2E
          $c = '=2E';
        }
        if ( $dec == 32 ) {
          if ( $i == ( $linlen - 1 ) ) { // convert space at eol only
            $c = '=20';
          } else if ( $space_conv ) {
            $c = '=20';
          }
        } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
          $h2 = (integer)floor($dec/16);
          $h1 = (integer)floor($dec%16);
          $c = $escape.$hex[$h2].$hex[$h1];
        }
        if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
          $output .= $newline.$escape.$eol; //  soft line break; " =\r\n" is okay
          $newline = '';
          // check if newline first character will be point or not
          if ( $dec == 46 ) {
            $c = '=2E';
          }
        }
        $newline .= $c;
      } // end of for
      $output .= $newline.$eol;
    } // end of while
    return $output;
  }

  public function EncodeQP($string, $line_max = 76, $space_conv = false) {
    if (function_exists('quoted_printable_encode')) { //Use native function if it's available (>= PHP5.3)
      return quoted_printable_encode($string);
    }
    $filters = stream_get_filters();
    if (!in_array('convert.*', $filters)) { //Got convert stream filter?
      return $this->EncodeQPphp($string, $line_max, $space_conv); //Fall back to old implementation
    }
    $fp = fopen('php://temp/', 'r+');
    $string = preg_replace('/\r\n?/', $this->LE, $string); //Normalise line breaks
    $params = array('line-length' => $line_max, 'line-break-chars' => $this->LE);
    $s = stream_filter_append($fp, 'convert.quoted-printable-encode', STREAM_FILTER_READ, $params);
    fputs($fp, $string);
    rewind($fp);
    $out = stream_get_contents($fp);
    stream_filter_remove($s);
    $out = preg_replace('/^\./m', '=2E', $out); //Encode . if it is first char on a line, workaround for bug in Exchange
    fclose($fp);
    return $out;
  }

  /**
   * Encode string to q encoding.
   * @link http://tools.ietf.org/html/rfc2047
   * @param string $str the text to encode
   * @param string $position Where the text is going to be used, see the RFC for what that means
   * @access public
   * @return string
   */
  public function EncodeQ($str, $position = 'text') {
    //There should not be any EOL in the string
        $pattern="";
    $encoded = str_replace(array("\r", "\n"), '', $str);
    switch (strtolower($position)) {
      case 'phrase':
        $pattern = '^A-Za-z0-9!*+\/ -';
        break;

      case 'comment':
        $pattern = '\(\)"';
        //note that we dont break here!
        //for this reason we build the $pattern withoud including delimiters and []

      case 'text':
      default:
        //Replace every high ascii, control =, ? and _ characters
        //We put \075 (=) as first value to make sure it's the first one in being converted, preventing double encode
        $pattern = '\075\000-\011\013\014\016-\037\077\137\177-\377' . $pattern;
        break;
    }
    
    if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
      foreach (array_unique($matches[0]) as $char) {
        $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
      }
    }
    
    //Replace every spaces to _ (more readable than =20)
    return str_replace(' ', '_', $encoded);
}


  /**
   * Adds a string or binary attachment (non-filesystem) to the list.
   * This method can be used to attach ascii or binary data,
   * such as a BLOB record from a database.
   * @param string $string String attachment data.
   * @param string $filename Name of the attachment.
   * @param string $encoding File encoding (see $Encoding).
   * @param string $type File extension (MIME) type.
   * @return void
   */
  public function AddStringAttachment($string, $filename, $encoding = 'base64', $type = 'application/octet-stream') {
    // Append to $attachment array
    $this->attachment[] = array(
      0 => $string,
      1 => $filename,
      2 => basename($filename),
      3 => $encoding,
      4 => $type,
      5 => true,  // isStringAttachment
      6 => 'attachment',
      7 => 0
    );
  }

  public function AddEmbeddedImage($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {

    if ( !@is_file($path) ) {
      $this->SetError($this->Lang('file_access') . $path);
      return false;
    }

    $filename = basename($path);
    if ( $name == '' ) {
      $name = $filename;
    }

    // Append to $attachment array
    $this->attachment[] = array(
      0 => $path,
      1 => $filename,
      2 => $name,
      3 => $encoding,
      4 => $type,
      5 => false,  // isStringAttachment
      6 => 'inline',
      7 => $cid
    );

    return true;
  }

  public function AddStringEmbeddedImage($string, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream') {
    // Append to $attachment array
    $this->attachment[] = array(
      0 => $string,
      1 => $name,
      2 => $name,
      3 => $encoding,
      4 => $type,
      5 => true,  // isStringAttachment
      6 => 'inline',
      7 => $cid
    );
  }

  public function InlineImageExists() {
    foreach($this->attachment as $attachment) {
      if ($attachment[6] == 'inline') {
        return true;
      }
    }
    return false;
  }

  public function AttachmentExists() {
    foreach($this->attachment as $attachment) {
      if ($attachment[6] == 'attachment') {
        return true;
      }
    }
    return false;
  }

  public function AlternativeExists() {
    return !empty($this->AltBody);
  }

  public function ClearAddresses() {
    foreach($this->to as $to) {
      unset($this->all_recipients[strtolower($to[0])]);
    }
    $this->to = array();
  }

  /**
   * Clears all recipients assigned in the CC array.  Returns void.
   * @return void
   */
  public function ClearCCs() {
    foreach($this->cc as $cc) {
      unset($this->all_recipients[strtolower($cc[0])]);
    }
    $this->cc = array();
  }

  /**
   * Clears all recipients assigned in the BCC array.  Returns void.
   * @return void
   */
  public function ClearBCCs() {
    foreach($this->bcc as $bcc) {
      unset($this->all_recipients[strtolower($bcc[0])]);
    }
    $this->bcc = array();
  }

  /**
   * Clears all recipients assigned in the ReplyTo array.  Returns void.
   * @return void
   */
  public function ClearReplyTos() {
    $this->ReplyTo = array();
  }

  public function ClearAllRecipients() {
    $this->to = array();
    $this->cc = array();
    $this->bcc = array();
    $this->all_recipients = array();
  }

  public function ClearAttachments() {
    $this->attachment = array();
  }

  public function ClearCustomHeaders() {
    $this->CustomHeader = array();
  }

  protected function SetError($msg) {
    $this->error_count++;
    if ($this->Mailer == 'smtp' and !is_null($this->smtp)) {
      $lasterror = $this->smtp->getError();
      if (!empty($lasterror) and array_key_exists('smtp_msg', $lasterror)) {
        $msg .= '<p>' . $this->Lang('smtp_error') . $lasterror['smtp_msg'] . "</p>\n";
      }
    }
    $this->ErrorInfo = $msg;
  }

  public static function RFCDate() {
    $tz = date('Z');
    $tzs = ($tz < 0) ? '-' : '+';
    $tz = abs($tz);
    $tz = (int)($tz/3600)*100 + ($tz%3600)/60;
    $result = sprintf("%s %s%04d", date('D, j M Y H:i:s'), $tzs, $tz);

    return $result;
  }

  protected function ServerHostname() {
    if (!empty($this->Hostname)) {
      $result = $this->Hostname;
    } elseif (isset($_SERVER['SERVER_NAME'])) {
      $result = $_SERVER['SERVER_NAME'];
    } else {
      $result = 'localhost.localdomain';
    }

    return $result;
  }

  protected function Lang($key) {
    if(count($this->language) < 1) {
      $this->SetLanguage('en');
    }

    if(isset($this->language[$key])) {
      return $this->language[$key];
    } else {
      return 'Language string failed to load: ' . $key;
    }
  }

  public function IsError() {
    return ($this->error_count > 0);
  }

  public function FixEOL($str) {
        $nstr = str_replace(array("\r\n", "\r"), "\n", $str);
        if ($this->LE !== "\n") {
                $nstr = str_replace("\n", $this->LE, $nstr);
        }
    return  $nstr;
  }

  public function AddCustomHeader($name, $value=null) {
        if ($value === null) {
                $this->CustomHeader[] = explode(':', $name, 2);
        } else {
                $this->CustomHeader[] = array($name, $value);
        }
  }

  public function MsgHTML($message, $basedir = '') {
    preg_match_all("/(src|background)=[\"'](.*)[\"']/Ui", $message, $images);
    if(isset($images[2])) {
      foreach($images[2] as $i => $url) {
        if (!preg_match('#^[A-z]+://#', $url)) {
          $filename = basename($url);
          $directory = dirname($url);
          if ($directory == '.') {
            $directory = '';
          }
          $cid = 'cid:' . md5($url);
          $ext = pathinfo($filename, PATHINFO_EXTENSION);
          $mimeType  = self::_mime_types($ext);
          if ( strlen($basedir) > 1 && substr($basedir, -1) != '/') { $basedir .= '/'; }
          if ( strlen($directory) > 1 && substr($directory, -1) != '/') { $directory .= '/'; }
          if ( $this->AddEmbeddedImage($basedir.$directory.$filename, md5($url), $filename, 'base64', $mimeType) ) {
            $message = preg_replace("/".$images[1][$i]."=[\"']".preg_quote($url, '/')."[\"']/Ui", $images[1][$i]."=\"".$cid."\"", $message);
          }
        }
      }
    }
    $this->IsHTML(true);
    $this->Body = $message;
    if (empty($this->AltBody)) {
        $textMsg = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $message)));
        if (!empty($textMsg)) {
            $this->AltBody = html_entity_decode($textMsg, ENT_QUOTES, $this->CharSet);
        }
    }
    if (empty($this->AltBody)) {
      $this->AltBody = 'To view this email message, open it in a program that understands HTML!' . "\n\n";
    }
    return $message;
  }

  /**
   * Gets the MIME type of the embedded or inline image
   * @param string $ext File extension
   * @access public
   * @return string MIME type of ext
   * @static
   */
  public static function _mime_types($ext = '') {
    $mimes = array(
      'xl'    =>  'application/excel',
      'hqx'   =>  'application/mac-binhex40',
      'cpt'   =>  'application/mac-compactpro',
      'bin'   =>  'application/macbinary',
      'doc'   =>  'application/msword',
      'word'  =>  'application/msword',
      'class' =>  'application/octet-stream',
      'dll'   =>  'application/octet-stream',
      'dms'   =>  'application/octet-stream',
      'exe'   =>  'application/octet-stream',
      'lha'   =>  'application/octet-stream',
      'lzh'   =>  'application/octet-stream',
      'psd'   =>  'application/octet-stream',
      'sea'   =>  'application/octet-stream',
      'so'    =>  'application/octet-stream',
      'oda'   =>  'application/oda',
      'pdf'   =>  'application/pdf',
      'ai'    =>  'application/postscript',
      'eps'   =>  'application/postscript',
      'ps'    =>  'application/postscript',
      'smi'   =>  'application/smil',
      'smil'  =>  'application/smil',
      'mif'   =>  'application/vnd.mif',
      'xls'   =>  'application/vnd.ms-excel',
      'ppt'   =>  'application/vnd.ms-powerpoint',
      'wbxml' =>  'application/vnd.wap.wbxml',
      'wmlc'  =>  'application/vnd.wap.wmlc',
      'dcr'   =>  'application/x-director',
      'dir'   =>  'application/x-director',
      'dxr'   =>  'application/x-director',
      'dvi'   =>  'application/x-dvi',
      'gtar'  =>  'application/x-gtar',
      'php3'  =>  'application/x-httpd-php',
      'php4'  =>  'application/x-httpd-php',
      'php'   =>  'application/x-httpd-php',
      'phtml' =>  'application/x-httpd-php',
      'phps'  =>  'application/x-httpd-php-source',
      'js'    =>  'application/x-javascript',
      'swf'   =>  'application/x-shockwave-flash',
      'sit'   =>  'application/x-stuffit',
      'tar'   =>  'application/x-tar',
      'tgz'   =>  'application/x-tar',
      'xht'   =>  'application/xhtml+xml',
      'xhtml' =>  'application/xhtml+xml',
      'zip'   =>  'application/zip',
      'mid'   =>  'audio/midi',
      'midi'  =>  'audio/midi',
      'mp2'   =>  'audio/mpeg',
      'mp3'   =>  'audio/mpeg',
      'mpga'  =>  'audio/mpeg',
      'aif'   =>  'audio/x-aiff',
      'aifc'  =>  'audio/x-aiff',
      'aiff'  =>  'audio/x-aiff',
      'ram'   =>  'audio/x-pn-realaudio',
      'rm'    =>  'audio/x-pn-realaudio',
      'rpm'   =>  'audio/x-pn-realaudio-plugin',
      'ra'    =>  'audio/x-realaudio',
      'wav'   =>  'audio/x-wav',
      'bmp'   =>  'image/bmp',
      'gif'   =>  'image/gif',
      'jpeg'  =>  'image/jpeg',
      'jpe'   =>  'image/jpeg',
      'jpg'   =>  'image/jpeg',
      'png'   =>  'image/png',
      'tiff'  =>  'image/tiff',
      'tif'   =>  'image/tiff',
      'eml'   =>  'message/rfc822',
      'css'   =>  'text/css',
      'html'  =>  'text/html',
      'htm'   =>  'text/html',
      'shtml' =>  'text/html',
      'log'   =>  'text/plain',
      'text'  =>  'text/plain',
      'txt'   =>  'text/plain',
      'rtx'   =>  'text/richtext',
      'rtf'   =>  'text/rtf',
      'xml'   =>  'text/xml',
      'xsl'   =>  'text/xml',
      'mpeg'  =>  'video/mpeg',
      'mpe'   =>  'video/mpeg',
      'mpg'   =>  'video/mpeg',
      'mov'   =>  'video/quicktime',
      'qt'    =>  'video/quicktime',
      'rv'    =>  'video/vnd.rn-realvideo',
      'avi'   =>  'video/x-msvideo',
      'movie' =>  'video/x-sgi-movie'
    );
    return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
  }

  public function set($name, $value = '') {
    try {
      if (isset($this->$name) ) {
        $this->$name = $value;
      } else {
        throw new phpmailerException($this->Lang('variable_set') . $name, self::STOP_CRITICAL);
      }
    } catch (Exception $e) {
      $this->SetError($e->getMessage());
      if ($e->getCode() == self::STOP_CRITICAL) {
        return false;
      }
    }
    return true;
  }

  public function SecureHeader($str) {
    return trim(str_replace(array("\r", "\n"), '', $str));
  }

  public function Sign($cert_filename, $key_filename, $key_pass) {
    $this->sign_cert_file = $cert_filename;
    $this->sign_key_file = $key_filename;
    $this->sign_key_pass = $key_pass;
  }

  public function DKIM_QP($txt) {
    $line = '';
    for ($i = 0; $i < strlen($txt); $i++) {
      $ord = ord($txt[$i]);
      if ( ((0x21 <= $ord) && ($ord <= 0x3A)) || $ord == 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E)) ) {
        $line .= $txt[$i];
      } else {
        $line .= "=".sprintf("%02X", $ord);
      }
    }
    return $line;
  }

  public function DKIM_Sign($s) {
    $privKeyStr = file_get_contents($this->DKIM_private);
    if ($this->DKIM_passphrase != '') {
      $privKey = openssl_pkey_get_private($privKeyStr, $this->DKIM_passphrase);
    } else {
      $privKey = $privKeyStr;
    }
    if (openssl_sign($s, $signature, $privKey)) {
      return base64_encode($signature);
    }
    return '';
  }

  /**
   * Generate DKIM Canonicalization Header
   *
   * @access public
   * @param string $s Header
   * @return string
   */
  public function DKIM_HeaderC($s) {
    $s = preg_replace("/\r\n\s+/", " ", $s);
    $lines = explode("\r\n", $s);
    foreach ($lines as $key => $line) {
      list($heading, $value) = explode(":", $line, 2);
      $heading = strtolower($heading);
      $value = preg_replace("/\s+/", " ", $value) ; // Compress useless spaces
      $lines[$key] = $heading.":".trim($value) ; // Don't forget to remove WSP around the value
    }
    $s = implode("\r\n", $lines);
    return $s;
  }

  public function DKIM_BodyC($body) {
    if ($body == '') return "\r\n";
    // stabilize line endings
    $body = str_replace("\r\n", "\n", $body);
    $body = str_replace("\n", "\r\n", $body);
    // END stabilize line endings
    while (substr($body, strlen($body) - 4, 4) == "\r\n\r\n") {
      $body = substr($body, 0, strlen($body) - 2);
    }
    return $body;
  }

  public function DKIM_Add($headers_line, $subject, $body) {
    $DKIMsignatureType    = 'rsa-sha1'; // Signature & hash algorithms
    $DKIMcanonicalization = 'relaxed/simple'; // Canonicalization of header/body
    $DKIMquery            = 'dns/txt'; // Query method
    $DKIMtime             = time() ; // Signature Timestamp = seconds since 00:00:00 - Jan 1, 1970 (UTC time zone)
    $subject_header       = "Subject: $subject";
    $headers              = explode($this->LE, $headers_line);
        $from_header          = "";
        $to_header            = "";
    foreach($headers as $header) {
      if (strpos($header, 'From:') === 0) {
        $from_header = $header;
      } elseif (strpos($header, 'To:') === 0) {
        $to_header = $header;
      }
    }
    $from     = str_replace('|', '=7C', $this->DKIM_QP($from_header));
    $to       = str_replace('|', '=7C', $this->DKIM_QP($to_header));
    $subject  = str_replace('|', '=7C', $this->DKIM_QP($subject_header)) ; // Copied header fields (dkim-quoted-printable
    $body     = $this->DKIM_BodyC($body);
    $DKIMlen  = strlen($body) ; // Length of body
    $DKIMb64  = base64_encode(pack("H*", sha1($body))) ; // Base64 of packed binary SHA-1 hash of body
    $ident    = ($this->DKIM_identity == '')? '' : " i=" . $this->DKIM_identity . ";";
    $dkimhdrs = "DKIM-Signature: v=1; a=" . $DKIMsignatureType . "; q=" . $DKIMquery . "; l=" . $DKIMlen . "; s=" . $this->DKIM_selector . ";\r\n".
                "\tt=" . $DKIMtime . "; c=" . $DKIMcanonicalization . ";\r\n".
                "\th=From:To:Subject;\r\n".
                "\td=" . $this->DKIM_domain . ";" . $ident . "\r\n".
                "\tz=$from\r\n".
                "\t|$to\r\n".
                "\t|$subject;\r\n".
                "\tbh=" . $DKIMb64 . ";\r\n".
                "\tb=";
    $toSign   = $this->DKIM_HeaderC($from_header . "\r\n" . $to_header . "\r\n" . $subject_header . "\r\n" . $dkimhdrs);
    $signed   = $this->DKIM_Sign($toSign);
    return "X-PHPMAILER-DKIM: code.google.com/a/apache-extras.org/p/phpmailer/\r\n".$dkimhdrs.$signed."\r\n";
  }

  protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from=null) {
    if (!empty($this->action_function) && is_callable($this->action_function)) {
      $params = array($isSent, $to, $cc, $bcc, $subject, $body, $from);
      call_user_func_array($this->action_function, $params);
    }
  }
}





class phpmailerException extends Exception {
  public function errorMessage() {
    $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
    return $errorMsg;
  }
}




class SMTP {
  public $SMTP_PORT = 25;
  public $CRLF = "\r\n";
  public $do_debug;
  public $do_verp = false;
  public $Version         = '5.1';

  private $smtp_conn;
  private $error;
  private $helo_rply;

  public function __construct() {
    $this->smtp_conn = 0;
    $this->error = null;
    $this->helo_rply = null;

    $this->do_debug = 0;
  }

  public function Connect($host, $port = 0, $tval = 30) {
    $this->error = null;
    if($this->connected()) {
      $this->error = array("error" => "Already connected to a server");
      return false;
    }

    if(empty($port)) {
      $port = $this->SMTP_PORT;
    }
    $this->smtp_conn = @fsockopen($host,  $port, $errno, $errstr, $tval);
    if(empty($this->smtp_conn)) {
      $this->error = array("error" => "Failed to connect to server",
                           "errno" => $errno,
                           "errstr" => $errstr);
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": $errstr ($errno)" . $this->CRLF . '<br />';
      }
      return false;
    }
    if(substr(PHP_OS, 0, 3) != "WIN")
     socket_set_timeout($this->smtp_conn, $tval, 0);

    $announce = $this->get_lines();

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $announce . $this->CRLF . '<br />';
    }

    return true;
  }

  public function StartTLS() {
    $this->error = null;

    if(!$this->connected()) {
      $this->error = array("error" => "Called StartTLS() without being connected");
      return false;
    }

    fputs($this->smtp_conn,"STARTTLS" . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 220) {
      $this->error =
         array("error"     => "STARTTLS not accepted from server",
               "smtp_code" => $code,
               "smtp_msg"  => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    if(!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
      return false;
    }

    return true;
  }

  public function Authenticate($username, $password) {
    fputs($this->smtp_conn,"AUTH LOGIN" . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($code != 334) {
      $this->error =
        array("error" => "AUTH not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    fputs($this->smtp_conn, base64_encode($username) . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($code != 334) {
      $this->error =
        array("error" => "Username not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    fputs($this->smtp_conn, base64_encode($password) . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($code != 235) {
      $this->error =
        array("error" => "Password not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    return true;
  }

  public function Connected() {
    if(!empty($this->smtp_conn)) {
      $sock_status = socket_get_status($this->smtp_conn);
      if($sock_status["eof"]) {
        if($this->do_debug >= 1) {
            echo "SMTP -> NOTICE:" . $this->CRLF . "EOF caught while checking if connected";
        }
        $this->Close();
        return false;
      }
      return true;
    }
    return false;
  }

  public function Close() {
    $this->error = null;
    $this->helo_rply = null;
    if(!empty($this->smtp_conn)) {
      fclose($this->smtp_conn);
      $this->smtp_conn = 0;
    }
  }

  public function Data($msg_data) {
    $this->error = null;

    if(!$this->connected()) {
      $this->error = array(
              "error" => "Called Data() without being connected");
      return false;
    }

    fputs($this->smtp_conn,"DATA" . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 354) {
      $this->error =
        array("error" => "DATA command not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    $msg_data = str_replace("\r\n","\n",$msg_data);
    $msg_data = str_replace("\r","\n",$msg_data);
    $lines = explode("\n",$msg_data);

    $field = substr($lines[0],0,strpos($lines[0],":"));
    $in_headers = false;
    if(!empty($field) && !strstr($field," ")) {
      $in_headers = true;
    }

    $max_line_length = 998;

    while(list(,$line) = @each($lines)) {
      $lines_out = null;
      if($line == "" && $in_headers) {
        $in_headers = false;
      }
      while(strlen($line) > $max_line_length) {
        $pos = strrpos(substr($line,0,$max_line_length)," ");

        if(!$pos) {
          $pos = $max_line_length - 1;
          $lines_out[] = substr($line,0,$pos);
          $line = substr($line,$pos);
        } else {
          $lines_out[] = substr($line,0,$pos);
          $line = substr($line,$pos + 1);
        }

        if($in_headers) {
          $line = "\t" . $line;
        }
      }
      $lines_out[] = $line;

      while(list(,$line_out) = @each($lines_out)) {
        if(strlen($line_out) > 0)
        {
          if(substr($line_out, 0, 1) == ".") {
            $line_out = "." . $line_out;
          }
        }
        fputs($this->smtp_conn,$line_out . $this->CRLF);
      }
    }

    fputs($this->smtp_conn, $this->CRLF . "." . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 250) {
      $this->error =
        array("error" => "DATA not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }
    return true;
  }


  public function Hello($host = '') {
    $this->error = null;

    if(!$this->connected()) {
      $this->error = array(
            "error" => "Called Hello() without being connected");
      return false;
    }

    if(empty($host)) {
      $host = "localhost";
    }

    if(!$this->SendHello("EHLO", $host)) {
      if(!$this->SendHello("HELO", $host)) {
        return false;
      }
    }

    return true;
  }

  private function SendHello($hello, $host) {
    fputs($this->smtp_conn, $hello . " " . $host . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER: " . $rply . $this->CRLF . '<br />';
    }

    if($code != 250) {
      $this->error =
        array("error" => $hello . " not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    $this->helo_rply = $rply;

    return true;
  }

  public function Mail($from) {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
              "error" => "Called Mail() without being connected");
      return false;
    }

    $useVerp = ($this->do_verp ? "XVERP" : "");
    fputs($this->smtp_conn,"MAIL FROM:<" . $from . ">" . $useVerp . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 250) {
      $this->error =
        array("error" => "MAIL not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }
    return true;
  }

  public function Quit($close_on_error = true) {
    $this->error = null;

    if(!$this->connected()) {
      $this->error = array(
              "error" => "Called Quit() without being connected");
      return false;
    }

    fputs($this->smtp_conn,"quit" . $this->CRLF);

    $byemsg = $this->get_lines();

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $byemsg . $this->CRLF . '<br />';
    }

    $rval = true;
    $e = null;

    $code = substr($byemsg,0,3);
    if($code != 221) {
      $e = array("error" => "SMTP server rejected quit command",
                 "smtp_code" => $code,
                 "smtp_rply" => substr($byemsg,4));
      $rval = false;
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $e["error"] . ": " . $byemsg . $this->CRLF . '<br />';
      }
    }

    if(empty($e) || $close_on_error) {
      $this->Close();
    }

    return $rval;
  }

  public function Recipient($to) {
    $this->error = null;

    if(!$this->connected()) {
      $this->error = array(
              "error" => "Called Recipient() without being connected");
      return false;
    }

    fputs($this->smtp_conn,"RCPT TO:<" . $to . ">" . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 250 && $code != 251) {
      $this->error =
        array("error" => "RCPT not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }
    return true;
  }

  public function Reset() {
    $this->error = null;

    if(!$this->connected()) {
      $this->error = array(
              "error" => "Called Reset() without being connected");
      return false;
    }

    fputs($this->smtp_conn,"RSET" . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 250) {
      $this->error =
        array("error" => "RSET failed",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }

    return true;
  }

  public function SendAndMail($from) {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
          "error" => "Called SendAndMail() without being connected");
      return false;
    }

    fputs($this->smtp_conn,"SAML FROM:" . $from . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply,0,3);

    if($this->do_debug >= 2) {
      echo "SMTP -> FROM SERVER:" . $rply . $this->CRLF . '<br />';
    }

    if($code != 250) {
      $this->error =
        array("error" => "SAML not accepted from server",
              "smtp_code" => $code,
              "smtp_msg" => substr($rply,4));
      if($this->do_debug >= 1) {
        echo "SMTP -> ERROR: " . $this->error["error"] . ": " . $rply . $this->CRLF . '<br />';
      }
      return false;
    }
    return true;
  }

  public function Turn() {
    $this->error = array("error" => "This method, TURN, of the SMTP ".
                                    "is not implemented");
    if($this->do_debug >= 1) {
      echo "SMTP -> NOTICE: " . $this->error["error"] . $this->CRLF . '<br />';
    }
    return false;
  }

  public function getError() {
    return $this->error;
  }

  private function get_lines() {
    $data = "";
    while($str = @fgets($this->smtp_conn,515)) {
      if($this->do_debug >= 4) {
        echo "SMTP -> get_lines(): \$data was \"$data\"" . $this->CRLF . '<br />';
        echo "SMTP -> get_lines(): \$str is \"$str\"" . $this->CRLF . '<br />';
      }
      $data .= $str;
      if($this->do_debug >= 4) {
        echo "SMTP -> get_lines(): \$data is \"$data\"" . $this->CRLF . '<br />';
      }

      if(substr($str,3,1) == " ") { break; }
    }
    return $data;
  }

}
?>