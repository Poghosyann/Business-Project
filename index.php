<?php
session_start();
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set( 'date.timezone', 'Asia/Yerevan' );
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

require "function/main.php";

if(!empty($_POST)){
    $type = (isset($_GET["type"]) && $_GET["type"]=="original") ? false : true;
    $_POST = checkVariable($_POST, $type);
}

spl_autoload_register(function ($class){
    if(strpos($class, '\\')){
        $class_array = explode('\\', $class);
        $class_name = array_pop($class_array);
        $class_path = str_replace('\\', '/', $class).'.php';
    }else{
        $class_name = $class;
        $class_path = $class.'.php';
    }
    if(is_file("classes/$class_path")){
        require "classes/$class_path";
    }elseif(is_file("drivers/$class_path")){
        require "drivers/$class_path"; 
    }else{
        exit("Error loading: $class_name");
    }
});

$url = new Url();

if(is_dir("layouts/".$url->PATH) && !is_file("layouts/".$url->PATH.".php") && !empty($url->PAGE)){
    header("Location: /".$url->PATH."/");
    exit();
}


if(isset($url->GET['lang'])){
    $_SESSION['lang'] = $url->GET['lang'];
    header("Location: /".$url->PATH."".urldecode($url->GET['backUrl'])."");
}
if(!isset($_SESSION['lang'])){
    $_SESSION['lang'] = 'am';
}

if(isset($url->GET['currency'])){
    $_SESSION['currency'] = $url->GET['currency'];
    header("Location: /".$url->PATH."".urldecode($url->GET['backUrl'])."");
}
if(!isset($_SESSION['currency'])){
    $_SESSION['currency'] = 'AMD';
}



if(isset($url->DIR[0]) && $url->DIR[0] == 'admin'){ 
    $cnt = new Admin();
    $cnt->currency = 'AMD';
    $cnt->currency_symbol = "֏";
    $cnt->lang = 'am';
    $cnt->val = require "language/am.php";
    if(isset($url->GET['cmd']) && ((!isset($_SESSION['admin']) && $url->GET['cmd'] == 'login') || (isset($_SESSION['admin']["adminID"])))){
        $cnt->{$url->GET['cmd']}();
        if($url->type=='ajax'){
            exit();
        }else{
            header("Location: /".$url->PATH."".urldecode($url->GET['backUrl'])."");
            exit();
        }
    }
    if(isset($url->DIR[1]) && ($url->DIR[1]=="overlay" || $url->DIR[1]=="load") && ($url->type != "ajax")){
        exit;
    }
    if(isset($url->DIR[0]) && $url->DIR[0]=="inc"){
        exit;
    }
    if(isset($_SESSION['admin']["adminID"])){
        if($_SESSION['admin']["permission"] >= 2 && $url->PAGE!="products" && $url->PAGE!="item"){
            $url->PAGE = 'products';
            require "layouts/admin/".$url->PAGE.".php";
            exit;
        }
        if(!is_file("layouts/".$url->PATH.".php")){
            if($url->type == "ajax") exit;
            $url->PAGE = 'default';
            require "layouts/admin/".$url->PAGE.".php";
        }else{
            require "layouts/".$url->PATH.".php";
        }
    }else{       
        require "layouts/admin/login.php";
    }
}else{
    $cnt = new User();
    $cnt->currency = $_SESSION['currency'];
    if($_SESSION['currency']=='AMD') $cnt->currency_symbol = "<sum class='amd'>֏</sum>";
    if($_SESSION['currency']=='USD') $cnt->currency_symbol = "$";
    if($_SESSION['currency']=='RUB') $cnt->currency_symbol = "₽";
    $cnt->lang = $_SESSION['lang'];
    $cnt->val = require "language/".$_SESSION['lang'].".php";
    if(isset($url->GET['cmd'])){
        $cnt->{$url->GET['cmd']}();
        if($url->type=='ajax'){
            exit();
        }else{
            header("Location: /".$url->PATH."".urldecode($url->GET['backUrl'])."");
            exit();
        }
    }
    if(isset($url->DIR[0]) && ($url->DIR[0]=="overlay" || $url->DIR[0]=="load") && ($url->type != "ajax")){
        exit;
    }
    if(isset($url->DIR[0]) && $url->DIR[0]=="inc"){
        exit;
    }
    if(isset($url->DIR[0]) && $url->DIR[0]=="profile" && empty($cnt->profile["userID"])){
        header("Location: /signIn");
        exit;
    }
    if(is_file("layouts/default/develop.php")){
        require "layouts/default/develop.php";
        exit;
    }
    if(!is_file("layouts/default/".$url->PATH.".php")){
        if(!empty($url->DIRS) && is_file("layouts/default/".$url->DIRS."default.php")){
            $url->PATH = $url->DIRS."default";
        }else{
            if(!empty($url->PATH)){
                header("HTTP/1.0 404 Not Found");
            }
            $url->DIRS = "";
            $url->PATH = "default";
        }
    }
    require "layouts/default/".$url->PATH.".php";
}
?>