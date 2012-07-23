<?php

$output->addTitle("Menu knoppen");

ob_start();
?>
    <li<?php doActive('list',1); ?><?php doActive('start',1); ?>>
        <a href="/beheer/menu/list/"><img style="vertical-align: bottom;" src="/icons/fugues/icons/application-blue.png" alt="Overzicht icon" /> Overzicht weergeven</a>
    </li>
    <li<?php doActive('new',1); ?>>
        <a href="/beheer/menu/new/"><img style="vertical-align: bottom;" src="/icons/fugues/icons/plus-circle.png" alt="Nieuwe menu knop icon" /> Nieuwe menu knop</a>
    </li>
<?php
$quickMenuContent = ob_get_clean();
QuickMenu::add($quickMenuContent);


//	How deep may menu's go?
define('MENUMAXDEPTH', 4, false);