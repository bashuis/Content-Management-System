<?php

if (isset($request[2]) && is_numeric($request[2]))
{
    $db->query("SELECT `m_id` AS id,
                       `m_name` AS name,
                       `m_path` AS path
                FROM   `cms_modules`
                WHERE  `m_id` = " . intval($request[2]) . "
                LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $module = $db->fetch_assoc();
        ?>
        <div class="box ui-tabs ui-widget ui-widget-content ui-corner-all" id="box-tabs">
            <div class="title">
                <h5>Vertalen</h5>
                <ul class="links ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                    <?php
                    $langList = $db->query("SELECT `lang_id` AS id,
                                                   `lang_tag` AS tag,
                                                   `lang_flag` AS flag,
                                                   `lang_name` AS name
                                            FROM   `cms_language`
                                            ORDER BY `lang_name`");
                    while ($lang = $db->fetch_assoc($langList))
                    {
                        if ((isset($_GET['lang']) && $_GET['lang'] == $lang['tag']) || (!isset($_GET['lang']) && $lang['id'] == $cms_settings['default_language']))
                        {
                            ?>
                            <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active ui-state-focus"><a href="?lang=<?=$lang['tag']?>"><?=$lang['name']?></a></li>
                            <?php
                        }
                        else
                        {
                            ?>
                            <li class="ui-state-default ui-corner-top"><a href="?lang=<?=$lang['tag']?>"><?=$lang['name']?></a></li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
            <div class="content">
                <?php
                if (!isset($_GET['lang']))
                {
                    $db->query("SELECT `lang_tag`
                                FROM   `cms_language`
                                WHERE  `lang_id` = " . intval($cms_settings['default_language']) . "
                                LIMIT 1");
                    $lang = $db->fetch_assoc();

                    $_GET['lang'] = $lang['lang_tag'];
                }

                $langFile = Generic::cleanFileName($_GET['lang']) . '.php';
                $langCustomFile = Generic::cleanFileName($_GET['lang']) . '.custom.php';

                if (isset($_GET['template']))
                {
                    $templateFile = cleanFileName($_GET['template']) . '.php';
                    copy(MODULES . $module['path'] . '/lang/' . $templateFile, MODULES . $module['path'] . '/lang/' . $langFile);
                }

                if (sizeof($_POST) > 1)
                {
                    unset($_POST['submit']);

                    $fileContents = '<?php\n$modLang = array();\n';
                    foreach ($_POST as $customKey => $customValue)
                    {
                        if (!empty($customValue))
                        {
                            $customKey      = str_replace("+", " ", $customKey);
                            $customKey      = str_replace("%2F", "/", $customKey);
                            $customValue    = addslashes($customValue);
                            $fileContents  .= '\n$modLang[\'' . $customKey . '\'] = \'' . $customValue . '\';';
                        }
                    }
                    $fileContents .= '\n\n?>';

                    $fileContents = str_replace('\n', "\n", $fileContents);

                    file_put_contents(MODULES . $module['path'] . '/lang/' . $langCustomFile, $fileContents);

                    Messager::ok("De aanpassingen zijn opgeslagen.");
                }

                function loadLangForEditting ($file)
                {
                    global $module;
                    $fullFile = MODULES . $module['path'] . '/lang/' . $file;
                    if (file_exists($fullFile))
                    {
                        require_once $fullFile;
                        return $modLang;
                    }
                    else
                    {
                        return array();
                    }
                }

                $langDefaults = loadLangForEditting($langFile);
                $langCustoms = loadLangForEditting($langCustomFile);

                if (sizeof($langDefaults) == 0)
                {
                    $langList = $db->query("SELECT `lang_tag` AS tag,
                                                   `lang_flag` AS flag,
                                                   `lang_name` AS name
                                            FROM   `cms_language`
                                            ORDER BY `lang_name`");
                    ?>
                    <p>Voor deze vertaling is nog geen bestand aangemaakt. Om de vertalinge aan te passen, moet dit eerst gedaan worden. Op welk bestaand bestand wilt u het nieuwe bestand baseren?</p>
                    <ul>
                        <?php
                        while ($lang = $db->fetch_assoc($langList))
                        {
                            if (file_exists(MODULES . $module['path'] . '/lang/' . $lang['tag'] . '.php'))
                            {
                                ?>
                                <li>
                                    <a href="?lang=<?php echo $_GET['lang']; ?>&amp;template=<?php echo $lang['tag']; ?>">
                                        <?php echo Generic::stripAndClean( $lang['name'] ); ?>
                                        <img src="<?php echo $lang['flag']; ?>" alt="Vlag van dit land" />
                                    </a>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                    <?php
                }
                else
                {
                    ?>
                    <form action="?lang=<?php echo Generic::cleanFileName($_GET['lang']); ?>" method="post">
                        <table>
                            <tr>
                                <th>Code:</th>
                                <th>Vertaling:</th>
                                <th>Standaard:</th>
                            </tr>
                            <?php
                            foreach ($langDefaults AS $defaultKey => $defaultValue)
                            {
                                ?>
                                <tr>
                                    <td><?php echo $defaultKey; ?></td>
                                    <?php if(strlen($defaultValue) > 250): ?>
                                        <td><textarea cols="50" rows="5" name="<?php echo urlencode($defaultKey); ?>"><?php if( isset( $langCustoms[ $defaultKey ] ) ): echo $langCustoms[ $defaultKey ]; endif; ?></textarea></td>
                                    <?php else: ?>
                                        <td><input size="53" type="text" name="<?php echo urlencode($defaultKey); ?>" value="<?php if( isset( $langCustoms[ $defaultKey ] ) ): echo $langCustoms[ $defaultKey ]; endif; ?>" /></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlentities($defaultValue); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <input type="submit" name="submit" value="Opslaan" />
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
    else
    {
        Messager::error("De gekozen module bestaat niet.");
    }
}
else
{
    Messager::error("U heeft geen module gekozen.");
}