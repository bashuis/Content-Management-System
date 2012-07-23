<h1>Menu knop toevoegen</h1>
<?php
$output->addTitle('Toevoegen');

$totalSteps = 3;
$step       = &$_SESSION['wizard']['add_menu']['current_step'];
$steps      = &$_SESSION['wizard']['add_menu']['steps'];
$languages  = Lang::getAll();
$langErrors = array();
$action     = &$_SESSION['wizard']['add_menu'];

for ($i = 1; $i <= $totalSteps; $i++)
{
    if (!isset($action['step_' . $i]))
        $action['step_' . $i] = array();
}

if (!isset($step))
    $step  = 1;

// Huidige stap controleren
if (!isset($steps) || !in_array($step - 1, $steps) || $step - 1 < 1)
    $step = 1;
?>

<div class="box ui-tabs ui-widget ui-widget-content ui-corner-all" id="box-tabs">
    <div class="title">
        <h5>Stap <?=$step?></h5>
        <ul class="links ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top <?=$step == 1 ? 'ui-tabs-selected ui-state-active ui-state-focus' : ''?>"><a>Stap 1</a></li>
            <li class="ui-state-default ui-corner-top <?=$step == 2 ? 'ui-tabs-selected ui-state-active ui-state-focus' : ''?>"><a>Stap 2</a></li>
        </ul>
    </div>
    <div class="content">
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (isset($_POST['previous_step']))
            {
                if ($step > 1)
                {
                    $step--;
                    redirect(REDIRECT_PATH);
                }
            }
            else if (isset($_POST['cancel']))
            {
                $_SESSION['wizard'] = array();
                redirect('/beheer/menu/list');
            }
            else if (isset($_POST['next_step']))
            {
                if ($step == 1)
                {
                    $langErrors = array();

                    foreach ($languages as $language)
                    {
                        if (empty($_POST['name_' . $language['id']]))
                        {
                            $langErrors[] = $language;
                        }
                    }

                    if (count($langErrors) > 0)
                    {
                        Messager::error('Voor elke taal moet u minimaal het <strong>naam</strong> veld invullen.', false);
                    }
                    else
                    {
                        $action['step_1'] = $_POST;
                        if (isset($_GET['parent']) && is_numeric($_GET['parent']))
                            $action['step_1']['parent'] = intval($_GET['parent']);
                        
                        $steps = array(1);
                        $step++;
                        redirect(REDIRECT_PATH);
                    }
                }
                else if ($step == 2)
                {
                    $errors = array();

                    /**
                     * Error handling
                     */
                    if ($action['step_1']['type'] == 'page')
                    {
                        if (!isset($_POST['item_id']) || !is_numeric($_POST['item_id']) || empty($_POST['item_id']))
                        {
                            $errors[] = 'U heeft geen geldige pagina geselecteerd.';
                        }
                    }

                    else if ($action['step_1']['type'] == 'link')
                    {
                        $useForAll = isset($_POST['useFirstForAll']) ? true : false;

                        if ($useForAll)
                        {
                            if (isset($_POST['firstFile']) && !empty($_POST['firstFile']))
                            {
                                if (!isset($_POST['url_' . $_POST['firstFile']]) || empty($_POST['url_' . $_POST['firstFile']]))
                                {
                                    $errors[] = 'U moet minimaal de url van de hoofdtaal invullen.';
                                }
                            }
                            else
                            {
                                $errors[] = 'De standaard taal is niet bekend. Probeer het opnieuw.';
                            }
                        }
                        else
                        {
                            foreach ($languages as $language)
                            {
                                if (!isset($_POST['url_' . $language['id']]) || empty($_POST['url_' . $language['id']]))
                                {
                                    $errors[] = 'U heeft geen url ingevoerd voor de taal <strong>' . $language['name'] . '</strong>.';
                                }
                            }
                        }
                    }

                    else if ($action['step_1']['type'] == 'iframe')
                    {
                        $useForAll = isset($_POST['useFirstForAll']) ? true : false;

                        if ($useForAll)
                        {
                            if (isset($_POST['firstFile']) && !empty($_POST['firstFile']))
                            {
                                if (!isset($_POST['url_' . $_POST['firstFile']]) || empty($_POST['url_' . $_POST['firstFile']]))
                                {
                                    $errors[] = 'U moet minimaal de url van de hoofdtaal invullen.';
                                }
                            }
                            else
                            {
                                $errors[] = 'De standaard taal is niet bekend. Probeer het opnieuw.';
                            }
                        }
                        else
                        {
                            foreach ($languages as $language)
                            {
                                if (!isset($_POST['url_' . $language['id']]) || empty($_POST['url_' . $language['id']]))
                                {
                                    $errors[] = 'U heeft geen url ingevoerd voor de taal <strong>' . $language['name'] . '</strong>.';
                                }
                            }
                        }

                        if (!isset($_POST['height']) || empty($_POST['height']))
                            $errors[] = 'U heeft geen hoogte ingevuld.';

                        if (!isset($_POST['height']) || empty($_POST['height']))
                            $errors[] = 'U heeft geen breedte ingevuld.';
                    }

                    else if ($action['step_1']['type'] == 'include')
                    {
                        if (!isset($_POST['filepath']) || empty($_POST['filepath']))
                        {
                            $errors[] = 'U heeft geen bestandslocatie ingevuld.';
                        }
                        else if (!file_exists($_POST['filepath']))
                        {
                            $errors[] = 'De door u ingevulde bestandslocatie bestaat niet.';
                        }
                    }

                    else if ($action['step_1']['type'] == 'module')
                    {
                        if (!isset($_POST['module']) || empty($_POST['module']))
                            $errors[] = 'U heeft geen geldige module geselecteerd.';
                    }

                    else if ($action['step_1']['type'] == 'file')
                    {
                        $useForAll = isset($_POST['useFirstForAll']) ? true : false;

                        if ($useForAll)
                        {
                            if (isset($_POST['firstFile']) && !empty($_POST['firstFile']))
                            {
                                if ((!isset($_POST['browse_' . $_POST['firstFile']]) || empty($_POST['browse_' . $_POST['firstFile']])) && empty($_FILES['upload_' . $_POST['firstFile']]['name']))
                                {
                                    $errors[] = 'U moet minimaal een bestand voor de hoofdtaal selecteren.';
                                }
                            }
                            else
                            {
                                $errors[] = 'De standaard taal is niet bekend. Probeer het opnieuw.';
                            }
                        }
                        else
                        {
                            foreach ($languages as $language)
                            {
                                if ((!isset($_POST['browse_' . $language['id']]) || empty($_POST['browse_' . $language['id']])) && empty($_FILES['upload_' . $language['id']]['name']))
                                {
                                    $errors[] = 'U heeft geen bestand geselecteerd voor de taal <strong>' . $language['name'] . '</strong>.';
                                }
                            }
                        }
                    }

                    /**
                     * There are 0 errors. Let's go!
                     */
                    if (count($errors) == 0)
                    {
                        $parent   = isset($action['step_1']['parent']) ? $action['step_1']['parent'] : 0;
                        $itemId   = intval(isset($_POST['item_id']) ? $_POST['item_id'] : 0);
                        $template = isset($action['step_1']['template']) && $action['step_1']['template'] > 0 ? $action['step_1']['template'] : NULL;

                        $replacements = array(' ' => '-',
                                              '&' => '+',
                                              '_' => '-' );
                        $tag = str_ireplace(array_keys($replacements), array_values($replacements), $action['step_1']['name_1']);
                        $tag = preg_replace("/[^0-9a-zA-Z\-]/", '', $tag );
                        $tag = strtolower($tag);

                        $nth = 0;
                        do
                        {
                            $tmpTag = $nth == 0 ? $tag : $tag . '-' . $nth;
                            $nth++;
                            $db->query("SELECT 1
                                        FROM   `cms_menuitem`
                                        WHERE  `mi_tag` = '" . $tmpTag . "'
                                        LIMIT 1");
                        }
                        while ($db->num_rows() == 1);
                        $tag = $tmpTag;

                        $db->query("SELECT MAX(`mi_position`) AS highest
                                    FROM   `cms_menuitem`
                                    WHERE  `mi_parent` = " . intval($parent));
                        $positionCheck  = $db->fetch_assoc();
                        $position       = $positionCheck['highest'] + 1;

                        $db->prepare("INSERT INTO `cms_menuitem` (`mi_tag`,
                                                                  `mi_position`,
                                                                  `mi_parent`,
                                                                  `mi_active`,
                                                                  `mi_type`,
                                                                  `mi_item_id`,
                                                                  `template_id`)
                                      VALUES (:tag,
                                              :position,
                                              :parent,
                                              :active,
                                              :type,
                                              :item_id,
                                              :template)")
                           ->bindValue('tag',       $tag)
                           ->bindValue('position',  $position)
                           ->bindValue('parent',    $parent)
                           ->bindValue('active',    1)
                           ->bindValue('type',      $action['step_1']['type'])
                           ->bindValue('item_id',   $itemId)
                           ->bindValue('template',  $template)
                           ->execute();

                        $mid = $db->insert_id();

                        foreach ($languages as $language)
                        {
                            $db->prepare("INSERT INTO `cms_menuitem_translation` (`mi_id`,
                                                                                  `lang_id`,
                                                                                  `name`,
                                                                                  `description`,
                                                                                  `keywords`)
                                          VALUES (:id,
                                                  :lang,
                                                  :name,
                                                  :description,
                                                  :keywords)")
                               ->bindValue('id',            $mid)
                               ->bindValue('lang',          $language['id'])
                               ->bindValue('name',          $action['step_1']['name_' . $language['id']])
                               ->bindValue('description',   $action['step_1']['description_' . $language['id']], false)
                               ->bindValue('keywords',      $action['step_1']['keywords_' . $language['id']],    false)
                               ->execute();
                        }

                        if ($action['step_1']['type'] == 'page')
                        {
                            if ($_POST['item_id'] == '-1')
                            {
                                $db->query("UPDATE `cms_menuitem`
                                            SET `mi_active` = 0
                                            WHERE `mi_id` = " . $mid . "
                                            LIMIT 1");
                                $_SESSION['wizard'] = array();
                                
                                Messager::ok('De menu knop is succesvol aangemaakt. U kunt nu de pagina aanmaken voor deze menu knop.', false, true);
                                redirect('/beheer/page/new/?mid=' . $mid);
                                exit();
                            }
                        }
                        else if ($action['step_1']['type'] == 'link')
                        {
                            $db->prepare("INSERT INTO `cms_link` (`l_target`)
                                          VALUES (:target)")
                                ->bindValue('target', $_POST['target'])
                                ->execute();
                            $linkId = $db->insert_id();

                            foreach ($languages as $language)
                            {
                                $theUrl = $_POST['url_' . $language['id']];

                                $db->prepare("INSERT INTO `cms_link_translation` (`l_id`,
                                                                                  `lang_id`,
                                                                                  `url`)
                                              VALUES (:id,
                                                      :lang,
                                                      :url)")
                                   ->bindValue('id', $linkId)
                                   ->bindValue('lang', $language['id'])
                                   ->bindValue('url', $theUrl)
                                   ->execute();
                            }

                            $db->prepare("UPDATE `cms_menuitem`
                                          SET    `mi_item_id` = :item_id
                                          WHERE  `mi_id` = :mi_id
                                          LIMIT 1")
                               ->bindValue('item_id', $linkId)
                               ->bindValue('mi_id',   $mid)
                               ->execute();

                            makePublic($linkId, 'link');
                        }
                        else if ($action['step_1']['type'] == 'iframe')
                        {
                            $transparancy = isset($_POST['allowtransparency']) ? 1 : 0;

                            $db->prepare("INSERT INTO `cms_iframe` (`ifr_height`,
                                                                    `ifr_width`,
                                                                    `ifr_allowtransparency`)
                                          VALUES (:height,
                                                  :width,
                                                  :allowtransparency)")
                               ->bindValue('height',            $_POST['height'])
                               ->bindValue('width',             $_POST['width'])
                               ->bindValue('allowtransparency', isset($_POST['allowtransparency']) ? 1 : 0)
                               ->execute();

                            $frameId = $db->insert_id();

                            foreach ($languages as $language)
                            {
                                if (isset($_POST['useFirstForAll']))
                                {
                                    $theUrl = $_POST['url_1'];
                                }
                                else
                                {
                                    $theUrl = $_POST['url_' . $language['id']];
                                }

                                $db->prepare("INSERT INTO `cms_iframe_translation` (`ifr_id`,
                                                                                    `lang_id`,
                                                                                    `url`)
                                              VALUES (:ifrid,
                                                      :langid,
                                                      :url)")
                                   ->bindValue('ifrid', $frameId)
                                   ->bindValue('langid', $language['id'])
                                   ->bindValue('url', $theUrl)
                                   ->execute();
                            }

                            $db->prepare("UPDATE `cms_menuitem`
                                          SET    `mi_item_id` = :item_id
                                          WHERE  `mi_id` = :mi_id
                                          LIMIT 1")
                               ->bindValue('item_id', $frameId)
                               ->bindValue('mi_id',   $mid)
                               ->execute();

                            makePublic($frameId, 'frame');
                        }
                        else if ($action['step_1']['type'] == 'include')
                        {
                            $db->prepare("INSERT INTO `cms_include` (`inc_file`)
                                          VALUES (:filepath)")
                               ->bindValue('filepath', $_POST['filepath'])
                               ->execute();

                            $fileId = $db->insert_id();

                            $db->prepare("UPDATE `cms_menuitem`
                                          SET    `mi_item_id` = :item_id
                                          WHERE  `mi_id` = :mi_id
                                          LIMIT 1")
                               ->bindValue('item_id', $fileId)
                               ->bindValue('mi_id',   $mid)
                               ->execute();

                            makePublic($fileId, 'include');
                        }
                        else if ($action['step_1']['type'] == 'module')
                        {
                            $choice = explode(":", $_POST['module'] );
                            $module = intval($choice[0]);
                            if (sizeof($choice) == 1)
                            {
                                $instance           = NULL;
                                $permissionCheckId  = $module;
                            }
                            else
                            {
                                $instance           = intval($choice[1]);
                                $permissionCheckId  = $instance;
                            }

                            $db->prepare("UPDATE `cms_menuitem`
                                          SET    `mi_item_id` = :module,
                                                 `mi_mod_instance` = :instance
                                          WHERE  `mi_id` = :mid
                                          LIMIT 1")
                               ->bindValue('module',    $module)
                               ->bindValue('instance',  $instance)
                               ->bindValue('mid',       $mid)
                               ->execute();

                            makePublic($permissionCheckId, 'module');
                        }
                        else if ($action['step_1']['type'] == 'file')
                        {
                            $db->prepare("INSERT INTO `cms_file` (`f_target`)
                                          VALUES (:target)")
                               ->bindValue('target', $_POST['target'])
                               ->execute();

                            $fileId = $db->insert_id();

                            if (isset($_POST['useFirstForAll']))
                            {
                                if (!empty($_FILES['upload_' . $_POST['firstFile']]['name']))
                                {
                                    $fileName = $_FILES['upload_' . $_POST['firstFile']]['name'];
                                    move_uploaded_file($_FILES['upload_' . $_POST['firstFile']]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $fileName);
                                }
                                else
                                {
                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $_POST['browse_' . $_POST['firstFile']]))
                                    {
                                        $fileName = $_POST['browse_' . $_POST['firstFile']];
                                    }
                                }
                            }

                            foreach ($languages as $language)
                            {
                                if (isset($fileName))
                                {
                                    $langFileName = $fileName;
                                }
                                if (!empty($_FILES['upload_' . $language['id']]['name']))
                                {
                                    $langFileName = $_FILES['upload_' . $language['id']]['name'];
                                    move_uploaded_file($_FILES['upload_' . $language['id']]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $langFileName);
                                }
                                else
                                {
                                    if(file_exists( $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $_POST['browse_' . $language['id']]))
                                    {
                                        $langFileName = $_POST['browse_' . $language['id']];
                                    }
                                }

                                $db->prepare("INSERT INTO `cms_file_translation` (`f_id`,
                                                                                  `lang_id`,
                                                                                  `file`)
                                              VALUES (:fid,
                                                      :langid,
                                                      :file)")
                                   ->bindValue('fid',       $fileId)
                                   ->bindValue('langid',    $language['id'])
                                   ->bindValue('file',      $langFileName)
                                   ->execute();
                            }

                            $db->prepare("UPDATE `cms_menuitem`
                                          SET    `mi_item_id` = :item_id
                                          WHERE  `mi_id` = :mi_id
                                          LIMIT 1")
                               ->bindValue('item_id', $fileId)
                               ->bindValue('mi_id',   $mid)
                               ->execute();

                            makePublic($fileId, 'file');
                        }

                        $_SESSION['wizard'] = array();
                        Messager::ok('De menu knop is succesvol toegevoegd.', false, true);
                        redirect('/beheer/menu/list');
                    }
                    else
                    {
                        foreach ($errors as $error)
                        {
                            Messager::error($error, false);
                        }
                    }
                }
            }
        }

        if ($step == 1)
        {
            ?>
            <script type="text/javascript">
                $(function() {
                    $( "#accordion" ).accordion({
                        header: 'div.onlyinput',
                        icons: false,
                        event: "click hoverintent"
                    });
                });
            </script>
            <form action="" method="post">
                <div id="accordion">
                    <?php
                    foreach ($languages as $language)
                    {
                        ?>
                        <div class="onlyinput" style="cursor: pointer; background-color: <?=in_array($language, $langErrors) ? '#FBC2C4' : '#EEEEEE'?>; padding: 10px; font-weight: bold;">
                            <img src="<?php echo $language['flag']; ?>" alt=""/>
                            <?php echo $language['name']; ?>
                        </div>
                        <div>
                            <div class="normalrow">
                                <label class="required">Naam:</label>
                                <input type="text" name="name_<?=$language['id']?>" value="<?=Form::getValue($_POST, $action['step_1'], 'name_' . $language['id'])?>" />
                            </div>
                            <div class="normalrow">
                                <label>Beschrijving:</label>
                                <textarea name="description_<?=$language['id']?>" cols="44" rows="3"><?=Form::getValue($_POST, $action['step_1'], 'description_' . $language['id'])?></textarea>
                            </div>
                            <div class="normalrow">
                                <label>Sleutelwoorden:</label>
                                <textarea name="keywords_<?=$language['id']?>" cols="44" rows="3"><?=Form::getValue($_POST, $action['step_1'], 'keywords_' . $language['id'])?></textarea>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="normalrow">
                    <label class="required">Koppeling:</label>
                    <select name="type">
                        <?php
                        $value = Form::getValue($_POST, $action['step_1'], 'type');
                        $previousTypeChoice = empty($value) ? '' : $value;
                        $types = array('page'       => 'Pagina op de website',
                                       'link'       => 'Externe link',
                                       'iframe'     => 'iFrame',
                                       'include'    => 'PHP Script',
                                       'module'     => 'PHP Module',
                                       'file'       => 'Bestand');

                        foreach ($types as $type => $niceName)
                        {
                            ?>
                            <option value="<?=$type?>"<?php if ($previousTypeChoice == $type){ echo ' selected="selected"'; } ?>><?=$niceName?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="normalrow">
                    <label class="required">Template:</label>
                    <select name="template">
                        <option value="-1">Gebruik de standaard template</option>
                        <?php
                        $db->query("SELECT `template_id`,
                                           `title`
                                    FROM   `cms_template`
                                    ORDER BY `title`");
                        while ($template = $db->fetch_assoc())
                        {
                            if (Form::getValue($_POST, $action['step_1'], 'template') == $template['template_id'])
                            {
                                ?>
                                <option value="<?=$template['template_id']?>" selected="selected"><?=$template['title']?></option>
                                <?php
                            }
                            else
                            {
                                ?>
                                <option value="<?=$template['template_id']?>"><?=$template['title']?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="onlyinput">
                    <input type="submit" name="cancel" value="Annuleer" />
                    <input type="submit" name="next_step" value="Volgende &rarr;" />
                </div>
            </form>
            <?php
        }
        else if ($step == 2)
        {
            ?>
            <form action="" method="post">
                <?php
                if ($action['step_1']['type'] == 'page')
                {
                    $db->query("SELECT p.`p_id` AS id,
                                       t.`name` AS name
                                FROM   `cms_page` AS p
                                JOIN   `cms_page_translation` AS t
                                ON	   (p.`p_id` = t.`p_id`)
                                WHERE  t.`lang_id` = 1
                                AND    p.`p_intrash` = 0
                                ORDER BY  p.`p_id`");

                    ?>
                    <div class="normalrow">
                        <label class="required">Kies een pagina:</label>
                        <select name="item_id">
                            <option value="-1">Nieuwe pagina aanmaken</option>
                            <?php

                            while ($page = $db->fetch_assoc())
                            {
                                if (Form::getValue($_POST, $action['step_2'], 'item_id') == $page['id'])
                                {
                                    ?>
                                    <option value="<?=$page['id']?>" selected="selected"><?=$page['name']?></option>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <option value="<?=$page['id']?>"><?=$page['name']?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                else if ($action['step_1']['type'] == 'link')
                {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function(){
                            if ($('#useFirstForAll').is(':checked')) {
                                $('.langHide').hide();
                            }

                            $('#useFirstForAll').click(function() {
                                if ($(this).is(':checked')) {
                                    $('.langHide').slideUp();
                                } else {
                                    $('.langHide').slideDown();
                                }
                            });
                        });
                    </script>
                    <div class="form">
                        <div class="normalrow">
                            Lokatie voor:
                        </div>
                        <?php
                        $first = true;
                        foreach ($languages as $language)
                        {
                            ?>
                            <div class="normalrow<?=!$first ? ' langHide': ''?>">
                                <label class="required"><img src="<?=$language['flag']?>" alt="<?=$language['flag']?>" /> <?=$language['name']?>:</label>
                                <input type="text" id="url_<?=$language['id']?>" name="url_<?=$language['id']?>" value="<?=Form::getValue($_POST, $action['step_2'], 'url_' . $language['id'])?>">
                                <?php
                                if ($first)
                                {
                                    $first = false;
                                    ?>
                                    <input type="hidden" name="firstFile" id="firstFile" value="<?php echo $language['id']; ?>" />
                                    <input type="checkbox" name="useFirstForAll" id="useFirstForAll" <?php if (isset($action['step_2']['useFirstForAll']) || isset($_POST['useFirstForAll'])) echo 'checked '; ?>/> <small>Gebruik dit bestand voor alle talen.</small>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="normalrow">
                            <label class="required">Openen in:</label>
                            <select name="target">
                                <option value="_self">Het zelfde venster</option>
                                <option value="_blank">Een nieuw venster</option>
                            </select>
                        </div>
                    </div>
                    <?php
                }
                else if ($action['step_1']['type'] == 'iframe')
                {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function(){
                            if ($('#useFirstForAll').is(':checked')) {
                                $('.langHide').hide();
                            }

                            $('#useFirstForAll').click(function() {
                                if ($(this).is(':checked')) {
                                    $('.langHide').slideUp();
                                } else {
                                    $('.langHide').slideDown();
                                }
                            });
                        });
                    </script>
                    <div class="form">
                        <div class="normalrow">
                                Lokatie voor:
                        </div>
                        <?php
                        $first = true;
                        foreach ($languages as $language)
                        {
                            ?>
                            <div class="normalrow<?=!$first ? ' langHide': ''?>">
                                <label class="required"><img src="<?=$language['flag']?>" alt="<?=$language['flag']?>" /> <?=$language['name']?>:</label>
                                <input type="text" id="url_<?=$language['id']?>" name="url_<?=$language['id']?>" value="<?=Form::getValue($_POST, $action['step_2'], 'url_' . $language['id'])?>">
                                <?php
                                if ($first)
                                {
                                    $first = false;
                                    ?>
                                    <input type="hidden" name="firstFile" id="firstFile" value="<?php echo $language['id']; ?>" />
                                    <input type="checkbox" name="useFirstForAll" id="useFirstForAll" <?php if (isset($action['step_2']['useFirstForAll']) || isset($_POST['useFirstForAll'])) echo 'checked '; ?>/> <small>Gebruik dit bestand voor alle talen.</small>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="normalrow">
                            <label class="required">Hoogte:</label>
                            <input type="text" name="height" />
                        </div>
                        <div class="normalrow">
                            <label class="required">Breedte:</label>
                            <input type="text" name="width" />
                        </div>
                        <div class="normalrow">
                            <label class="required">Transparantie:</label>
                            <input type="checkbox" name="allowtransparency" checked="checked" />
                        </div>
                    </div>
                    <?php
                }
                else if ($action['step_1']['type'] == 'include')
                {
                    ?>
                    <div class="form">
                        <div class="normalrow">
                            <label class="required">Bestandslocatie</label>
                            <input type="text" name="filepath" value="<?=Form::getValue($_POST, $action['step_2'], 'filepath')?>" />
                        </div>
                    </div>
                    <?php
                }
                else if ($action['step_1']['type'] == 'module')
                {
                    ?>
                    <div class="normalrow">
                        <label class="required">Module:</label>
                        <select name="module">
                            <?php
                            $modules = $db->query("SELECT `m_id` AS id,
                                                          `m_name` AS name,
                                                          `m_supports_multiple_instances` AS multi_instance
                                                   FROM   `cms_modules`
                                                   WHERE  `m_is_owned` = 1
                                                   AND	   `m_is_active` = 1");
                            while ($module = $db->fetch_assoc($modules))
                            {
                                if ($module['multi_instance'] == 1)
                                {
                                    $db->query("SELECT `instance`,
                                                       `name`
                                                FROM   `cms_module_instances`
                                                WHERE  `module` = " . intval($module['id']));
                                    if ($db->num_rows() > 0)
                                    {
                                        ?>
                                        <optgroup label="<?php echo Generic::stripAndClean($module['name']); ?>">
                                            <?php
                                            while ($instance = $db->fetch_assoc())
                                            {
                                                if (Form::getValue($_POST, $action['step_2'], 'module') == $module['id'] . ':' . $instance['instance'])
                                                {
                                                    ?>
                                                    <option value="<?=$module['id']?>:<?=$instance['instance']?>" selected="selected">- <?=Generic::stripAndClean($instance['name'])?></option>
                                                    <?php
                                                }
                                                else
                                                {
                                                    ?>
                                                    <option value="<?=$module['id']?>:<?=$instance['instance']?>">- <?=Generic::stripAndClean($instance['name'])?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </optgroup>
                                        <?php
                                    }
                                }
                                else
                                {
                                    if (Form::getValue($_POST, $action['step_2'], 'module') == $module['id'])
                                    {
                                        ?><option value="<?=$module['id']?>" selected="selected"><?=Generic::stripAndClean($module['name'])?></option><?php
                                    }
                                    else
                                    {
                                        ?><option value="<?=$module['id']?>"><?=Generic::stripAndClean($module['name'])?></option><?php
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                else if ($action['step_1']['type'] == 'file')
                {
                    $files = scandir( $_SERVER['DOCUMENT_ROOT'] . '/upload/' );
                    foreach ($files as $index => $file)
                    {
                        if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $file))
                            unset($files[$index]);
                    }

                    ?>
                    <script type="text/javascript">
                        $(document).ready(function(){
                            if ($('#useFirstForAll').is(':checked')) {
                                $('.langHide').hide();
                            }

                            $('#useFirstForAll').click(function() {
                                if ($(this).is(':checked')) {
                                    $('.langHide').slideUp();
                                } else {
                                    $('.langHide').slideDown();
                                }
                            });
                        });
                    </script>
                    <div class="normalrow">
                        Kies een bestand:
                    </div>
                    <?php
                    $first = true;
                    foreach ($languages as $language)
                    {
                        ?>
                        <div class="normalrow<?=!$first ? ' langHide': ''?>">
                            <label class="required"><img src="<?=$language['flag']?>" alt="<?=$language['name']?> Icon" /> <?=$language['name']?>:</label>
                            <select class="fileInput" name="browse_<?=$language['id']?>" id="browse_<?=$language['id']?>">
                                <?php
                                foreach ($files AS $file)
                                {
                                    if (Form::getValue($_POST, $action['step_2'], 'browse' . $language['id']) == $file)
                                    {
                                        ?>
                                        <option value="<?=$file?>" selected="selected"><?=$file?></option>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <option value="<?=$file?>"><?=$file?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                            <input type="file" class="fileInput" name="upload_<?=$language['id']?>" id="upload_<?=$language['id']?>" />
                            <?php
                            if ($first)
                            {
                                $first = false;
                                ?>
                                <input type="hidden" name="firstFile" id="firstFile" value="<?php echo $language['id']; ?>" />
                                <input type="checkbox" name="useFirstForAll" id="useFirstForAll" style="margin-left: 35px" <?php if (isset($action['step_2']['useFirstForAll']) || isset($_POST['useFirstForAll'])) echo 'checked '; ?>/> <small>Gebruik dit bestand voor alle talen.</small>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="normalrow">
                        <label class="required">Openen in:</label>
                        <select name="target">
                            <option value="_self"<?=Form::getValue($_POST, $action['step_2'], 'target') == '_self' ? ' selected="selected"' : ''?>>Het zelfde venster</option>
                            <option value="_blank"<?=Form::getValue($_POST, $action['step_2'], 'target') == '_blank' ? ' selected="selected"' : ''?>>Een nieuw venster</option>
                        </select>
                    </div>
                    <?php
                }
                ?>
                <div class="onlyinput">
                    <input type="submit" name="previous_step" value="&larr; Vorige" />
                    <input type="submit" name="cancel" value="Annuleer" />
                    <input type="submit" name="next_step" value="Volgende &rarr;" />
                </div>
            </form>
            <?php
        }
        ?>
    </div>
</div>