<?php
namespace Scalr;

/**
 * Simple Mailer class
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    13.05.2013
 */
class SimpleMailer
{

    const DEFAULT_CHARSET = 'UTF-8';

    private $version = '0.1';

    /**
     * The list of the addresses by type
     *
     * @var array
     */
    private $addresses;

    /**
     * Whether all headers has been set
     *
     * @var bool
     */
    private $needUpdateHeader = true;

    /**
     * Subject
     *
     * @var string
     */
    private $subject;

    /**
     * Message
     *
     * @var string
     */
    private $message;

    /**
     * Headers
     *
     * @var array
     */
    private $headers;

    /**
     * Default charset
     *
     * @var string
     */
    private $charset = self::DEFAULT_CHARSET;

    /**
     * Default content-Type
     *
     * @var string
     */
    private $contentType = 'text/plain';

    /**
     * New line delimiter
     *
     * @var string
     */
    private $nl = "\r\n";

    /**
     * Gets composed address according to MIME rules
     *
     * @param   string     $address Valid email address
     * @param   string     $name    optional A name
     * @param   string     $charset optional A charset
     * @return  string     Returns composed address according to rfc rules
     */
    public static function addr($address, $name = null, $charset = null)
    {
        $address = trim($address, "<> \r\n");
        $charset = empty($charset) ? self::DEFAULT_CHARSET : $charset;
        if (!empty($name)) {
            $name = '=?' . $charset . '?B?' . base64_encode($name) . '?=';
        }
        return $name === null ? $address : $name . ' <' . $address . '>';
    }

    public function __construct()
    {
        $this->addresses = array();
        $this->headers = array(
            'MIME-Version' => '1.0',
            'X-Mailer'     => 'SimpleMailer-' . $this->version . ' PHP/' . phpversion()
        );
    }

    /**
     * Gets subject
     *
     * @return   string  Returns the subject
     */
    public function getSubject()
    {
        return isset($this->subject) ? $this->subject : '';
    }

    /**
     * Gets the message
     *
     * @return  string  Returns the message
     */
    public function getMessage()
    {
        return isset($this->message) ? $this->message : '';
    }

    /**
     * Gets headers
     *
     * @return  string Returns the headers
     */
    public function getHeaders()
    {
        $headers = '';

        foreach ($this->addresses as $type => $list) {
            if ($type == 'To') continue;
            $address = $this->getAddress($type);
            if ($address && !isset($this->headers[$type])) {
                $headers .= $type . ': ' . $address . $this->nl;
            }
        }

        foreach ($this->headers as $name => $value) {
            if ($value === null) continue;
            $headers .= $name . ': ' . $value . $this->nl;
        }

        return $headers;
    }

    /**
     * Gets charser
     *
     * @return  string Returns charset
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Adds an address
     *
     * @param   string     $type    An address type [To|Cc|Bcc|Reply-To]
     * @param   string     $address An email address
     * @param   string     $name    optional The name
     * @param   string     $charset optional A charset
     * @return  SimpleMailer
     * @throws  \InvalidArgumentException
     */
    public function addAnAddress($type, $address, $name = null, $charset = null)
    {
        $valid = array('To', 'Cc', 'Bcc', 'Reply-To');
        if (in_array($type, $valid)) {
            if (!isset($this->addresses[$type])) {
                $this->addresses[$type] = array();
            }

            $this->addresses[$type][] = array(
                'address' => trim($address, ' <>'),
                'name'    => $name,
                'charset' => $charset,
            );

            //Prevents from being overridden
            if (isset($this->headers[$type])) {
                unset($this->headers[$type]);
            }

        } else {
            throw new \InvalidArgumentException(
                'Illegal address type. Valid types are [' . join('|', $valid) . ']'
            );
        }
        return $this;
    }

