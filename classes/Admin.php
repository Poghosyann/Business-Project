<?php 
class Admin extends Model{
    
    public $user_cnt;
    
    public function __construct(){
        parent::__construct();
        $this->user_cnt = new User();
    }
    
    // Sign
    
    public function login(){
        if(empty($_POST['login']) || empty($_POST['password'])){
            unset($_SESSION['admin']);
            return false;
        }
        $admins = $this->db->query("SELECT * FROM `admin` WHERE `email`='".$_POST['login']."' AND `password`='".$_POST['password']."'");
        if($admins->num_rows == 1){
            $_SESSION['admin'] = $admins->fetch_assoc();
            echo json_encode([
                "error" => false,
                "location" => [
                    "href" => false,
                    "hash" => false,
                    "reload" => true
                ]
            ]);
            return false;
        }else{
            unset($_SESSION['admin']);
            echo json_encode([
                "error" => [
                    "field" => ["password", "email"],
                    "message" => '<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>Սխալ մոտքանուն կամ գաղտնաբառ!</div>'
                ],
                "location" => false
            ]);
            return false;
        }
    }
    
    public function logOut(){
        unset($_SESSION['admin']);
    }

    // User
    
    public function setUser(){
        if(!isset($_GET['userID']) || empty($_GET['userID'])){
            return false;
        }else{
            $_SESSION['userID'] = $_GET['userID'];
            header("Location: /profile/");
            exit();
        }
    }
    
