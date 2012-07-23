<h1>Template overzicht</h1>

<table>
    <tr>
        <th>Titel:</th>
        <th colspan="5">Opties:</th>
    </tr>
    <?php

    $db->query("SELECT `template_id`,
                       `title`,
                       `default`
                FROM   `cms_template`
                ORDER BY `title`");
    while ($template = $db->fetch_assoc())
    {
        ?>
        <tr>
            <td>
                <?=$template['title']?>
                <?php
                if ($template['default'] == 1)
                    echo '<img src="/icons/fugues/icons/star.png" alt="Default template" style="vertical-align: bottom;" title="Standaard template" />';
                ?>
            </td>
            <td class="last" width="120">
                <?php if ($template['default'] == 0)
                {
                    ?>
                    <a href="?do=default&amp;id=<?php echo $template['template_id']; ?>"><img src="/icons/fugues/icons/star.png" style="vertical-align: bottom;" alt="Standaard" /></a>
                    <a href="?do=default&amp;id=<?php echo $template['template_id']; ?>">Maak standaard</a>
                    <?php
                }
                ?>
            </td>
            <td class="last" width="100">
                <a href="?do=edit&amp;id=<?php echo $template['template_id']; ?>"><img src="/icons/fugues/icons/pencil.png" style="vertical-align: bottom;" alt="Bewerk" /></a>
                <a href="?do=edit&amp;id=<?php echo $template['template_id']; ?>">Bewerk titel</a>
            </td>
            <td class="last" width="140">
                <a href="?do=delete&amp;id=<?php echo $template['template_id']; ?>"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijder" /></a>
                <a href="?do=delete&amp;id=<?php echo $template['template_id']; ?>">Verwijder template</a>
            </td>
            <td class="last" width="140">
                <a href="?do=update&amp;id=<?php echo $template['template_id']; ?>"><img src="/icons/fugues/icons/block.png" style="vertical-align: bottom;" alt="Beheren" /></a>
                <a href="?do=update&amp;id=<?php echo $template['template_id']; ?>">Bestanden beheren</a>
            </td>
            <td class="last">
                <a href="?do=minify&amp;id=<?php echo $template['template_id']; ?>"><img src="/icons/fugues/icons/compile.png" style="vertical-align: bottom;" alt="Optimaliseren" /></a>
                <a href="?do=minify&amp;id=<?php echo $template['template_id']; ?>">Bestanden optimaliseren</a>
            </td>
            <td></td>
        </tr>
        <?php
    }
    ?>
</table>