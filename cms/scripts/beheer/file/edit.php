<?php

$output->addTitle("Menu");
$output->addTitle("File aanpassen");

if (isset($request[2]) && is_numeric($request[2]))
{
    $id = intval($request[2]);

    $db->query("SELECT `f_target` AS target
		FROM   `cms_file`
		WHERE  `f_id` = " . $id. "
		LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $file = $db->fetch_assoc();

	if (isset($_POST['submit']))
	{
            $db->query("UPDATE `cms_file`
                        SET    `f_target` = '" . $_POST['target'] . "'
                        WHERE  `f_id` = " . $id . "
                        LIMIT 1");

            if (isset($_POST['useFirstForAll']))
            {
                if (!empty($_FILES['upload_' . $_POST['firstFile']]['name']))
                {
                    $fileName = $_FILES['upload_' . $_POST['firstFile']]['name'];
                    move_uploaded_file($_FILES['upload_' . $_POST['firstFile']]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $fileName);
                }
                else
                {
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $_POST['browse_' . $_POST['firstFile'] ]))
                    {
                        $fileName = $_POST['browse_' . $_POST['firstFile']];
                    }
                }
            }

            foreach (Lang::getAll() as $lang)
            {
                if (isset($fileName))
                {
                    $langFileName = $fileName;
                }
                else
                {
                    if (!empty($_FILES['upload_' . $lang['id']]['name']))
                    {
                        $langFileName = $_FILES['upload_' . $lang['id']]['name'];
                        move_uploaded_file($_FILES['upload_' . $lang['id']]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $langFileName);
                    }
                    else
                    {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $_POST['browse_' . $lang['id'] ]))
                        {
                            $langFileName = $_POST['browse_' . $lang['id']];
                        }
                    }
                }

                $db->query("UPDATE `cms_file_translation`
                            SET    `file` = '" . $langFileName . "'
                            WHERE  `f_id` = " . $id . "
                            AND    `lang_id` = " . $lang['id'] . "
                            LIMIT 1");
            }
		
            Messager::ok('De file is succesvol opgeslagen.', false, true);
            redirect('/beheer/menu/list');
	}


        $db->query("SELECT `lang_id` AS lang,
                           `file`
                    FROM   `cms_file_translation`
                    WHERE  `f_id` = " . $id . "
                    ORDER BY `lang_id`");

        $translations = array();
        while ($translationResult = $db->fetch_assoc())
        {
            $translations['browse_' . $translationResult['lang']] = $translationResult['file'];
        }

        ?>

        <form action="" enctype="multipart/form-data" method="post">
            <?php
            $files = scandir($_SERVER['DOCUMENT_ROOT'] . '/upload/');
            foreach ($files as $index => $curFile)
            {
                if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $curFile))
                {
                    unset($files[$index]);
                }
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
            
            <div class="form">
                <div class="normalrow">
                    Kies een bestand:
                </div>

                <?php
                $first = true;
                foreach (Lang::getAll() as $language)
                {
                    ?>
                    <div class="normalrow<?=!$first ? ' langHide': ''?>">
                        <label class="required"><img src="<?=$language['flag']?>" alt="<?=$language['name']?> Icon" /> <?=$language['name']?>:</label>
                        <select class="fileInput" name="browse_<?=$language['id']?>" id="browse_<?=$language['id']?>">
                            <?php
                            foreach ($files as $curFile)
                            {
                                ?>
                                <option value="<?php echo $curFile; ?>"<?php Form::isPrevious($translations, 'browse_' . $language['id'], $curFile); ?>><?php echo $curFile; ?></option>
                                <?php
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
                        <option value="_self"<?php Form::isPrevious($file, 'target', '_self'); ?>>Het zelfde venster</option>
                        <option value="_blank"<?php Form::isPrevious($file, 'target', '_blank'); ?>>Een nieuw venster</option>
                    </select>
                </div>

                <div class="onlyinput">
                    <input type="submit" name="submit" value="Opslaan" />
                </div>
            </div>
        </form>
        <?php
    }
    else
    {
        Messager::error('Deze file bestaat niet (meer).', false, true);
        redirect('/beheer/menu/list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingevuld.', false, true);
    redirect('/beheer/menu/list');
}