    /**
     * Gets the address, encoded according to rfc, for the specified type
     *
     * @param   string      $type An address type ['To', 'From', 'Cc', 'Bcc', 'Reply-To', 'Return-Path']
     * @throws  \InvalidArgumentException
     * @return  string      Returns encoded address
     */
    public function getAddress($type)
    {
        $valid = array('To', 'From', 'Cc', 'Bcc', 'Reply-To', 'Return-Path');
        if (!in_array($type, $valid)) {
            throw new \InvalidArgumentException(
                'Illegal address type. Valid types are [' . join('|', $valid) . ']'
            );
        }
        $address = '';
        if (!empty($this->addresses[$type])) {
            foreach ($this->addresses[$type] as $v) {
                $charset = isset($v['charset']) ? $v['charset'] : $this->charset;
                $address .= "," . (empty($v['name']) ? $v['address'] :
                    '=?' . $charset . '?B?' . base64_encode($v['name']) . '?= <' . $v['address'] . '>');
            }
        }

        return $address == '' ? null : ltrim($address, ', ');
    }

    /**
     * Sets a subject
     *
     * @param   string  $subject The subject
     * @param   string  $charset optional The charset
     * @return  SimpleMailer
     */
    public function setSubject($subject, $charset = null)
    {
        $charset = empty($charset) ? $this->charset : $charset;
        $this->subject = '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
        return $this;
    }

    /**
     * Sets a message
     *
     * @param   string      $message The message
     * @return  SimpleMailer
     */
    public function setMessage($message)
    {
        $this->message = quoted_printable_encode($message);
        return $this;
    }

    /**
     * Sets charset
     *
     * @param   string $charset The charset
     * @return  SimpleMailer
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Adds additional header
     *
     * @param   array   $headers  The list of the header/value pairs
     * @return  SimpleMailer
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set a header value
     *
     * @param   string     $name  A header name
     * @param   string     $value A header value
     * @return  SimpleMailer
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Sets FROM email address
     *
     * @param   string     $address A FROM email
     * @param   string     $name    optional A from name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function setFrom($address, $name = null, $charset = null)
    {
        if (isset($this->headers['From'])) {
            unset($this->headers['From']);
        }
        $this->addresses['From'] = array(array(
            'address' => trim($address, " <>\r\n"),
            'name'    => $name,
            'charset' => $charset,
        ));
        return $this;
    }

    /**
     * Sets Return-Path email address
     *
     * @param   string     $address A Return-Path email address
     * @return  SimpleMailer
     */
    public function setReturnPath($address)
    {
        if (isset($this->headers['Return-Path'])) {
            unset($this->headers['Return-Path']);
        }
        $this->addresses['Return-Path'] = array(array(
            'address' => trim($address, ' <>'),
            'name'    => null,
            'charset' => null,
        ));
        return $this;
    }

    /**
     * Sets 'to' email address
     *
     * @param   string     $address A to email
     * @param   string     $name    optional A to name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function setTo($address, $name = null, $charset = null)
    {
        if (isset($this->addresses['To'])) {
            unset($this->addresses['To']);
        }
        return $address !== null ? $this->addAnAddress('To', $address, $name, $charset) : $this;
    }

    /**
     * Adds 'to' email address
     *
     * @param   string     $address A to email
     * @param   string     $name    optional A to name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function addTo($address, $name = null, $charset = null)
    {
        return $this->addAnAddress('To', $address, $name, $charset);
    }

    /**
     * Sets CC email address
     *
     * @param   string     $address A CC email
     * @param   string     $name    optional The name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function setCc($address, $name = null, $charset = null)
    {
        if (isset($this->addresses['Cc'])) {
            unset($this->addresses['Cc']);
        }
        return $address !== null ? $this->addAnAddress('Cc', $address, $name, $charset) : $this;
    }

    /**
     * Adds CC email address
     *
     * @param   string     $address A CC email
     * @param   string     $name    optional The name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function addCc($address, $name = null, $charset = null)
    {
        return $this->addAnAddress('Cc', $address, $name, $charset);
    }

    /**
     * Sets BCC email address
     *
     * @param   string     $address A BCC email
     * @param   string     $name    optional The name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function setBcc($address, $name = null, $charset = null)
    {
        if (isset($this->addresses['Bcc'])) {
            unset($this->addresses['Bcc']);
        }
        return $address !== null ? $this->addAnAddress('Bcc', $address, $name, $charset) : $this;
    }

    /**
     * Adds BCC email address
     *
     * @param   string     $address A BCC email
     * @param   string     $name    optional The name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function addBcc($address, $name = null, $charset = null)
    {
        return $this->addAnAddress('Bcc', $address, $name, $charset);
    }

    /**
     * Sets ReplyTo email address
     *
     * @param   string     $address A ReplyTo email
     * @param   string     $name    optional The name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function setReplyTo($address, $name = null, $charset = null)
    {
        if (isset($this->addresses['Reply-To'])) {
            unset($this->addresses['Reply-To']);
        }
        return $address !== null ? $this->addAnAddress('Reply-To', $address, $name, $charset) : $this;
    }

    /**
     * Adds ReplyTo email address
     *
     * @param   string     $address A ReplyTo email
     * @param   string     $name    optional The name
     * @param   string     $charset optional Charset
     * @return  SimpleMailer
     */
    public function addReplyTo($address, $name = null, $charset = null)
    {
        return $this->addAnAddress('Reply-To', $address, $name, $charset);
    }

