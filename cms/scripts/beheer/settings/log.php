<h2>Log bekijken</h2>

<?php
$itemsPage = 50;
$searchIP  = "";

if (isset($_GET['searchIP']) && strlen($_GET['searchIP']) >= 7)
{
    $query = $db->query("SELECT 1
                         FROM `log_site`
                         WHERE `ip` = '" . ip2long($_GET['searchIP']) . "'
                         LIMIT 1");
    if ($db->num_rows($query) == 1)
    {
        $searchIP = ip2long($_GET['searchIP']);
    }
    else
    {
        Messager::error('Er zijn geen zoek resultaten gevonden.');
    }
}

$countItems = $db->num_rows($db->query("SELECT 1 FROM `log_site` WHERE `ip` LIKE '%" . $searchIP . "%'"));
$pages = ceil($countItems / $itemsPage);

$currentPage = 1;
if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] >= 1 && $_GET['page'] <= $pages)
{
    $currentPage = intval($_GET['page']);
}

$startItem = ($currentPage - 1) * $itemsPage;

?>
<br />
<table>
    <tr>
        <td colspan="4">
        <?php
            if ($currentPage > 1)
            {
                print '<a href="?searchIP=' . $searchIP . '&page=' . ($currentPage - 1) . '"><< Vorige</a> | ';
            }

            for ($i = 5; $i > 0; $i--)
            {
                if ($currentPage - $i >= 1)
                    print '<a href="?searchIP=' . $searchIP . '&page=' . ($currentPage - $i) . '">' . ($currentPage - $i) . '</a> | ';
            }

            print '<a href="?searchIP=' . $searchIP . '&page=' . $currentPage . '"><strong><u>' . $currentPage . '</u></strong></a> | ';

            for ($i = 1; $i < 6; $i++)
            {
                if (($currentPage + $i) <= $pages)
                    print '<a href="?searchIP=' . $searchIP . '&page=' . ($currentPage + $i) . '">' . ($currentPage + $i) . '</a> | ';
            }

            if ($currentPage < $pages)
            {
                print '<a href="?searchIP=' . $searchIP . '&page=' . ($currentPage + 1) . '">Volgende >></a>';
            }
        ?>
        </td>
    </tr>
    <tr>
        <th width="125">Datum</th>
        <th>Ip</th>
        <th>Url</th>
        <th>Referer</th>
    </tr>
<?php

$query = $db->query("SELECT `date_time`,
                           DATE_FORMAT(`date_time`, '%d-%m-%Y %H:%i') as date,
                           `ip`,
                           `url`,
                           `referer`
                    FROM   `log_site`
                    WHERE  `ip` LIKE '%" . $searchIP . "%'
                    ORDER BY `date_time` DESC
                    LIMIT " . $startItem . ", " . $itemsPage);
while ($log = $db->fetch_assoc($query))
{
    ?>
    <tr>
        <td><?php print $log['date']; ?></td>
        <td>            
            <a href="?searchIP=<?php print long2ip($log['ip']); ?>">
                <?php print long2ip($log['ip']); ?>
            </a>
        </td>
        <td><a href="<?php print $log['url']; ?>" target="_blank"><?php print $log['url']; ?></a></td>
        <td><?php print $log['referer']; ?></td>
    </tr>
    <?php
}
?>
</table>
<br />
<form action="" method="get">
    <input type="text" name="searchIP" value="Zoek op IP" onclick="this.value = ''" />
    <input type="submit" value="Zoek" />
</form>
