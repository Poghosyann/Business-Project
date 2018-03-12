
<header class="header">
    <div class="navigation-trigger" data-ma-action="aside-open" data-ma-target=".sidebar">
        <div class="navigation-trigger__inner">
            <i class="navigation-trigger__line"></i>
            <i class="navigation-trigger__line"></i>
            <i class="navigation-trigger__line"></i>
        </div>
    </div>

    <div class="header__logo">
        <h1><a href="/stock/">OfficePro Admin Panel</a></h1>
    </div>
    
    <form action="/stock/" method="get" class="search">
        <div class="search__inner">
            <input type="text" name="query" class="search__text" value="<?php echo @$url->GET["query"]?>" placeholder="Ոորոնում․․․">
            <i class="zmdi zmdi-search search__helper" data-ma-action="search-close"></i>
        </div>
    </form>

    <ul class="top-nav">

        <li class="dropdown top-nav__notifications">
            <a href="#" data-toggle="dropdown" class="top-nav__notify">
                <i class="zmdi zmdi-notifications"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
                <div class="listview listview--hover">
                    <div class="listview__header">
                        Ծանուցումներ
                        <div class="actions">
                            <a href="?cmd=removeNotification" class="actions__item zmdi zmdi-check-all" data-ma-action="notifications-clear"></a>
                        </div>
                    </div>
                    <div class="notification-box scrollbar-main"></div>
                    <a href="/stock/load/notification" class="btn btn-secondary btn-block btn--icon-text waves-effect" data-reload="content" data-box="notification-box" data-end="10"><i class="zmdi zmdi-more zmdi-hc-fw"></i></a>
                </div>
            </div>
        </li>
        

        <li class="dropdown">
            <a href="" data-toggle="dropdown">
                <i class="zmdi zmdi-more-vert"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="?cmd=logOut" class="dropdown-item">Ելք</a>
            </div>
        </li>
        <!--
        <li class="hidden-xs-down">
            <a href="" data-ma-action="aside-open" data-ma-target=".chat" class="top-nav__notify">
                <i class="zmdi zmdi-comment-alt-text"></i>
            </a>
        </li>
        -->
    </ul>
</header>