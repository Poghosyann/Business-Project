<?php
class DataBase{
   private $mysqli;
   private $db_host;
   private $db_login;
   private $db_pass;
   private $db_name;
   private $time_zone;
   public  $insert_id;
    
   public function __construct($db_host = "localhost", $db_login = "root", $db_pass = "", $db_name = "officepro", $time_zone = "+4:00"){
       $this->db_host = $db_host;
       $this->db_login = $db_login;
       $this->db_pass = $db_pass;
       $this->db_name = $db_name;
       $this->time_zone = $time_zone;
       $this->connect();
   }
    
   public function __destruct(){
       $this->close();
   }
    
   public function connect(){
       $this->mysqli = new mysqli($this->db_host, $this->db_login, $this->db_pass, $this->db_name);
       if($this->mysqli->connect_errno){
           exit('Connect Error...');
       }else{
           $this->mysqli->set_charset("utf8");
           $this->mysqli->query("SET `time_zone` = '".$this->time_zone."'");
       }
   }
    
   public function close(){
       return $this->mysqli->close();        
   }
    
   public function query($query = ""){
       $result = $this->mysqli->query($query);
       if(isset($this->mysqli->insert_id)){
            $this->insert_id = $this->mysqli->insert_id;
       }
       return new Result($result);
   }
    
}


class Result{
   private $result;
   public $num_rows;    
    
   public function __construct($result){
       $this->result = $result;
       if(isset($this->result->num_rows)){
            $this->num_rows = $result->num_rows;
       }
   }    
    
   public function fetch_all($type = MYSQLI_ASSOC){
       return $this->result->fetch_all($type);
   }
    
   public function fetch_array($type){
       return $this->result->fetch_array($type);
   }
    
   public function fetch_assoc(){
       return $this->result->fetch_assoc();
   }   
}
?>