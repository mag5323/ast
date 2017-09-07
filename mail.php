<?php
require_once('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = file_get_contents("php://input");
$req  = json_decode($input)->data;
$body = body($req->job_title, $req->company, $req->comment);

mail_sent($req, $body);

function mail_sent($guest, $body)
{
    try {
        $mail= new PHPMailer();

        // SMTP設定
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        //$mail->SMTPDebug  = 2; // Debug mode
        $mail->SMTPSecure = 'tls';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->Timeout = 30;

        // 信件設定
        $mail->setFrom('', 'Mailer');
        $mail->CharSet = 'utf8';
        $mail->Username = ''; // 設定驗證帳號
        $mail->Password = ''; // 設定驗證密碼
        $mail->FromName = '三得電子'; // 設定寄件者姓名
        $mail->Subject = 'Inquiry about ASTECH'; // 設定郵件標題
        $mail->Body = $body; // 設定郵件內容
        $mail->IsHTML(true); // 設定郵件內容為HTML

        // 寄信
        $mail->AddAddress($guest->email, sprintf('%s %s', $guest->first_name, $guest->last_name)); // 設定收件者郵件及名稱

        $mail->Send();
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

function body($job_title, $company, $comment)
{
    $job_title = sprintf('<b>Job Title : </b>   %s', $job_title);
    $company = sprintf('<b>Company : </b>   %s', $company);
    $comment = sprintf('<b>Questions or Comments : </b> <br> %s', nl2br($comment));
    return $job_title . '<br>' . $company . '<hr>' . $comment;
}
