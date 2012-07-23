<?php

function listFlags($selected = 'none')
{
    ?>
    <table>
        <tr>
            <?php
            $flags = explode("\n", file_get_contents( 'http://' . $_SERVER['HTTP_HOST'] . '/icons/flags/list.php' ) );

            $td = 0;
            $rows = 8;

            foreach ($flags AS $vlag)
            {
                $vId = $vlag;
                $vName = substr($vId, 0, -4);
                if (strlen($vName) < 4)
                {
                    $vName = strtoupper($vName);
                }
                else
                {
                    $vName = ucfirst($vName);
                }
                ?>
                    <td>
                        <input type="radio" name="flag" id="<?php echo $vId; ?>" value="<?php echo $vId; ?>" <?php if($vId == $selected) { echo ' checked="checked" checked'; } ?> />
                        <label for="<?php echo $vId; ?>"><img src="/icons/flags/<?php echo $vlag; ?>" align="<?php echo $vName; ?>" /> <?php echo $vName; ?></label>
                    </td>
                <?php
                $td++;
                if ($td == $rows)
                {
                    ?>
                    </tr>
                    <tr>
                    <?php
                    $td = 0;
                }
            }

            for ($fixUp = 0; $td > $rows; $td++)
            {
                echo '<td></td>';
            }
            ?>
        </tr>
    </table>
    <?php
}

function listLocales ($selected = 'none')
{
    $locales = scandir("/usr/lib/locale");
    $locales = array_diff($locales, array('.', '..'));
    ?>
    <select name="locale">
        <?php
        foreach ($locales AS $locale)
        {
            ?>
            <option value="<?php echo $locale; ?>"<?php if( $locale == $selected ): echo ' selected="selected"'; endif; ?>><?php echo $locale; ?></option>
            <?php
        }
        ?>
    </select>
    <?php
}

function showLangForm ($action, $input = array())
{
    if (empty($input['name']))
        $input['name'] = '';

    if(empty( $input['se_name']))
        $input['se_name'] = '';

    if(empty( $input['tag'] ) )
        $input['tag'] = '';

    if(empty( $input['flag'] ) )
        $input['flag'] = 'none';

    if(empty( $input['locale'] ) )
        $input['locale'] = 'nl_NL.ISO8859-15';
    ?>

    <form action="?<?php echo $action; ?>" method="post">
        <div class="normalrow">
            <label class="required">Naam:</label>
            <input type="text" name="name" value="<?php echo $input['name']; ?>" />
            <small>De naam van de taal. Deze word alleen in het beheerders paneel gebruikt.</small>
        </div>
        <div class="normalrow">
            <label class="required">Naam:</label>
            <input type="text" name="se_name" value="<?php echo $input['se_name']; ?>" />
            <small>De naam van de taal in het Engels. Dit word gebruikt door zoekmachines om de taal van de website te bepalen.</small>
        </div>
        <?php if( $action == 'new' ) : ?>
            <div class="normalrow">
                <label class="required">Tag:</label>
                <input type="text" name="tag" maxlength="2" value="<?php echo $input['tag']; ?>" /><br />
                <small>Dit is hoe de taal in de URL word weergegeven. Gebruikelijk is om hiervoor de landcode te nemen (bijv. nl voor Nederland, en voor Engeland, de voor Duitsland, etc.). De tag <strong>moet</strong> uniek zijn.</small>
            </div>
        <?php endif; ?>
        <div class="normalrow">
            <label class="required">Locale:</label>
            <?php listLocales( $input['locale'] ); ?>
        </div>
        <div class="onlytext">
            <label class="required">Vlag:</label>
        </div>
        <?php
            listFlags( $input['flag'] );
        ?>
        <input type="submit" value="Opslaan" />
    </form>
    <?php
}


