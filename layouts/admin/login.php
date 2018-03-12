<!DOCTYPE html>
<html>
    <head>
        <?php require "inc/head.php";?>

        <title>Admin</title>
    </head>

    <body data-ma-theme="indigo">
       <form action="?cmd=login" class="login form-ajax" method="post">
            <div class="login__block active" id="l-login">
                <div class="page-loader">
                    <div class="page-loader__spinner">
                        <svg viewBox="25 25 50 50">
                            <circle cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
                        </svg>
                    </div>
                </div>
                <div class="login__block__header">
                    <i class="zmdi zmdi-account-circle"></i>
                    <p>Login</p>
                </div>
                <div class="login__block__body">
                    <div class="form-group form-group--float form-group--centered">
                        <input type="text" name="login" class="form-control" value="">
                        <label>Էլ․փոստ</label>
                        <i class="form-group__bar"></i>
                    </div>
                    <div class="form-group form-group--float form-group--centered">
                        <input type="password" name="password" class="form-control" value="">
                        <label>Գաղտնաբառ</label>
                        <i class="form-group__bar"></i>
                    </div>
                    <div class="form-message"></div>
                    <button type="submit" class="btn btn--icon login__block__btn waves-effect"><i class="zmdi zmdi-long-arrow-right"></i></button>
                </div>
            </div>
        </form>
    </body>
</html>