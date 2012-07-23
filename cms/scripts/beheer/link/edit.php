<?php

$output->addTitle("Menu");
$output->addTitle("Link aanpassen");

if (isset($request[2]) && is_numeric($request[2]))
{
    $id = intval($request[2]);

    $db->query("SELECT `l_target` AS target
		FROM   `cms_link`
		WHERE  `l_id` = " . $id . "
		LIMIT 1");
    if ($db->num_rows() == 1)
    {
        $link = $db->fetch_assoc();

	if (isset($_POST['submit']))
	{
            $useForAll = isset($_POST['useFirstForAll']) ? true : false;

            if ($useForAll)
            {
                if (isset($_POST['firstLink']) && !empty($_POST['firstLink']))
                {
                    if (!isset($_POST['url_' . $_POST['firstLink']]) || empty($_POST['url_' . $_POST['firstLink']]))
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
                foreach (Lang::getAll() as $language)
                {
                    if (!isset($_POST['url_' . $language['id']]) || empty($_POST['url_' . $language['id']]))
                    {
                        $errors[] = 'U heeft geen url ingevoerd voor de taal <strong>' . $language['name'] . '</strong>.';
                    }
                }
            }

            if (isset($errors) && count($errors) > 0)
            {
                foreach ($errors as $error)
                {
                    Messager::error($error);
                }
            }
            else
            {
		$db->prepare("UPDATE `cms_link`
                             SET     `l_target` = :target
                             WHERE   `l_id` = :id
			     LIMIT 1")
                   ->bindValue('target', $_POST['target'])
                   ->bindValue('id',     $id)
                   ->execute();
                
		foreach (Lang::getAll() as $language)
		{
                    if (isset($_POST['useFirstForAll']))
                    {
                        $theUrl = $_POST['url_1'];
                    }
                    else
                    {
                        $theUrl = $_POST['url_' . $language['id']];
                    }

                    $db->prepare("UPDATE `cms_link_translation`
                                  SET `url` = :url
                                  WHERE `l_id`	= :id
                                  AND	`lang_id` = :lang
                                  LIMIT 1")
                       ->bindValue('url',  $theUrl)
                       ->bindValue('id',   $id)
                       ->bindValue('lang', $language['id'])
                       ->execute();
		}

                Messager::ok('De link is succesvol opgeslagen.', false, true);
                redirect('/beheer/menu/list');
            }
	}
	
        $db->query("SELECT `lang_id` AS lang,
                           `url`
                    FROM   `cms_link_translation`
                    WHERE  `l_id` = " . $id . "
                    ORDER BY `lang_id`");

        $translations = array();
        while ($translationResult = $db->fetch_assoc())
        {
            $translations[$translationResult['lang']] = $translationResult['url'];
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

        <form action="" method="post">
            <div class="form">
		<?php
                $first = true;
		foreach (Lang::getAll() as $language)
		{
                    ?>
                    <div class="normalrow<?=!$first ? ' langHide': ''?>">
                        <label class="required" for="url_<?php echo $language['id']; ?>"><img src="<?php echo $language['flag']; ?>" /> <?php echo $language['name']; ?></label>
                        <input name="url_<?php echo $language['id']; ?>" type="text" value="<?php echo (isset($translations[$language['id']]) ? $translations[$language['id']] : '' ); ?>" />
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
                    <label class="required" for="target">Openen in:</label>
                    <select name="target" id="target">
                        <?php
                        foreach (array('_self'=>'Het zelfde venster','_blank'=>'Een nieuw venster') as $code => $name)
                        {
                            ?>
                            <option value="<?php echo $code; ?>"<?php if($link['target'] == $code) echo ' selected="selected"'; ?>><?php echo $name; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>

                <div class="onlyinput">
                    <input type="submit" name="submit" value="Bewerk deze link" />
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