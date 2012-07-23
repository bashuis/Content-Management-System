<h1>Menu knoppen</h1>
<?php

if (isset($_GET['do']) && $_GET['do'] == 'update')
{
    $result = $_REQUEST['item'];

    $i = 1;
    foreach ($result as $item)
    {
        if (!empty($item))
        {
            echo $item . ' - ' . $i . '<br />';
            $db->prepare("UPDATE `cms_menuitem`
                          SET    `mi_position` = :position
                          WHERE  `mi_id` = :id
                          LIMIT 1")
               ->bindValue('position', $i)
               ->bindValue('id',       $item)
               ->execute();

            $i++;
        }
    }
    exit();
}
else
{
    $classTicker = true;

    function listMenuItems ($parent, $depth)
    {
        global $classTicker, $ticketClass, $db;

        $query = $db->query("SELECT m.`mi_id` AS id,
                                    m.`mi_position` AS position,
                                    m.`mi_parent` AS parent,
                                    m.`mi_active` AS active,
                                    t.`name` AS name,
                                    m.`mi_type` AS type,
                                    m.`mi_item_id` AS backend,
                                    m.`mi_mod_instance` AS mod_instance,
                                    (SELECT COUNT(1)
                                     FROM  `cms_menuitem`
                                     WHERE `mi_parent` = id) AS subs
                             FROM   `cms_menuitem` m
                             JOIN   `cms_menuitem_translation` t
                                 ON (m.`mi_id` = t.`mi_id`)
                             WHERE  m.`mi_parent` = " . $parent . "
                             AND    t.`lang_id` = 1
                             ORDER BY m.`mi_position` ASC");

        if ($db->num_rows($query) > 0)
        {
            if ($db->num_rows($query) > 1)
            {
                $db->query("SELECT MIN(`mi_position`) AS top,
                                   MAX(`mi_position`) AS bottom
                            FROM   `cms_menuitem`
                            WHERE  `mi_parent` = " . $parent);

                $topBottom  = $db->fetch_assoc();
                $top        = $topBottom['top'];
                $bottom     = $topBottom['bottom'];
            }
            else
            {
                $top        = PHP_INT_MAX;
                $bottom     = 0;
            }
            $i = 0;
            while ($item = $db->fetch_assoc($query))
            {
                if ($i == 0)
                {
                    ?>
                    <ul <?php if ($depth == 1) echo 'class="menuList"'; ?> id="menu_<?=$item['id']?>">
                    <?php
                }

                $iid        = $item['id'];

                $db->query("SELECT 1
                            FROM   `cms_menuitem`
                            WHERE  `mi_parent` = " . intval($item['parent']));
                $items = $db->num_rows();
                ?>
                <li id="item_<?=$item['id']?>">
                    <div style="width: 100%; overflow: hidden; height: 20px; border-bottom: 1px solid #CDCDCD; padding: 5px 0;">
                        <!--<div style="float: left; width: <?=(555 - (($depth-1)*15)+10)?>px; margin-top: 3px; margin-left: <?=(($depth-1)*15)+10?>px;">-->
                        <div style="float: left; margin-top: 3px; margin-left: <?=(($depth-1)*15)+10?>px;">
                            <img src="/icons/fugues/icons/arrow-resize-090.png" class="handle" alt="Sleep icon" title="Hiermee kunt u door te slepen de volgorde van de menu knoppen wijzigen." style="vertical-align: middle; cursor: pointer"/>

                            <?php
                            echo Generic::stripAndClean($item['name']);

                            echo '&nbsp;';

                            if ($item['subs'] > 0)
                            {
                                echo '<img src="/icons/fugues/icons/plus-small.png" class="toggle plus"  id="' . $iid . '" style="vertical-align: middle;" />';
                                echo '<img src="/icons/fugues/icons/minus-small.png" class="toggle minus"  id="' . $iid . '" style="vertical-align: middle; display: none" />';
                            }

                            if ($item['active'] == 0): ?>
                                <img src="/icons/fugues/icons/exclamation.png" style="vertical-align: middle;" alt="Niet actief" title="Dit menu item is momenteel niet actief." />
                            <?php endif;

                            $query2 = $db->query("SELECT 1
                                                  FROM  `cms_menuitem`
                                                  WHERE `mi_parent` = " . intval($item['parent']) . "
                                                  AND   `mi_position` = " . intval($item['position']) . "
                                                  AND   `mi_id` <> " . intval($item['id']));
                            if ($db->num_rows($query2) > 0)
                            {
                                echo '<a href="#" title="Er is een probleem met de volgorde van dit menu item. Verplaats dit menuitem om het probleem te verhelpen."><img src="/icons/fugues/icons/exclamation-red.png" style="vertical-align: bottom;" /></a>';
                            }
                            ?>
                        </div>
                        <div style="float: right;">
                            <!-- Bewerk -->
                            <div style="width: 75px; float: left; padding: 0 8px;">
                                <a href="/beheer/menu/edit/<?php echo $iid; ?>/" title="Bewerk deze menu knop"><img style="vertical-align: bottom;" src="/icons/fugues/icons/pencil.png" alt="Pencil Icon" /></a>
                                <a href="/beheer/menu/edit/<?php echo $iid; ?>/" title="Bewerk deze menu knop">Bewerk</a>
                            </div>

                            <?php
                            $view = true;

                            if ($item['type'] == 'page')
                            {
                                $db->prepare("SELECT 1
                                              FROM  `cms_page`
                                              WHERE `p_id` = :id
                                              AND   `p_intrash` = 0
                                              LIMIT 1")
                                   ->bindValue('id', $item['backend'])
                                   ->execute();

                                if ($db->num_rows() == 0)
                                    $view = false;
                            }
                            ?>
                            <!-- Bewerk Inhoud-->
                            <div style="width: 120px; float: left; padding: 0 8px;">
                                <?php
                                if ($view)
                                {
                                    ?>
                                    <a href="/beheer/<?=$item['type']?>/edit/<?=$item['backend']?>/<?=!is_null($item['mod_instance']) ? $item['mod_instance'] : ''?>" title="Bewerk inhoud"><img style="vertical-align: bottom;" src="/icons/fugues/icons/document--pencil.png" alt="Pencil Icon" /></a>
                                    <a href="/beheer/<?=$item['type']?>/edit/<?=$item['backend']?>/<?=!is_null($item['mod_instance']) ? $item['mod_instance'] : ''?>" title="Bewerk inhoud">Bewerk inhoud</a>
                                    <?php
                                }
                                else
                                {
                                    echo '&nbsp;';
                                }
                                ?>
                            </div>

                            <!-- Rechten -->
                            <div style="width: 80px; float: left; padding: 0 8px;">
                                <a href="/beheer/<?php echo $item['type']; ?>/permissions/<?php echo $item['backend']; ?>/<?php if( isset( $item['mod_instance'] ) ): echo $item['mod_instance']."/"; endif; ?>" title="Stel de toegangs opties in voor deze menu knop"><img style="vertical-align: bottom;" src="/icons/fugues/icons/key.png" alt="Key Icon" /></a>
                                <a href="/beheer/<?php echo $item['type']; ?>/permissions/<?php echo $item['backend']; ?>/<?php if( isset( $item['mod_instance'] ) ): echo $item['mod_instance']."/"; endif; ?>" title="Stel de toegangs opties in voor deze menu knop">Toegang</a>
                            </div>

                            <!-- Menu knop toevoegen -->
                            <div style="width: 95px; float: left; padding: 0 8px;">
                                <?php
                                if ($depth <= (MENUMAXDEPTH - 1))
                                {
                                    ?>
                                    <a href="/beheer/menu/new/?parent=<?php echo $iid; ?>" title="Voeg een knop toe onder deze knop"><img style="vertical-align: bottom;" src="/icons/fugues/icons/plus-circle.png" alt="A plus icon" /></a>
                                    <a href="/beheer/menu/new/?parent=<?php echo $iid; ?>" title="Voeg een knop toe onder deze knop">Toevoegen</a>
                                    <?php
                                }
                                else
                                {
                                    echo '&nbsp;';
                                }
                                ?>
                            </div>

                            <!-- Verwijderen -->
                            <div style="float: left; padding: 0 8px;">
                                <a href="/beheer/menu/delete/<?php echo $iid; ?>/" title="Verwijder deze menu knop"><img style="vertical-align: bottom;" src="/icons/fugues/icons/cross.png" alt="Delete Icon" /></a>
                                <a href="/beheer/menu/delete/<?php echo $iid; ?>/" title="Verwijder deze menu knop">Verwijder</a>
                            </div>
                        </div>
                    </div>
                    <?php
                    listMenuItems( $iid, ($depth+1) );
                    ?>
                </li>
                <?php
                $i++;
            }
            ?>
            </ul>
            <?php
        }
    }

    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.menuList li > ul').each(function(i) {

                var parent_li = $(this).parent('li');
                var sub_ul = $(this).remove();

                parent_li.find('img.toggle').css('cursor', 'pointer').click(function() {
                    sub_ul.slideToggle();
                    $(this).toggle();
                    if ($(this).hasClass('plus')) {
                        $(this).next('img').toggle();
                    } else {
                        $(this).prev('img').toggle();
                    }
                });
                parent_li.append(sub_ul);

                sub_ul.sortable({
                    items: "> li",
                    handle : '.handle',
                    axi: 'y',
                    opacity: 0.6,
                    update : function () {
                        var order = sub_ul.sortable('serialize');
                        $("#info").load("?do=update&" + order);
                    }
                });
                sub_ul.mousedown( function(e){ return false; } );

                $('.menuList ul').hide();
            });

            $('.menuList').sortable({
                items: "> li",
                handle : '.handle',
                axi: 'y',
                opacity: 0.6,
                update : function () {
                    var order = $('.menuList').sortable('serialize');
                    $("#info").load("?do=update&" + order);
                }
            });

            $('.expand').click(function(event) {
                $('.menuList li > ul').each(function(i) {
                    event.preventDefault();
                    var parent_li = $(this).parent('li');
                    var sub_ul = $(this);

                    sub_ul.show();
                });
                $('img.plus').hide();
                $('img.minus').show();

                $('.expand').hide();
                $('.collapse').fadeIn();
            });

            $('.collapse').click(function(event) {
                $('.menuList li > ul').each(function(i) {
                    event.preventDefault();
                    var parent_li = $(this).parent('li');
                    var sub_ul = $(this);

                    sub_ul.hide();
                });
                $('img.minus').hide();
                $('img.plus').show();

                $('.collapse').hide();
                $('.expand').fadeIn();
            });
        });
    </script>

    <div id="info"></div>


    <a href="#" class="expand"><img src="/icons/fugues/icons/plus.png" style="vertical-align: middle" alt="Plus" /></a>
    <a href="#" class="expand">Klap alles uit</a>

    <a href="" class="collapse" style="display: none"><img src="/icons/fugues/icons/minus.png" style="vertical-align: middle" alt="Plus" /></a>
    <a href="" class="collapse" style="display: none">Klap alles in</a>
    <table style="margin-bottom: 0;">
        <tr>
            <th colspan="7">Naam</th>
        </tr>
    </table>
    <style type="text/css">
        ul.menuList    { list-style-type: none !important; margin: 0 !important; display: block; }
        ul.menuList li { display: block; margin: 0 !important; overflow: hidden; padding: 0 !important; }
        ul.menuList li ul { list-style-type: none !important; margin: 0 !important; }
        ul.menuList li ul li { margin: 0 !important; padding: 0 !important; }
    </style>
    <div id="menuList">
    <?php listMenuItems(0, 1); ?>
    </div>
    <?php
}