    /**
     * Enables/Disables html content-type
     *
     * @param   bool $enabled optional Set to true to enable html
     * @return  SimpleMailer
     */
    public function setHtml($enabled = true)
    {
        if ($enabled) {
            $this->contentType = 'text/html';
        } else {
            $this->contentType = 'text/plain';
        }
        return $this;
    }

    /**
     * Gets hostname
     *
     * @return  string Returns hostname
     */
    protected function getHostname()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            $result = $_SERVER['SERVER_NAME'];
        } else {
            $result = 'localhost.localdomain';
        }
        return $result;
    }

    /**
     * Sends a message
     *
     * @param   string     $to      optional An email address to send
     * @param   string     $subject optional A subject of the message
     * @param   string     $message optional A message to send
     * @return  bool       Returns true on success
     * @throws  \Exception
     */
    public function send($to = null, $subject = null, $message = null)
    {
        $to = $to ?: $this->getAddress('To');
        if (empty($to)) {
            throw new \Exception('"To" email address has not been set.');
        }

        if ($subject !== null) {
            $this->setSubject($subject);
        }

        if ($message !== null) {
            $this->setMessage($message);
        }

        $this->setHeader('Content-type', $this->contentType . '; charset=' . $this->charset)
             ->setHeader('Date', date('r'))
             ->setHeader('Content-Transfer-Encoding', 'QUOTED-PRINTABLE')
             ->setHeader('Message-ID', md5(uniqid(time())) . "." . $this->getHostname())
        ;

        return mail($to, $this->getSubject(), $this->getMessage(), $this->getHeaders());
    }

    /**
     * Sends a email message from specified templated.
     *
     * This method uses translate array to parse template before sending.
     *
     * @param   string     $template      Path to template file.
     * @param   array      $translate     optional Transtation array looks like array(key => value)
     * @param   string     $to            optional "To" email address
     * @param   string     $toName        ]optional "To" email name.
     * @return  boolean    Returns true on succes
     * @throws  \Exception
     */
    public function sendTemplate($template, array $translate = array(), $to = null, $toName = null)
    {
        if (!is_file($template) || !is_readable($template)) {
            throw new \Exception(sprintf(
                'Could not open the template "%s"', (string)$template
            ));
        }

        //Loads template
        $body = file_get_contents($template);
        if (!empty($translate)) {
            $body = str_replace(array_keys($translate), array_values($translate), $body);
        }

        //Extracts subject from the template
        if (preg_match('~\[subject\](.+?)\[/subject\]~U', $body, $matches)) {
            $subject = $matches[1];
            $body = preg_replace('~\[subject\].+?\[/subject\]~U', '', $body);
        }

        //Transforms according to MIME standards
        if (!empty($toName) && !empty($to)) {
            $to = self::addr($to, $toName, $this->charset);
        }

        return $this->send($to, (isset($subject) ? $subject : null), $body);
    }
}
