<?php
class PHPMailerOAuth extends \PHPMailer\PHPMailer\PHPMailer {

    /**
     * The OAuth user's email address
     * @type string
     */
    public $oauthUserEmail = '';

    /**
     * The OAuth refresh token
     * @type string
     */
    public $oauthRefreshToken = '';

    /**
     * The OAuth client ID
     * @type string
     */
    public $oauthClientId = '';

    /**
     * The OAuth client secret
     * @type string
     */
    public $oauthClientSecret = '';

    /**
     * An instance of the OAuth class.
     * @type OAuth
     * @access protected
     */
    protected $oauth = null;
    
    /**
     * Constructor.
     * @param bool $exceptions Optional. Whether to throw exceptions for errors. Default false.
     */
    public function __construct( $exceptions = false ) {
        parent::__construct( $exceptions );
    }
    
    /**
     * Get an OAuth instance to use.
     * @return OAuth
     */
    public function getOAUTHInstance()
    {
        if (!is_object($this->oauth)) {
            /* this is the only part that differs,
             * we create an object of our class GmailXOAuth2 instead of the original OAuth class 
             */
            $this->oauth = new GmailXOAuth2 (
                $this->oauthUserEmail,
                $this->oauthClientSecret,
                $this->oauthClientId,
                $this->oauthRefreshToken
            );
        }
        return $this->oauth;
    }

    /**
     * Initiate a connection to an SMTP server.
     * Overrides the original smtpConnect method to add support for OAuth.
     * @param array $options An array of options compatible with stream_context_create()
     * @uses SMTP
     * @access public
     * @throws phpmailerException
     * @return boolean
     */
    public function smtpConnect($options = array()) {
        if (is_null($this->smtp)) {
            $this->smtp = $this->getSMTPInstance();
        }

        if (is_null($this->oauth)) {
            $this->oauth = $this->getOAUTHInstance();
        }

        // Already connected?
        if ($this->smtp->connected()) {
            return true;
        }

        $this->smtp->setTimeout($this->Timeout);
        $this->smtp->setDebugLevel($this->SMTPDebug);
        $this->smtp->setDebugOutput($this->Debugoutput);
        $this->smtp->setVerp($this->do_verp);
        $hosts = explode(';', $this->Host);
        $lastexception = null;

        foreach ($hosts as $hostentry) {
            $hostinfo = array();
            if (!preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*):?([0-9]*)$/', trim($hostentry), $hostinfo)) {
                // Not a valid host entry
                continue;
            }
            // $hostinfo[2]: optional ssl or tls prefix
            // $hostinfo[3]: the hostname
            // $hostinfo[4]: optional port number
            // The host string prefix can temporarily override the current setting for SMTPSecure
            // If it's not specified, the default value is used
            $prefix = '';
            $secure = $this->SMTPSecure;
            $tls = ($this->SMTPSecure == 'tls');
            if ('ssl' == $hostinfo[2] or ('' == $hostinfo[2] and 'ssl' == $this->SMTPSecure)) {
                $prefix = 'ssl://';
                $tls = false; // Can't have SSL and TLS at the same time
                $secure = 'ssl';
            } elseif ($hostinfo[2] == 'tls') {
                $tls = true;
                // tls doesn't use a prefix
                $secure = 'tls';
            }
            //Do we need the OpenSSL extension?
            $sslext = defined('OPENSSL_ALGO_SHA1');
            if ('tls' === $secure or 'ssl' === $secure) {
                //Check for an OpenSSL constant rather than using extension_loaded, which is sometimes disabled
                if (!$sslext) {
                    throw new \PHPMailer\PHPMailer\Exception($this->lang('extension_missing').'openssl', self::STOP_CRITICAL);
                }
            }
            $host = $hostinfo[3];
            $port = $this->Port;
            $tport = (integer)$hostinfo[4];
            if ($tport > 0 and $tport < 65536) {
                $port = $tport;
            }
            if ($this->smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                try {
                    if ($this->Helo) {
                        $hello = $this->Helo;
                    } else {
                        $hello = $this->serverHostname();
                    }
                    $this->smtp->hello($hello);
                    //Automatically enable TLS encryption if:
                    // * it's not disabled
                    // * we have openssl extension
                    // * we are not already using SSL
                    // * the server offers STARTTLS
                    if ($this->SMTPAutoTLS and $sslext and $secure != 'ssl' and $this->smtp->getServerExt('STARTTLS')) {
                        $tls = true;
                    }
                    if ($tls) {
                        if (!$this->smtp->startTLS()) {
                            throw new \PHPMailer\PHPMailer\Exception($this->lang('connect_host'));
                        }
                        // We must resend HELO after tls negotiation
                        $this->smtp->hello($hello);
                    }
                    if ($this->SMTPAuth) {
                        if (!$this->smtp->authenticate(
                            $this->Username,
                            $this->Password,
                            $this->AuthType,
                            $this->oauth
                        )
                        ) {
                            throw new \PHPMailer\PHPMailer\Exception($this->lang('authenticate'));
                        }
                    }
                    return true;
                } catch (\PHPMailer\PHPMailer\Exception $exc) {
                    $lastexception = $exc;
                    $this->edebug($exc->getMessage());
                    // We must have connected, but then failed TLS or Auth, so close connection nicely
                    $this->smtp->quit();
                }
            }
        }
        // If we get here, all connection attempts have failed, so close connection hard
        $this->smtp->close();
        // As we've caught all exceptions, just report whatever the last one was
        if ($this->exceptions and !is_null($lastexception)) {
            throw $lastexception;
        }
        return false;
    }
}

