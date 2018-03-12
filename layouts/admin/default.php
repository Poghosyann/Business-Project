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
                <div class="content__inner content__inner--sm" style="max-width:1100px;">

                    <div class="card">
                        <div class="card-header">
                            <form action="?cmd=addList&backUrl=<?php echo urlencode("?cat_1=".@$url->GET["cat_1"]."&cat_2=".@$url->GET["cat_2"]."")?>" method="post" style="width:100%;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="hidden" name="group" value="category">
                                        <select name="parent[]" class="form-control" onchange="location = '?cat_1='+this.value">
                                            <option value="0">...</option>
                                            <?php foreach($cnt->getList("category", 0) as $cat_1){?>
                                            <?php $selected = (@$url->GET["cat_1"]==$cat_1['listID']) ? "selected" : "";?>
                                            <option value="<?php echo $cat_1['listID']?>" <?php echo $selected?>><?php echo $cat_1['name']?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="parent[]" class="form-control" onchange="location = '?cat_1=<?php echo @$url->GET["cat_1"]?>&cat_2='+this.value">
                                            <option value="">...</option>
                                            <?php foreach($cnt->getList("category", @$url->GET["cat_1"]) as $cat_2){?>
                                            <?php $selected = (@$url->GET["cat_2"]==$cat_2['listID']) ? "selected" : "";?>
                                            <option value="<?php echo $cat_2['listID']?>" <?php echo $selected?>><?php echo $cat_2['name']?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="name_am" placeholder="Անվանում АМ" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-secondary btn-block">+</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php foreach($cnt->getList("category", 0) as $cat_1){?>
                    <div class="card sortable" data-sort="list, listID, <?php echo $cat_1['listID']?>">
                        <div class="card-block">
                            <div class="listview__actions pull-right" style="font-size:16px;">
                                <i class="fa fa-trash-o actions__item" onClick="removeList(<?php echo $cat_1['listID']?>)"></i>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control live" data-live="list, name_am, listID, <?php echo $cat_1['listID']?>" value="<?php echo $cat_1['name_am']?>" placeholder="Անվանում АМ">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control live" data-live="list, name_ru, listID, <?php echo $cat_1['listID']?>" value="<?php echo $cat_1['name_ru']?>" placeholder="Անվանում RU">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control live" data-live="list, name_en, listID, <?php echo $cat_1['listID']?>" value="<?php echo $cat_1['name_en']?>" placeholder="Անվանում EN">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <?php if(count($cnt->getList($cat_1['group'], $cat_1['listID'])) > 0){?>
                            <div class="card-block" style="padding:1.1rem 2.1rem 0;">
                                <?php foreach($cnt->getList($cat_1['group'], $cat_1['listID']) as $cat_2){?>
                                <div class="sortable" data-sort="list, listID, <?php echo $cat_2['listID']?>">
                                    <div class="listview__actions" style="font-size:16px;cursor:pointer;position:absolute;right:40px;">
                                        <i class="fa fa-trash-o remove" onClick="removeList(<?php echo $cat_2['listID']?>)"></i>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control live" data-live="list, name_am, listID, <?php echo $cat_2['listID']?>" value="<?php echo $cat_2['name_am']?>" placeholder="Անվանում АМ">
                                            <i class="form-group__bar"></i>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control live" data-live="list, name_ru, listID, <?php echo $cat_2['listID']?>" value="<?php echo $cat_2['name_ru']?>" placeholder="Անվանում RU">
                                            <i class="form-group__bar"></i>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control live" data-live="list, name_en, listID, <?php echo $cat_2['listID']?>" value="<?php echo $cat_2['name_en']?>" placeholder="Անվանում EN">
                                            <i class="form-group__bar"></i>
                                        </div>
                                    </div>
                                    <?php if(count($cnt->getList($cat_2['group'], $cat_2['listID'])) > 0){?>
                                    <div class="card-block" style="padding: 1.1rem 2.1rem 0;">
                                        <?php foreach($cnt->getList($cat_2['group'], $cat_2['listID']) as $cat_3){?>
                                        <div class="sortable" data-sort="list, listID, <?php echo $cat_3['listID']?>">
                                            <div class="listview__actions" style="font-size:16px;cursor:pointer;position:absolute;right:40px;">
                                                <i class="fa fa-trash-o remove" onClick="removeList(<?php echo $cat_3['listID']?>)"></i>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-1 text-right">
                                                    <div class="p-1"></div>
                                                    <input type="checkbox" class="form-check-input live" style="transform:scale(1.2);cursor:pointer;" data-toggle="tooltip" title="Զեղչ" data-live="list, note, listID, <?php echo $cat_3['listID']?>" value="1" <?php if($cat_3['note']==1){?>checked<?php }?>>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control live" data-live="list, name_am, listID, <?php echo $cat_3['listID']?>" value="<?php echo $cat_3['name_am']?>" placeholder="Անվանում АМ">
                                                    <i class="form-group__bar"></i>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control live" data-live="list, name_ru, listID, <?php echo $cat_3['listID']?>" value="<?php echo $cat_3['name_ru']?>" placeholder="Անվանում RU">
                                                    <i class="form-group__bar"></i>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control live" data-live="list, name_en, listID, <?php echo $cat_3['listID']?>" value="<?php echo $cat_3['name_en']?>" placeholder="Անվանում EN">
                                                    <i class="form-group__bar"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <?php }?>
                                    </div>
                                    <?php }?>
                                </div>
                                <div class="p-4"></div>
                                <?php }?>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                    
                </div>
                
                <?php require "inc/footer.php"?>
                    
            </section>
        </main>
 
    </body>
</html>