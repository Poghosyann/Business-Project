<?php
class Url{  
    public $type = "";
    public $PATH = "";
    public $DIR = "";
    public $DIRS = "";
    public $PAGE = "";
    public $GET = [];
    
    public function __construct(){
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest'){
            $this->type = 'ajax';
        }
        
        $url = urldecode($_SERVER['REQUEST_URI']);
        $url = strip_tags($url);
        $url = ltrim($url, '/');
        $url = explode('?', $url);
        
        if(isset($url[0])){
            $this->DIR = explode('/', $url[0]);
            $this->PAGE = array_pop($this->DIR);
            $this->DIRS = ltrim(implode('/', $this->DIR)."/", '/');
            $this->PATH = $this->DIRS.$this->PAGE;
        }
        
        if(isset($url[1])){
            $this->GET = $_GET;
        }
    }
    
    public function cleanURL($url){
        $url = str_replace(",", " ", $url);
        $url = str_replace(".", " ", $url);
        $url = str_replace("․", " ", $url);
        $url = str_replace("…", " ", $url);
        $url = str_replace("՝", " ", $url);
        $url = str_replace("`", " ", $url);
        $url = str_replace(":", " ", $url);
        $url = str_replace("։", " ", $url);
        $url = str_replace(";", " ", $url);
        $url = str_replace(",", " ", $url);
        $url = str_replace("•", " ", $url);
        $url = str_replace("+", " ", $url);
        $url = str_replace("/", " ", $url);
        $url = str_replace("-", " ", $url);
        $url = str_replace("'", " ", $url);
        $url = str_replace("՛՛", " ", $url);
        $url = str_replace("#", " ", $url);
        $url = str_replace("@", " ", $url);
        $url = str_replace("&", " ", $url);
        $url = str_replace("%", " ", $url);
        $url = str_replace("$", " ", $url);
        $url = str_replace("(", " ", $url);
        $url = str_replace(")", " ", $url);
        $url = str_replace("«", " ", $url);
        $url = str_replace("»", " ", $url);
        $url = str_replace("\n", " ", $url);
        $url = str_replace("\r", " ", $url);
        $url = str_replace("\r\n", " ", $url);
        $url = str_replace(" ", "-", $url);
        $url = str_replace("--", "-", $url);
        $url = str_replace("--", "-", $url);
        $url = str_replace("--", "-", $url);
        $url = trim($url, '-');
        $url = mb_strtolower($url);
        return $url;
    }
}
?>