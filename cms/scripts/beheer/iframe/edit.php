<?php

$output->addTitle("Menu");
$output->addTitle("iFrame aanpassen");

if (isset($request[2]) && is_numeric($request[2]))
{
    $id = intval($request[2]);

    $db->query("SELECT `ifr_allowtransparency` AS allowtransparency,
		       `ifr_height` AS height,
                       `ifr_width` AS width
		FROM   `cms_iframe`
		WHERE  `ifr_id` = " . $id . "
		LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $iframe = $db->fetch_assoc();

	if (isset($_POST['submit']))
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
                foreach (Lang::getAll() as $language)
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


            if (isset($errors) && count($errors) > 0)
            {
                foreach ($errors as $error)
                {
                    Messager::error($error);
                }
            }
            else
            {

		$db->prepare("UPDATE `cms_iframe`
                              SET `ifr_allowtransparency` = :allowtransparency,
				  `ifr_height` = :height,
				  `ifr_width` = :width
                              WHERE `ifr_id` = :ifr_id
                              LIMIT 1")
                   ->bindValue('allowtransparency', (isset($_POST['allowtransparency']) ? 1 : 0))
                   ->bindValue('height', $_POST['height'])
                   ->bindValue('width', $_POST['width'])
                   ->bindValue('ifr_id', $id)
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

                    $db->prepare("UPDATE `cms_iframe_translation`
                                  SET    `url` = :url
                                  WHERE  `ifr_id` = :ifr_id
                                  AND    `lang_id` = :lang_id
                                  LIMIT 1")
                       ->bindValue('url',     $theUrl)
                       ->bindValue('ifr_id',  $id)
                       ->bindValue('lang_id', $language['id'])
                       ->execute();
		}

                Messager::ok('Het iframe is succesvol opgeslagen.', false, true);
                redirect('/beheer/menu/list');
            }
	}

        $db->query("SELECT `lang_id` AS lang,
                           `url`
                    FROM   `cms_iframe_translation`
                    WHERE  `ifr_id` = " . $id . "
                    ORDER BY `lang_id` ");

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
                        <input name="url_<?php echo $language['id']; ?>" type="text" value="<?php echo ( isset($translations[$language['id']]) ? $translations[$language['id']] : '' ); ?>" />
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
                    <label class="required" for="height">Hoogte:</label>
                    <input name="height" id="height" type="text" value="<?php echo $iframe['height']; ?>" />
                </div>
                <div class="normalrow">
                    <label class="required" for="width">Breedte:</label>
                    <input name="width" id="width" type="text" value="<?php echo $iframe['width']; ?>" />
                </div>
                <div class="normalrow">
                    <label class="required" for="allowtransparency">Mag transparant zijn?</label>
                    <input name="allowtransparency" id="allowtransparency" type="checkbox"<?php if ($iframe['allowtransparency'] == 1) echo ' checked="checked"'; ?> />
                </div>
                <div class="onlyinput">
                    <input type="submit" name="submit" value="Bewerk dit iFrame" />
                </div>
            </div>
        </form>
        <?php
    }
    else
    {
        Messager::error('Dit iframe bestaat niet (meer).', false, true);
        redirect('/beheer/menu/list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingevuld.', false, true);
    redirect('/beheer/menu/list');
}