<?php
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
    $db->prepare("SELECT `template_id`,
                         `title`,
                         `identifier`
                  FROM   `cms_template`
                  WHERE  `template_id` = :template_id
                  LIMIT 1")
       ->bindValue('template_id', $_GET['id'])
       ->execute();
    if ($db->num_rows() == 1)
    {        
        $template = $db->fetch_assoc();
        $templateDir = TEMPLATES . $template['identifier'] . DS;

        if (isset($_GET['action']) && $_GET['action'] == 'uploadCSS')
        {
            if (isset($_FILES['css']['tmp_name']) && !empty($_FILES['css']['tmp_name']))
            {
                if (end(explode('.', $_FILES['css']['name'])) == 'css')
                {
                    if (!is_dir($templateDir . 'css' . DS))
                        mkdir($templateDir . 'css' . DS);

                    move_uploaded_file($_FILES['css']['tmp_name'], $templateDir . 'css' . DS . $_FILES['css']['name']);
                    Messager::ok('Het bestand is succesvol geupload.', false, true);
                }
                else
                {
                    Messager::error('U heeft geen CSS bestand geselecteerd.', false, true);
                }
                redirect('?do=update&id=' . $template['template_id']);
            }
        }
        else if (isset($_GET['action']) && $_GET['action'] == 'uploadJS')
        {
            if (isset($_FILES['js']['tmp_name']) && !empty($_FILES['js']['tmp_name']))
            {
                if (end(explode('.', $_FILES['js']['name'])) == 'js')
                {
                    if (!is_dir($templateDir . 'js' . DS))
                        mkdir($templateDir . 'js' . DS);

                    move_uploaded_file($_FILES['js']['tmp_name'], $templateDir . 'js' . DS . $_FILES['js']['name']);
                    Messager::ok('Het bestand is succesvol geupload.', false, true);
                }
                else
                {
                    Messager::error('U heeft geen javascript bestand geselecteerd.', false, true);
                }
                redirect('?do=update&id=' . $template['template_id'] . '&type=js');
            }
        }
        else if (isset($_GET['action']) && $_GET['action'] == 'uploadImage')
        {
            if (isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name']))
            {
                if (!is_dir($templateDir . 'images' . DS))
                    mkdir($templateDir . 'images' . DS);

                move_uploaded_file($_FILES['image']['tmp_name'], $templateDir . 'images' . DS . $_FILES['image']['name']);
                Messager::ok('De afbeelding is succesvol geupload.', false, true);

                redirect('?do=update&id=' . $template['template_id'] . '&type=images');
            }
        }
        else if (isset($_GET['action']) && $_GET['action'] == 'orderCSS')
        {
            $result = $_REQUEST["cssList"];
            
            $i = 1;
            $cssFiles = array();
            foreach($result as $value)
            {
                if (!empty($value))
                {
                    $ex = explode('_', $value);
                    if (is_numeric($ex[0]) && is_numeric($ex[1]))
                    {
                        $db->prepare("SELECT `filename`
                                      FROM   `cms_template_css`
                                      WHERE  `template_id` = :template
                                      AND    `order` = :order
                                      LIMIT 1")
                           ->bindValue('template', $ex[0])
                           ->bindValue('order',    $ex[1])
                           ->execute();
                        if ($db->num_rows() == 1)
                        {
                            $css = $db->fetch_assoc();
                            $cssFiles[$i] = $css['filename'];
                            $i++;
                        }
                    }
                }
            }

            foreach ($cssFiles as $id => $value)
            {
                $db->prepare("UPDATE `cms_template_css`
                              SET    `order` = :order
                              WHERE  `filename` = :filename
                              AND    `template_id` = :id
                              LIMIT 1")
                   ->bindValue('order',    $id)
                   ->bindValue('filename', $value)
                   ->bindValue('id',       $template['template_id'])
                   ->execute();

            }
            exit();
        }
        else if (isset($_GET['action']) && $_GET['action'] == 'orderJS')
        {
            $result = $_REQUEST["jsList"];

            $i = 1;
            $jsFiles = array();
            foreach($result as $value)
            {
                if (!empty($value))
                {
                    $ex = explode('_', $value);
                    if (is_numeric($ex[0]) && is_numeric($ex[1]))
                    {
                        $db->prepare("SELECT `filename`
                                      FROM   `cms_template_js`
                                      WHERE  `template_id` = :template
                                      AND    `order` = :order
                                      LIMIT 1")
                           ->bindValue('template', $ex[0])
                           ->bindValue('order',    $ex[1])
                           ->execute();
                        if ($db->num_rows() == 1)
                        {
                            $js = $db->fetch_assoc();
                            $jsFiles[$i] = $js['filename'];
                            $i++;
                        }
                    }
                }
            }

            foreach ($jsFiles as $id => $value)
            {
                $db->prepare("UPDATE `cms_template_js`
                              SET    `order` = :order
                              WHERE  `filename` = :filename
                              AND    `template_id` = :id
                              LIMIT 1")
                   ->bindValue('order',    $id)
                   ->bindValue('filename', $value)
                   ->bindValue('id',       $template['template_id'])
                   ->execute();                
            }
            exit();
        }
        else
        {
            Template::minify($template['template_id']);
            $output->addJavascript("/cms/pub/jquery/jquery.tablednd_0_5.js");
            ?>
            <div id="box-tabs" class="box ui-tabs ui-widget ui-widget-content ui-corner-all">
                <div class="title">
                    <h5>Bestanden beheren [Template: <?=$template['title']?>]</h5>
                    <ul class="links ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
                        <li class="ui-state-default ui-corner-top<?php if (!isset($_GET['type']) || $_GET['type'] == 'index') { echo ' ui-tabs-selected ui-state-active ui-state-focus'; } ?>"><a href="?do=update&id=<?=$template['template_id']?>&type=index">Index.php</a></li>
                        <li class="ui-state-default ui-corner-top<?php if (isset($_GET['type']) && $_GET['type'] == 'css') { echo ' ui-tabs-selected ui-state-active ui-state-focus'; } ?>"><a href="?do=update&id=<?=$template['template_id']?>&type=css">CSS bestanden</a></li>
                        <li class="ui-state-default ui-corner-top<?php if (isset($_GET['type']) && $_GET['type'] == 'js') { echo ' ui-tabs-selected ui-state-active ui-state-focus'; } ?>"><a href="?do=update&id=<?=$template['template_id']?>&type=js">Javascript bestanden</a></li>
                        <li class="ui-state-default ui-corner-top<?php if (isset($_GET['type']) && $_GET['type'] == 'images') { echo ' ui-tabs-selected ui-state-active ui-state-focus'; } ?>"><a href="?do=update&id=<?=$template['template_id']?>&type=images">Afbeeldingen</a></li>
                    </ul>
                </div>
                <div class="content" style="display: block;">
                    <?php
                    if ((isset($_GET['type']) && $_GET['type'] == 'css'))
                    {
                        $dbCss = array();
                        $db->prepare("SELECT `filename`
                                      FROM   `cms_template_css`
                                      WHERE  `template_id` = :template_id
                                      ORDER BY `order` ASC")
                           ->bindValue('template_id', $template['template_id'])
                           ->execute();
                        while ($css = $db->fetch_assoc())
                        {
                            $dbCss[] = $css['filename'];
                        }

                        $css = Dir::getFiles($templateDir . 'css', 'css');

                        $dbExists = array();
                        foreach ($dbCss as $file)
                        {
                            if (in_array($file, $css))
                            {
                                $dbExists[] = $file;
                            }
                        }

                        $newArray = array_merge($dbExists, array_diff($css, $dbExists));

                        if (count($newArray) > 0)
                        {
                            $db->prepare("DELETE FROM `cms_template_css`
                                          WHERE  `template_id` = :template_id")
                               ->bindValue('template_id', $template['template_id'])
                               ->execute();
                            ?>
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    var template = <?=$template['template_id']?>;
                                    
                                    $('#cssList').tableDnD({
                                        onDrop: function(table, row) {
                                            var list = $.tableDnD.serialize();
                                            splitted = list.split("&");

                                            var newArray = new Array();
                                            count = 1;
                                            for(var split in splitted) {
                                                var newSplit = splitted[split].replace("cssList[]=", "");
                                                if (newSplit.length > 0) {
                                                    $("tr#" + newSplit.toString() + "").attr('class', newSplit.toString());
                                                    newArray[newSplit] = count;
                                                    count++;
                                                }
                                            }

                                            for (var id in newArray) {
                                                $("." + id).attr('id', template + "_" + newArray[id]);
                                                $("#" + template + "_" + newArray[id]).removeClass();
                                            }
                                            
                                            $('#CssResult').load("?do=update&id=<?=$template['template_id']?>&action=orderCSS&"+list);
                                        },
                                        dragHandle: "dragHandle"
                                    });
                                });
                            </script>
                            <div id="CssResult"></div>
                            <table id="cssList">
                                <tr class="nodrag">
                                    <th></th>
                                    <th>Bestandsnaam:</th>
                                    <th colspan="2">Opties:</th>
                                </tr>
                                <?php
                                $i = 1;
                                foreach ($newArray as $file)
                                {
                                    $db->prepare("INSERT INTO `cms_template_css` (`template_id`,
                                                                                  `filename`,
                                                                                  `order`)
                                                  VALUES (:template_id,
                                                          :filename,
                                                          :order)")
                                       ->bindValue('template_id', $template['template_id'])
                                       ->bindValue('filename',    $file)
                                       ->bindValue('order',       $i)
                                       ->execute();                                    
                                    ?>
                                    <tr id="<?=$template['template_id'] . '_' . $i?>">
                                        <td style="vertical-align: middle; cursor: move; width: 20px;" class="dragHandle"><img src="/beheer/resources/images/icons/updown.gif" alt="Verplaats" /></td>
                                        <td><?=$file?></td>
                                        <td class="last" width="80">
                                            <a href="?do=editFile&amp;type=css&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>"><img src="/icons/fugues/icons/pencil.png" style="vertical-align: bottom;" alt="Bewerken" /></a>
                                            <a href="?do=editFile&amp;type=css&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>">Bewerken</a>
                                        </td>
                                        <td>
                                            <a href="?do=deleteFile&amp;type=css&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijderen" /></a>
                                            <a href="?do=deleteFile&amp;type=css&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>">Verwijderen</a>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                }
                                ?>
                            </table>
                            <?php
                        }
                        ?>
                        <form action="?do=update&id=<?=$template['template_id']?>&action=uploadCSS" method="post" enctype="multipart/form-data">
                            <div class="normalrow">
                                <label class="required">CSS bestand uploaden</label>
                                <input type="file" name="css" />
                            </div>
                            <div class="onlyinput">
                                <input type="submit" value="Uploaden" />
                            </div>
                        </form>    
                        <?php
                    }

                    else if ((isset($_GET['type']) && $_GET['type'] == 'js'))
                    {
                        $dbJs = array();
                        $db->prepare("SELECT `filename`
                                      FROM   `cms_template_js`
                                      WHERE  `template_id` = :template_id
                                      ORDER BY `order` ASC")
                           ->bindValue('template_id', $template['template_id'])
                           ->execute();
                        while ($js = $db->fetch_assoc())
                        {
                            $dbJs[] = $js['filename'];
                        }

                        $js = Dir::getFiles($templateDir . 'js', 'js');

                        $dbExists = array();
                        foreach ($dbJs as $file)
                        {
                            if (in_array($file, $js))
                            {
                                $dbExists[] = $file;
                            }
                        }

                        $newArray = array_merge($dbExists, array_diff($js, $dbExists));

                        if (count($newArray) > 0)
                        {
                            $db->prepare("DELETE FROM `cms_template_js`
                                          WHERE  `template_id` = :template_id")
                               ->bindValue('template_id', $template['template_id'])
                               ->execute();
                            ?>
                            <script type="text/javascript">
                                $(document).ready(function() {
                                    var template = <?=$template['template_id']?>;

                                    $('#jsList').tableDnD({
                                        onDrop: function(table, row) {
                                            var list = $.tableDnD.serialize();
                                            splitted = list.split("&");

                                            var newArray = new Array();
                                            count = 1;
                                            for(var split in splitted) {
                                                var newSplit = splitted[split].replace("jsList[]=", "");
                                                if (newSplit.length > 0) {
                                                    $("tr#" + newSplit.toString() + "").attr('class', newSplit.toString());
                                                    newArray[newSplit] = count;                                                    
                                                    count++;
                                                }
                                            }

                                            for (var id in newArray) {
                                                $("." + id).attr('id', template + "_" + newArray[id]);
                                                $("#" + template + "_" + newArray[id]).removeClass();
                                            }
                                            
                                            $('#JsResult').load("?do=update&id=<?=$template['template_id']?>&action=orderJS&"+list);
                                        },
                                        dragHandle: "dragHandle"
                                    });
                                });
                            </script>
                            <div id="JsResult"></div>
                            <table id="jsList">
                                <tr class="nodrag">
                                    <th></th>
                                    <th>Bestandsnaam:</th>
                                    <th colspan="2">Opties:</th>
                                </tr>
                                <?php
                                $i = 1;
                                foreach ($newArray as $file)
                                {
                                    $db->prepare("INSERT INTO `cms_template_js` (`template_id`,
                                                                                  `filename`,
                                                                                  `order`)
                                                  VALUES (:template_id,
                                                          :filename,
                                                          :order)")
                                       ->bindValue('template_id', $template['template_id'])
                                       ->bindValue('filename',    $file)
                                       ->bindValue('order',       $i)
                                       ->execute();                                    
                                    ?>
                                    <tr id="<?=$template['template_id'] . '_' . $i?>">
                                        <td style="vertical-align: middle; cursor: move; width: 20px;" class="dragHandle"><img src="/beheer/resources/images/icons/updown.gif" alt="Verplaats" /></td>
                                        <td><?=$file?></td>
                                        <td class="last" width="80">
                                            <a href="?do=editFile&amp;type=js&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>"><img src="/icons/fugues/icons/pencil.png" style="vertical-align: bottom;" alt="Bewerken" /></a>
                                            <a href="?do=editFile&amp;type=js&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>">Bewerken</a>
                                        </td>
                                        <td>
                                            <a href="?do=deleteFile&amp;type=js&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijderen" /></a>
                                            <a href="?do=deleteFile&amp;type=js&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>">Verwijderen</a>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                }
                                ?>
                            </table>
                            <?php
                        }
                        ?>
                        <form action="?do=update&id=<?=$template['template_id']?>&action=uploadJS" method="post" enctype="multipart/form-data">
                            <div class="normalrow">
                                <label class="required">Javascript bestand uploaden</label>
                                <input type="file" name="js" />
                            </div>
                            <div class="onlyinput">
                                <input type="submit" value="Uploaden" />
                            </div>
                        </form>     
                        <?php
                    }

                    else if ((isset($_GET['type']) && $_GET['type'] == 'images'))
                    {
                        $images = Dir::getFiles($templateDir . 'images');
                        ?>
                        <table>
                            <tr>
                                <th>Bestandsnaam:</th>
                                <th colspan="1">Opties:</th>
                            </tr>
                            <?php
                            foreach ($images as $file)
                            {
                                ?>
                                <tr>
                                    <td><?=$file?></td>
                                    <td>
                                        <a href="?do=deleteFile&amp;type=image&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijderen" /></a>
                                        <a href="?do=deleteFile&amp;type=image&amp;id=<?=$template['template_id']?>&amp;file=<?=$file?>">Verwijderen</a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <form action="?do=update&id=<?=$template['template_id']?>&action=uploadImage" method="post" enctype="multipart/form-data">
                            <div class="normalrow">
                                <label class="required">Afbeelding uploaden</label>
                                <input type="file" name="image" />
                            </div>
                            <div class="onlyinput">
                                <input type="submit" value="Uploaden" />
                            </div>
                        </form>
                        <?php
                    }

                    else if (!isset($_GET['type']) || $_GET['type'] == 'index')
                    {
                        if (file_exists($templateDir . 'index.php'))
                        {
                            if ($_SERVER['REQUEST_METHOD'] == 'POST')
                            {
                                if (!empty($_POST['content']))
                                {
                                    file_put_contents($templateDir . 'index.php', str_replace('→', '&rarr;', $_POST['content']));
                                    Messager::ok('Het bestand is succesvol aangepast.');
                                }
                            }

                            $fileContent = file_get_contents($templateDir . 'index.php');

                            ?>
                            <form action="" method="post">
                                <div class="onlyinput">
                                    <textarea rows="10" cols="50" name="content" style="width: 80%; min-height: 400px;"><?=$fileContent?></textarea>
                                </div>
                                <div class="onlyinput">
                                    <input type="submit" value="Opslaan" />
                                </div>
                            </form>
                            <?php
                        }
                        else
                        {
                            $fileContent = file_get_contents(dirname(__FILE__) . '/defaultIndex.php');

                            $fileHandle = fopen($templateDir . 'index.php', 'w');
                            fwrite($fileHandle, str_replace('→', '&rarr;', $fileContent));
                            fclose($fileHandle);

                            Messager::notify('De index.php bestond niet. Er is een standaard index.php aangemaakt.', false, true);
                            redirect('?do=update&id=1&type=index');
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }
    else
    {
        Messager::error('Het door u ingegeven id bestaat niet.', false, true);
        redirect('?do=list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingegeven.', false, true);
    redirect('?do=list');
}
