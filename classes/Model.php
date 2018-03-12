<?php
abstract class Model{
    
    protected $db;
    public $lang;
    public $val;
    public $currency;
    public $currency_symbol;
    
    public function __construct(){
        //$this->db = new DataBase("localhost", "root", "", "blc", "+4:00");
    }
    
    public function __call($name, $value){
        return false;
    }
    
    public function getConfig(){
        echo json_encode([
            "lang" => $this->lang,
            "val" => $this->val,
            "currency" => $this->currency,
            "currency_symbol" => $this->currency_symbol
        ]);
    }
    
    public function exchangeCurrency($sum = ""){
        if(isset($_GET["sum"])){
            $sum = $_GET["sum"];
        }
        $row = $this->db->query("SELECT * FROM `currency`")->fetch_assoc();
        if(isset($_GET["sum"])){
            echo ceil($sum / $row[''.$this->currency.'']);
        }else{
            return ceil($sum / $row[''.$this->currency.'']);
        }
    }
    
    public function getCurrency(){
        return $this->db->query("SELECT * FROM `currency`")->fetch_assoc();
    }
    
    public function getText($page = "", $group = ""){
        return $this->db->query("SELECT *, `text_".$this->lang."` AS `text` FROM `text` WHERE `page`='$page' AND `group`='$group'")->fetch_assoc();
    }
    
    public function getContacts(){
        return $this->db->query("SELECT * FROM `contacts`")->fetch_assoc();
    }
    
    
    public function getList($group = "", $parent = 0, $listID = false){
        if($listID==false){
            return $this->db->query("SELECT *, `name_".$this->lang."` AS `name` FROM `list` WHERE `group`='$group' AND `parent`='$parent' ORDER BY `sort` ASC, `listID` ASC")->fetch_all(MYSQLI_ASSOC);
        }else{
            return $this->db->query("SELECT *, `name_".$this->lang."` AS `name` FROM `list` WHERE `listID`='$listID'")->fetch_assoc();
        }
    }
    
