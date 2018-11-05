<?php 

namespace Hcode;

use Rain\Tpl;

class Mailer {

	const USERNAME = "nao-responda@imm-tecnologia.com.br";
	const PASSWORD = "123456@654321";
	const NAME_FROM = "Ilan - Saia do Buraco";
	const SMTP = "mail.imm-tecnologia.com.br";
	const PORTA = "587";

	private $mail;


	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{

		$config = array(//configurações do RainTpl
		    "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/email/", //Caminho dos templates do sistema
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",//Caminhos dos arquivos em cache do sistema
		    "debug"         => false//
		);

		Tpl::configure( $config );

		$tpl = new Tpl();

		foreach ($data as $key => $value) {
			$tpl->assign($key, $value);
		}

		$html = $tpl->draw($tplName, true);//true joga o template para a variavel, não deixa exibir na tela

		$this->mail = new \PHPMailer;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		//Ask for HTML-friendly debug output
		$this->mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$this->mail->Host = Mailer::SMTP;
		//use
		//$this->mail->Host = gethostbyname('smtp.gmail.com');
		//if your network does not support SMTP over IPv6

		//Set the SMTP port number = 587 for authenticated TLS, a.k.a RFC4409 SMTP submission
		$this->mail->Port = Mailer::PORTA;

		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP
		$this->mail->Username = Mailer::USERNAME;

		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;

		//Set who the message is to be sent from
		$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

		$this->mail->addAddress($toAddress, $toName);

		//Assunto do email
		$this->mail->Subject = $subject;

		$this->mail->msgHTML($html);

		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		//$mail->addAttachment(images/phpmailer_mini.png);

	}

	public function send()
	{
		return $this->mail->send();
	}

}

?>