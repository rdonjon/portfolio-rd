<?php

include_once(__DIR__.'/phpmailer/phpmailer/class.phpmailer.php');

/*
	********************************************************************************************
	CONFIGURATION
	********************************************************************************************
*/
// destinataire est votre adresse mail. Pour envoyer à plusieurs à la fois, séparez-les par une virgule
// $destinataire = 'romane.donjon.iw@gmail.com';
$destinataire = 'b.chemier@eobs.fr';
$replyTo = 'b.chemier@eobs.fr';


// copie ? (envoie une copie au visiteur)
$copie = 'oui';

// Action du formulaire (si votre page a des paramètres dans l'URL)
// si cette page est index.php?page=contact alors mettez index.php?page=contact
// sinon, laissez vide
$form_action = '';

// Messages de confirmation du mail
$message_envoye = "Votre message nous est bien parvenu !";
$message_non_envoye = "L'envoi du mail a échoué, veuillez réessayer SVP.";

// Message d'erreur du formulaire
$message_formulaire_invalide = "Vérifiez que tous les champs soient bien remplis et que l'email soit sans erreur.";

/*
	********************************************************************************************
	FIN DE LA CONFIGURATION
	********************************************************************************************
*/

/*
 * cette fonction sert à nettoyer et enregistrer un texte
 */
function Rec($text)
{
	$text = htmlspecialchars(trim($text), ENT_QUOTES);
	if (1 === get_magic_quotes_gpc())
	{
		$text = stripslashes($text);
	}

	$text = nl2br($text);
	return $text;
};

/*
 * Cette fonction sert à vérifier la syntaxe d'un email
 */
function IsEmail($email)
{
	$value = preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $email);
	return (($value === 0) || ($value === false)) ? false : true;
}

// formulaire envoyé, on récupère tous les champs.
$nom     = (isset($_POST['nom']))     ? Rec($_POST['nom'])     : '';
$email   = (isset($_POST['email']))   ? Rec($_POST['email'])   : '';
$objet   = (isset($_POST['objet']))   ? Rec($_POST['objet'])   : '';
$message = (isset($_POST['message'])) ? Rec($_POST['message']) : '';

// On va vérifier les variables et l'email ...
$email = (IsEmail($email)) ? $email : ''; // soit l'email est vide si erroné, soit il vaut l'email entré
$err_formulaire = false; // sert pour remplir le formulaire en cas d'erreur si besoin

$contenu = sprintf('<p>Bonjour,</p>

                    <p>Une demande vous a été émise via le formulaire de contact sur le site <a href="www.romanedonjon.fr">www.romanedonjon.fr</a></p>

                    <p><u>Données de l\'internaute :</u></p>

                    <p><em>%s</em></p>

                    <p><strong><a href="mailto:%s">%s</a></strong></p>

                    <p><u>Son message :</u></p>

                    <p>%s</p>

                    <p>%s</p>

                    <p>Cordialement,</p>

                    ',
                $nom,
                $email,
                $email,
                $objet,
                $message
            );

$params = array();
$params['subject'] = '[Romane Donjon] - Un nouveau message vous est parvenu';
$params['to'] = $destinataire;
$params['cc'] = '';
$params['cci'] = 'b.chemier@eobs.fr';
$params['content'] = $contenu;
$params['isAccuseReception'] = 0;
$params['replyTo'] = $replyTo;
$params['from'] = 'contact@eobs.fr';
$params['fromName'] = '';
$params['attachments'] = '';



$result = sendWithPHPMailer($params);

if ($result) {
    header(sprintf("HTTP/1.1 %s %s", 302, 'Found'));
    header(sprintf('Location: %s', 'mail-send.html'));
    header("Connection: close");
} else {
   echo
   '<div>
        <p>
            Une erreur est survenue lors de l\'envoi. Veuillez réessayer plus tard.
        </p>
        <p>
            <a href="www.romanedonjon.fr" > Retournez à l\'accueil</a>
        </p>
    </div>';
}


function sendWithPHPMailer(array $params)
{
    // Config de test en local avec maildev:
    // $mailHost = 'localhost';
    // $mailPort = 1025;
    // $mailPwd = '';
    // $mailUser = '';

    // Config de prod:
    $mailHost = 'smtp.eobs.fr';
    $mailPort = 25;
    $mailUser = 'noreply@eobs.fr';
    $mailPwd = 'no11reply09';

    $subject = $params['subject'];
    $to = $params['to'];
    $cc = $params['cc'];
    $bcc = $params['cci'];
    $content = $params['content'];
    $isAccuseReception = $params['isAccuseReception'];
    $reply_to = $params['replyTo'];
    $from = $params['from'];
    $from_name = $params['fromName'];
    $attachments = $params['attachments'];

    $Mail = new \PHPMailer();
    $Mail->Priority = 3;
    $Mail->Encoding = "8bit";
    $Mail->CharSet = "iso-8859-1";
    $Mail->From = $from;

    if(!empty($from_name)){
        $Mail->FromName = $from_name;
    } else{
        $Mail->FromName = $Mail->From;
    }
    $Mail->Sender = $Mail->From;

    if($isAccuseReception) {
        $Mail->ConfirmReadingTo = $Mail->From;
    }

    $Mail->Subject= utf8_decode(stripslashes($subject));
    $Mail->IsHTML(true);

    $Mail->Body = utf8_decode(stripslashes($content));
    $Mail->AltBody = "";
    $Mail->WordWrap = 0;

    $smtpAuth = true;

    $Mail->Host = $mailHost;
    $Mail->Port = $mailPort;
    $Mail->Helo = "you";
    $Mail->SMTPAuth = $smtpAuth;

    $Mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        )
    );

    $Mail->Username = $mailUser;
    $Mail->Password = $mailPwd;
    $Mail->PluginDir = $INCLUDE_DIR;


    if(strlen($Mail->Host) > 0) {
        $Mail->Mailer = "smtp";
    } else {
        $Mail->Mailer = "mail";
        $Sender = $Mail->From;
    }

    $tab_dest = explode(',',$to);
    foreach($tab_dest as $dest) {
        $Mail->AddAddress($dest, '');
    }

    $tab_cc = $cc;
    if (!empty($tab_cc)) {
        $tab_cc = explode(',',$cc);
        foreach($tab_cc as $cc) {
            $Mail->AddCC($cc,'');
        }
    }

    $tab_bcc = $bcc;
    if (!empty($tab_bcc)) {
        $tab_bcc = explode(',',$bcc);
        foreach($tab_bcc as $bcc) {
            $Mail->AddBCC($bcc,'');
        }
    }

    if(!empty($reply_to)) {
        $Mail->AddReplyTo($reply_to, $reply_to);
    }

    if(!empty($attachments) && is_array($attachments)) {
        foreach($attachments as $attachment) {
            $Mail->AddAttachment($attachment);
        }
    }

    if(!$Mail->Send()) {
        $result = sprintf("Mailer Error: %s",$Mail->ErrorInfo);
    } else {
        $result = true;
    }

    return $result;
}
?>