<?php

include("include/jcode.php");
//include_once('include/bytesmailer/include.php');

/*---------------------------------------------------------
���[�����M
MailSend($to_addres,$from_addres,$make_subject,$message);
---------------------------------------------------------*/

function MailSend($to_addres,$from_addres,$make_subject,$message)
{

	MailSendJomon($to_addres,$from_addres,$make_subject,$message);
	MailSendJomon($from_addres,$to_addres,$make_subject,$message);

}

function MailSendJomon($to_addres,$from_addres,$make_subject,$message)
{

	$message = str_replace("\r", "\n", str_replace("\r\n", "\n", $message));
	$message = mb_convert_encoding($message,"JIS","SHIFT_JIS");
	$make_subject = "=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($make_subject,"JIS","SHIFT_JIS"))."?=";
	$header = "From: $from_addres\nReply-To: ".$from_addres."\nReturn-Path: <$from_addres>\nContent-Type: text/plain;charset=iso-2022-jp\nContent-Transfer-Encoding: 7bit\nX-Mailer: DESIGN STUDIO OriginalMailer";

	mail($to_addres,$make_subject,$message,$header);

}

/*-------------------------------------------------------------------------
valid_mail($email)
TRUE; ���������
--------------------------------------------------------------------------*/

function valid_mail($email) // �����������̃��[���A�h���X�̏ꍇ�́A1 ��Ԃ�
{
if (! ereg("^[0-9A-Za-z._-]+@[0-9A-Za-z.-]+$", $email)) {
	return TRUE;
}else{
	return FALSE;

}
}
/*-------------------------------------------------------------------------
�z��o�b�N�X���b�V������
backslashesdell($a)
--------------------------------------------------------------------------*/
function backslashesdell($a)
{
if(is_array($a)){
	foreach($a as $b => $c){
		$z[$b]=stripslashes($c);
	}
	return $z;
}
}
/*-------------------------------------------------------------------------
�z��o�b�N�X���b�V���ǉ�
backslashesAdd($a)
--------------------------------------------------------------------------*/
function backslashesAdd($a)
{
if(is_array($a)){
	foreach($a as $b => $c){
		$c = mb_convert_encoding($c, 'EUC-JP', 'SJIS');
		$c = addslashes($c);

		$z[$b]=mb_convert_encoding($c, 'SJIS', 'EUC-JP');
	}
	return $z;
}
}

/*-------------------------------------------------------------------------
�z��HTML��
backslashesdHTML($a)
--------------------------------------------------------------------------*/
function backslashesdHTML($a)
{
if(is_array($a)){
	foreach($a as $b => $c){
		$z[$b]=htmlspecialchars($c);
	}
	return $z;
}
}


?>
