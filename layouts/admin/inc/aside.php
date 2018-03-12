<aside class="sidebar">
    <div class="scrollbar-inner">
        <ul class="navigation">
            <li class="<?php if(!isset($url->DIR[1]) && $url->PAGE==""){?>navigation__active<?php }?>">
                <a href="/admin/"><i class="zmdi zmdi-plus-square"></i> Բաժինների կառավարում</a>
            </li>
            <li class="<?php if(!isset($url->DIR[1]) && $url->PAGE=="products"){?>navigation__active<?php }?>">
                <a href="/admin/products"><i class="zmdi zmdi-plus-square"></i> Տեսականի</a>
            </li>
            <hr>
            <li>
                <a href="?cmd=logOut"><i class="zmdi zmdi-sign-in"></i> Ելք</a>
            </li>
        </ul>
    </div>
</aside>

<aside class="chat aside-notification">
    <div class="chat__header">
        <span class="zmdi zmdi-arrow-right" data-ma-action="aside-close" data-ma-target=".aside-notification"></span>
        <h2 class="chat__title">Ծանուցումներ <small></small></h2>
        <!--
        <div class="chat__search">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Search...">
                <i class="form-group__bar"></i>
            </div>
        </div>
        -->
    </div>
    <div class="listview listview--hover scrollbar-main notification-2-box"></div>
    <a href="/load/notification" class="btn btn-secondary btn-block btn--icon-text waves-effect" data-reload="content" data-box="notification-2-box" data-end="10"><i class="zmdi zmdi-more zmdi-hc-fw"></i></a>
    <!--
    <a href="messages.html" class="btn btn--action btn--fixed btn-danger"><i class="zmdi zmdi-plus"></i></a>
    -->
</aside>

<aside class="chat aside-content">
    <div class="page-loader">
        <div class="page-loader__spinner">
            <svg viewBox="25 25 50 50">
                <circle cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
            </svg>
        </div>
    </div>
    <span class="zmdi zmdi-close hidden-sm-down" data-ma-action="aside-close" data-ma-target=".aside-content"></span>
    <div class="chat__header">
        <span class="zmdi zmdi-arrow-right" data-ma-action="aside-close" data-ma-target=".aside-content"></span>
        <a href="" target="_blank" class="chat__title">Դիտել հղումը <i class="zmdi zmdi-share"></i></a>
    </div>
    <div class="scrollbar-main content-box"></div>
</aside>