<?php

include("include/jcode.php");
//include_once('include/bytesmailer/include.php');

/*---------------------------------------------------------
メール送信
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
TRUE; 正しければ
--------------------------------------------------------------------------*/

function valid_mail($email) // 正しい書式のメールアドレスの場合は、1 を返す
{
if (! ereg("^[0-9A-Za-z._-]+@[0-9A-Za-z.-]+$", $email)) {
	return TRUE;
}else{
	return FALSE;

}
}
/*-------------------------------------------------------------------------
配列バックスラッシュ除去
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
配列バックスラッシュ追加
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
配列HTML化
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
