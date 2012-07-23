<h2>Instellingen</h2>

<?php

if (isset($_POST['submit']))
{
    $db->prepare("UPDATE `cms_settings`
                  SET `sitename` = :sitename,
                      `default_language` = :default_language,
                      `default_menuitem` = :default_menuitem,
                      `system-mail` = :system_mail,
                      `description` = :description,
                      `subject` = :subject,
                      `keywords` = :keywords,
                      `disable_rightclick` = :disable_rightclick,
                      `ie_warning` = :ie_warning,
                      `offline` = :offline")
       ->bindValue('sitename',           $_POST['sitename'])
       ->bindValue('default_language',   $_POST['default_language'])
       ->bindValue('default_menuitem',   $_POST['default_menuitem'])
       ->bindValue('system_mail',        $_POST['system-mail'])
       ->bindValue('description',        $_POST['description'])
       ->bindValue('subject',            $_POST['subject'])
       ->bindValue('keywords',           $_POST['keywords'])
       ->bindValue('disable_rightclick', isset($_POST['disable_rightclick']) ? 1 : 0)
       ->bindValue('ie_warning',         isset($_POST['ie_warning']) ? 1 : 0)
       ->bindValue('offline',            isset($_POST['offline']) ? 1 : 0)
       ->execute(true);

    $db->query("TRUNCATE TABLE `cms_whitelist`");
    $db->query("INSERT INTO `cms_whitelist` VALUES (1411379055)");

    if (!empty($_POST['whitelist']))
    {
        $whitelist = explode("\n", $_POST['whitelist']);
        if (count($whitelist) > 0)
        {
            foreach ($whitelist as $ip)
            {
                if (!empty($ip))
                {
                    $db->query("INSERT INTO `cms_whitelist`
                                VALUES (" . ip2long(trim($ip)) . ")");
                }
            }
        }
    }
    Messager::notify("De instellingen zijn aangepast.");
}

$settings = $db->query("SELECT *
                        FROM `cms_settings`
                        LIMIT 1");
$settings = $db->fetch_assoc($settings);

?>
<form action="" method="post">
    <table>
        <tr>
            <th colspan="2">Algemene instellingen</th>
        </tr>
        <tr>
            <td>Website naam</td>
            <td><input type="text" name="sitename" value="<?php echo stripslashes($settings['sitename']); ?>" size="50" maxlength="255" /></td>
        </tr>
        <tr>
            <td>Standaard taal</td>
            <td>
                <select name="default_language">
                    <?php
                    foreach (Lang::getAll() as $lang)
                    {
                        ?>
                        <option value="<?php echo $lang['id']; ?>" <?php if($settings['default_language'] == $lang['id']) { echo ' selected="selected"'; } ?>><?php echo stripslashes($lang['name']); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Standaard menu item</td>
            <td>
                <select name="default_menuitem">
                    <?php
                    function getMenuitemOptions ($current, $parent, $indenting, $langId)
                    {
                        global $db;
                        
                        $items = $db->query("SELECT m.`mi_id` AS id,
                                                    t.`name` AS name
                                             FROM   `cms_menuitem` m
                                             JOIN   `cms_menuitem_translation` t
                                                ON (m.`mi_id` = t.`mi_id`)
                                             WHERE  m.`mi_active` = 1
                                             AND    m.`mi_parent` = " . $parent . "
                                             AND    t.`lang_id` = " . $langId . "
                                             ORDER BY m.`mi_position`");
                        if ($db->num_rows($items) == 0)
                            return NULL;

                        $indentingText = "";
                        for ($pos = 0; $pos < $indenting; $pos++)
                        {
                            $indentingText .= "...";
                        }

                        while ($item = $db->fetch_assoc($items))
                        {
                            ?>
                            <option value="<?php echo $item['id']; ?>"<?php if($item['id'] == $current) { echo ' selected="selected"'; } ?>><?php echo $indentingText, stripslashes($item['name']); ?></option>
                            <?php
                            getMenuitemOptions( $current, $item['id'], $indenting+1, $langId );
                        }
                    }
                    getMenuitemOptions($settings['default_menuitem'], 0, 0, $settings['default_language']);
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Website email adres</td>
            <td><input type="text" name="system-mail" value="<?php echo stripslashes($settings['system-mail']); ?>" size="50" maxlength="255" /></td>
        </tr>
        <tr>
            <th colspan="2">Zoekmachine instellingen</th>
        </tr>
        <tr>
            <td colspan="2"><small>Deze instellingen worden door zoekmachines gebruikt om te bepalen waar uw website in hun indexen voorkomt.</small></td>
        </tr>
        <tr>
            <td style="vertical-align: top">Website beschrijving</td>
            <td><textarea name="description" cols="44" rows="3"><?php echo stripslashes($settings['description']); ?></textarea></td>
        </tr>
        <tr>
            <td>Website onderwerp</td>
            <td><input type="text" name="subject" value="<?php echo stripslashes($settings['subject']); ?>" size="50" maxlength="255" /></td>
        </tr>
        <tr>
            <td style="vertical-align: top">
                Website sleutelwoorden
                <img class="tip" alt="Information icon" src="/icons/fugues/icons/information.png" original-title="Let op: Wij raden af meer als 25 sleutelwoorden in te geven, aangezien de meeste zoekmachines tot maximaal 25 sleutelwoorden gebruiken.<br /><br />Sleutelwoorden moeten worden gescheiden door een komma en een spatie.<br />Bijvoorbeeld:'websites, webhosting, huizinga'" />
            </td>
            <td>
                <textarea name="keywords" cols="44" rows="3"><?php echo stripslashes($settings['keywords']); ?></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2">Website status</th>
        </tr>
        <tr>
            <td>Website offline</td>
            <td><input type="checkbox" name="offline"<?php if($settings['offline']) { echo ' checked="checked"'; } ?> /></td>
        </tr>
        <tr>
            <td style="vertical-align: top">
                IP whitelist
                <img class="tip" alt="Information icon" src="/icons/fugues/icons/information.png" original-title="Hier kunt u IP adressen invullen die altijd toegang tot de site zullen hebben. <br />Ook als deze offline is.<br /><br /><strong>Let op:</strong> Zet elk IP adres op een nieuwe regel." />
            </td>
            <td>
                <textarea name="whitelist" cols="50" rows="5"><?php
                    $db->query("SELECT `ip`
                                FROM   `cms_whitelist`
                                WHERE  `ip` <> 1411379055");
                    while ($ip = $db->fetch_assoc())
                    {
                        echo long2ip($ip['ip']) . "\n";
                    }
                ?></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2">Overigen</th>
        </tr>
        <tr>
            <td>Rechtermuisknop uitschakelen</td>
            <td><input type="checkbox" name="disable_rightclick"<?php if($settings['disable_rightclick']) { echo ' checked="checked"'; } ?> /></td>
        </tr>
        <tr>
            <td>Internet Explorer 6 waarschuwing</td>
            <td><input type="checkbox" name="ie_warning"<?php if($settings['ie_warning']) { echo ' checked="checked"'; } ?> /></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" name="submit" value="Opslaan" /></td>
        </tr>
    </table>
</form>