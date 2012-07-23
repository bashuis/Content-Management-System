<?php

/**
 * @ignore
 */

// We are in control.
define('CMSBEHEER',         0.1);

// Define some constants.
define('DS',                DIRECTORY_SEPARATOR);
define('ROOT',              dirname(__FILE__) . DS . '..' . DS);
define('SYSTEMPATH',        ROOT . 'cms' . DS);

// Grab our configuration
require_once ROOT . 'cms_config.php';

// Get the bootstrap file, which handles everything else for us.
require SYSTEMPATH . 'bootstrap.php';
require SYSTEMPATH . 'backend.php';

define('BEHEERSCRIPTS', SCRIPTS . 'beheer/', false);

if (!User::loggedin())
{
    $output->addTitle(BRANDED_NAME . " CMS versie " . CMS_VERSION . " - Log-in");
    ob_start();
    require_once 'beheer-login.php';
    $content = ob_get_clean();
    $output->setContent($content);
    $showMenu = false;

    $mysqlTime  = Registry::get('database')->fb();
    $totalTime  = microtime(true) - $timeStarted;
    $phpTime    = $totalTime - $mysqlTime;

    Registry::get('firephp')->info(round($phpTime, 3) * 1000 . 'ms', 'PHP loadtime');
    Registry::get('firephp')->info(round($totalTime, 3) * 1000 . 'ms', 'PHP + MySQL loadtime');
    
    echo $output->getContent();
    exit();
}
else if (!(user::isAdmin() || user::isInAdminGroup()))
{
    $output->addTitle("Geen toegang");
    $output->setContent("Helaas, U bent geen beheerder van deze website en heeft dus geen toegang tot het beheer gedeelte.");
    $showMenu = false;
}
else
{
    $showMenu = true;
    //-----
    //	Ok, so they are admin.
    //	Now, lets check if they have permission to access this part of the admin panel.
    //-----
    $adminMayVisitThisPage = true;

    if (!user::isAdmin())
    {
        $allowedPages = array();
        $allowedPages['start'] 	= array('start');
        $allowedPages['page'] 	= array('start', 'list', 'edit');
        $allowedPages['module'] 	= array('start','view');
        $allowedPages['plugins'] 	= array('start','manage','settings','explain');
        $allowedPages['logoff'] 	= array('start');

        if (!(isset( $allowedPages[$request[0]]) && in_array($request[1], $allowedPages[$request[0]])))
        {
            $output->addTitle("Geen toegang");
            $output->setContent( "Helaas, op uw niveau heeft u geen toegang tot dit deel van het beheerder paneel." );
            $adminMayVisitThisPage = false;
        }
    }

    if( $adminMayVisitThisPage )
    {
        if (file_exists(BEHEERSCRIPTS . $request[0] . '/' . $request[1].'.php'))
        {
            ob_start();
            require_once BEHEERSCRIPTS . 'common.php';
            require_once BEHEERSCRIPTS.$request[0] . '/common.php';
            $content = ob_get_clean();

            ob_start();
            Messager::getMessages();
            $msgHTML = ob_get_clean();

            ob_start();
            require BEHEERSCRIPTS . $request[0] . '/' . $request[1].'.php';
            $content .= ob_get_clean();

            $output->setContent($content, $msgHTML);
        }
        else
        {
            Messager::error('De pagina die u probeerde te bezoeken bestaat niet.', false, true);
            redirect('/beheer');
        }
    }
}

function doActive ($what, $level)
{
    global $request;

    if (isset($request[$level]) && $request[$level] == $what)
        echo ' class="active"';
}

error_reporting ($cms_oldErrorReporting);

