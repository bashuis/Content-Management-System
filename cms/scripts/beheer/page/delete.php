<?php

$output->addTitle("Verwijderen");

if (empty($request[2]))
{
    Messager::warning('Je moet wel een pagina kiezen om te verwijderen he!');
}
else
{
    $db->query("SELECT t.`name`
		FROM   `cms_page` p
		JOIN   `cms_page_translation` t
                    ON ( p.`p_id` = t.`p_id`)
		WHERE  p.`p_id` = ".intval($request[2])."
                AND    t.`lang_id` = 1
                AND    p.`p_intrash` = 0
		LIMIT 1");
	
    if ($db->num_rows() == 1)
    {
        $page = $db->fetch_assoc();

        if (isset($_POST['confirm']))
        {
            $db->query("UPDATE `cms_page`
                        SET    `p_intrash` = 1
                        WHERE  `p_id` = ".intval($request[2])."
                        LIMIT 1");

            Messager::ok('De pagina &quot;' . $page['name'] . '&quot; is verwijderd.', false);

            $db->query("SELECT mi.`mi_id` AS id,
                               t.`name`
                        FROM   `cms_menuitem` AS mi
                        JOIN   `cms_menuitem_translation` AS t
                            ON (mi.`mi_id` = t.`mi_id`)
                        WHERE  mi.`mi_type` = 'page'
                        AND	   mi.`mi_item_id` = " . intval($request[2]) . "
                        AND	   t.`lang_id` = 1");

            if ($db->num_rows() > 0)
            {                
                $menuItems = array();
                while ($menuItem = $db->fetch_assoc())
                {
                    $menuItems[] = $menuItem['name'];
                    $db->query("UPDATE `cms_menuitem`
                                SET    `mi_active` = 0
                                WHERE  `mi_id` = " . $menuItem['id'] . "
                                LIMIT 1");
                }

                Messager::warning('Let op, de volgende menu items verwezen naar deze pagina en zijn nu op non-actief gezet: ' . implode(', ', $menuItems), false, true);
            }

            redirect('/beheer/page/list');
        }
        else if (isset($_POST['noconfirm']))
        {
            redirect('/beheer/page/list');
        }
        else
        {
            Messager::notify('Weet je zeker dat je de pagina &quot;' . $page['name']. '&quot; wilt verwijderen?', false);
            ?>
            <form action="./" method="post">
                <input type="submit" name="confirm" value="Ja, verwijder de pagina." />
                <input type="submit" name="noconfirm" value="Nee, breng me terug naar het pagina overzicht." />
            </form>
            <?php
        }
    }
    else
    {
        Messager::warning('De pagina die je wil verwijderen bestaat niet (meer).');
    }
}