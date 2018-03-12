<?php
function addImage($file, $path, $width, $height, $type, $watermark){  
    $image = new SimpleImage();
    $image->load($file);
    if($type == 'resize'){
        if($image->getWidth() >= $image->getHeight()){
            $image->resizeToHeight($height);
            if($image->getWidth() > $width){
                $image->resizeToWidth($width);
            }
        }else{
            $image->resizeToWidth($width);
            if($image->getHeight() > $height){
                $image->resizeToHeight($height);
            }
        }
    }elseif($type == 'crop'){
        $image->crop($width, $height);
    }
    if($watermark === true){
        $image->watermark(20, 20, "public/img/watermark.png");
    }
    $image->save($path);
}


function sendMailSmtp($to = "", $subject = "", $message = "", $file = ""){
    
    $message = '<div style="width:100%;border:4px solid #efefef;padding:25px;box-sizing:border-box;background-image:url('.(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER["HTTP_HOST"].'/public/img/bg.png);"><img src="'.(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER["HTTP_HOST"].'/public/img/logo.png" style="width:200px;"><div style="background:#efefef;margin:20px 0 30px;padding:20px;font-size:15px;">'.$message.'</div><div style="height:10px;overflow:hidden;background:#434a54;"><div style="position:relative;height:66px;margin:0 auto;width:1000px;padding:0;z-index:1;"><div style="background-image:url('.(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER["HTTP_HOST"].'/public/img/footer-border.png);background-repeat:no-repeat;position:absolute;background-position:0;height:92px;width:558px;top:-20px;right:14px;z-index:0;"></div></div></div><div style="padding:10px 20px;background:#efefef;">Մենք համացանցում` <a href="https://www.facebook.com/www.domain.am" style="margin:0 4px;color:#0f4490;text-decoration:none;">Facebook</a>•<a href="https://www.instagram.com/domain.am" style="margin:0 4px;color:#0f4490;text-decoration:none;">Instagram</a>•<a href="https://www.youtube.com/channel/UChATDOuumL4BCdE5jFSaF8w" style="margin:0 4px;color:#0f4490;text-decoration:none;">Youtube</a></div></div>';
    
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";
    $mail->SMTPSecure = "ssl";
    $mail->Host = "smtp.mail.ru";
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->Username = "shop@domain.am";
    $mail->Password = "";
    $mail->FromName = "domain.am";
    $mail->From = "shop@domain.am";
    $mail->AddAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $message;
    if(!empty($file)){
        if(isset($file['tmp_name'])){
            $mail->AddAttachment($file['tmp_name'], $file['name']);
        }else{
            $mail->AddStringAttachment($file["body"], $file["name"], 'base64', 'application/octet-stream');
        }
    }
    $mail->WordWrap = 50;
    $mail->ContentType = 'text/html';
    $mail->CharSet="UTF-8";
    $mail->Send(); 
}


function sendMail($to, $subject, $message){   
    $from = 'shop@domain.am';    
    $headers = "From: domain.am <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    mail($to, $subject, $message, $headers);
}


function checkVariable($value, $type = true){
    if(is_array($value)){
        return array_map(function($item) {
            return checkVariable($item);
        }, $value);
    }else{
        $item = addslashes($value);
        $item = trim($item);
        if($type==true){
            $item = htmlspecialchars($item);
            $item = preg_replace("/[\n\r]{3,}/","\r\r", $item);
        }
        return $item;
    }
}
?>