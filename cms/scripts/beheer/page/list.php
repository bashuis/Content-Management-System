<?php

$output->addTitle("Overzicht");

if (User::isAdmin())
{
    $pages = $db->query("SELECT p.`p_id` AS id,
                       t.`name` AS name,
                       p.`locked`,
                       p.`locked_by`
                FROM   `cms_page` AS p
                JOIN   `cms_page_translation` AS t
                ON     (p.`p_id` = t.`p_id`)
                WHERE  t.`lang_id` = 1
                AND    p.`p_intrash` = 0
                ORDER BY p.`p_id`");
}
else
{
    $pages = $db->query("SELECT p.`p_id` AS id,
                       t.`name` AS name,
                       p.`locked`,
                       p.`locked_by`
                FROM   `cms_page` AS p
                JOIN   `cms_page_translation` AS t
                    ON (p.`p_id` = t.`p_id`)
                JOIN   `cms_item_edit_permission` AS iep
                    ON (p.`p_id` = iep.`item`)
                WHERE  t.`lang_id` = 1
                AND    p.`p_intrash` = 0
                AND    iep.`type` = 'page'
                AND    iep.`group` IN (" . implode(",", user::getGroups() ) . ")
                ORDER BY p.`p_id`");
}

if ($db->num_rows($pages) > 0)
{
    ?>
    <table id="page-overview" class="overview">
        <tr class="odd">
                <th>Naam</th>
                <th colspan="3">Opties</th>
        </tr>
        <?php
        $classTicker = false;
        while ($page = $db->fetch_assoc($pages))
        {
            ?>
            <tr class="<?php echo ($classTicker = !$classTicker) ? 'even' : 'odd'; ?>">
                <td>
                    <?php echo Generic::stripAndClean( $page['name'] ); ?>
                    
                    <?php
                    	$pid = $page['id'];
	
						$menuItems = $db->query("
							SELECT
									t.`name`,
									mi.`mi_parent` AS parent
							FROM
									`cms_menuitem` AS mi
							JOIN
									`cms_menuitem_translation` AS t
								ON	mi.`mi_id` = t.`mi_id`
							WHERE
									mi.`mi_type` = 'page'
								AND	mi.`mi_item_id` = " . $pid . "
								AND	t.`lang_id` = 1	
							;
						");
						
						if( $db->num_rows( $menuItems ) == 0 )
						{
							?><img onclick="alert('Deze pagina is NIET gekoppeld aan een menuknop.\n\nVerwijderen van deze pagina heeft dus geen gevolgen voor de website.');" style="cursor:pointer;" align="right" src="/icons/fugues/icons/chain-unchain.png" title="Niet gekoppeld aan een menuknop"/><?php
						}
						else{ ?><img onclick="alert('Deze pagina is gekoppeld aan een of meerdere menuknoppen.')" style="cursor:pointer;" align="right" src="/icons/fugues/icons/chain.png" title="Gekoppeld aan een of meerdere menuknoppen" /><?php }
					?>
                    
                    
                </td>

                <?php
                if (User::isAdmin()|| (is_null($page['locked']) && is_null($page['locked_by']) || $page['locked_by'] == User::id() || (time() - $page['locked']) > 900))
                {
                    ?>
                    <td width="80px;" class="last">
                        <a href="/beheer/page/edit/<?php echo $page['id']; ?>/" title="Bewerk deze pagina"><img src="/icons/fugues/icons/pencil.png" alt="Potlood" /></a>
                        <a href="/beheer/page/edit/<?php echo $page['id']; ?>/" title="Bewerk deze pagina">Bewerken</a>
                    </td>

                    <td width="90px;" class="last">
                        <?php if( user::isAdmin() ): ?>
                        <a href="/beheer/page/delete/<?php echo $page['id']; ?>/" title="Verwijder deze pagina"><img src="/icons/fugues/icons/cross.png" alt="Verwijder teken" /></a>
                        <a href="/beheer/page/delete/<?php echo $page['id']; ?>/" title="Verwijder deze pagina">Verwijderen</a>
                    </td>

                    <td>
                        <a href="/beheer/page/permissions/<?php echo $page['id']; ?>/" title="Stel de toegangs opties voor deze pagina in"><img src="/icons/fugues/icons/key.png" alt="Sleutel" /></a>
                        <a href="/beheer/page/permissions/<?php echo $page['id']; ?>/" title="Stel de toegangs opties voor deze pagina in">Toegang</a>
                        <?php endif; ?>
                    </td>
                    <?php
                }
                else if (!User::isAdmin())
                {
                    ?>
                    <td>
                        <img src="/icons/fugues/icons/lock.png" alt="Potlood" title="Deze pagina is gelockt door <?=User::fullName($page['locked_by'])?>" /> Gelockt
                    </td>
                    <?php
                }
                ?>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}
else
{
    Messager::error('Deze website bevat momenteel geen pagina\'s.');
}