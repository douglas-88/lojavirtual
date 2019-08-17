<?php


namespace App\Models\Model;

use Rain\Tpl;
use Model\Model;

class Mailer extends Model
{
    private $username;
    private $password;
    private $host;
    private $port;
    private $sender;
    private $mail;

    public function __construct($toAdress, $toName, $subject, $tplName, $data = [])
    {

        $config = array(
            "tpl_dir" => getenv("BASE_DIR") . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR ."email" . DIRECTORY_SEPARATOR,
            "cache_dir" => getenv("BASE_DIR") . DIRECTORY_SEPARATOR . "views-cache" . DIRECTORY_SEPARATOR,
            "debug" => false
        );

        Tpl::configure($config);

        $tpl = new Tpl;
        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName,true);

        $this->username = getenv("MAIL_DEFAULT");
        $this->password = getenv("MAIL_PASSWORD");
        $this->host     = getenv("MAIL_HOST");
        $this->port     = getenv("MAIL_PORT");
        $this->sender   = getenv("MAIL_SENDER");
//Create a new PHPMailer instance
        $this->mail = new \PHPMailer;


//Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
        $this->mail->SMTPDebug = 2;

//Set the hostname of the mail server
        $this->mail->Host = $this->host;
// use
// $this->>mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = $this->port;

//Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = $this->username;

//Password to use for SMTP authentication
        $this->mail->Password = $this->password;

//Set who the message is to be sent from
        $this->mail->setFrom($this->username, $this->sender);

//Set an alternative reply-to address
//$this->>mail->addReplyTo('replyto@example.com', 'First Last');

//Set who the message is to be sent to
        $this->mail->addAddress($toAdress, $toName);

//Set the subject line
        $this->mail->Subject = $subject;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
        $this->mail->msgHTML($html);

//Replace the plain text body with one created manually
        $this->mail->AltBody = 'This is a plain-text message body';

//Attach an image file
//$this->>mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors

    }

    public function send(){
       return $this->mail->send();
    }
}