    public function getListJson(){
        if(!isset($_GET["group"]) || !isset($_GET["parent"])){
            echo json_encode([]);
        }
        echo json_encode($this->getList($_GET["group"], $_GET["parent"]));
    }
    
    
    public function getPhoto($group = "", $parent = "", $count = ""){
        $where = "";
        $limit = "";
        if(!empty($group)){
            $where .= "WHERE `group`='$group'";
        }
        if(!empty($parent)){
            $where .= " AND `parent`='$parent'";
        }
        if(!empty($count)){
            $limit = "limit $count";
        }
        if($count == 1){
            return $this->db->query("SELECT * FROM `photo` $where ORDER BY `sort` ASC, `photoID` ASC $limit")->fetch_assoc();
        }else{
            return $this->db->query("SELECT * FROM `photo` $where ORDER BY `sort` ASC, `photoID` ASC $limit")->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    public function getUser($userID = ""){
        if(empty($userID)){
            return false;
        }
        return $this->db->query("
            SELECT *
            FROM `user`
            WHERE `userID`='$userID'
            ORDER BY `userID` DESC
        ")->fetch_assoc();
    }
    
    
    public function getUsers($filter = []){
        $where = "WHERE `userID` IS NOT NULL";
        $groupBY = "GROUP BY `userID`";
        $orderBY = "ORDER BY `userID` DESC";
        $limit = "";
        
        if(isset($filter["start"]) && isset($filter["limit"])){
            $limit = " limit ".$filter["start"].",".$filter["limit"]."";
        }
        
        if(isset($filter["userID"])){
            $where .= " AND `userID`='".$filter["userID"]."'";
        }
        
        $user = $this->db->query("
            SELECT *
            FROM `user`
            LEFT JOIN `photo` ON `group`='user' AND `parent`=`userID`
            $where
            $groupBY
            $orderBY
            $limit
        ");
        
        if(isset($filter["userID"])){
            return $user->fetch_assoc();
        }else{
            return $user->fetch_all(MYSQLI_ASSOC);
        }
        
    }
    
    
    public function getProducts($filter = []){
        $where = "WHERE `products`.`productID` IS NOT NULL";    
        if(!empty($filter["cat_1"])){
            $where .= " AND `products`.`cat_1`='".$filter["cat_1"]."'";
        }      
        if(!empty($filter["cat_2"])){
            $where .= " AND `products`.`cat_2`='".$filter["cat_2"]."'";
        }      
        if(!empty($filter["cat_3"])){
            $where .= " AND `products`.`cat_3`='".$filter["cat_3"]."'";
        }      
        if(isset($filter["expected"])){
            if($filter["expected"]==0){
                $where .= " AND `option`.`optionID` IS NOT NULL";
            }else{
                $where .= " AND `option`.`optionID` IS NULL";
            }
        }      
        if(isset($filter["status"])){
            if($filter["status"]=="0-1"){
                $where .= " AND (`products`.`status`='0' OR `products`.`status`='1')";
            }else{
                $where .= " AND `products`.`status`='".$filter["status"]."'";
            }
        }
        if(!empty($filter["productsID"])){
            $where .= " AND (`products`.`productID`='".explode("-",$filter["productsID"])[0]."' OR `products`.`product_altID`='".explode("-",$filter["productsID"])[0]."')";
        }
        if(!empty($filter["productsID"]) && isset(explode("-",$filter["productsID"])[1])){
            $where .= " AND (SELECT MAX(`optionID`) FROM `option` WHERE `optionID`='".explode("-",$filter["productsID"])[1]."' OR `option_altID`='".explode("-",$filter["productsID"])[1]."') IS NOT NULL";
        }
        if(isset($filter["adminID"]) && !empty($filter["adminID"])){
            $where .= " AND `products`.`owner`='".$filter["adminID"]."'";
        }
        $url = new Url();
        if(isset($url->DIR[0]) && $url->DIR[0] == 'trans' && isset($_SESSION["admin"]) && $_SESSION["admin"]["permission"] > 1){
            $where .= " AND `products`.`owner`='".$_SESSION["admin"]["adminID"]."'";
        }
        if(!empty($filter["productID"])){
            $where .= " AND `products`.`productID`='".$filter["productID"]."'";
        }
        if(isset($filter["type"]) && $filter["type"] == "rand"){
            //$where .= " AND `products`.`similar_products`='1'";
            $orderBY = "ORDER BY RAND() limit 16";
        }else{
            $orderBY = "ORDER BY `products`.`view_count` DESC, `products`.`sort` ASC, `products`.`productID` DESC"; 
        }
        if(isset($filter["start"]) && isset($filter["limit"])){
            $orderBY = "ORDER BY `products`.`view_count` DESC, `products`.`sort` ASC, `products`.`productID` DESC limit ".$filter["start"].",".$filter["limit"]."";
        }

        $products = $this->db->query("
        SELECT `products`.*, SUM(`option`.`count`) AS `count`, `title_".$this->lang."` AS `title`, `desc_".$this->lang."` AS `desc`
        FROM `products`
        LEFT JOIN `option` ON `option`.`productID`=`products`.`productID` AND `option`.`count`>0
        $where
        GROUP BY `products`.`productID`
        $orderBY");
        if(!empty($filter["productID"])){
            return $products->fetch_assoc();
        }else{
            return $products->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    public function getOption($productID = "", $optionID = "", $type = false){
        $where = "WHERE `optionID` IS NOT NULL";
        if(!empty($productID)){
            $where .= " AND `productID`='$productID'";
        }
        if(!empty($optionID)){
            $where .= " AND `optionID`='$optionID'";
        }
        if($type == true){
            $where .= " AND `count`>'0'";
        }
        $option = $this->db->query("SELECT * FROM `option` $where ORDER BY `sort` ASC, `optionID` ASC");
        if(!empty($optionID)){
            return $option->fetch_assoc();
        }else{
            return $option->fetch_all(MYSQLI_ASSOC);
        } 
    }
    
    public function getProductsCart($productID = ""){
        return $this->db->query("SELECT *, `title_".$this->lang."` AS `title`, `desc_".$this->lang."` AS `desc` FROM `products` LEFT JOIN `photo` ON `photo`.`group`='products' AND `photo`.`parent`=`products`.`productID` WHERE `productID`='$productID' ORDER BY `photo`.`sort` ASC, `photo`.`photoID`")->fetch_assoc();
    }
    
    public function getOrder($orderID = "", $userID = "", $status = "", $filter = []){
        $where = "WHERE `orderID` IS NOT NULL";
        $groupBY = "GROUP BY `orderID`";
        $orderBY = "ORDER BY `orderID` DESC";
        if(!empty($orderID)){
            $where .= " AND `orderID`='$orderID'";
        }
        if(isset($filter["ordersID"])){
            $where .= " AND `orderID`='".$filter["ordersID"]."'";
        }
        if(isset($filter["status_data"])){
            if($filter["status_data"]=="0"){
                $where .= " AND `status_data_simple`='' AND `order_data` REGEXP '\"option_count\":\"0\"'";
            }else{
                $where .= " AND `status_data_simple` REGEXP ':\"".$filter["status_data"]."'";
            }
        }
        if(isset($filter["phone"])){
            $where .= " AND (`user`.`phone` LIKE '%".$filter["phone"]."%' OR `order`.`phone_2` LIKE '%".$filter["phone"]."%' OR `order`.`location_data` LIKE '%".$filter["phone"]."%')";
        }
        if(isset($filter["productID"])){
            $where .= " AND `order_data` REGEXP '\"productID\":\"".$filter["productID"]."'";
        }
        if(!empty($userID)){
            $where .= " AND `userID`='$userID'";
        }
        if(!empty($status) && $status=="undelivered"){
            $where .= " AND `order`.`status`!='3'";
            
        }elseif(!empty($status)){
            $where .= " AND `order`.`status`='$status'";
        }

        if(isset($filter["date_start"])){
            $where .= " AND `order`.`date`>='".$filter["date_start"]." 00:00:00'";
        }
        if(isset($filter["date_end"])){
            $where .= " AND `order`.`date`<='".$filter["date_end"]." 00:00:00'";
        }
        if(isset($filter["start"]) && isset($filter["limit"])){
            $orderBY = "ORDER BY `orderID` DESC limit ".$filter["start"].",".$filter["limit"]."";
        }
        $order = $this->db->query("
            SELECT `order`.* FROM `order`
            LEFT JOIN `user` using(`userID`)
            $where
            $groupBY
            $orderBY
        ");
        if(!empty($orderID)){
            return $order->fetch_assoc();
        }else{
            return $order->fetch_all(MYSQLI_ASSOC);
        }  
    }
    
    public function getPreOrder($userID = "", $productID = "", $orderID = "", $filter = []){
        $where = "WHERE `orderID` IS NOT NULL";
        if(!empty($userID)){
            $where .= " AND `userID`='$userID'";
        }
        if(!empty($productID)){
            $where .= " AND `productID`='$productID'";
        }
        if(!empty($orderID)){
            $where .= " AND `orderID`='$orderID'";
        }
        if(isset($filter["productsID"])){
            $where .= " AND `productID`='".$filter["productsID"]."'";
        }
        if(isset($filter["ordersID"])){
            $where .= " AND `orderID`='".$filter["ordersID"]."'";
        }
        if(isset($filter["status"])){
            $where .= " AND `status`='".$filter["status"]."'";
        }
        if(isset($filter["date_start"])){
            $where .= " AND `date`>='".$filter["date_start"]." 00:00:00'";
        }
        if(isset($filter["date_end"])){
            $where .= " AND `date`<='".$filter["date_end"]." 00:00:00'";
        }
        $order = $this->db->query("SELECT * FROM `pre_order` $where ORDER BY `orderID` DESC");
        if(!empty($productID) || !empty($orderID)){
            return $order->fetch_assoc();
        }else{
            return $order->fetch_all(MYSQLI_ASSOC);
        }  
    }
    
    public function getOrderCount($productID = ""){
        $count = 0;
        $pruduct = $this->getProducts(["productID"=>$productID]);
        $orders = $this->db->query("SELECT * FROM `order` WHERE `order_data` REGEXP '(\"productID\":\"$productID\")'")->fetch_all(MYSQLI_ASSOC);
        foreach($orders as $order){
            $order_data = json_decode($this->getOrder($order["orderID"])["order_data"], true);
            foreach($order_data as $cart){
                if($cart["productID"]==$productID){
                    $count += $cart["count"];
                }
            }
        }
        $pre_orders = $this->db->query("SELECT * FROM `pre_order` WHERE `productID`='$productID'")->fetch_all(MYSQLI_ASSOC);
        foreach($pre_orders as $pre_order){
            $count += $pre_order["count"];
        }
        return $count + 1;
    }
    
    public function getLocationJSON(){
        $parent = (isset($_GET["parent"])) ? $_GET["parent"] : 0;
        $location = $this->getList("location", $parent);
        echo json_encode($location, JSON_UNESCAPED_UNICODE);
    }

    
    public function getPayments($orderID = 0){
        $arca = $this->db->query("SELECT *, SUM(`amount`) AS `amount`  FROM `arca` WHERE `orderID`='".$orderID."' AND `status`='1'")->fetch_assoc();
        $idram = $this->db->query("SELECT *, SUM(`amount`) AS `amount`  FROM `idram` WHERE `orderID`='".$orderID."' AND `status`='1'")->fetch_assoc();
        return ["amount" => ($arca["amount"] + $idram["amount"])];
    }

    
    public function getSearch($start = 0, $end=6){
        $where = "WHERE `products`.`productID` IS NOT NULL";
        if(isset($_SESSION['search']['query'])){
            $where .= " AND (";
            $i = 0;
            foreach(preg_split("/[-, ]+/", $_SESSION['search']['query']) as $query){
                if(mb_strlen($query) > 2){
                    $prefix = ($i == 0) ? "" : " OR ";
                    $i++;
                    $query = (mb_strlen($query) >= 5) ? mb_substr($query, 0, -1) : $query;
                    $query = (mb_strlen($query) >= 5) ? mb_substr($query, 0, -1) : $query;
                    $query_am = transcriptionAM($query);
                    $query_en = transcriptionEN($query);
                    $query_ru = transcriptionRU($query);
                    $where .= " $prefix(`products`.`title_am` REGEXP '($query|$query_am|$query_en|$query_ru)' OR `products`.`desc_am` REGEXP '($query|$query_am|$query_en|$query_ru)')";
                }
            }
            $where .= ")";
        }
        if(isset($_SESSION['search']['cat_1'])){
            $where .= " AND `products`.`cat_1`='".$_SESSION['search']['cat_1']."'";
        }
        if(isset($_SESSION['search']['cat_2'])){
            $where .= " AND `products`.`cat_2`='".$_SESSION['search']['cat_2']."'";
        }
        if(isset($_SESSION['search']['cat_3'])){
            $where .= " AND `products`.`cat_3`='".$_SESSION['search']['cat_3']."'";
        }
        if(isset($_SESSION['search']['productsID'])){
            $where .= " AND (`products`.`productID`='".explode("-",$_SESSION['search']['productsID'])[0]."' OR `products`.`product_altID`='".explode("-",$_SESSION['search']['productsID'])[0]."')";
        }
        if(isset($_SESSION['search']['productsID']) && isset(explode("-",$_SESSION['search']['productsID'])[1])){
            $where .= " AND (SELECT MAX(`optionID`) FROM `option` WHERE `optionID`='".explode("-",$_SESSION['search']['productsID'])[1]."' OR `option_altID`='".explode("-",$_SESSION['search']['productsID'])[1]."') IS NOT NULL";
        }
        if(isset($_SESSION['search']['expected'])){
            if($_SESSION['search']['expected']==0){
                $where .= " AND `option`.`optionID` IS NOT NULL";
            }else{
                $where .= " AND `option`.`optionID` IS NULL";
            }
        }
        if(isset($_SESSION['search']['status'])){
            $where .= " AND `products`.`status`='".$_SESSION['search']['status']."'";
        }
        $groupBY = "GROUP BY `products`.`productID`";
        $orderBY = "ORDER BY `products`.`view_count` DESC, `products`.`sort` ASC, `products`.`productID` DESC";
        $limit = "limit $start,$end";
        return $this->db->query("
            SELECT `products`.*, SUM(`option`.`count`) AS `count`, `title_".$this->lang."` AS `title`, `desc_".$this->lang."`
            FROM `products`
            LEFT JOIN `option` ON `option`.`productID`=`products`.`productID` AND `option`.`count`>0
            $where
            $groupBY
            $orderBY
            $limit
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public function setSearch($_args = array()){
        if(!empty($_POST)){
            $_args = $_POST;
        }
        
        if(isset($_args['query'])){
            $_args['query'] = str_replace(")", "", $_args['query']);  
            $_args['query'] = str_replace("(", "", $_args['query']);  
        }
        
        if(isset($_args['query']) && !empty($_args['query'])){
            $_SESSION['search']['query'] = $_args['query'];
        }else{
            unset($_SESSION['search']['query']);
        }
        if(isset($_args['cat_1']) && !empty($_args['cat_1'])){
            $_SESSION['search']['cat_1'] = $_args['cat_1'];
        }else{
            unset($_SESSION['search']['cat_1']);
        }
        if(isset($_args['cat_2']) && !empty($_args['cat_2'])){
            $_SESSION['search']['cat_2'] = $_args['cat_2'];
        }else{
            unset($_SESSION['search']['cat_2']);
        }
        if(isset($_args['cat_3']) && !empty($_args['cat_3'])){
            $_SESSION['search']['cat_3'] = $_args['cat_3'];
        }else{
            unset($_SESSION['search']['cat_3']);
        }
        if(isset($_args['productsID']) && !empty($_args['productsID'])){
            $_SESSION['search']['productsID'] = $_args['productsID'];
        }else{
            unset($_SESSION['search']['productsID']);
        }
        if(isset($_args['expected'])){
            $_SESSION['search']['expected'] = $_args['expected'];
        }else{
            unset($_SESSION['search']['expected']);
        }
        if(isset($_args['status'])){
            $_SESSION['search']['status'] = $_args['status'];
        }else{
            unset($_SESSION['search']['status']);
        }
        
        if(!empty($_POST)){
            echo count($this->getSearch(0, 1000));
        }else{
            return count($this->getSearch(0, 1000));
        }
    }
    
    public function scanBuyforme($scanURL = ""){
        if(empty($scanURL)){
            return [];
        }
        
        if(isset($_SESSION["product"]["url"]) && $_SESSION["product"]["url"]==$scanURL){
            return $_SESSION["product"];
        }

        parse_str(parse_url($scanURL, PHP_URL_QUERY), $url_array);
        
        
        if(preg_match("/detail.tmall.com/", $scanURL) || preg_match("/item.taobao.com/", $scanURL)){
            if(!isset($url_array["id"])){
                return [];
            }

            if(!isset($_SESSION["rateCNY"])){
                $content_rate = file_get_contents("https://www.cba.am/AM/SitePages/ExchangeArchive.aspx");
                preg_match_all("/<td><span>CNY<\/span><em>1<\/em><\/td><td>(.*)<\/td>/siU", $content_rate, $rateCNY, PREG_SET_ORDER);
                if(isset($rateCNY[0][1])){
                    $_SESSION["rateCNY"] = $rateCNY[0][1];
                }else{
                    return [];
                }
            }

            $content = file_get_contents("https://t-b.ru.com/catalog/product/".$url_array["id"]."/");
            $dom = new DOMDocument();
            @$dom->loadHTML($content);
            $finder = new DomXPath($dom);

            $title =  $dom->saveHTML($dom->getElementsByTagName('h1')->item(0));
            $title = preg_replace('/\s+(?![^<>]*>)/x', '', $title);
            $title = preg_replace('/<script>.*<\/script>/x', '', $title);
            $title = strip_tags($title);
            $desc = $dom->saveHTML($dom->getElementById('detail'));
            $desc = preg_replace('/\s+(?![^<>]*>)/x', '', $desc);

            //preg_match_all("/var sellerNick = '(.*)'/siU", $content, $nick_name, PREG_SET_ORDER);
            //$nick_name = (isset($nick_name[0][1])) ? $nick_name[0][1] : "";

            $amount = (int) $finder->query("//*[contains(@class, 'pricevalspammy')]")->item(0)->nodeValue * $_SESSION["rateCNY"];
            $amount_discount = $finder->query("//*[contains(@class, 'old_price_y')]")->item(0)->nodeValue;
            $amount_discount = intval(preg_replace('/[^0-9]+\./', '', $amount_discount), 10) * $_SESSION["rateCNY"];

            $photos = [];
            $photos_box = $dom->saveHTML($finder->query("//*[contains(@class, 'miniImgs')]")->item(0));
            preg_match_all("/<a .*tmpurl=\"(.*)\"/siU", $photos_box, $photos_src, PREG_SET_ORDER);
            foreach($photos_src as $photo_src){
                $photos[] = [
                    "small"=>$photo_src[1]."_150x150q90.jpg",
                    "middle"=>$photo_src[1]."_500x500q90.jpg",
                    "large"=>$photo_src[1]."_800x800q90.jpg",
                ];  
            }

            $colors = [];
            $colors_box = $dom->saveHTML($finder->query("//*[contains(@class, 'miniImgs')]")->item(1));
            preg_match_all("/<a .*tmpurl=\"(.*)\"/siU", $colors_box, $colors_src, PREG_SET_ORDER);
            foreach($colors_src as $color_src){
                $colors[] = [
                    "small"=>$color_src[1]."_150x150q90.jpg",
                    "middle"=>$color_src[1]."_500x500q90.jpg",
                    "large"=>$color_src[1]."_800x800q90.jpg",
                ];  
            }

            $sizes = [];
            //$sizes_box = $dom->saveHTML($finder->query("//*[contains(@class, 'changesel')]")->item(1));
            preg_match_all("/<select .*name=\"尺码\".*>(.*)<\/select>/siU", $content, $sizes_box, PREG_SET_ORDER);
            if(!isset($sizes_box[0][1])){
                preg_match_all("/<select .*name=\"尺寸\".*>(.*)<\/select>/siU", $content, $sizes_box, PREG_SET_ORDER);
            }
            if(!isset($sizes_box[0][1])){
                preg_match_all("/<select .*name=\"适用年龄\".*>(.*)<\/select>/siU", $content, $sizes_box, PREG_SET_ORDER);
            }
            if(isset($sizes_box[0][1])){
                preg_match_all("/<option .*nameopt=\"(.*)\"/siU", $sizes_box[0][1], $sizes_item, PREG_SET_ORDER);
                foreach($sizes_item as $size_item){
                    $sizes[] = explode("【", $size_item[1])[0];
                }
            }

            $_SESSION["product"] = [
                "url"=>$scanURL,
                "title"=>(isset($title)) ? $title : "",
                "desc"=>(isset($desc)) ? $desc : "",
                "amount"=>(isset($amount)) ? $amount : "",
                "amount_discount"=>(isset($amount_discount)) ? $amount_discount : "",
                "photos"=>(isset($photos)) ? $photos : [],
                "sizes"=>(isset($sizes)) ? $sizes : [],
                "colors"=>(isset($colors)) ? $colors : [],
            ];
        }else{
            return [];
        }
        
        
        /*
        $content = file_get_contents("http://www.yoybuy.com/en/getgoodsitem?goodsUrl=".urlencode($scanURL)."");
        $result = json_decode($content, true);
        
        //ini_set('xdebug.var_display_max_depth', 5);
        //ini_set('xdebug.var_display_max_children', 256);
        //ini_set('xdebug.var_display_max_data', 1024);
        //var_dump($result["data"]);

        $title = $result["data"]["Title"];
        $amount = $result["data"]["Skus"][0]["Price"]*$rateCNY;
        $amount_discount = $result["data"]["Price"]*$rateCNY;
        
        $photos = [];
        foreach($result["data"]["ItemImgs"] as $photo_item){
            $photos[] = [
                "small"=>$photo_item["Url"]."_150x150q90.jpg",
                "middle"=>$photo_item["Url"]."_500x500q90.jpg",
                "large"=>$photo_item["Url"]."_800x800q90.jpg",
            ];  
        }
        
        $colors = [];
        if(isset($result["data"]["GoodsShowSkuItems"][0]["PropertyAliasList"])){
            foreach($result["data"]["GoodsShowSkuItems"][0]["PropertyAliasList"] as $color_item){
                if(isset($color_item["BigImage"]) && !empty($color_item["BigImage"])){
                    $colors[] = $color_item["BigImage"]."_150x150q90.jpg";
                }
            }
        }
        
        $sizes = [];
        if(isset($result["data"]["GoodsShowSkuItems"][1]["PropertyAliasList"])){
            foreach($result["data"]["GoodsShowSkuItems"][1]["PropertyAliasList"] as $size_item){
                if(isset($size_item["CustomName"]) && !empty($size_item["CustomName"])){
                    $sizes[] = $size_item["CustomName"];
                }
            }
        }
        */
        
        /*
        $content = file_get_contents($scanURL);
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        $finder = new DomXPath($dom);

        //preg_match_all("/\"title\":\"(.*)\"/siU", $content, $title, PREG_SET_ORDER);
        //$title = $title[0][1];
        if($dom->getElementsByTagName('h1')->item(1)){
            $title =  $dom->getElementsByTagName('h1')->item(1)->nodeValue;
        }elseif($dom->getElementsByTagName('h1')->item(0)){
            $title =  $dom->getElementsByTagName('h1')->item(0)->nodeValue;
        }

        //preg_match_all("/<div .*id=\"mainwrap\">(.*)<\/div>/siU", $content, $desc, PREG_SET_ORDER);
        //$desc = $desc[0][1];
        $desc = $dom->saveHTML($dom->getElementById('mainwrap'));

        preg_match_all("/\"defaultItemPrice\":\"(.*)\"/siU", $content, $defaultItemPrice, PREG_SET_ORDER);
        $amount = $defaultItemPrice[0][1]*$rateCNY;

        preg_match_all("/\"default\":\[\"(.*)\"\]/siU", $content, $photos, PREG_SET_ORDER);
        $photos = explode("\",\"", $photos[0][1]);
        for($i=0; $i<count($photos); $i++){
            $photos[$i] = [
                "small"=>$photos[$i]."_150x150q90.jpg",
                "middle"=>$photos[$i]."_500x500q90.jpg",
                "large"=>$photos[$i]."_800x800q90.jpg",
            ];  
        }

        preg_match_all("/\"propertyPics\":{\";.*:.*;\":(\[\"(.*)\"\],.*\"\],)+\"default\"/siU", $content, $colors_old, PREG_SET_ORDER);
        $colors_old = preg_split("/(\"])|(\[\")/", $colors_old[0][1], NULL, PREG_SPLIT_NO_EMPTY);
        $i=0;
        $colors = [];
        foreach($colors_old as $color_old){
            if(strpos($color_old, "//")===false){
            }else{
                $colors[$i] = $color_old."_150x150q90.jpg";
                $i++;
            }
        }
        */
        
        
        return (isset($_SESSION["product"])) ? $_SESSION["product"] : [];
        

    }
    
    public function getOrderBuyforme($orderID = "", $userID = "", $status = "", $filter = []){
        $where = "WHERE `orderID` IS NOT NULL";
        $groupBY = "GROUP BY `orderID`";
        $orderBY = "ORDER BY `orderID` DESC";
        if(!empty($orderID)){
            $where .= " AND `orderID`='$orderID'";
        }
        if(isset($filter["ordersID"])){
            $where .= " AND `orderID`='".$filter["ordersID"]."'";
        }
        if(isset($filter["status_data"])){
            if($filter["status_data"]=="0"){
                $where .= " AND `status_data_simple`='' AND `order_data` REGEXP '\"option_count\":\"0\"'";
            }else{
                $where .= " AND `status_data_simple` REGEXP ':\"".$filter["status_data"]."'";
            }
        }
        if(isset($filter["phone"])){
            $where .= " AND `user`.`phone` LIKE '%".$filter["phone"]."%'";
        }
        if(isset($filter["productID"])){
            $where .= " AND `order_data` REGEXP '\"productID\":\"".$filter["productID"]."'";
        }
        if(!empty($userID)){
            $where .= " AND `userID`='$userID'";
        }
        if(!empty($status) && $status=="undelivered"){
            $where .= " AND `status`!='3'";
            
        }elseif(!empty($status)){
            $where .= " AND `status`='$status'";
        }

        if(isset($filter["date_start"])){
            $where .= " AND `date`>='".$filter["date_start"]." 00:00:00'";
        }
        if(isset($filter["date_end"])){
            $where .= " AND `date`<='".$filter["date_end"]." 00:00:00'";
        }
        if(isset($filter["start"]) && isset($filter["limit"])){
            $orderBY = "ORDER BY `orderID` DESC limit ".$filter["start"].",".$filter["limit"]."";
        }
        $order = $this->db->query("
            SELECT `order_buyforme`.* FROM `order_buyforme`
            LEFT JOIN `user` using(`userID`)
            $where
            $groupBY
            $orderBY
        ");
        if(!empty($orderID)){
            return $order->fetch_assoc();
        }else{
            return $order->fetch_all(MYSQLI_ASSOC);
        }  
    }
    
}
?>