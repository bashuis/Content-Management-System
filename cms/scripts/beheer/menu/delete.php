<?php

$output->addTitle("Verwijderen");

if (empty($request[2]))
{
    Messager::error('U heeft geen geldig id ingevuld.', false, true);
    redirect('/beheer/menu/list');
}
else
{
    $db->query("SELECT b.`mi_id` AS id,
                       t.`name` AS name,
                       b.`mi_type` AS type,
                       b.`mi_item_id` AS backend
                FROM   `cms_menuitem` b
                JOIN   `cms_menuitem_translation` t
                    ON (b.`mi_id` = t.`mi_id`)
                WHERE  b.`mi_id` = ".intval( $request[2] )."
                AND	   t.`lang_id` = 1
                LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $button = $db->fetch_assoc();
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (isset($_POST['confirm']))
            {
                function deleteButton ($id)
                {
                    global $db;
                    
                    $db->query("SELECT `mi_type` AS type,
                                       `mi_item_id` AS id
                                FROM   `cms_menuitem`
                                WHERE  `mi_id` = ".$id."
                                LIMIT 1");
                    $backend = $db->fetch_assoc();


                    $db->query("DELETE FROM `cms_menuitem`
                                WHERE `mi_id` = " . $id . "
                                LIMIT 1");

                    $db->query("DELETE FROM `cms_menuitem_translation`
                                WHERE `mi_id` = " . $id);

                    $deletePerms = true;

                    switch ($backend['type'])
                    {
                        case 'page':
                            //	Is this the only menu item that is using the page?
                            if (isset($_POST['toTrash']) && $_POST['toTrash'] == 1)
                                $db->query("UPDATE `cms_page`
                                            SET `p_intrash` = 1
                                            WHERE `p_id` = " . $backend['id'] . "
                                            LIMIT 1");

                            //	Pages are trashed, not deleted, so we don't want to remove permissions for them on trash.
                            $deletePerms = false;
                            break;

                        case 'link':
                            $db->query("DELETE FROM `cms_link`
                                        WHERE `l_id` = " . $backend['id'] . "
                                        LIMIT 1");

                            $db->query("DELETE FROM `cms_link_translation`
                                        WHERE `l_id` = " . $backend['id']);
                            break;

                        case 'file':
                            if( isset( $_POST['deletePhysicalFiles'] ) )
                            {
                                $db->query("SELECT DISTINCT `file`
                                            FROM `cms_file_translation`
                                            WHERE `f_id` = " . $backend['id']);
                                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/';
                                while ($file = fetch_assoc($files))
                                {
                                    if (file_exists($uploadDir . $file['file']))
                                        unlink( $uploadDir . $file['file'] );

                                    $db->query("UPDATE `cms_menuitem`
                                                SET    `mi_active` = 0
                                                WHERE  `mi_type` = 'file'
                                                AND    `mi_item_id` IN (SELECT DISTINCT `f_id`
                                                                        FROM `cms_file_translation`
                                                                        WHERE `file` = '" . $file['file'] . "')");

                                    $db->query("UPDATE `cms_file_translation`
                                                SET    `file` = ''
                                                WHERE  `file` = '" . $file['file'] . "'");
                                }
                            }

                            $db->query("DELETE FROM `cms_file`
                                        WHERE `f_id` = " . $backend['id'] . "
                                        LIMIT 1");
                            $db->query("DELETE FROM `cms_file_translation`
                                        WHERE `f_id` = " . $backend['id']);
                            break;

                        case 'iframe':
                                $db->query("DELETE FROM `cms_iframe`
                                            WHERE `ifr_id` = " . $backend['id'] . "
                                            LIMIT 1");
                                $db->query("DELETE FROM `cms_iframe_translation`
                                            WHERE `ifr_id` = " . $backend['id']);
                                break;

                        case 'include':
                                $db->query("DELETE FROM `cms_include`
                                            WHERE `inc_id` = " . $backend['id'] . "
                                            LIMIT 1");
                                $db->query("DELETE FROM `cms_include_translation`
                                            WHERE `inc_id` = " . $backend['id']);
                                break;

                        case 'module':
                                //	We do not delete module instances and related things.
                                $deletePerms = false;
                                break;
                    }

                    if ($deletePerms)
                    {
                        $db->query("DELETE FROM `cms_item_permission`
                                    WHERE `item` = " . $backend['id']."
                                    AND   `type` = '" . $backend['type'] . "'");
                    }

                    //	See if the item had any kinds running around.
                    $db->query("SELECT `mi_id` AS id
                                FROM   `cms_menuitem`
                                WHERE  `mi_parent` = " . $id);

                    while ($kid = $db->fetch_assoc())
                    {
                        deleteButton( $kid['id'] );
                    }
                }

                deleteButton($button['id']);
                Messager::ok('De menu knop &quot;' . $button['name'] . '&quot; is verwijderd. Je word terug gebracht naar het overzicht.', false, true);
            }
            
            redirect('/beheer/menu/list');
        }
        else
        {
            //	We want to make a summary of the menu item, so that the user knows what they are deleting before they do so.
            //	Hopefully, this will prevent lots of "can you restore that for me" requests.
            $db->query("SELECT `name`
                        FROM   `cms_menuitem_translation`
                        WHERE `mi_id` = " . $button['id']);
            $translations = array();

            while($translationResult = $db->fetch_assoc())
            {
                $translations[] = $translationResult['name'];
            }

            $translation = implode(', ', $translations);

            Messager::notify('Weet je zeker dat je de menu knop &quot;' . $button['name'] . '&quot; wilt verwijderen?<br /><strong>Let op:</strong> eventuele onderliggende knoppen worden ook verwijderd!', false);
            ?>
            <form action="" method="post">
                <table>
                <tr>
                    <td>Naam:</td>
                    <td><?php echo $translation; ?></td>
                </tr>
                <tr>
                    <td>Soort menu knop:</td>
                    <td><?php
                    switch ($button['type'])
                    {
                        case 'page';
                            echo 'Pagina op de website.';

                            $db->query("SELECT `name`
                                        FROM   `cms_page_translation`
                                        WHERE  `p_id` = ".$button['backend']."
                                        AND    `lang_id` = 1
                                        LIMIT 1");
                            $page = $db->fetch_assoc();

                            echo ' [ <a href="/beheer/page/edit/' . $button['backend'] . '">' . $page['name'] . '</a> ]';

                            if( $db->num_rows($db->query("SELECT 1 FROM `cms_menuitem` WHERE `mi_type` = 'page' AND `mi_item_id` = " . $button['backend'] . " LIMIT 2;")) == 2)
                            {
                                echo ' (<small>Er zijn meer menu knoppen die deze pagina gebruiken, dus de pagina zal in tact blijven.</small>)';
                            }
                            else
                            {
                                echo ' (<small>Wilt u dat de pagina in de <a href="/beheer/page/trash/">prullenbak</a> word geplaatst? <select name="toTrash"><option value="0">Nee</option><option value="1">Ja</option></select></small>)';
                            }

                            break;

                        case 'link':
                            echo 'Link naar een andere pagina';

                            $db->query("SELECT `url`
                                        FROM   `cms_link_translation`
                                        WHERE  `l_id` = " . $button['backend']);
                            $links = array();

                            while($linkResult = $db->fetch_assoc())
                            {
                                $links[] = $linkResult['url'];
                            }

                            $db->query("SELECT `l_target` AS target
                                        FROM   `cms_link`
                                        WHERE  `l_id` = ".$button['backend']."
                                        LIMIT 1");

                            $link = $db->fetch_assoc();

                            echo ' [ ' . implode(', ',$links) . '  - Word geopend in ';

                            switch ($link['target'])
                            {
                                case '_blank':      echo 'een nieuw venster'; break;
                                case '_self':       echo 'het zelfde venster'; break;
                                case '_parent':     echo 'het bovenliggende venster'; break;
                                case '_top':        echo 'het bovenste venster'; break;
                            }
                            echo ' ]';

                            break;

                        case 'iframe':
                            echo 'iFrame';

                            $db->query("SELECT `url`
                                        FROM   `cms_iframe_translation`
                                        WHERE  `ifr_id` = " . $button['backend']);

                            $sources = array();

                            while($sourceResult = $db->fetch_assoc())
                            {
                                $sources[] = $sourceResult['url'];
                            }

                            $db->query("SELECT `ifr_allowtransparency` AS allowtransparency
                                        FROM   `cms_iframe`
                                        WHERE  `ifr_id` = ".$button['backend']."
                                        LIMIT 1");

                            $iFrame = $db->fetch_assoc();

                            echo ' [ ' . implode(', ',$sources) . '  - Mag ' . ( $iFrame['allowtransparency'] == 1 ? 'wel' : 'niet' ) . ' transparant zijn. ]';

                            break;

                        case 'include':
                            echo 'PHP Include ';

                            $db->query("SELECT `inc_file` AS file
                                        FROM   `cms_include`
                                        WHERE `inc_id` = ".$button['backend']."
                                        LIMIT 1");

                            $include = $db->fetch_assoc();

                            echo ' [ ' . $include['file'] . ' ]';

                            break;

                        case 'file':
                            echo 'Bestand ';

                            $db->query("SELECT DISTINCT `file`
                                        FROM   `cms_file_translation`
                                        WHERE  `f_id` = " . $button['backend']);

                            echo ' [ ';
                            $first = true;
                            while ($file = $db->fetch_assoc())
                            {
                                if($first)
                                {
                                    $first = false;
                                }
                                else
                                {
                                    echo ", ";
                                }

                                echo $file['file'];

                                $db->query("SELECT COUNT(1) sharedCount
                                            FROM   `cms_file_translation`
                                            WHERE  `f_id` != " . $button['backend'] . "
                                            AND    `file` = '" . $file['file'] . "'
                                            AND    `lang_id` = " . $cms_settings['default_language']);

                                $sharedCount = $db->fetch_assoc();
                                if ($sharedCount['sharedCount'] > 0)
                                {
                                    echo "<small>(<a href=\"/beheer/file/show-shared/" . $button['backend'] . "/\" rel=\"moodalbox\">Gedeeld met " . $sharedCount['sharedCount'] . " andere knoppen</a>)</small>";
                                }
                            }
                            
                            echo ' ]';
                            ?>
                            <input type="checkbox" name="deletePhysicalFiles" id="deletePhysicalFiles" /> <label for="deletePhysicalFiles">Bestanden van server verwijderen.</label>
                            <?php
                            break;
                    }
                    ?></td>
                </tr>
                <tr>
                    <td>Onderliggende knoppen:</td>
                    <td><?php
                    $db->query("SELECT t.`name` AS name
                                FROM   `cms_menuitem` m
                                JOIN   `cms_menuitem_translation` t
                                    ON (m.`mi_id` = t.`mi_id`)
                                WHERE  m.`mi_parent` = " . $button['id'] . "
                                AND    t.`lang_id` = 1
                                ORDER BY t.`name`");

                    if ($db->num_rows() == 0)
                    {
                        ?><em>Geen</em><?php
                    }
                    else
                    {
                        $kids = array();
                        while( $kidResult = fetch_assoc( $kidResults ) )
                        {
                            $kids[] = $kidResult['name'];
                        }
                        echo implode(', ', $kids);
                    }
                    ?></td>
                </tr>
                </table>
                <input type="submit" name="confirm" value="Ja, verwijder de menu knop." />
                <input type="submit" name="noconfirm" value="Nee, breng me terug naar het overzicht." />
            </form>
            <?php
        }
    }
    else
    {
        Messager::warning('De menu knop die je wil verwijderen bestaat niet (meer).');
    }
}