    public function getUserAdmin($limit = ""){
        return $this->db->query("
            SELECT *
            FROM `user`
            LEFT JOIN `photo` ON `group`='user' AND `parent`=`userID`
            GROUP BY `userID`
            ORDER BY `userID` DESC, `photo`.`sort` ASC, `photo`.`photoID` DESC
            $limit
        ")->fetch_all(MYSQLI_ASSOC);
    }
    
    // Editor
    
    public function imageUpload(){  
        $dir = 'public/gallery/pages/';
        $filename = $_FILES['file']['name'];
        $path = $dir.$filename;
        $link = '/public/gallery/pages/'.$filename;
        $_FILES['file']['type'] = strtolower($_FILES['file']['type']);
        if ($_FILES['file']['type'] == 'image/png' 
        || $_FILES['file']['type'] == 'image/jpg' 
        || $_FILES['file']['type'] == 'image/gif' 
        || $_FILES['file']['type'] == 'image/jpeg'){	
            copy($_FILES['file']['tmp_name'], $path);
            //echo stripslashes(json_encode(array('filelink' => $link)));
            exit;
        }
    }
    
    public function imageGetJson(){
        /*
        $dir = "public/gallery/pages/";
        $list = "[";
        foreach(array_diff(scandir($dir), array(".", "..", "")) as $file){
            if(is_file($dir.$file)){
                $list .= '{"thumb":"/'.$dir.$file.'", "image":"/'.$dir.$file.'", "title":"Image 1", "folder": "Folder 1"},';
            }
        }
        $list .= "{}]";
        echo $list;
        */
    }
    
    public function addText(){
        if($this->db->query("SELECT * FROM `text` WHERE `page`='".$_POST['page']."' AND `group`='".$_POST['group']."'")->num_rows > 0){
            $this->db->query("UPDATE `text` SET `text_am`='".$_POST['text_am']."', `text_ru`='".$_POST['text_ru']."', `text_en`='".$_POST['text_en']."' WHERE `page`='".$_POST['page']."' AND `group`='".$_POST['group']."'");
        }else{
            $this->db->query("INSERT INTO `text`(`page`, `group`, `text_am`, `text_ru`, `text_en`)VALUES('".$_POST['page']."', '".$_POST['group']."', '".$_POST['text_am']."', '".$_POST['text_ru']."', '".$_POST['text_en']."')"); 
        }
    }
    
    public function changeCss(){
        if(!empty($_POST["desc"])){
            $backup = file_get_contents("public/css/main.css");
            file_put_contents("public/css/main.css.backup", $backup);
            file_put_contents("public/css/main.css", $_POST["desc"]);
        }
    }
    
    // Photo
    
    public function addPhoto(){   
        if($_POST['cat']=='item'){
            if($_POST['act']=='change'){
                $res = $this->db->query("SELECT * FROM `photo` WHERE `group`='".$_POST['group']."' AND `parent`='".$_POST['parent']."'");
                while($row = $res->fetch_assoc()){
                    unlink("public/gallery/items/large/".$row['photoID'].".jpg");
                    unlink("public/gallery/items/middle/".$row['photoID'].".jpg");
                    unlink("public/gallery/items/small/".$row['photoID'].".jpg");
                }
                $this->db->query("DELETE FROM `photo` WHERE `group`='".$_POST['group']."' AND `parent`='".$_POST['parent']."'");
            }
            foreach ($_FILES["file"]["error"] as $key => $error) {
                $this->db->query("INSERT INTO `photo` (`group`, `parent`, `sort`) VALUES ('".$_POST['group']."', '".$_POST['parent']."', '1')");
                $name = $this->db->insert_id.".jpg";
				
				
				// addImage($_FILES['file']['tmp_name'][$key], "public/gallery/items/large/".$name, 1200, 1200, 'resize', true);
                addImage($_FILES['file']['tmp_name'][$key], "public/gallery/items/large/".$name, 55555, 55555, '', true);
                addImage($_FILES['file']['tmp_name'][$key], "public/gallery/items/middle/".$name, 400, 400, 'crop', true);
                addImage("public/gallery/items/large/".$name, "public/gallery/items/small/".$name, 150, 150, 'crop', false);
            }
            if($_SESSION["admin"]["permission"]>1){
                $this->db->query("UPDATE `products` SET `status`='1' WHERE `productID`='".$_POST['parent']."'");
            }
        }
        if($_POST['cat']=='option'){
            if($_POST['act']=='change'){
                $res = $this->db->query("SELECT * FROM `photo` WHERE `group`='".$_POST['group']."' AND `parent`='".$_POST['parent']."'");
                while($row = $res->fetch_assoc()){
                    unlink("public/gallery/option/".$row['photoID'].".jpg");
                }
                $this->db->query("DELETE FROM `photo` WHERE `group`='".$_POST['group']."' AND `parent`='".$_POST['parent']."'");
            }
            foreach ($_FILES["file"]["error"] as $key => $error) {
                $this->db->query("INSERT INTO `photo` (`group`, `parent`, `sort`) VALUES ('".$_POST['group']."', '".$_POST['parent']."', '1')");
                $name = $this->db->insert_id.".jpg";
                addImage($_FILES['file']['tmp_name'][$key], "public/gallery/option/".$name, 100, 100, 'crop', false);
            }
        }
        if($_POST['cat']=='category'){
            if($_POST['act']=='change'){
                $res = $this->db->query("SELECT * FROM `photo` WHERE `group`='".$_POST['group']."' AND `parent`='".$_POST['parent']."'");
                while($row = $res->fetch_assoc()){
                    unlink("public/gallery/category/".$row['photoID'].".jpg");
                }
                $this->db->query("DELETE FROM `photo` WHERE `group`='".$_POST['group']."' AND `parent`='".$_POST['parent']."'");
            }
            foreach ($_FILES["file"]["error"] as $key => $error) {
                $this->db->query("INSERT INTO `photo` (`group`, `parent`, `sort`) VALUES ('".$_POST['group']."', '".$_POST['parent']."', '1')");
                $name = $this->db->insert_id.".jpg";
                addImage($_FILES['file']['tmp_name'][$key], "public/gallery/category/".$name, 50, 50, 'resize', false);
            }
        }
    }
    
    public function removePhoto(){
        if($_POST['cat']=='item'){
            $this->db->query("DELETE FROM `photo` WHERE `photoID`='".$_POST['photoID']."'");
            unlink("public/gallery/items/large/".$_POST['photoID'].".jpg");
            unlink("public/gallery/items/middle/".$_POST['photoID'].".jpg");
            unlink("public/gallery/items/small/".$_POST['photoID'].".jpg");
        }
        if($_POST['cat']=='option'){
            $this->db->query("DELETE FROM `photo` WHERE `photoID`='".$_POST['photoID']."'");
            unlink("public/gallery/option/".$_POST['photoID'].".jpg");
        }
        if($_POST['cat']=='receipt'){
            $this->db->query("DELETE FROM `photo` WHERE `photoID`='".$_POST['photoID']."'");
            unlink("public/gallery/receipt/".$_POST['photoID'].".jpg");
        }
        if($_POST['cat']=='category'){
            $this->db->query("DELETE FROM `photo` WHERE `photoID`='".$_POST['photoID']."'");
            unlink("public/gallery/category/".$_POST['photoID'].".jpg");
        }
    }
    
    // Sortable
    
    public function sortable(){
        $table = ltrim($_POST['table']);
        $id_name = ltrim($_POST['id_name']);
        $id_value = ltrim($_POST['id_value']);
        $sort = ltrim($_POST['sort']);
        if(!empty($table) && !empty($id_name) && !empty($id_value)){
            $this->db->query("UPDATE `$table` SET `sort`='$sort' WHERE `$id_name`='$id_value'");
            echo "ok - $id_value - $sort";
        }
    }
    
    // Change database table field
    
    public function changeField(){
        if(!empty($_POST['table_name']) && !empty($_POST['field_name']) && !empty($_POST['id_name']) && !empty($_POST['id_value'])){
            $table_name = ltrim($_POST['table_name']);
            $field_name = ltrim($_POST['field_name']);
            $field_value = ltrim($_POST['field_value']);
            $id_name = ltrim($_POST['id_name']);
            $id_value = ltrim($_POST['id_value']);
            
            
            if($table_name=="products" && $field_name=="cat_1"){
                $this->db->query("UPDATE `products` SET `cat_2`='' WHERE `productID`='$id_value'");
            }
            
            
            if($table_name=="products" && $field_name=="new" && $field_value==1){
                $this->db->query("UPDATE `products` SET `date`=NOW() WHERE `productID`='$id_value'");
            }
            
            
            if($table_name=="order" && $field_name=="status"){
                $order =  $this->getOrder($id_value);
                $user =  $this->getUser($order["userID"]);
                $order_data = json_decode($order["order_data"], true);
                
                
                $order_canceled = count($this->getOrder("", $order["userID"], 4));
                $order_delivered = count($this->getOrder("", $order["userID"], 3));
                if($field_value==3 && $order["status"]!=3){
                    $order_delivered += 1;
                }
                if($field_value==4 && $order["status"]!=4){
                    $order_canceled += 1;
                }
                $this->db->query("UPDATE `user` SET `order_history`='$order_delivered-$order_canceled' WHERE `userID`='".$order["userID"]."'");
                
                
                if($field_value==3 && $order["status"]!=3){
                    for($i=0; $i<count($order_data); $i++){
                       if($order_data[$i]["option_count"] > 0){
                          $_args = [
                              "orderID" => $order["orderID"],
                              "userID" => $order["userID"],
                              "status" => "5",
                              "productID" => $order_data[$i]["productID"],
                              //"title" => "Պատվեր B-".$order["orderID"]."",
                              //"desc" => "Հարգելի ".$user['name'].",\n\nՁեր պատվերը (B-".$order["orderID"]."-".$order_data[$i]["productID"].") Butik.am -ի խանութում է՝ Հ․ Հակոբյան 3 հասցեով։"
                              "title" => "",
                              "desc" => ""
                          ];
                          $this->orderStatusData($_args);
                       }
                    }
                }
                
                if($field_value==4 && $order["status"]!=4){
                    for($i=0; $i<count($order_data); $i++){
                       if($order_data[$i]["option_count"] > 0){
                          $_args = [
                              "orderID" => $order["orderID"],
                              "userID" => $order["userID"],
                              "status" => "6",
                              "productID" => $order_data[$i]["productID"],
                              //"title" => "Պատվեր B-".$order["orderID"]."",
                              //"desc" => "Հարգելի ".$user['name'].",\n\nՁեր պատվերը (B-".$order["orderID"]."-".$order_data[$i]["productID"].") Butik.am -ի խանութում է՝ Հ․ Հակոբյան 3 հասցեով։"
                              "title" => "",
                              "desc" => ""
                          ];
                          $this->orderStatusData($_args);
                       }
                    }
                }

            }
            
            
            if($table_name=="products"){
                if($_SESSION["admin"]["permission"]>1){
                    $this->db->query("UPDATE `products` SET `status`='1' WHERE `productID`='".$id_value."'");
                }
            }
            
            
            if($table_name=="order" && $field_name=="option_count"){
                $order =  $this->getOrder($id_value);
                $user =  $this->getUser($order["userID"]);
                if(empty($order["order_data"])){
                    $order["order_data"] = "{}";
                }
                $order_data = json_decode($order["order_data"], true);
                for($i=0; $i<count($order_data); $i++){
                    if($order_data[$i]["productID"]==$field_value){
                       if($order_data[$i]["option_count"] > 0){
                          $order_data[$i]["option_count"] = "0";
                       }else{
                          $order_data[$i]["option_count"] = "1000";
                          $this->db->query("UPDATE `order` SET `status`='1' WHERE `orderID`='".$order["orderID"]."'");
                          $_args = [
                              "orderID" => $order["orderID"],
                              "userID" => $order["userID"],
                              "status" => "4",
                              "productID" => $order_data[$i]["productID"],
                              "title" => "Պատվեր B-".$order["orderID"]."",
                              "desc" => "Հարգելի ".$user['name'].",\n\nՁեր պատվերը (B-".$order["orderID"]."-".$order_data[$i]["productID"].") Butik.am -ի խանութում է՝ Հ․ Հակոբյան 3 հասցեով։"
                          ];
                          $this->orderStatusData($_args);
                       }
                    }
                }
                $order_data = json_encode($order_data, JSON_UNESCAPED_UNICODE);
                $order_data = str_replace('\n','',$order_data);
                $order_data = str_replace('\r','',$order_data);
                $this->db->query("UPDATE `order` SET `order_data`='$order_data' WHERE `orderID`='".$order["orderID"]."'");
                exit;
            }
            
            
            if($table_name=="order" && $field_name=="name"){
                $order =  $this->getOrder($id_value);
                $user =  $this->getUser($order["userID"]);
                $location_data = json_decode($order["location_data"], true);
                $location_data["name"] = $field_value;
                $location_data = json_encode($location_data, JSON_UNESCAPED_UNICODE);
                $this->db->query("UPDATE `order` SET `location_data`='$location_data' WHERE `orderID`='".$order["orderID"]."'");
                $this->db->query("UPDATE `user` SET `name`='".$field_value."' WHERE `userID`='".$user["userID"]."'");
                exit;
            }
            
            
            if($table_name=="order" && $field_name=="location_address"){
                $order =  $this->getOrder($id_value);
                $user =  $this->getUser($order["userID"]);
                $location_data = json_decode($order["location_data"], true);
                $location_data["location_address"] = (isset($field_value)) ? $field_value : "";
                $location_data = json_encode($location_data, JSON_UNESCAPED_UNICODE);
                $this->db->query("UPDATE `order` SET `location_data`='$location_data' WHERE `orderID`='".$order["orderID"]."'");
                exit;
            }
            
            
            $this->db->query("UPDATE `$table_name` SET `$field_name`='$field_value' WHERE `$id_name`='$id_value'");
        }
    }
    
    // Order Status

    public function orderStatus(){
        if(!empty($_POST["title"]) && !empty($_POST["desc"])){
            $this->user_cnt->addNotification($_POST["userID"], $_POST["title"], nl2br(str_replace("\n", "<br>", $_POST["desc"])));
            sendMailSmtp($_POST['email'], $_POST["title"], nl2br(str_replace("\n", "<br>", $_POST["desc"])));
        }
        echo json_encode([
            "message" => 0,
            "location" => [
                "href" => false,
                "hash" => "close",
                "reload" => false
            ]
        ]);
        return false;
    }
    
    // Order Status

    public function orderStatusData($_args = []){
        if(isset($_POST["productID"])){
            $_args = $_POST;
        }
        
        $order = $this->getOrder($_args["orderID"]);
        $order["status_data"] = (empty($order["status_data"])) ? "{}" : $order["status_data"];
        $order["status_data_simple"] = (empty($order["status_data_simple"])) ? "{}" : $order["status_data_simple"];
        $status_data = json_decode($order["status_data"], true);
        $status_data_simple = json_decode($order["status_data_simple"], true);
        $user =  $this->getUser($_args["userID"]);
        
        for($i=$_args["status"]; $i<=6; $i++){
            unset($status_data[$_args["productID"]][$i]);
        }
        
        if($_args["status"]==3){
            $status_data[$_args["productID"]][$_args["status"]] = [
                "date"=> date("Y-m-d H:i:s"),
                "date_start"=> date("Y-m-d"),
                "date_end"=> (isset($_args["date"])) ? $_args["date"] : date("Y-m-d"),
            ];
        }else{
            $status_data[$_args["productID"]][$_args["status"]] = [
                "date"=> date("Y-m-d H:i:s"),
            ];
        }
        
        $status_data_simple[$_args["productID"]] = $_args["status"];
        
        $status_data = json_encode($status_data, JSON_UNESCAPED_UNICODE);
        $status_data_simple = json_encode($status_data_simple, JSON_UNESCAPED_UNICODE);
        
        $this->db->query("UPDATE `order` SET `status_data`='".$status_data."', `status_data_simple`='".$status_data_simple."' WHERE `orderID`='".$order["orderID"]."'");
        if(isset($_args["title"]) && !empty($_args["title"]) && isset($_args["desc"]) && !empty($_args["desc"]) && $_args["status"]!=0 && $_args["status"]!=5){
            $this->user_cnt->addNotification($user["userID"], $_args["title"], nl2br(str_replace("\n", "<br>", $_args["desc"])));
            sendMailSmtp($user['email'], $_args["title"], nl2br(str_replace("\n", "<br>", $_args["desc"])));
        }
        if(isset($_POST["productID"])){
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => false,
                    "hash" => "close",
                    "reload" => false
                ]
            ]);
            return false;
        }
    }
    
    // List
    
    public function addList(){
        if(!empty($_POST['name_am'])){
            $parent = array_pop($_POST['parent']);
            $this->db->query("INSERT INTO `list`(`group`, `parent`, `name_am`, `name_ru`, `name_en`, `note`)VALUES('".$_POST['group']."', '".$parent."', '".$_POST['name_am']."', '".$_POST['name_ru']."', '".$_POST['name_en']."', 1)");
        }
    }
    
    public function removeList(){
        $this->db->query("DELETE FROM `list` WHERE `listID`='".$_POST['listID']."'");
    }
    
    
    // Option
    
    public function addOption(){
        if(!empty($_POST['color'])){
            $this->db->query("INSERT INTO `option`(`productID`, `color`)VALUES('".$_POST['productID']."', '".$_POST['color']."')");
            if($_SESSION["admin"]["permission"]>1){
                $this->db->query("UPDATE `products` SET `status`='1' WHERE `productID`='".$_POST['productID']."'");
            }
        }
    }
    
    public function removeOption(){
        $res = $this->db->query("SELECT * FROM `photo` WHERE `group`='option' AND `parent`='".$_POST['optionID']."'");
        while($row = $res->fetch_assoc()){
            unlink("public/gallery/option/".$row['photoID'].".jpg");
        }
        $this->db->query("DELETE FROM `photo` WHERE `group`='option' AND `parent`='".$_POST['optionID']."'");
        $this->db->query("DELETE FROM `option` WHERE `optionID`='".$_POST['optionID']."'");
    }
    
    // Currency
    
    public function changeCurrency(){
        if($this->db->query("SELECT * FROM `currency`")->num_rows > 0){
            $this->db->query("UPDATE `currency` SET `AMD`='1', `USD`='".$_POST['USD']."', `RUB`='".$_POST['RUB']."', `EUR`='".$_POST['EUR']."'");
        }else{
            $this->db->query("INSERT INTO `currency`(`AMD`, `USD`, `RUB`, `EUR`)VALUES('1', '".$_POST['USD']."', '".$_POST['RUB']."', '".$_POST['EUR']."')"); 
        }
    }
    
    // Contacts
    
    public function changeContacts(){
        if($this->db->query("SELECT * FROM `contacts`")->num_rows > 0){
            $this->db->query("UPDATE `contacts` SET `address`='".$_POST['address']."', `phone_1`='".$_POST['phone_1']."', `phone_2`='".$_POST['phone_2']."', `phone_3`='".$_POST['phone_3']."'");
        }else{
            $this->db->query("INSERT INTO `contacts`(`address`, `phone_1`, `phone_2`, `phone_3`)VALUES('".$_POST['address']."', '".$_POST['phone_1']."', '".$_POST['phone_2']."', '".$_POST['phone_3']."')"); 
        }
    }
    
    // Admins
    
    public function addAdmin(){
        if(!empty($_POST['name'])){
            $this->db->query("INSERT INTO `admin`(`name`, `permission`, `date`)VALUES('".$_POST['name']."', '2', NOW())");
        }
    }
    
    public function getAdmin($adminID = ""){
        $where = "WHERE `adminID` IS NOT NULL";
        if(!empty($adminID)){
            $where .= " AND `adminID`='$adminID'";
        }
        $admins = $this->db->query("
            SELECT *
            FROM `admin`
            $where
            ORDER BY `adminID` DESC
        ");
        if(!empty($adminID)){
            return $admins->fetch_assoc();
        }else{
            return $admins->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    // Products
    
    public function addProducts(){
        if(!empty($_POST['title_am'])){
            $this->db->query("INSERT INTO `products`(`cat_1`, `cat_2`, `cat_3`, `title_am`, `min_count`, `owner`, `status`, `date`)VALUES('".$_POST['cat_1']."', '".$_POST['cat_2']."', '".$_POST['cat_3']."', '".$_POST['title_am']."', '1', '".$_SESSION["admin"]["adminID"]."', '1', NOW())");
            $productID = $this->db->insert_id;
            header("Location: item?productID=$productID");
            exit;
        }
    }
    
    public function changeProducts(){
        $this->db->query("UPDATE `products` SET `cat_1`='".$_POST['cat_1']."', `cat_2`='".$_POST['cat_2']."', `cat_3`='".$_POST['cat_3']."', `url_tag`='".$_POST['url_tag']."', `product_altID`='".$_POST['product_altID']."', `title_am`='".$_POST['title_am']."', `title_ru`='".$_POST['title_ru']."', `title_en`='".$_POST['title_en']."', `desc_am`='".$_POST['desc_am']."', `desc_ru`='".$_POST['desc_ru']."', `desc_en`='".$_POST['desc_en']."', `delivery_desc`='".$_POST['delivery_desc']."' `guarantee_desc`='".@$_POST['guarantee_desc']."', `new`='".@$_POST['new']."', `quality_assurance`='".@$_POST['quality_assurance']."', `weight`='".$_POST['weight']."', `min_count`='".((isset($_POST['min_count']) && $_POST['min_count'] > 0) ? $_POST['min_count'] : 1)."', `video`='".$_POST['video']."', `similar_products`='".@$_POST['similar_products']."', `amount`='".$_POST['amount']."', `amount_discount`='".$_POST['amount_discount']."' WHERE `productID`='".$_POST['productID']."'");
        if($_SESSION["admin"]["permission"]>1){
            $this->db->query("UPDATE `products` SET `status`='1' WHERE `productID`='".$_POST['productID']."'");
        }
    }
    
    public function removeProducts(){
        $res_1 = $this->db->query("SELECT * FROM `option` WHERE `productID`='".$_POST['productID']."'");
        while($row_1 = $res_1->fetch_assoc()){
            $res_2 = $this->db->query("SELECT * FROM `photo` WHERE `group`='option' AND `parent`='".$row_1['optionID']."'");
            while($row_2 = $res_2->fetch_assoc()){
                unlink("public/gallery/option/".$row_2['photoID'].".jpg");
            }
            $this->db->query("DELETE FROM `photo` WHERE `group`='option' AND `parent`='".$row_1['optionID']."'");
            $this->db->query("DELETE FROM `option` WHERE `optionID`='".$row_1['optionID']."'");
        }   
        
        $res = $this->db->query("SELECT * FROM `photo` WHERE `group`='products' AND `parent`='".$_POST['productID']."'");
        while($row = $res->fetch_assoc()){
            unlink("public/gallery/items/large/".$row['photoID'].".jpg");
            unlink("public/gallery/items/middle/".$row['photoID'].".jpg");
            unlink("public/gallery/items/small/".$row['photoID'].".jpg");
        }
        $this->db->query("DELETE FROM `photo` WHERE `group`='products' AND `parent`='".$_POST['productID']."'");
        
        $this->db->query("DELETE FROM `products` WHERE `productID`='".$_POST['productID']."'");
    }
    
    // Order
    
    public function removeOrder(){
        $this->db->query("DELETE FROM `order` WHERE `orderID`='".$_POST['orderID']."'");
    }
    
    public function removePreOrder(){
        $this->db->query("DELETE FROM `pre_order` WHERE `orderID`='".$_POST['orderID']."'");
    }
    
    // Shut Down
    
    public function shutDown(){
        if(is_file("layouts/default/develop.php")){
            unlink("layouts/default/develop.php");
        }else{
            $content = '<!doctype html><html lang="hy"><head><title>Պրոֆիլակտիկ աշխատանքներ - Butik.am</title><meta name="description" content=""></head><body><div style="max-width:650px;margin:10% auto 0;padding:20px;"><img src="/public/img/logo.png"><h4 style="font-size:26px;color:#333333;margin:20px 0;">Պրոֆիլակտիկ աշխատանքներ</h4><hr style="border:1px solid #e2e2e2;"><p style="color:#808080;font-size:24px;line-height:1.3;">Հայցում ենք Ձեր ներողամտությունը, կայքը կգործարկվի հնարավորինս շուտ։</p></div></body></html>';
            file_put_contents("layouts/default/develop.php", $content);
        }
    }

}