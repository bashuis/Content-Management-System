<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo $output->getTitle(' &rarr; '); ?></title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <!-- stylesheets -->
        <link rel="stylesheet" type="text/css" href="/beheer/resources/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="/beheer/resources/css/style.css" media="screen" />
        <link id="color" rel="stylesheet" type="text/css" href="/beheer/resources/css/colors/blue.css" />
        <!-- scripts (jquery) -->
        <script src="/beheer/resources/scripts/jquery-1.4.2.min.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/jquery-ui-1.8.custom.min.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/smooth.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/messages.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("input.focus").focus(function () {
                    if (this.value == this.defaultValue) {
                        this.value = "";
                    }
                    else {
                        this.select();
                    }
                });

                $("input.focus").blur(function () {
                    if ($.trim(this.value) == "") {
                        this.value = (this.defaultValue ? this.defaultValue : "");
                    }
                });

                $("input:submit, input:reset").button();
            });
        </script>
    </head>
    <body onload="document.loginform.username.focus();">
        <div id="login">
            <!-- login -->
            <div class="title">
                <h5><?php print BRANDED_NAME." CMS versie ".CMS_VERSION." - Log-in"; ?></h5>
                <div class="corner tl"></div>
                <div class="corner tr"></div>
            </div>
            <?php
                Messager::getMessages();
            ?>
            <div class="inner">
                <form action="/?x=login&amp;beheer" id="loginform" name="loginform" method="post">
                    <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
                    <div class="form">
                        <!-- fields -->
                        <div class="fields">
                            <div class="field">
                                <div class="label">
                                    <label for="username">Username:</label>
                                </div>
                                <div class="input">
                                    <input type="text" id="username" name="usr" size="40" class="focus" />
                                </div>
                            </div>
                            <div class="field">
                                <div class="label">
                                    <label for="password">Password:</label>
                                </div>
                                <div class="input">
                                    <input type="password" id="password" name="pwd" size="40" class="focus" />
                                </div>
                            </div>
                            <div class="buttons">
                                <input type="submit" name="login" value="Inloggen" />
                            </div>
                        </div>
                        <!-- end fields -->
                        <!-- links -->
                        <div class="links">
                            <a href="/?x=forgot-password&amp;beheer">Wachtwoord vergeten?</a>
                        </div>
                        <!-- end links -->
                    </div>
                </form>
            </div>
            <!-- end login -->
        </div>
    </body>
</html>