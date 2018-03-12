<?php 
class User extends Model{
    
    public $profile;
    
    public function __construct(){
        parent::__construct();
        if(isset($_SESSION['userID']) && !empty($_SESSION['userID'])){
            $this->profile = $this->getUser($_SESSION['userID']);
        }
    }

    
    public function logOut(){
        unset($_SESSION['userID']);
    }
        
    public function signIn(){  
        if(empty($_POST['email']) || empty($_POST['password'])){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք բոլոր դաշտերը!</p>",
                "field" => ["password", "email"],
                "location" => false
            ]);
            return false;
        }  
        $user = $this->db->query("SELECT `userID` FROM `user` WHERE `email`='".$_POST['email']."' AND `password`='".$_POST['password']."'");
        if($user->num_rows == 1){
            $Row = $user->fetch_assoc();
            $_SESSION['userID'] = $Row['userID'];
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => false,
                    "hash" => false,
                    "reload" => true
                ]
            ]);
            return false;
        }else{
            echo json_encode([
                "message" => "<p class='error'>Սխալ մոտքանուն կամ գաղտնաբառ!</p>",
                "location" => false
            ]);
            return false;
        }
    }

    public function resetPass(){
        if(empty($_POST['email'])){
            echo json_encode([
                "message" => "<p class='error'>Մուտքագրեք Էլ.փոստը</p>",
                "field" => ["email"],
                "location" => false
            ]);
            return false;
        } 
        $user = $this->db->query("SELECT `password`, `email` FROM `user` WHERE `email`='".$_POST['email']."'");
        if($user->num_rows == 1){
            $Row = $user->fetch_assoc();
            if(!isset($_SESSION["reset_pass_notification"])){
                $_SESSION["reset_pass_notification"] = 1;
            }else{
                $_SESSION["reset_pass_notification"]++;
            }
            if($_SESSION["reset_pass_notification"] > 3){
                echo json_encode([
                    "message" => "<p class='error'>Դուք գերազանցել եք թույլատրված սահմանաչափը, խնդրում ենք կրկնել մի փոքր ուշ:</p>",
                    "location" => false
                ]);
                return false;
            }
            sendMailSmtp($Row['email'], "Ծածկագրի վերականգնում", "Ձեր ծածկագիրն է` ".$Row['password']."");
            echo json_encode([
                "message" => "<p class='success'>Ձեր ծածկագիրը ուղարկվել է Ձեր էլեկտրոնային փոստին։</p>",
                "location" => false,
                "reset" => true
            ]);
            return false;
        }else{
            echo json_encode([
                "message" => "<p class='error'>Էլ.փոստը չի գտնվել</p>",
                "location" => false
            ]);
            return false;
        }
    }
    
    public function signUp(){   
        if(empty($_POST['email']) || empty($_POST['password']) || empty($_POST['name']) || empty($_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք բոլոր դաշտերը!</p>",
                "field" => ["password", "email", "name", "phone"],
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("|[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}|i", $_POST['email'])){
            echo json_encode([
                "message" => "<p class='error'>Էլ.փոստը պատշաճ չէ!</p>",
                "field" => ["email"],
                "location" => false
            ]);
            return false;
        }      
        $mail = $this->db->query("SELECT `userID` FROM `user` WHERE `email`='".$_POST['email']."'");
        if($mail->num_rows > 0){
            echo json_encode([
                "message" => "<p class='error'>Էլ.փոստը զբաղված է!</p>",
                "field" => ["email"],
                "location" => false
            ]);
            return false;
        }      
        $phone = $this->db->query("SELECT `userID` FROM `user` WHERE `phone`='".$_POST['phone']."'");
        if($phone->num_rows > 0){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսահամարը զբաղված է!</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/\A(\w){6,20}\Z/",$_POST['password'])){
            echo json_encode([
                "message" => "<p class='error'>Ծածկագիրը պետք է բաղկացած լինի 6-20 նիշերից!</p>",
                "field" => ["password"],
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/^[\/(\/)\- 0-9]{14}$/", $_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսի համարը պետք է բաղկացած լինի 9 նիշերից!</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        $this->db->query("INSERT INTO `user`(`email`, `password`, `phone`, `name`, `date`) VALUES ('".$_POST['email']."', '".$_POST['password']."', '".$_POST['phone']."', '".$_POST['name']."', NOW())");
        $userID = $this->db->insert_id;    
        if(isset($userID) && !empty($userID) && $userID !== NULL){
            $_SESSION['userID'] = $userID;
            sendMailSmtp($_POST['email'], $_POST['name'], "Հարգելի «".$_POST['name']."» Ձեր գրանցումը հաջողությամբ կատարված է`<br><a href='http://butik.am/'>butik.am</a><br>Login: ".$_POST['email']."<br>Password: ".$_POST['password']."");
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => false,
                    "hash" => false,
                    "reload" => true
                ]
            ]);
            return false;
        }else{
            echo json_encode([
                "message" => "<p class='error'>Համակարգային սխալ!</p>",
                "location" => false
            ]);
            return false;
        }
    }
    
    public function FBlogin(){
        $fb = new Facebook\Facebook([
            'app_id' => '1198944430209831',
            'app_secret' => '756aac36b3e823446fe421c6648124b0',
            'default_graph_version' => 'v2.3',
        ]);

        try{
            $response = $fb->get('/me?fields=id,name,email', $_GET["accessToken"]);
        }catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo json_encode([
                "message" => "<p class='error'>".$e->getMessage()."</p>",
                "location" => false
            ]);
            return false;
        }catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo json_encode([
                "message" => "<p class='error'>".$e->getMessage()."</p>",
                "location" => false
            ]);
            return false;
        }

        $user_profile = $response->getGraphUser();
        if(isset($user_profile["id"]) && isset($user_profile["email"]) && isset($user_profile["name"])){
            $fbID = $user_profile["id"];
            $email = $user_profile["email"];
            $password = "FB".rand(000000,999999);
            $name = $user_profile["name"];
            $photo = "https://graph.facebook.com/".$fbID."/picture?type=large&width=720&height=720";
            if(!$email || !$name){
                echo json_encode([
                    "message" => "<p class='error'>Մեզ չհաջողվեց ստանալ Ձեր տվյալները!</p>",
                    "location" => false
                ]);
                return false;
            }
            $user = $this->db->query("SELECT * FROM `user` WHERE `email`='".$email."'");
            $userCol = $user->fetch_assoc();
            if($user->num_rows == 1){
                $_SESSION['userID'] = $userCol['userID'];
                echo json_encode([
                    "message" => 0,
                    "location" => [
                        "href" => false,
                        "hash" => false,
                        "reload" => true
                    ]
                ]);
                return false;
            }elseif($user->num_rows == 0){	
                $this->db->query("INSERT INTO `user`(`email`, `password`, `name`, `phone`, `date`) VALUES ('$email', '$password', '$name', '', NOW())");
                $userID = $this->db->insert_id;
                if($file = file_get_contents($photo)){
                    $this->db->query("INSERT INTO `photo` (`group`, `parent`) VALUES ('user', '".$userID."')");
                    $tmp_name = $this->db->insert_id.".jpg";
                    if(file_put_contents("public/gallery/user/large/".$tmp_name, $file)){
                        addImage("public/gallery/user/large/".$tmp_name, "public/gallery/user/large/".$tmp_name, 600, 600, 'crop', false);
                        addImage("public/gallery/user/large/".$tmp_name, "public/gallery/user/middle/".$tmp_name, 400, 400, 'crop', false);
                        addImage("public/gallery/user/large/".$tmp_name, "public/gallery/user/small/".$tmp_name, 150, 150, 'crop', false);
                    }
                }
                sendMailSmtp($email, $name, "Հարգելի «".$name."» Ձեր գրանցումը հաջողությամբ կատարված է`<br><a href='http://butik.am/'>butik.am</a><br>Login: ".$email."<br>Password: ".$password."");
                $_SESSION['userID'] = $userID;
                echo json_encode([
                    "message" => 0,
                    "location" => [
                        "href" => false,
                        "hash" => false,
                        "reload" => true
                    ]
                ]);
                return false;
            }
        }
    }
    
    
    public function addRss(){
        if(!isset($_POST["email"]) || empty($_POST["email"])){
            echo json_encode([
                "message" => "<p class='error'>Մուտքագրեք էլ․ փոստը</p>",
                "field" => ["email"],
                "location" => false
            ]);
            return false;
        }
        if($this->db->query("SELECT `id` FROM `rss` WHERE `email`='".$_POST["email"]."'")->num_rows > 0){
            echo json_encode([
                "message" => "<p class='error'>Բաժանորդագրումը արդեն կատարված է</p>",
                "field" => ["email"],
                "location" => false
            ]);
            return false;
        }
        $this->db->query("INSERT INTO `rss`(`email`)VALUES('".$_POST["email"]."')");
        echo json_encode([
            "message" => "<p class='success'>Բաժանորդագրումը ընդունված է</p>",
            "location" => false
        ]);
        return false;
    }
    
    
    public function changeUser(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => false,
                    "hash" => "signIn",
                    "reload" => false
                ]
            ]);
            return false;
        }
        if(!isset($_POST['password']) || empty($_POST['password'])){
            $_POST['password']=$this->profile['password'];
        }
        if(empty($_POST['password']) || empty($_POST['name']) || empty($_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք բոլոր դաշտերը!</p>",
                "location" => false
            ]);
            return false;
        }    
        if(!preg_match("/\A(\w){6,20}\Z/",$_POST['password'])){
            echo json_encode([
                "message" => "<p class='error'>Ծածկագիրը պետք է բաղկացած լինի 6-20 նիշերից!</p>",
                "field" => ["password"],
                "location" => false
            ]);
            return false;
        }
        $phone = $this->db->query("SELECT `userID` FROM `user` WHERE `phone`='".$_POST['phone']."' AND `userID`!='".$this->profile["userID"]."'");
        if($phone->num_rows > 0){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսահամարը զբաղված է!</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/^[\/(\/)\- 0-9]{14}$/", $_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսի համարը պետք է բաղկացած լինի 9 նիշերից!</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        $this->db->query("UPDATE `user` SET `password`='".$_POST['password']."', `phone`='".$_POST['phone']."', `name`='".$_POST['name']."', `birthday`='".$_POST['birthday']."', `location`='".@$_POST['location']."', `address`='".$_POST['address']."' WHERE `userID`='".$this->profile['userID']."'");   
        echo json_encode([
            "message" => 0,
            "location" => [
                "href" => false,
                "hash" => false,
                "reload" => true
            ]
        ]);
        return false;
    }
    
    
    public function addPhoto($group = "", $parent = "", $file = []){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        if(isset($_POST['group']) && isset($_POST['parent']) && isset($_FILES['file'])){
            $group = $_POST['group'];
            $parent = $_POST['parent'];
            $file = $_FILES['file'];
        }
        if(empty($group) || empty($parent) || empty($file)){
            return false;
        }
        if($group == 'user'){
            if($parent != $this->profile["userID"]){
                return false;
            }
            foreach ($file["error"] as $key => $error) {
                if(count($this->getPhoto($group, $parent)) > 10){
                    return false;
                }
                if (strtolower($file['type'][$key]) != 'image/png'
                && strtolower($file['type'][$key]) != 'image/jpg'
                && strtolower($file['type'][$key]) != 'image/gif'
                && strtolower($file['type'][$key]) != 'image/jpeg'){
                    return false;
                }
                if ($file['size'][$key] > 10000000) {
                    return false;
                }
                $this->db->query("INSERT INTO `photo` (`group`, `parent`, `sort`) VALUES ('$group', '$parent', '0')");
                $name = $this->db->insert_id.".jpg";
                addImage($file['tmp_name'][$key], "public/gallery/$group/large/".$name, 600, 600, 'crop', false);
                addImage($file['tmp_name'][$key], "public/gallery/$group/middle/".$name, 400, 400, 'crop', false);
                addImage($file['tmp_name'][$key], "public/gallery/$group/small/".$name, 150, 150, 'crop', false);
            }
        }
        if($group == 'receipt'){
            if(!isset($this->getOrder($parent, $this->profile["userID"])["orderID"])){
                return false;
            }
            foreach ($file["error"] as $key => $error) {
                if (strtolower($file['type'][$key]) != 'image/png'
                && strtolower($file['type'][$key]) != 'image/jpg'
                && strtolower($file['type'][$key]) != 'image/gif'
                && strtolower($file['type'][$key]) != 'image/jpeg'){
                    return false;
                }
                if ($file['size'][$key] > 10000000) {
                    return false;
                }
                $this->db->query("INSERT INTO `photo` (`group`, `parent`, `sort`) VALUES ('$group', '$parent', '0')");
                $name = $this->db->insert_id.".jpg";
                addImage($file['tmp_name'][$key], "public/gallery/$group/".$name, 700, 700, 'resize', false);
            }
        }
    }
    
    public function removePhoto($group = "", $parent = "", $photoID = ""){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        if(isset($_POST['group']) && isset($_POST['parent']) && isset($_POST['photoID'])){
            $group = $_POST['group'];
            $parent = $_POST['parent'];
            $photoID = $_POST['photoID'];
        }
        if(empty($group) || empty($parent) || empty($photoID)){
            return false;
        }
        if($group=='user'){
            if($parent != $this->profile["userID"]){
                return false;
            }
            $this->db->query("DELETE FROM `photo` WHERE `photoID`='$photoID'");
            unlink("public/gallery/$group/large/$photoID.jpg");
            unlink("public/gallery/$group/middle/$photoID.jpg");
            unlink("public/gallery/$group/small/$photoID.jpg");
        }
        if($group=='receipt'){
            if(!isset($this->getOrder($parent, $this->profile["userID"])["orderID"])){
                return false;
            }
            $this->db->query("DELETE FROM `photo` WHERE `photoID`='$photoID'");
            unlink("public/gallery/$group/$photoID.jpg");
        }
    }
    
    
    public function addRating(){
        $productID = (int) $_GET['id'];
        $rating = (int) $_GET['rating'];
        
        if(empty($productID) || empty($rating)){
            return false;
        }
        
        $row = $this->db->query("SELECT `rating`, `rating_count`, `productID` FROM `products` WHERE `productID`='$productID'")->fetch_assoc();
        
        if(isset($_SESSION["rating"][$productID])){
            if($row["rating_count"] > 1){
                $row["rating"] = ($row["rating"] * $row["rating_count"] - $_SESSION["rating"][$productID]) / ($row["rating_count"] - 1);
                $row["rating_count"] = $row["rating_count"] - 1;
            }else{
                $row["rating"] = 0;
                $row["rating_count"] = 0;
            }
        }
        $_SESSION["rating"][$productID] = $rating;
        
        $rating_total = (@$row["rating"] * @$row["rating_count"] + $rating) / (@$row["rating_count"] + 1);
        $rating_count = @$row["rating_count"] + 1;
        
        $this->db->query("UPDATE `products` SET `rating`='$rating_total', `rating_count`='$rating_count' WHERE `productID`='".$row["productID"]."'");
        
        echo $rating_total;
    }
    
    public function addView($productID = ""){
        if(!empty($productID)){
            $this->db->query("UPDATE `products` SET `view_count`=`view_count`+1 WHERE `productID`='".$productID."'");
        }
    }
    
    
    public function addPreOrder(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => false,
                    "hash" => "signIn",
                    "reload" => false
                ]
            ]);
            return false;
        }
        if(empty($_POST['productID']) || empty($_POST['count']) || empty($_POST['name']) || empty($_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք բոլոր դաշտերը</p>",
                "location" => false
            ]);
            return false;
        }
        $this->db->query("UPDATE `user` SET `phone`='".$_POST['phone']."', `name`='".$_POST['name']."' WHERE `userID`='".$this->profile['userID']."'");  

        if(count($this->getOption($_POST['productID'])) > 1 && !isset($_POST['optionID'])){
            echo json_encode([
                "message" => "<p class='error'>Գույնը ընտրված չէ</p>",
                "location" => false
            ]);
            return false;
        }
        $optionID = (isset($_POST['optionID'])) ? $_POST['optionID'] : 0;
        $size = (isset($_POST['size'])) ? $_POST['size'] : "";
        $pre_order = $this->getPreOrder($this->profile['userID'], "", $_POST['orderID']);
        if(isset($pre_order["orderID"])){
            $note = (!empty($_POST['note'])) ? $_POST['note'] : $pre_order["note"];
            $this->db->query("UPDATE `pre_order` SET `optionID`='$optionID', `size`='$size', `count`='".$_POST['count']."', `note`='".$note."', `date`=NOW() WHERE `orderID`='".$pre_order["orderID"]."'");
        }else{
            $this->db->query("INSERT INTO `pre_order`(`userID`, `productID`, `optionID`, `size`, `count`, `note`, `date`)VALUES('".$this->profile['userID']."', '".$_POST['productID']."', '$optionID', '$size', '".$_POST['count']."', '".$_POST['note']."', NOW())");
            $orderID = $this->db->insert_id;
            $subject = "Պատվեր ".$orderID."";
            $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերը ընդունված է։ Պատվերի կատարման մասին շուտով կստանաք մանրամասն տեղեկատվություն։<br><br>Ձեր պատվերի համարն է՝ B-".$orderID."<br><br><br>Շնորհակալություն գնումների համար";
            $subject_not = "Հարգելի ".$this->profile['name']."";
            $message_not = "Ձեր պատվերը ընդունված է։ Պատվերի կատարման մասին շուտով կստանաք մանրամասն տեղեկատվություն։<br><br>Ձեր պատվերի համարն է՝ B-".$orderID."<br><br>Շնորհակալություն գնումների համար";
            $this->addNotification($this->profile["userID"], $subject_not, $message_not);
            sendMailSmtp($this->profile['email'], $subject, $message);
            sendMailSmtp("shop@butik.am", "Նախնական պատվեր՝ ".$orderID."", "Պատվիրատու՝ ".$this->profile['name']."");
        }
        echo json_encode([
            "message" => 0,
            "location" => [
                "href" => (isset($_GET["refrash"])) ? "/profile/pre-orders" : "/profile/notification#getStatus?cat=1",
                "hash" => false,
                "reload" => false
            ]
        ]);
        return false;
    }
    
    public function removePreOrder(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => false,
                    "hash" => "signIn",
                    "reload" => false
                ]
            ]);
            return false;
        }
        $this->db->query("DELETE FROM `pre_order` WHERE `productID`='".$_GET['productID']."' AND `userID`='".$this->profile['userID']."'");
    }
  
    
    public function getToCart(){
        if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])){
            $data = [];
            foreach($_SESSION['cart'] as $productID => $count){
                 $data[] = array_merge($this->getProductsCart($productID), array(
                     "count" => $count,
                     "optionID" => $_SESSION['optionID'][$productID],
                     "option_photoID" => (isset($this->getPhoto('option', $_SESSION['optionID'][$productID], 1)["photoID"])) ? $this->getPhoto('option', $_SESSION['optionID'][$productID], 1)["photoID"] : false,
                     "option_count" => $_SESSION['option_count'][$productID],
                     "size" => $_SESSION['size'][$productID]
                 ));
            }
            return $data;
        }else{
            return [];
        }
    }
    
    public function getToCartJSON(){
        echo json_encode($this->getToCart());
    }
    
    public function addToCart(){
        if(!isset($_GET["productID"]) || empty($_GET["productID"])){
            return false;
        }
        $product = $this->getProducts(["productID"=>$_GET["productID"]]);
        $productID = $product["productID"];
        $min_count = (!empty($product["min_count"])) ? $product["min_count"] : 1;
        
        //$optionID = (isset($_GET["optionID"])) ? $_GET["optionID"] : $this->getOption($productID, "", true)[0]["optionID"];
        $optionID = (isset($_GET["optionID"])) ? $_GET["optionID"] : $this->getOption($productID, "")[0]["optionID"];
        $option_count = $this->getOption("", $optionID)["count"];
        
        if(isset($_SESSION["cart"][$productID]) && @$_GET["type"]!="all"){
            $_SESSION["cart"][$productID]+=$min_count;
            $_SESSION["optionID"][$productID]=$optionID;
            $_SESSION["option_count"][$productID]=$option_count;
            $_SESSION["size"][$productID] = (isset($_GET["size"])) ? $_GET["size"] : "";
        }else{
            $_SESSION["cart"][$productID] = (isset($_GET["count"])) ? $_GET["count"] : $min_count;
            $_SESSION["optionID"][$productID]=$optionID;
            $_SESSION["option_count"][$productID]=$option_count;
            $_SESSION["size"][$productID] = (isset($_GET["size"])) ? $_GET["size"] : "";
        }
        echo json_encode([
            "productID" => "$productID",
            "count" => count($_SESSION["cart"][$productID]),
            "optionID" => "".$_SESSION["optionID"][$productID]."",
            "option_count" => "".$_SESSION["option_count"][$productID]."",
            "size" => "".$_SESSION["size"][$productID].""
        ]);
        
    }
    
    public function removeToCart(){
        if(!isset($_GET["productID"]) || empty($_GET["productID"])){
            return false;
        }
        $product = $this->getProducts(["productID"=>$_GET["productID"]]);
        $productID = $product["productID"];
        $min_count = (!empty($product["min_count"])) ? $product["min_count"] : 1;
        
        if(isset($_SESSION["cart"][$productID]) && !empty($_SESSION["cart"][$productID])){
            if($_SESSION["cart"][$productID] == $min_count || (isset($_GET["type"]) && $_GET["type"]=="all")){
                unset($_SESSION["cart"][$productID]);
            }elseif($_SESSION["cart"][$productID] > $min_count){
                $_SESSION["cart"][$productID] -= $min_count;
            }
        }
        echo json_encode([
            "productID" => "$productID",
            "count" => (isset($_SESSION["cart"][$productID])) ? count($_SESSION["cart"][$productID]) : 0
        ]);
        
    }
    
    
    public function addOrder(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => "/step-2",
                    "hash" => false,
                    "reload" => false
                ]
            ]);
            return false;
        }
        if(!isset($_POST['location_city']) || empty($_POST['location_city'])){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք առաքման մարզը։</p>",
                "field" => ["location_city"],
                "location" => false
            ]);
            return false;
        }
        if(count($this->getList("location", $_POST['location_city'])) > 0 && (!isset($_POST["location_area"]) || empty($_POST["location_area"]))){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք Ձեր բնակավայրը։</p>",
                "field" => ["location_area"],
                "location" => false
            ]);
            return false;
        }
        if((@$_POST['location_city']==66 && @$_POST['delivery_method']!=204) && (!isset($_POST['location_address']) || empty($_POST['location_address']))){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք առաքման հասցեն։</p>",
                "field" => ["location_address"],
                "location" => false
            ]);
            return false;
        }
        if(!isset($_POST['phone']) || empty($_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք հեռախոսահամարը։</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/^[\/(\/)\- 0-9]{14}$/", $_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսի համարը պետք է բաղկացած լինի 9 նիշերից!</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        if(!isset($_POST['delivery_method']) || empty($_POST['delivery_method'])){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք առաքման եղանակը։</p>",
                "location" => false
            ]);
            return false;
        }
        if(!isset($_POST['pay_method']) || empty($_POST['pay_method'])){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք վճարման եղանակը։</p>",
                "location" => false
            ]);
            return false;
        }
        $order_data = json_encode($this->getToCart(), JSON_UNESCAPED_UNICODE);
        $order_data = checkVariable($order_data, false);
        $location_data = json_encode([
            "location_city" => $_POST["location_city"],
            "location_area" => (isset($_POST["location_area"]) && !empty($_POST["location_area"])) ? $_POST["location_area"] : "",
            "location_address" => (isset($_POST["location_address"]) && !empty($_POST["location_address"])) ? $_POST["location_address"] : "",
            "location_code" => $_POST["location_code"],
            "name" => $this->profile["name"],
            "phone" => $_POST["phone"],
            "notes" => "",
        ], JSON_UNESCAPED_UNICODE);
        
        $total = 0;
        foreach($this->getToCart() as $products){
            $total += $products['count'] * $products['amount'];
        }
        
        $delivery = $this->getList("", "", $_POST['delivery_method']);
        if($delivery['listID']==206){
            $weight = 0;
            foreach($this->getToCart() as $cart){
                $weight += $cart["weight"] * $cart["count"];
            }
            if($weight >= 1000){
                $delivery["note_2"] = 1000;
            }elseif($weight >= 500){
                $delivery["note_2"] = 700;
            }elseif($weight >= 250){
                $delivery["note_2"] = 500;
            }elseif($weight >= 0){
                $delivery["note_2"] = 350;
            }
        }
        
        if(isset($_SESSION["orderID"])){
            $order =  $this->getOrder($_SESSION["orderID"]);
            if(!isset($order["orderID"]) || empty($order["orderID"])){
                unset($_SESSION["cart"]);
                unset($_SESSION["orderID"]);
            }
        }
        
        if(!isset($order["orderID"])){
            $this->db->query("INSERT INTO `order`(`userID`, `pay_method`, `delivery_method`, `location_data`, `order_data`, `total`, `delivery`, `date`)VALUES('".$this->profile['userID']."', '".$_POST['pay_method']."', '".$_POST['delivery_method']."', '".$location_data."', '".$order_data."', '".$total."', '".$delivery["note_2"]."', NOW())");
            $_SESSION["orderID"] = $this->db->insert_id;
        }else{
            $this->db->query("UPDATE `order` SET `pay_method`='".$_POST['pay_method']."', `delivery_method`='".$_POST['delivery_method']."', `location_data`='".$location_data."', `order_data`='".$order_data."', `total`='".$total."', `delivery`='".$delivery["note_2"]."' WHERE `orderID`='".$order["orderID"]."'");
        }
        echo json_encode([
            "message" => 0,
            "location" => [
                "href" => "/step-4",
                "hash" => false,
                "reload" => false
            ]
        ]);
        return false;
    }

    public function confirmOrder(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        if(!isset($_SESSION["orderID"]) || empty($_SESSION["orderID"])){
            return false;
        }
        if(empty($_POST["name"]) || empty($_POST["phone"]) || (isset($_POST["location_address"]) && empty($_POST["location_address"]))){
            echo json_encode([
                "message" => "<p class='error'>Առաքման տվյալները թերի են լրացված։</p>",
                "location" => false
            ]);
            return false;
        }
        if(substr_count($_POST['name'], " ") < 1){
            echo json_encode([
                "message" => "<p class='error'>Ձեր «Անուն Ազգանունը» պատշաճ լրացված չէ։</p>",
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/^[\/(\/)\- 0-9]{14}$/", $_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսի համարը պետք է բաղկացած լինի 9 նիշերից!</p>",
                "location" => false
            ]);
            return false;
        }

        $order =  $this->getOrder($_SESSION["orderID"]);
        if(!isset($order["orderID"]) || empty($order["orderID"])){
            unset($_SESSION["cart"]);
            unset($_SESSION["orderID"]);
            echo json_encode([
                "message" => "<p class='error'>Պատվերի խափանում</p>",
                "location" => false
            ]);
            return false;            
        }
        
        if(!isset($order["total"]) || empty($order["total"])){
            echo json_encode([
                "message" => "<p class='error'>Գումարը նշված չէ</p>",
                "location" => false
            ]);
            return false;
        }
        
        //$delivery = $this->getList("", "", $order["delivery_method"]);
        
        $location_data = json_decode($order["location_data"], true);
        $location_data["location_address"] = (isset($_POST["location_address"])) ? $_POST["location_address"] : "";
        $location_data["phone"] = $_POST["phone"];
        $location_data["name"] = $_POST["name"];
        $location_data["notes"] = (isset($_POST["notes"]) && !empty($_POST["notes"])) ? $_POST["notes"] : "";
        $location_data = json_encode($location_data, JSON_UNESCAPED_UNICODE);
        $this->db->query("UPDATE `order` SET `location_data`='$location_data' WHERE `orderID`='".$order["orderID"]."'");
        
        if($order["status"]==0){
            $order_data = json_decode($order["order_data"], true);
            foreach($order_data as $order_data_item){
                if($this->getOption("", $order_data_item['optionID'])["count"] > 0){
                    if($this->getOption("", $order_data_item['optionID'])["count"] < $order_data_item["count"]){
                        echo json_encode([
                            "message" => "<p class='error'>Տվյալ տեսակի մաքսիմալ քանակը գերազանցված է։</p>",
                            "location" => false
                        ]);
                        return false;
                    }
                    $this->db->query("UPDATE `option` SET `count`=`count`-".$order_data_item["count"]." WHERE `optionID`='".$order_data_item["optionID"]."'");
                }
            }
            $this->db->query("UPDATE `order` SET `status`='1' WHERE `orderID`='".$order["orderID"]."'");
        }
        
        unset($_SESSION["cart"]);
        unset($_SESSION["orderID"]);      
        
        if($order["pay_method"] == "pay_by_card"){
            $this->getPayArca($order["orderID"], ($order["total"] + $order["delivery"]));
        }elseif($order['pay_method'] == "pay_by_idram"){
            $this->getPayIdram($order["orderID"], ($order["total"] + $order["delivery"]));
        }else{
            if($order['pay_method'] == "pay_by_terminal"){
                $subject = "Պատվեր B-".$order["orderID"]."";
                $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է Idram, TellCell կամ EasyPay տերմինալներից Butik.am -ի <b>Idram</b> հաշվին <b>(295969939)</b> մուտք անել «".($order["total"] + $order["delivery"])."» ՀՀ դրամ, նկարել վճարման կտրոնը և տեղադրել այն Butik.am -ի Ձեր էջում։<br><br><br>Շնորհակալություն գնումների համար";
                $subject_not = "Հարգելի ".$this->profile['name']."";
                $message_not = "Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է Idram, TellCell կամ EasyPay տերմինալներից Butik.am -ի <b>Idram</b> հաշվին <b>(295969939)</b> մուտք անել «".($order["total"] + $order["delivery"])."» ՀՀ դրամ, նկարել վճարման կտրոնը և տեղադրել այն Butik.am -ի Ձեր էջում։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                $this->addNotification($this->profile["userID"], $subject_not, $message_not);
                sendMailSmtp($this->profile['email'], $subject, $message);
                sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$this->profile['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
            }elseif($order['pay_method'] == "pay_by_post"){
                $subject = "Պատվեր B-".$order["orderID"]."";
                $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է մոտենալ մոտակա բանկ կամ փոստային բաժանմունք և փոխանցում կատարել Ձեր պատվերի դիմաց<br><br>Կազմակերպության անվանումը՝ «Բիգտեխլայն» ՍՊԸ<br>Կոնվերսբանկ ՓԲԸ՝ ՀՀ 19300 32483440200<br>Փոխանցման նպատակը՝ պատվեր B-".$order["orderID"]."<br>Գումարի չափը՝ ".($order["total"] + $order["delivery"])." ՀՀ դրամ<br><br><br>Շնորհակալություն գնումների համար";
                $subject_not = "Հարգելի ".$this->profile['name']."";
                $message_not = "Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է մոտենալ մոտակա փոստային բաժանմունք և փոխանցում կատարել Ձեր պատվերի դիմաց<br><br>Կազմակերպության անվանումը՝ «Բիգտեխլայն» ՍՊԸ<br>Կոնվերսբանկ ՓԲԸ՝ ՀՀ 19300 32483440200<br>Փոխանցման նպատակը՝ պատվեր B-".$order["orderID"]."<br>Գումարի չափը՝ ".($order["total"] + $order["delivery"])." ՀՀ դրամ<br><br><br>Շնորհակալություն գնումների համար";
                $this->addNotification($this->profile["userID"], $subject_not, $message_not);
                sendMailSmtp($this->profile['email'], $subject, $message);
                sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$this->profile['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
            }else{
                $subject = "Պատվեր B-".$order["orderID"]."";
                $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերը ընդունված է։ Պատվերի կատարման մասին շուտով կստանաք մանրամասն տեղեկատվություն։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                $subject_not = "Հարգելի ".$this->profile['name']."";
                $message_not = "Ձեր պատվերը ընդունված է։ Պատվերի կատարման մասին շուտով կստանաք մանրամասն տեղեկատվություն։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                $this->addNotification($this->profile["userID"], $subject_not, $message_not);
                sendMailSmtp($this->profile['email'], $subject, $message);
                sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$this->profile['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
            }
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => "/profile/notification#getStatus?cat=2&orderID=".$order["orderID"]."",
                    "hash" => false,
                    "reload" => false
                ]
            ]);
            return false;
        }
    }
    
    
    public function addOrderBuyforme(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => "/buyforme/step-1",
                    "hash" => false,
                    "reload" => false
                ]
            ]);
            return false;
        }
        if(!isset($_POST['location_city']) || empty($_POST['location_city'])){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք առաքման մարզը։</p>",
                "field" => ["location_city"],
                "location" => false
            ]);
            return false;
        }
        if(count($this->getList("location", $_POST['location_city'])) > 0 && (!isset($_POST["location_area"]) || empty($_POST["location_area"]))){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք Ձեր բնակավայրը։</p>",
                "field" => ["location_area"],
                "location" => false
            ]);
            return false;
        }
        if((@$_POST['location_city']==66 && @$_POST['delivery_method']!=204) && (!isset($_POST['location_address']) || empty($_POST['location_address']))){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք առաքման հասցեն։</p>",
                "field" => ["location_address"],
                "location" => false
            ]);
            return false;
        }
        if(!isset($_POST['phone']) || empty($_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Լրացրեք հեռախոսահամարը։</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/^[\/(\/)\- 0-9]{14}$/", $_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսի համարը պետք է բաղկացած լինի 9 նիշերից!</p>",
                "field" => ["phone"],
                "location" => false
            ]);
            return false;
        }
        if(!isset($_POST['delivery_method']) || empty($_POST['delivery_method'])){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք առաքման եղանակը։</p>",
                "location" => false
            ]);
            return false;
        }
        if(!isset($_POST['pay_method']) || empty($_POST['pay_method'])){
            echo json_encode([
                "message" => "<p class='error'>Ընտրեք վճարման եղանակը։</p>",
                "location" => false
            ]);
            return false;
        }
        $order_data = $_SESSION["product"];
        unset($order_data["desc"]);
        $order_data["title"] = mb_convert_encoding($order_data["title"], "UTF8", "UTF8");
        $order_data = json_encode($order_data, JSON_UNESCAPED_UNICODE);
        $location_data = json_encode([
            "location_city" => $_POST["location_city"],
            "location_area" => (isset($_POST["location_area"]) && !empty($_POST["location_area"])) ? $_POST["location_area"] : "",
            "location_address" => (isset($_POST["location_address"]) && !empty($_POST["location_address"])) ? $_POST["location_address"] : "",
            "location_code" => $_POST["location_code"],
            "name" => $this->profile["name"],
            "phone" => $_POST["phone"],
            "notes" => "",
        ], JSON_UNESCAPED_UNICODE);
        
        $total = $_SESSION["product"]["option"]['count'] * $_SESSION["product"]['amount'];
        
        /*
        $delivery = $this->getList("", "", $_POST['delivery_method']);
        if($delivery['listID']==206){
            $weight = 0;
            foreach($this->getToCart() as $cart){
                $weight += $cart["weight"] * $cart["count"];
            }
            if($weight >= 1000){
                $delivery["note_2"] = 1000;
            }elseif($weight >= 500){
                $delivery["note_2"] = 700;
            }elseif($weight >= 250){
                $delivery["note_2"] = 500;
            }elseif($weight >= 0){
                $delivery["note_2"] = 350;
            }
        }
        */
        $delivery["note_2"] = 1000;
        
        if(isset($_SESSION["orderID"])){
            $order =  $this->getOrderBuyforme($_SESSION["orderID"]);
            if(!isset($order["orderID"]) || empty($order["orderID"])){
                unset($_SESSION["orderID"]);
            }
        }
        
        if(!isset($order["orderID"])){
            $this->db->query("INSERT INTO `order_buyforme`(`userID`, `pay_method`, `delivery_method`, `location_data`, `order_data`, `total`, `delivery`, `date`)VALUES('".$this->profile['userID']."', '".$_POST['pay_method']."', '".$_POST['delivery_method']."', '".$location_data."', '".$order_data."', '".$total."', '".$delivery["note_2"]."', NOW())");
            $_SESSION["orderID"] = $this->db->insert_id;
        }else{
            $this->db->query("UPDATE `order_buyforme` SET `pay_method`='".$_POST['pay_method']."', `delivery_method`='".$_POST['delivery_method']."', `location_data`='".$location_data."', `order_data`='".$order_data."', `total`='".$total."', `delivery`='".$delivery["note_2"]."' WHERE `orderID`='".$order["orderID"]."'");
        }
        echo json_encode([
            "message" => 0,
            "location" => [
                "href" => "/buyforme/step-3",
                "hash" => false,
                "reload" => false
            ]
        ]);
        return false;
    }

    public function confirmOrderBuyforme(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        if(!isset($_SESSION["orderID"]) || empty($_SESSION["orderID"])){
            return false;
        }
        if(empty($_POST["name"]) || empty($_POST["phone"]) || (isset($_POST["location_address"]) && empty($_POST["location_address"]))){
            echo json_encode([
                "message" => "<p class='error'>Առաքման տվյալները թերի են լրացված։</p>",
                "location" => false
            ]);
            return false;
        }
        if(substr_count($_POST['name'], " ") < 1){
            echo json_encode([
                "message" => "<p class='error'>Ձեր «Անուն Ազգանունը» պատշաճ լրացված չէ։</p>",
                "location" => false
            ]);
            return false;
        }
        if(!preg_match("/^[\/(\/)\- 0-9]{14}$/", $_POST['phone'])){
            echo json_encode([
                "message" => "<p class='error'>Հեռախոսի համարը պետք է բաղկացած լինի 9 նիշերից!</p>",
                "location" => false
            ]);
            return false;
        }

        $order =  $this->getOrderBuyforme($_SESSION["orderID"]);
        if(!isset($order["orderID"]) || empty($order["orderID"])){
            unset($_SESSION["orderID"]);
            echo json_encode([
                "message" => "<p class='error'>Պատվերի խափանում</p>",
                "location" => false
            ]);
            return false;            
        }
        
        if(!isset($order["total"]) || empty($order["total"])){
            echo json_encode([
                "message" => "<p class='error'>Գումարը նշված չէ</p>",
                "location" => false
            ]);
            return false;
        }
        
        //$delivery = $this->getList("", "", $order["delivery_method"]);
        
        $location_data = json_decode($order["location_data"], true);
        $location_data["location_address"] = (isset($_POST["location_address"])) ? $_POST["location_address"] : "";
        $location_data["phone"] = $_POST["phone"];
        $location_data["name"] = $_POST["name"];
        $location_data["notes"] = (isset($_POST["notes"]) && !empty($_POST["notes"])) ? $_POST["notes"] : "";
        $location_data = json_encode($location_data, JSON_UNESCAPED_UNICODE);
        $this->db->query("UPDATE `order` SET `location_data`='$location_data' WHERE `orderID`='".$order["orderID"]."'");
        
        if($order["status"]==0){
            $this->db->query("UPDATE `order_buyforme` SET `status`='1' WHERE `orderID`='".$order["orderID"]."'");
        }

        unset($_SESSION["orderID"]);      
        
        if($order["pay_method"] == "pay_by_card" && $order["status_local"]==1){
            $this->getPayArca($order["orderID"], ($order["total"] + $order["delivery"]));
        }elseif($order['pay_method'] == "pay_by_idram" && $order["status_local"]==1){
            $this->getPayIdram($order["orderID"], ($order["total"] + $order["delivery"]));
        }else{
            if($order['pay_method'] == "pay_by_terminal" && $order["status_local"]==1){
                $subject = "Պատվեր B-".$order["orderID"]."";
                $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է Idram, TellCell կամ EasyPay տերմինալներից Butik.am -ի <b>Idram</b> հաշվին <b>(295969939)</b> մուտք անել «".($order["total"] + $order["delivery"])."» ՀՀ դրամ, նկարել վճարման կտրոնը և տեղադրել այն Butik.am -ի Ձեր էջում։<br><br><br>Շնորհակալություն գնումների համար";
                $subject_not = "Հարգելի ".$this->profile['name']."";
                $message_not = "Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է Idram, TellCell կամ EasyPay տերմինալներից Butik.am -ի <b>Idram</b> հաշվին <b>(295969939)</b> մուտք անել «".($order["total"] + $order["delivery"])."» ՀՀ դրամ, նկարել վճարման կտրոնը և տեղադրել այն Butik.am -ի Ձեր էջում։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                $this->addNotification($this->profile["userID"], $subject_not, $message_not);
                sendMailSmtp($this->profile['email'], $subject, $message);
                sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$this->profile['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
            }elseif($order['pay_method'] == "pay_by_post" && $order["status_local"]==1){
                $subject = "Պատվեր B-".$order["orderID"]."";
                $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է մոտենալ մոտակա փոստային բաժանմունք և փոխանցում կատարել Ձեր պատվերի դիմաց<br><br>Կազմակերպության անվանումը՝ «Բիգտեխլայն» ՍՊԸ<br>Կոնվերսբանկ ՓԲԸ՝ ՀՀ 19300 32483440200<br>Փոխանցման նպատակը՝ պատվեր B-".$order["orderID"]."<br>Գումարի չափը՝ ".($order["total"] + $order["delivery"])." ՀՀ դրամ<br><br><br>Շնորհակալություն գնումների համար";
                $subject_not = "Հարգելի ".$this->profile['name']."";
                $message_not = "Ձեր պատվերն ընդունված է, վճարում կատարելու համար անհրաժեշտ է մոտենալ մոտակա փոստային բաժանմունք և փոխանցում կատարել Ձեր պատվերի դիմաց<br><br>Կազմակերպության անվանումը՝ «Բիգտեխլայն» ՍՊԸ<br>Կոնվերսբանկ ՓԲԸ՝ ՀՀ 19300 32483440200<br>Փոխանցման նպատակը՝ պատվեր B-".$order["orderID"]."<br>Գումարի չափը՝ ".($order["total"] + $order["delivery"])." ՀՀ դրամ<br><br><br>Շնորհակալություն գնումների համար";
                $this->addNotification($this->profile["userID"], $subject_not, $message_not);
                sendMailSmtp($this->profile['email'], $subject, $message);
                sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$this->profile['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
            }else{
                $subject = "Պատվեր B-".$order["orderID"]."";
                $message = "Հարգելի ".$this->profile['name'].",<br><br>Ձեր պատվերը ընդունված է։ Պատվերի կատարման մասին շուտով կստանաք մանրամասն տեղեկատվություն։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                $subject_not = "Հարգելի ".$this->profile['name']."";
                $message_not = "Ձեր պատվերը ընդունված է։ Պատվերի կատարման մասին շուտով կստանաք մանրամասն տեղեկատվություն։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                $this->addNotification($this->profile["userID"], $subject_not, $message_not);
                sendMailSmtp($this->profile['email'], $subject, $message);
                sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$this->profile['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
            }
            echo json_encode([
                "message" => 0,
                "location" => [
                    "href" => "/profile/notification#getStatus?cat=2&orderID=".$order["orderID"]."",
                    "hash" => false,
                    "reload" => false
                ]
            ]);
            return false;
        }
    }
    
    
    private function getPayArca($orderID, $amount){
        $merchant = '';
        $password = '';
        $currency = '051';
        $return_url = urlencode((isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER["HTTP_HOST"].'/?cmd=getConfirmArca');
        $language = 'hy';
        $description = urlencode('Butik.am / Order Payment');
        
        $this->db->query("INSERT INTO `arca`(`orderID`, `mdorder`, `amount`, `status`, `date`)VALUES('".$orderID."', '', '".$amount."', '0', NOW())");
        $arcaID = $this->db->insert_id;
        
        if($data = file_get_contents('https://ipay.arca.am/payment/rest/register.do?userName='.$merchant.'&password='.$password.'&orderNumber='.$arcaID.'&amount='.$amount.'00&currency='.$currency.'&returnUrl='.$return_url.'&description='.$description.'&language='.$language.'')){
            $data = json_decode($data);
            $arca_orderId = $data->orderId;
            $form_url = $data->formUrl;
            $error_code = $data->errorCode;
            //$error_message = $data->errorMessage;
            if($error_code == 0 && $form_url != '') {
                $this->db->query("UPDATE `arca` SET `mdorder`='$arca_orderId' WHERE `arcaID`='$arcaID'");
                echo json_encode([
                    "message" => 0,
                    "location" => [
                        "href" => ''.$form_url.'',
                        "hash" => false,
                        "reload" => false
                    ]
                ]);
                return false;
            }else{
                echo json_encode([
                    "message" => "<p class='error'>Վճարման խափանում</p>",
                    "location" => false
                ]);
                return false;
            }
        }else{
            echo json_encode([
                "message" => "<p class='error'>Վճարման խափանում</p>",
                "location" => false
            ]);
            return false;
        }
    }
    
    public function getConfirmArca(){
        $merchant = '';
        $password = '';
        $language = 'hy';
        $arca_orderId = $_GET['orderId'];
        if(isset($arca_orderId) && !empty($arca_orderId)){
            if($data = file_get_contents('https://ipay.arca.am/payment/rest/getOrderStatus.do?userName='.$merchant.'&password='.$password.'&orderId='.$arca_orderId.'&language='.$language.'')){
                $data = json_decode($data);
                $amount = substr($data->depositAmount, 0, -2);
                $arca_error_code = $data->ErrorCode;
                $arca_order_status = $data->OrderStatus;
                if($arca_error_code == 0 && $arca_order_status == 2 && $amount > 0){          
                    $arca_res = $this->db->query("SELECT * FROM `arca` WHERE `mdorder`='$arca_orderId' AND `status`='0'");
                    if($arca_res->num_rows==1){
                        $arca_row = $arca_res->fetch_assoc();
                        $this->db->query("UPDATE `arca` SET `amount`='$amount', `status`='1' WHERE `arcaID`='".$arca_row['arcaID']."'");
                        $order = $this->getOrder($arca_row['orderID']);
                        $user = $this->getUser($order['userID']);
                        $subject = "Պատվեր B-".$order["orderID"]."";
                        $message = "Հարգելի ".$user['name'].",<br><br>Ձեր պատվերն ընդունված է։<br><br><br>Շնորհակալություն գնումների համար";
                        $subject_not = "Հարգելի ".$user['name']."";
                        $message_not = "Ձեր պատվերն ընդունված է։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                        $this->addNotification($user["userID"], $subject_not, $message_not);
                        sendMailSmtp($user['email'], $subject, $message);
                        sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$user['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
                        header("Location: /profile/notification#getStatus?cat=2&orderID=".$arca_row["orderID"]."&status=1");
                        exit;
                    }
                }else{
                    $arca_res = $this->db->query("SELECT * FROM `arca` WHERE `mdorder`='$arca_orderId' AND `status`='0'");
                    $arca_row = $arca_res->fetch_assoc();
                    header("Location: /profile/orders#getStatus?cat=2&orderID=".$arca_row["orderID"]."&status=0");
                    exit;
                }
            }
        }
    }
    
    public function getPayIdram($orderID, $amount){
        $this->db->query("INSERT INTO `idram`(`orderID`, `amount`, `status`, `date`) VALUES ('$orderID', '".$amount."', '0', NOW())");
        $idramID = $this->db->insert_id;   
        $EDP_LANGUAGE = "AM";
        $EDP_REC_ACCOUNT = "100000624";
        $EDP_DESCRIPTION = urlencode("Butik.am / Deposit");
        $EDP_AMOUNT = $amount;
        $EDP_BILL_NO = $idramID;
        echo json_encode([
            "message" => 0,
            "location" => [
                "href" => "https://money.idram.am/payment.aspx?EDP_LANGUAGE=$EDP_LANGUAGE&EDP_REC_ACCOUNT=$EDP_REC_ACCOUNT&EDP_DESCRIPTION=$EDP_DESCRIPTION&EDP_AMOUNT=$EDP_AMOUNT&EDP_BILL_NO=$EDP_BILL_NO",
                "hash" => false,
                "reload" => false
            ]
        ]);
    }
    
    public function getConfirmIdram(){
        define("SECRET_KEY", "");
        define("EDP_REC_ACCOUNT", "");
        if(isset($_REQUEST['EDP_PRECHECK']) && isset($_REQUEST['EDP_BILL_NO']) &&
        isset($_REQUEST['EDP_REC_ACCOUNT']) && isset($_REQUEST['EDP_AMOUNT'])){
            if($_REQUEST['EDP_PRECHECK'] == "YES"){
                if($_REQUEST['EDP_REC_ACCOUNT'] == EDP_REC_ACCOUNT){
                    $bill_no = $_REQUEST['EDP_BILL_NO'];
                    exit("OK");
                }
            }
        }
        if(isset($_REQUEST['EDP_PAYER_ACCOUNT']) && isset($_REQUEST['EDP_BILL_NO']) &&
        isset($_REQUEST['EDP_REC_ACCOUNT']) && isset($_REQUEST['EDP_AMOUNT'])
        && isset($_REQUEST['EDP_TRANS_ID']) && isset($_REQUEST['EDP_CHECKSUM'])){
            $txtToHash =
            EDP_REC_ACCOUNT . ":" .
            $_REQUEST['EDP_AMOUNT'] . ":" .
            SECRET_KEY . ":" .
            $_REQUEST['EDP_BILL_NO'] . ":" .
            $_REQUEST['EDP_PAYER_ACCOUNT'] . ":" .
            $_REQUEST['EDP_TRANS_ID'] . ":" .
            $_REQUEST['EDP_TRANS_DATE'];
            if(strtoupper($_REQUEST['EDP_CHECKSUM']) != strtoupper(md5($txtToHash))){
                exit("Error");
            }else{
                $amount = $_REQUEST['EDP_AMOUNT'];
                if($amount > 0){
                    $idram_res = $this->db->query("SELECT * FROM `idram` WHERE `idramID`='".$_REQUEST['EDP_BILL_NO']."' AND `status`='0'");
                    if($idram_res->num_rows==1){
                        $idram_row = $idram_res->fetch_assoc();
                        $this->db->query("UPDATE `idram` SET `amount`='$amount', `status`='1' WHERE `idramID`='".$idram_row['idramID']."'");
                        $order = $this->getOrder($idram_row['orderID']);
                        $user = $this->getUser($order['userID']);
                        $subject = "Պատվեր B-".$order["orderID"]."";
                        $message = "Հարգելի ".$user['name'].",<br><br>Ձեր պատվերն ընդունված է։<br><br><br>Շնորհակալություն գնումների համար";
                        $subject_not = "Հարգելի ".$user['name']."";
                        $message_not = "Ձեր պատվերն ընդունված է։<br><br>Ձեր պատվերի համարն է՝ B-".$order["orderID"]."<br><br>Շնորհակալություն գնումների համար";
                        $this->addNotification($user["userID"], $subject_not, $message_not);
                        sendMailSmtp($user['email'], $subject, $message);
                        sendMailSmtp("shop@butik.am", "Նոր պատվեր B-".$order["orderID"]."", "Պատվիրատու ".$user['name'].",<br>Գումար «".($order["total"] + $order["delivery"])."» ՀՀ դրամ");
                        exit("OK");
                    }
                }
            }
        }
    }
    
    public function getRedirectIdram(){
        $idram_res = $this->db->query("SELECT * FROM `idram` WHERE `idramID`='".$_REQUEST['EDP_BILL_NO']."'")->fetch_assoc();
        if($idram_res["status"]==1){
            header("Location: /profile/notification#getStatus?cat=2&orderID=".$idram_res["orderID"]."&status=1");
            exit;
        }else{
            header("Location: /profile/orders#getStatus?cat=2&orderID=".$idram_res["orderID"]."&status=0");
            exit;
        }
        exit;
    }
    
    
    public function addNotification($to = "", $title = "", $desc = ""){
        if(empty($to) || empty($title) || empty($desc)){
            return false;
        }
        $this->db->query("INSERT INTO `notification`(`to`, `title`, `desc`, `date`)VALUES('$to', '$title', '$desc', NOW())");
    }
    
    public function getNotification($to = "", $show = ""){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        $where = "WHERE `notificationID` IS NOT NULL";
        if(!empty($to)){
            $where .= " AND `to`='$to'";
        }
        if($show === 0 || $show === 1){
            $where .= " AND `show`=$show";
        }
        $notification = $this->db->query("SELECT * FROM `notification` $where ORDER BY `notificationID` DESC limit 10");
        return $notification->fetch_all(MYSQLI_ASSOC);
    }
    
    public function updateNotification(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        $this->db->query("UPDATE `notification` SET `show`=1 WHERE `to`='".$this->profile['userID']."'");
        //$this->db->query("DELETE FROM `notification` WHERE `show`=1 AND `date`<'".date('Y-m-d H:i:s', strtotime(''.date('Y-m-d H:i:s').' -7 days'))."'");
    }
    
    public function removeNotification(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            return false;
        }
        if(!isset($_GET['notificationID']) || empty($_GET['notificationID'])){
            return false;
        }
       $this->db->query("DELETE FROM `notification` WHERE `to`='".$this->profile['userID']."' AND `notificationID`='".$_GET['notificationID']."'");
    }
    
    public function getVoice(){
        $notification_count = $this->db->query("SELECT `notificationID` FROM `notification` WHERE `to`='".$this->profile['userID']."' AND `show`=0 AND `voice`=0")->num_rows;
        if($notification_count > 0){
            $this->db->query("UPDATE `notification` SET `voice`='1' WHERE `to`='".$this->profile['userID']."'");
            return true;
        }else{
            return false;
        }
    }
    
    public function getUpdate(){
        if(!isset($this->profile['userID']) || empty($this->profile['userID'])){
            echo json_encode([
                "error" => 0,
                "auth" => false,
                "message" => false,
                "location" => false
            ]);
            return false;
        }
        echo json_encode([
            "error" => 0,
            "auth" => true,
            "message" => [
                "notification_count" => count($this->getNotification($this->profile['userID'], 0))
            ],
            "voice" => $this->getVoice(),
            "location" => false
        ]);
        return false;
    }
    
}