ob_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo $output->getTitle(' &rarr; '); ?></title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <!-- stylesheets -->
        <link rel="stylesheet" type="text/css" href="/beheer/resources/css/beheer.css" />
        <script src="/cms/pub/jquery/jquery-1.5.2.min.js" type="text/javascript"></script>
        <script src="/cms/pub/jquery.prettyPhoto.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/colorpicker.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/smooth.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/jquery.ui.selectmenu.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/smooth.menu.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/smooth.table.js" type="text/javascript"></script>
        <script src="/beheer/resources/scripts/smooth.form.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $("a[rel^='prettyPhoto']").prettyPhoto();
            });
        </script>

        <?php
        $quick_menu = QuickMenu::get();
        if(empty($quick_menu) && $request[0] != "module" && $request[0] != "plugins")
        {
            ?>
            <link rel="stylesheet" type="text/css" href="/beheer/resources/css/style_full.css" />
            <?php
        }   
        echo $output->showHeadInfo();
        ?>
    
        <!--[if IE]>
        <script language="javascript" type="text/javascript" src="/beheer/resources/scripts/excanvas.min.js"></script>
        <![endif]-->

    </head>
    <body>
        <!-- header -->
        <div id="header">
            <!-- logo -->
            <div id="logo">
                <h1><?php print BRANDED_NAME; ?> Content Management System</h1>
            </div>
            <!-- end logo -->
            <!-- user -->
            <ul id="user">
                <li><a href="/beheer/help/">Hulp &amp; Ondersteuning</a></li>
                <li><a href="/?x=logout">Uitloggen</a></li>
                <li class="highlight last"><a href="/" target="_blank">Bekijk uw Website</a></li>
            </ul>
            <!-- end user -->
            <div id="header-inner">
                <div id="home">
                    <a href="/beheer/" title="Home"></a>
                </div>
                <!-- quick -->
                <?php
                if ($showMenu)
                {
                    if (User::isAdmin())
                    {
                        $modules = $db->query("SELECT `m_id` AS id,
                                                      `m_name` AS name,
                                                      `m_path` AS path,
                                                      `m_supports_multiple_instances` AS multiInstance
                                               FROM   `cms_modules`
                                               WHERE  `m_is_owned` = 1
                                               AND	  `m_is_active` = 1
                                               AND	  `m_has_admin` = 1");

                        $plugins = $db->query("SELECT `p_id` AS id,
                                                      `p_name` AS name,
                                                      `p_code` AS code,
                                                      `p_active` AS active
                                               FROM   `cms_plugins`
                                               WHERE  `p_owned` = 1
                                               ORDER BY `p_name`");

                        ?>
                        <ul id="quick">
                            <li<?php doActive('menu',0); ?>><a href="/beheer/menu/"><span class="icon"><img src="/icons/fugues/icons/ui-buttons.png" alt="ui-buttons" /></span><span>Menu knoppen</span></a></li>
                            <li<?php doActive('page',0); ?>><a href="/beheer/page/"><span class="icon"><img src="/icons/fugues/icons/documents-text.png" alt="documents-text" /></span><span>Pagina's</span></a></li>
                            <li<?php doActive('module',0); ?>><a href="/beheer/module/"><span class="icon"><img src="/icons/fugues/icons/block.png" alt="block" /></span><span>Modules</span></a>

                            <?php
                            print "<ul>";
                            while ($modulesR = $db->fetch_assoc($modules))
                            {
                                print "<li";
                                doActive($modulesR['id'],2);
                                print "><a href=\"/beheer/module/view/".$modulesR['id']."/\">".$modulesR['name']."</a></li>";
                            }
                            print "</ul>";

                            ?>
                            </li>

                            <li<?php doActive('plugins',0); ?>><a href="/beheer/plugins/"><span class="icon"><img src="/icons/fugues/icons/puzzle.png" alt="puzzle" /></span><span>Plugins</span></a>

                            <?php

                            print "<ul>";
                            while ($pluginsR = $db->fetch_assoc($plugins))
                            {
                                print "\n\t\t\t\t<li";
                                doActive($pluginsR['id'],2);
                                print "><a href=\"/beheer/plugins/manage/".$pluginsR['id']."/\">".$pluginsR['name']."</a></li>";
                            }
                            print "</ul>";

                            ?>
                            </li>

                            <li<?php doActive('users-and-groups',0); ?>><a href="/beheer/users-and-groups/"><span class="icon"><img src="/icons/fugues/icons/users.png" alt="users" /></span><span>Gebruikersbeheer</span></a></li>
                            <li<?php doActive('settings',0); ?>><a href="/beheer/settings/"><span class="icon"><img src="/icons/fugues/icons/wrench-screwdriver.png" alt="wrench-screwdriver" /></span><span>Instellingen</span></a></li>
                        </ul>
                        <?php
                    }
                    else
                    {
                        $modules = $db->query("SELECT DISTINCT m.`m_id` AS id,
                                                               m.`m_name` AS name,
                                                               m.`m_path` AS path,
                                                               m.`m_supports_multiple_instances` AS multiInstance
                                               FROM   `cms_modules` AS m
                                               JOIN   `cms_item_edit_permission` AS iep
                                                   ON (m.`m_id` = iep.`item`)
                                               WHERE  `m_is_owned` = 1
                                               AND	  `m_is_active` = 1
                                               AND	  `m_has_admin` = 1
                                               AND	  iep.`type` = 'module'
                                               AND    iep.`group` IN (" . implode(",", User::getGroups()) . ")");
                        $countModules = $db->num_rows();

                        $plugins = $db->query("SELECT `p_id` AS id,
                                                      `p_name` AS name,
                                                      `p_code` AS code,
                                                      `p_active` AS active
                                               FROM   `cms_plugins` AS p
                                               JOIN   `cms_item_edit_permission` AS iep
                                                   ON (p.`p_id` = iep.`item`)
                                               WHERE  `p_owned` = 1
                                               AND    `p_active` = 1
                                               AND    iep.`type` = 'plugin'
                                               AND    iep.`group` IN (" . implode(",", User::getGroups()) . ")
                                               ORDER BY `p_name`");
                        $countPlugins = $db->num_rows();
                        ?>
                        <ul id="quick">
                            <?php
                            $db->query("SELECT 1
                                        FROM   `cms_page` AS p
                                        JOIN   `cms_page_translation` AS t
                                            ON (p.`p_id` = t.`p_id`)
                                        JOIN   `cms_item_edit_permission` AS iep
                                            ON (p.`p_id` = iep.`item`)
                                        WHERE  t.`lang_id` = 1
                                        AND    p.`p_intrash` = 0
                                        AND    iep.`type` = 'page'
                                        AND    iep.`group` IN (" . implode(",", user::getGroups() ) . ")
                                        ORDER BY p.`p_id`");                            
                            
                            if ($db->num_rows() > 0)
                            {
                                ?>
                                <li <?php doActive('page', 0); ?>><a href="/beheer/page/"><span class="icon"><img src="/icons/fugues/icons/documents-text.png" alt="documents-text" /></span><span>Pagina's</span></a></li>
                                <?php
                            }
                            if ($countModules > 0)
                            {
                                ?>
                                <li<?php doActive('module', 0); ?>><a href="/beheer/module/"><span class="icon"><img src="/icons/fugues/icons/block.png" alt="block" /></span><span>Modules</span></a>
                                <?php

                                print "<ul>";

                                while ($modulesR = $db->fetch_assoc($modules))
                                {
                                    print "<li";
                                    doActive($modulesR['id'],2);
                                    print "><a href=\"/beheer/module/view/" . $modulesR['id'] . "/\">" . $modulesR['name'] . "</a></li>";
                                }
                                print "</ul>";

                                print "</li>";
                            }

                            if ($countPlugins > 0)
                            {
                                ?>
                                <li<?php doActive('plugins',0); ?>><a href="/beheer/plugins/"><span class="icon"><img src="/icons/fugues/icons/puzzle.png" alt="puzzle" /></span><span>Plugins</span></a>
                                <?php

                                print "<ul>";
                                while ($pluginsR = $db->fetch_assoc($plugins))
                                {
                                    print "<li";
                                    doActive($pluginsR['id'], 2);
                                    print "><a href=\"/beheer/plugins/manage/" . $pluginsR['id'] . "/\">" . $pluginsR['name'] . "</a></li>";
                                }
                                print "</ul>";

                                print "</li>";
                           }
                           ?>
                        </ul>
                        <?php
                    }
                }
                ?>
                <div class="corner tl"></div>
                <div class="corner tr"></div>
            </div>
        </div>
        <!-- end header -->
        <!-- content -->
        <div id="content">
            <?php
            if (!empty($quick_menu) || $request[0] == "module" || $request[0] == "plugins")
            {
                ?>
                <div id="left">
                    <div id="menu">
                        <?php
                        if (!empty($quick_menu))
                        {
                            ?>
                            <h6 id="h-menu-products" class="selected">
                                <a href="#products"><span><?php if($request[0] == "plugins") print "Plugin "; if($request[0] == "module") print $module['name']; ?></span></a>
                            </h6>

                            <ul id="menu-products" class="opened">
                                <?php print $quick_menu; ?>
                            </ul>
                            <?php
                        }

                        if ($request[0] == 'module')
                        {
                            if (User::isAdmin())
                            {
                                $modules = $db->query("SELECT `m_id` AS id,
                                                              `m_name` AS name,
                                                              `m_path` AS path,
                                                              `m_supports_multiple_instances` AS multiInstance
                                                       FROM   `cms_modules`
                                                       WHERE  `m_is_owned` = 1
                                                       AND	  `m_is_active` = 1
                                                       AND	  `m_has_admin` = 1
                                                       ORDER BY name");

                                $instancesRS = $db->query("SELECT i.`module`,
                                                                  i.`instance`,
                                                                  i.`name`
                                                           FROM   `cms_module_instances` AS i
                                                           ORDER BY i.`name`");

                                $instances = array();

                                while($instancesR = $db->fetch_assoc($instancesRS))
                                    $instances[] = $instancesR;
                            }
                            else
                            {
                                $modules = $db->query("SELECT DISTINCT m.`m_id` AS id,
                                                                       m.`m_name` AS name,
                                                                       m.`m_path` AS path,
                                                                       m.`m_supports_multiple_instances` AS multiInstance
                                                       FROM   `cms_modules` AS m
                                                       JOIN   `cms_item_edit_permission` AS iep
                                                          ON  (m.`m_id` = iep.`item`)
                                                       WHERE  `m_is_owned` = 1
                                                       AND    `m_is_active` = 1
                                                       AND    `m_has_admin` = 1
                                                       AND    iep.`type` = 'module'
                                                       AND    iep.`group` IN (" . implode(",", User::getGroups()).")
                                                       ORDER BY m.`m_name`");

                                $instancesRS = $db->query("SELECT i.`module`,
                                                                  i.`instance`,
                                                                  i.`name`
                                                            FROM  `cms_module_instances` AS i
                                                            JOIN  `cms_item_edit_permission` AS iep
                                                                ON (i.`module` = iep.`item` AND i.`instance` = iep.`mod_instance`)
                                                            WHERE iep.`group` IN (" . implode(",", User::getGroups()).")
                                                            ORDER BY i.`name`");

                                $instances = array();
                                while($instancesR = $db->fetch_assoc($instancesRS))
                                    $instances[] = $instancesR;
                            }

                            if ($db->num_rows($modules) > 0)
                            {
                                ob_start();
                                ?>
                                <h6 id="h-menu-modules" class="selected"><a href="#modules"><span>Modules</span></a></h6>

                                <ul id="menu-modules" class="opened">
                                    <?php
                                    while ($module = $db->fetch_assoc($modules))
                                    {
                                        if($module['multiInstance'])
                                        {
                                            print "\n\n<li class=\"collapsible\"><a class=\"plus\" href=\"#\">" . $module['name'] . "</a>";
                                        }
                                        else
                                        {
                                            print "\n\n<li><a href=\"/beheer/module/view/" . $module['id'] . "\">" . $module['name'] . "</a>";
                                        }

                                        if ($module['multiInstance']){
                                            print "<ul class=\"collapsed\">";
                                            foreach ($instances AS $instance)
                                            {
                                                if ($instance['module'] == $module['id'])
                                                    print "\n\t<li><a href=\"/beheer/module/view/" . $module['id'] . "/" . $instance['instance'] . "\">" . $instance['name'] . "</a><li>";
                                            }
                                            print "</ul>";
                                        }
                                        print "\n</li>";
                                    }
                                print "</ul>";

                                $quickMenuContent = ob_get_clean();
                                print $quickMenuContent;
                            }
                        }
                        else if($request[0] == "plugins")
                        {
                            if (User::isAdmin())
                            {
                                $plugins = $db->query("SELECT `p_id` AS id,
                                                              `p_name` AS name,
                                                              `p_code` AS code,
                                                              `p_active` AS active
                                                       FROM   `cms_plugins`
                                                       WHERE  `p_owned` = 1
                                                       ORDER BY `p_name`");
                            }
                            else
                            {
                                $plugins = $db->query("SELECT `p_id` AS id,
                                                              `p_name` AS name,
                                                              `p_code` AS code,
                                                              `p_active` AS active
                                                       FROM   `cms_plugins` AS p
                                                       JOIN   `cms_item_edit_permission` AS iep
                                                           ON (p.`p_id` = iep.`item`)
                                                       WHERE  `p_owned` = 1
                                                       AND    `p_active` = 1
                                                       AND    iep.`type` = 'plugin'
                                                       AND    iep.`group` IN (" . implode(",", User::getGroups()) . ")
                                                       ORDER BY `p_name`");
                            }

                            ?>
                            <h6 id="h-menu-modules" class="selected"><a href="#modules"><span>Plugins</span></a></h6>

                            <ul id="menu-modules" class="opened">
                            <?php
                                while ($pluginsR = $db->fetch_assoc($plugins))
                                {
                                    print "\n\t\t\t\t<li";
                                    doActive($pluginsR['id'], 2);
                                    print "><a href=\"/beheer/plugins/manage/" . $pluginsR['id'] . "/\">" . $pluginsR['name'] . "</a></li>";
                                }
                            print "\n\t\t\t</ul>";
                        }
                        ?>
                     </div>
                </div>

                <div id="right">
                <?php
            }
            ?>
            <div class="box">
                <!-- box / title -->
                <div class="title">

                    <h5><?php echo $output->getTitle(' &rarr; '); ?></h5>

                </div>
                <!-- end box / title -->
                <div class="table">
                    <!-- start CMS content -->
                    <?php echo $output->getContent(); ?>
                    <!-- end CMS content -->
                </div>
            </div>
            <?php if(!empty($quick_menu) || $request[0] == "module" || $request[0] == "plugins") { ?> </div> <?php } ?>
        </div>
        <!-- end content -->
        <!-- footer -->

        <div id="footer">
            <p><?php print BRANDED_NAME; ?> Content Management System - Versie <?php print CMS_VERSION; ?><br /><br />Copyright &copy; 2007-<?php print date("Y");?> <a style="color:#fff; text-decoration:none" href="<?php print BRANDED_WEBSITE; ?>" target="_blank"><?php print BRANDED_FULL_NAME; ?></a><?php if(BRANDED_BY_HUIZINGA) print " - Developed by <a style=\"color:#fff; text-decoration:none\" href=\"http://www.huizinga.nl/\" target=\"_blank\">Huizinga Hosting &amp; Webdesign</a>"; ?></p>
        </div>
        <!-- end footert -->
    </body>
</html>

<?php

$content = ob_get_clean();

$mysqlTime  = Registry::get('database')->fb();
$totalTime  = microtime(true) - $timeStarted;
$phpTime    = $totalTime - $mysqlTime;

Registry::get('firephp')->info(round($phpTime, 3) * 1000 . 'ms', 'PHP loadtime');
Registry::get('firephp')->info(round($totalTime, 3) * 1000 . 'ms', 'PHP + MySQL loadtime');

echo $content;