<?php

$output->addTitle("Prullenbak");

?>
<h2>Prullenbak</h2>
<p>In de prullenbak bevinden zich pagina&acute;s die zijn verwijderd. Mocht je de pagina terug willen, dan kun je hem hier met &eacute;&eacute;n druk op de knop herstellen. Let op, als je hier een pagina verwijderd is deze <strong>definitief</strong> verwijderd en kan deze niet hersteld worden!</p>
<?php

if (isset($_GET['restore']))
{
    $db->query("UPDATE `cms_page`
                SET `p_intrash` = 0
                WHERE `p_id` = " . intval($_GET['restore']) . "
                LIMIT 1");

    Messager::ok('De pagina is hersteld.');
}

if (isset($_GET['delete']))
{
    $db->query("DELETE FROM `cms_page`
                WHERE `p_id` = " . intval($_GET['delete']) . "
                LIMIT 1");

    $db->query("DELETE FROM `cms_page_translation`
                WHERE `p_id` = " . intval($_GET['delete']));

    $db->query("DELETE FROM `cms_item_permission`
                WHERE `item` = " . intval($_GET['delete']) . "
                AND type = 'page'");

    Messager::ok('De pagina is verwijderd.');
}

$db->query("SELECT p.`p_id` AS id,
		   t.`name` AS name,
		   'page' AS type
            FROM   `cms_page` AS p
            JOIN   `cms_page_translation` AS t
                ON (p.`p_id` = t.`p_id`)
            WHERE t.`lang_id` = 1
            AND   p.`p_intrash` = 1
            ORDER BY p.`p_id`");

if ($db->num_rows() > 0)
{
    ?>
    <table id="page-overview" class="overview">
	<tr>
            <th>Naam</th>
            <th>Opties</th>
	</tr>
	<?php
	$classTicker = false;
	while ($page = $db->fetch_assoc())
	{
            ?>
            <tr>
		<td><?php echo $page['name']; ?></td>
		<td>
                    <a href="./?restore=<?php echo $page['id']; ?>" title="Herstel deze pagina"><img src="/icons/fugues/icons/tick-circle.png" alt="Vinkje" /></a>
                    <a href="./?restore=<?php echo $page['id']; ?>" title="Herstel deze pagina">Herstellen</a>

                    <a href="./?delete=<?php echo $page['id']; ?>" title="Verwijder deze pagina" onClick="return confirm('Weet je zeker dat je deze pagina permanent wilt verwijderen?');"><img src="/icons/fugues/icons/minus-circle.png" alt="Verwijder teken" /></a>
                    <a href="./?delete=<?php echo $page['id']; ?>" title="Verwijder deze pagina" onClick="return confirm('Weet je zeker dat je deze pagina permanent wilt verwijderen?');">Permanent Verwijderen</a>
		</td>
            </tr>
            <?php
	}
	?>
    </table>
    <?php
}
else
{
    Messager::notify('De prullenbak bevat momenteel geen pagina\'s.', false);
}