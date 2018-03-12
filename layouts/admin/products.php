<!DOCTYPE html>
<html>
    <head>
        <?php require "inc/head.php";?>

        <title>Admin</title>
        <meta name="description" content="">
    </head>

    <body data-ma-theme="indigo">
        <main class="main">
            <?php require "inc/header.php"?>
            
            <?php require "inc/aside.php"?>

            <section class="content">
                <div class="content__inner">
                    <div class="card">

                        <div class="card-block">
                            <div class="table-responsive">
                                <table id="data-table" class="table table-bordered">
                                    <thead class="thead-default">
                                        <tr>
                                            <th>
                                                <a href="?cmd=addStock" class="btn btn-secondary waves-effect">
                                                    <i class="zmdi zmdi-plus"></i> Ավելացնել
                                                </a>
                                            </th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($cnt->getProducts() as $product){?>
                                        <tr>
                                            <td><?php echo $product["productID"]?></td>
                                            <td><?php echo $product["title"]?></td>
                                            <td><?php echo $product["amount"]?></td>
                                            <td><?php echo $product["desc"]?></td>
                                            <td><?php echo $product["weight"]?></td>
                                            <td><?php echo $product["date"]?></td>
                                            <td>
                                                <a href="/stock/aside-content/stock?id=<?php echo $product["productID"]?>" data-aside-content>
                                                    <i class="zmdi zmdi-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php }?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                
                <?php require "inc/footer.php"?>
                    
            </section>
        </main>
 
    </body>
</html>