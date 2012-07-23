<div id="box-tabs" class="box ui-tabs ui-widget ui-widget-content ui-corner-all">
    <div class="title">
        <h5>Template toevoegen</h5>
        <ul class="links ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top<?php if (!isset($_GET['type']) || $_GET['type'] == 'zip') { echo ' ui-tabs-selected ui-state-active ui-state-focus'; } ?>"><a href="?do=add&type=zip">Upload zip</a></li>
            <li class="ui-state-default ui-corner-top<?php if (isset($_GET['type']) && $_GET['type'] == 'map') { echo ' ui-tabs-selected ui-state-active ui-state-focus'; } ?>"><a href="?do=add&type=map">Kies een map</a></li>
        </ul>        
    </div>
    <div class="content" style="display: block;">
        <?php
        if (!isset($_GET['type']) || $_GET['type'] == 'zip')
        {
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                if (isset($_POST['title']) && isset($_POST['identifier']) && isset($_FILES['zip']) && !empty($_POST['title']) && !empty($_POST['identifier']))
                {
                    if (isset($_FILES['zip']['name']) && !empty($_FILES['zip']['name']))
                    {
                        $identifier =  Generic::cleanFileName(strtolower($_POST['identifier']));

                        $db->prepare("SELECT 1
                                      FROM   `cms_template`
                                      WHERE  `identifier` = :identifier
                                      OR     `title` = :title
                                      LIMIT 1")
                           ->bindValue('identifier', $identifier)
                           ->bindValue('title',      $_POST['title'])
                           ->execute();
                        if ($db->num_rows() == 0)
                        {
                            $dirName = $_SERVER['DOCUMENT_ROOT'] . DS . 'cms/templates' . DS;
                            $zip = new ZipArchive;
                            if ($zip->open($_FILES['zip']['tmp_name']) === true)
                            {
                                if (!is_dir($dirName))
                                    mkdir($dirName);

                                $tmpDir = date('d-m-Y') . DS;
                                $currentDir = $dirName . $tmpDir;
                                
                                if (!is_dir($currentDir))
                                {
                                    mkdir($currentDir);
                                }
                                else
                                {
                                    Dir::delete($currentDir);
                                    mkdir($currentDir);
                                }

                                if ($zip->extractTo($currentDir) === true)
                                {                              
                                    if (file_exists($currentDir . 'index.php'))
                                    {
                                        if (is_dir($dirName . $identifier . DS))
                                            Dir::delete($dirName . $identifier . DS);
                                        else
                                            mkdir($dirName . $identifier . DS);

                                        if ($zip->extractTo($dirName . $identifier . DS) === true)
                                        {
                                            $db->prepare("INSERT INTO `cms_template` (`title`,
                                                                                      `identifier`)
                                                          VALUES (:title,
                                                                  :identifier)")
                                               ->bindValue('title',      $_POST['title'])
                                               ->bindValue('identifier', $identifier)
                                               ->execute();

                                            $insertId = $db->insert_id();
                                            $db->query("SELECT 1
                                                        FROM   `cms_template`");
                                            if ($db->num_rows() == 1)
                                            {
                                                $db->query("UPDATE `cms_template`
                                                            SET    `default` = 1
                                                            LIMIT 1");
                                            }

                                            Messager::ok('De template is succesvol toegevoegd.', false, true);
                                            redirect('?do=update&id=' . $insertId);
                                        }
                                        else
                                        {
                                            Messager::error('Er is een fout opgetreden tijdens het verplaatsen van de bestanden.');
                                        }
                                    }
                                    else
                                    {
                                        Messager::error('Het ZIP bestand bevat geen index.php.');
                                        Messager::notify('De zip die u wilt uploaden moet uit het volgende bestaan:<br />-index.php <i>(bestand)</i><br />-js <i>(map)</i><br />-css <i>(map)</i><br />-images <i>(map)</i>', false);
                                    }
                                    
                                    Dir::delete($currentDir);
                                    $zip->close();
                                }
                                else
                                {
                                    Messager::error('Er is een fout opgetreden tijdens het uitpakken van uw ZIP bestand.');
                                }
                            }
                            else
                            {
                                Messager::error('U heeft geen geldig ZIP bestand geupload.');
                            }
                        }
                        else
                        {
                            Messager::error('De door u opgegeven titel en/of mapnaam bestaat al.');
                        }
                    }
                    else
                    {
                        Messager::error('U heeft geen bestand geselecteerd.');
                    }
                }
                else
                {
                    Messager::error('U heeft niet alle velden volledig ingevuld.');
                }
            }
            else
            {
                Messager::notify('De zip die u wilt uploaden moet uit het volgende bestaan:<br />-index.php <i>(bestand)</i><br />-js <i>(map)</i><br />-css <i>(map)</i><br />-images <i>(map)</i>', false);
            }
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="normalrow">
                    <label class="required">Template titel:</label>
                    <input type="text" name="title" <?php if (isset($_POST['title'])) echo 'value="' . $_POST['title'] . '" ';?>/>
                </div>
                <div class="normalrow">
                    <label class="required">Template mapnaam:</label>
                    <input type="text" name="identifier" <?php if (isset($_POST['identifier'])) echo 'value="' . $_POST['identifier'] . '" ';?> />
                    <i>Dit wordt de mapnaam van de template, gebruik kleine letters en geen speciale tekens.</i>
                </div>
                <div class="normalrow">
                    <label class="required">Zip bestand:</label>
                    <input type="file" name="zip" />
                </div>
                <div class="onlyinput">
                    <input type="submit" value="Toevoegen" />
                </div>
            </form>
            <?php
        }
        else if ($_GET['type'] == 'map')
        {
            $files = scandir(TEMPLATES);
            $templates = array();
            foreach ($files as $file)
            {
                if ($file != '.' && $file != '..')
                {
                    if (is_dir(TEMPLATES . $file))
                        $templates[] = $file;
                }
            }

            $dbTemplates = array();
            $db->query("SELECT `identifier`
                        FROM   `cms_template`");
            while ($template = $db->fetch_assoc())
                $dbTemplates[] = $template['identifier'];


            $templates = array_diff($templates, $dbTemplates);

            if (count($templates) > 0)
            {
                if ($_SERVER['REQUEST_METHOD'] == 'POST')
                {
                    if (isset($_POST['title']) && isset($_POST['directory']))
                    {
                        $db->prepare("SELECT 1
                                      FROM   `cms_template`
                                      WHERE  `title` = :title
                                      LIMIT 1")
                           ->bindValue('title',      $_POST['title'])
                           ->execute();
                        if ($db->num_rows() == 0)
                        {
                            if (is_dir(TEMPLATES . $_POST['directory']))
                            {
                                $db->prepare("INSERT INTO `cms_template` (`title`,
                                                                          `identifier`)
                                              VALUES (:title,
                                                      :identifier)")
                                   ->bindValue('title',      $_POST['title'])
                                   ->bindValue('identifier', $_POST['directory'])
                                   ->execute();

                                Messager::ok('De template is succesvol toegevoegd.', false, true);
                                redirect('?do=update&id=' . $db->insert_id());
                            }
                            else
                            {
                                Messager::error('De door u gekozen map bestaat niet.');
                            }
                        }
                        else
                        {
                            Messager::error('De door u opgegeven titel bestaat al.');
                        }
                    }
                    else
                    {
                        Messager::error('U heeft niet alle velden volledig ingevuld.');
                    }
                }
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="normalrow">
                        <label class="required">Template titel:</label>
                        <input type="text" name="title" <?php if (isset($_POST['title'])) echo 'value="' . $_POST['title'] . '" ';?>/>
                    </div>
                    <div class="normalrow">
                        <label class="required">Map:</label>
                        <select name="directory">
                            <?php
                            foreach ($templates as $template)
                            {
                                if (file_exists(TEMPLATES . $template . DS . 'index.php'))
                                    echo '<option>' . $template . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="onlyinput">
                        <input type="submit" value="Toevoegen" />
                    </div>
                </form>
                <?php
            }
            else
            {
                Messager::warning('Er zijn momenteel geen template mappen beschikbaar die nog niet gebruikt worden.');
            }
        }
        ?>
    </div>
</div>