if (isset($_GET['edit']))
{
    $db->query("SELECT `lang_name` AS name,
                       `lang_searchengine_name` AS se_name,
		       `lang_flag` AS flag,
		       `lang_locale` AS locale
		FROM   `cms_language`
		WHERE  `lang_id` = " . intval( $_GET['edit'] ) . "
		LIMIT 1");
    $lang = $db->fetch_assoc();
	
	
    $lang['flag'] = substr($lang['flag'], strlen('/icons/flags/'));

    ?>
    <h2>Taal Aanpassen:</h2>
    <?php

    showLangForm('doedit=' . intval( $_GET['edit'] ), $lang );
}
else if (isset($_GET['translate']))
{
    $db->query("SELECT `lang_name`,
                       `lang_flag`,
                       `lang_tag` AS tag
                FROM   `cms_language`
                WHERE  `lang_id` = " . intval( $_GET['translate'] ) . "
                LIMIT 1");
	
    if ($db->num_rows() == 1)
    {
        $lang = $db->fetch_assoc();
		
        $langFile = $lang['tag'] . '.php';
        $langCustomFile = $lang['tag'] . '.custom.php';
		
        if (sizeof($_POST) > 1)
        {
            unset($_POST['submit']);

            $fileContents = '<?php\n$cmsCurrentLanguage = array();\n';
            foreach ($_POST as $customKey => $customValue)
            {
                if (!empty( $customValue))
                {
                    $fileContents .= '\n$cmsCurrentLanguage[\'' . $customKey . '\'] = \'' . $customValue . '\';';
                }
            }
            $fileContents .= '\n\n?>';

            $fileContents = str_replace('\n', "\n", $fileContents);

            file_put_contents(SYSTEMPATH . 'lang/' . $langCustomFile, $fileContents);
        }
		
        function loadLangForEditting($file)
        {
            $fullFile = SYSTEMPATH . 'lang/' . $file;
            if (file_exists($fullFile))
            {
                require $fullFile;
                return $cmsCurrentLanguage;
            }
            else
            {
                return array();
            }
        }
		
        $langDefaults = loadLangForEditting( $langFile );
        $langCustoms = loadLangForEditting( $langCustomFile );
        ?>

        <form action="?translate=<?php echo intval( $_GET['translate'] ); ?>" method="post">
            <table>
                <tr>
                    <th>Code:</th>
                    <th>Vertaling:</th>
                    <th>Standaard:</th>
                </tr>
                <?php foreach ($langDefaults AS $defaultKey => $defaultValue): ?>
                    <tr>
                        <td><?php echo $defaultKey; ?></td>
                        <?php if (strlen($defaultValue) > 250): ?>
                        <td><textarea cols="50" rows="5" name="<?php echo $defaultKey; ?>" value="<?php if(isset($langCustoms[$defaultKey])): echo $langCustoms[$defaultKey]; endif; ?>"></textarea></td>
                        <?php else: ?>
                        <td><input size="53" type="text" name="<?php echo $defaultKey; ?>" value="<?php if(isset($langCustoms[$defaultKey])): echo $langCustoms[$defaultKey]; endif; ?>" /></td>
                        <?php endif; ?>
                        <td><?php echo htmlentities($defaultValue); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <input type="submit" name="submit" value="Opslaan" />
        </form>
        <?php
    }
    else
    {
        Messager::warn("Onbekende taal!");
    }
}
else
{
    if(isset($_GET['doedit']))
    {
        $db->prepare("UPDATE `cms_language`
                      SET    `lang_name` = :name,
                             `lang_searchengine_name` = :searchengine,
                             `lang_flag` = :flag,
                             `lang_locale` = :locale
                      WHERE `lang_id` = :id
                      LIMIT 1")
           ->bindValue('name', $_POST['name'])
           ->bindValue('searchengine', $_POST['se_name'])
           ->bindValue('flag', '/icons/flags/' . $_POST['flag'])
           ->bindValue('locale', $_POST['locale'])
           ->bindValue('id', $_GET['doedit'])
           ->execute();
        Messager::notify("De taal is aangepast.");
    }
	
    if (isset($_GET['delete']))
    {
        $lang = $db->query("SELECT `lang_tag` AS tag
                            FROM   `cms_language`
                            WHERE  `lang_id` = " . intval($_GET['delete']) . "
                            LIMIT 1");
		
        if ($db->num_rows($lang) == 1)
        {
            $lang = $db->fetch_assoc($lang);
			
            // Delete the language itself.
            $db->query("DELETE FROM `cms_language`
                        WHERE `lang_id` = " . intval($_GET['delete']) . "
                        LIMIT 1");
			
            // Delete all translations to this language
            $db->query("DELETE FROM `cms_menuitem_translation`
                        WHERE `lang_id` = " . intval($_GET['delete']));

            query("DELETE FROM `cms_iframe_translation`
                   WHERE `lang_id` = " . intval($_GET['delete']));
            
            query("DELETE FROM `cms_link_translation`
                   WHERE `lang_id` = " . intval($_GET['delete']));

            query("
                    DELETE FROM
                                    `cms_file_translation`
                    WHERE
                                    `lang_id` = " . intval( $_GET['delete'] ) . "
            ");
            query("
                    DELETE FROM
                                    `cms_page_translation`
                    WHERE
                                    `lang_id` = " . intval( $_GET['delete'] ) . "
            ");
			
			$langFile = SYSTEMPATH . 'lang/' . escape_string( $lang['tag'] ) . '.custom.php';
			if( file_exists( $langFile ) )
			{
				unlink( $langFile );
			}
		
			Messager::notify("De taal is verwijderd.");
		}
		else
		{
			Messager::warning("Onbekende taal!");
		}
	}
	
	if( isset( $_GET['new'] ) )
	{
		$defaultLanguage = query("SELECT `lang_tag` AS tag FROM `cms_language` WHERE `lang_id` = ".$cms_settings['default_language']." LIMIT 1;");
		$defaultLanguage = fetch_assoc( $defaultLanguage );
		
		//	Copy the language file.
		//	We copy the contents of the file used by the default language,
		//	but only if the file doesn't exist yet. (They may have previously created it,
		//	or it may be something we shipped with the system.)
		$sourceLangFile = SYSTEMPATH . 'lang/' . $defaultLanguage['tag'] . '.php';
		$destLangFile = SYSTEMPATH . 'lang/' . escape_string( $_POST['tag'] ) . '.php';
		if( ! file_exists( $destLangFile ) )
		{
			copy( $sourceLangFile, $destLangFile );
		}
		
		query("
			INSERT INTO
					`cms_language`
			(
				`lang_tag`,
				`lang_name`,
				`lang_searchengine_name`,
				`lang_flag`,
				`lang_locale`
			)
			VALUES
			(
				'" . escape_string( $_POST['tag'] ) . "',
				'" . escape_string( $_POST['name'] ) . "',
				'" . escape_string( $_POST['se_name'] ) . "',
				'/icons/flags/" . escape_string( $_POST['flag'] ) . "',
				'".escape_string( $_POST['locale'] )."'
			)
		");
		
		$langId = insert_id();
		
		//	Now that the language has been added,
		//	we need to create "translations" for it.
		//	This means, we copy the data from the default lang,
		//	and use it for this lang as well.
		
		$menuTranslations = query("
			SELECT
					`mi_id`,
					`name`
			FROM
					`cms_menuitem_translation`
			WHERE
					`lang_id` = " . $cms_settings['default_language'] . "
		");
		
		while( $menuTranslation = fetch_assoc( $menuTranslations ) )
		{
			query("
				INSERT INTO
						`cms_menuitem_translation`
				SET
						`lang_id` = " . $langId . ",
						`mi_id` = " . $menuTranslation['mi_id'] . ",
						`name` = '" . escape_string( $menuTranslation['name'] ) . "'
			");
		}
		
		$pageTranslations = query("
			SELECT
					`p_id`,
					`name`,
					`text`
			FROM
					`cms_page_translation`
			WHERE
					`lang_id` = " . $cms_settings['default_language'] . "
		");
		
		while( $pageTranslation = fetch_assoc( $pageTranslations ) )
		{
			query("
				INSERT INTO
						`cms_page_translation`
				SET
						`lang_id` = " . $langId . ",
						`p_id` = " . $pageTranslation['p_id'] . ",
						`name` = '" . escape_string( $pageTranslation['name'] ) . "',
						`text` = '<h1>Untranslated page</h1><p>This page has not yet been translated into your current language. If you are an administrator, you may translate it now in the control panel.</p>';
			");
		}
		
		$linkTranslations = query("
			SELECT
					`l_id`,
					`url`
			FROM
					`cms_link_translation`
			WHERE
					`lang_id` = " . $cms_settings['default_language'] . "
		");
		
		while( $linkTranslation = fetch_assoc( $linkTranslations ) )
		{
			query("
				INSERT INTO
						`cms_link_translation`
				SET
						`lang_id` = " . $langId . ",
						`l_id` = " . $linkTranslation['l_id'] . ",
						`url` = '" . escape_string( $linkTranslation['url'] ) . "'
			");
		}
		
		$fileTranslations = query("
			SELECT
					`f_id`,
					`file`
			FROM
					`cms_file_translation`
			WHERE
					`lang_id` = " . $cms_settings['default_language'] . "
		");
		
		while( $fileTranslation = fetch_assoc( $fileTranslations ) )
		{
			query("
				INSERT INTO
						`cms_file_translation`
				SET
						`lang_id` = " . $langId . ",
						`f_id` = " . $fileTranslation['f_id'] . ",
						`file` = '" . escape_string( $fileTranslation['file'] ) . "'
			");
		}
		
		$includeTranslations = query("
			SELECT
					`inc_id`,
					`name`
			FROM
					`cms_include_translation`
			WHERE
					`lang_id` = " . $cms_settings['default_language'] . "
		");
		
		while( $includeTranslation = fetch_assoc( $includeTranslations ) )
		{
			query("
				INSERT INTO
						`cms_include_translation`
				SET
						`lang_id` = " . $langId . ",
						`inc_id` = " . $includeTranslation['inc_id'] . ",
						`name` = '" . escape_string( $includeTranslation['name'] ) . "',
			");
		}
		
		$iframeTranslations = query("
			SELECT
					`ifr_id`,
					`name`,
					`url`
			FROM
					`cms_iframe_translation`
			WHERE
					`lang_id` = " . $cms_settings['default_language'] . "
		");
		
		while( $iframeTranslation = fetch_assoc( $iframeTranslations ) )
		{
			query("
				INSERT INTO
						`cms_iframe_translation`
				SET
						`lang_id` = " . $langId . ",
						`ifr_id` = " . $iframeTranslation['ifr_id'] . ",
						`name` = '" . escape_string( $iframeTranslation['name'] ) . "',
						`url` = '" . escape_string( $iframeTranslation['url'] ) . "'
			");
		}
		
		Messager::notify("De taal is toegevoegd.");
	}
	
	?>
<h2>Huidige talen:</h2>	
<table>
	<tr>
		<th>Vlag:</th>
		<th>Naam:</th>
		<th colspan="3">Opties:</th>
	</tr>
	<?php
	$installedLanguages = $db->query("
		SELECT
				`lang_id` AS id,
				`lang_flag` AS flag,
				`lang_name` AS name
		FROM
				`cms_language`
		ORDER BY
				`lang_name`
	");
	if( $db->num_rows( $installedLanguages ) == 0 )
	{
		?>
	<tr>
		<td colspan="3" class="error">Er zijn geen talen geinstalleerd.</td>
	</tr>	
		<?php
	}
	else
	{
		$toggle = true;
		while( $language = $db->fetch_assoc( $installedLanguages ) )
		{
			?>
	<tr class="<?php echo $toggle ? 'even' : 'odd'; $toggle = !$toggle;?>">
		<td><img src="<?php echo $language['flag']; ?>" /></td>
		<td><?php echo Generic::stripAndClean( $language['name'] ); ?></td>
		<td width="80px;" class="last">
			<a href="?edit=<?php echo $language['id']; ?>"><img src="/icons/fugues/icons/pencil.png" alt="Bewerken" style="vertical-align: bottom;" /></a>
			<a href="?edit=<?php echo $language['id']; ?>">Bewerken</a>
		</td>
        <td width="90px;" class="last">
			<a href="?delete=<?php echo $language['id']; ?>" onclick="return confirm('Weet u zeker dat u deze taal wil verwijderen? Let op, dit kan niet ongedaan worden gemaakt en ook alle vertalingen zullen verloren gaan!');"><img src="/icons/fugues/icons/cross.png" alt="Verwijderen" style="vertical-align: bottom;" /></a>
			<a href="?delete=<?php echo $language['id']; ?>" onclick="return confirm('Weet u zeker dat u deze taal wil verwijderen? Let op, dit kan niet ongedaan worden gemaakt en ook alle vertalingen zullen verloren gaan!');">Verwijderen</a>
		</td>
        <td>
			<a href="?translate=<?php echo $language['id']; ?>"><img src="/icons/fugues/icons/locale.png" alt="Vertalen" style="vertical-align: bottom;" /></a>
			<a href="?translate=<?php echo $language['id']; ?>">Vertalen</a>
		</td>
	</tr>		
			<?php
		}
	}
	?>
</table>
<h2>Taal Toevoegen:</h2>
<?php
    showLangForm('new');
}
?>