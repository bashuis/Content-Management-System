<?php

$output->addTitle("Pagina's");

ob_start();
?>
    <li<?php doActive('list',1); ?><?php doActive('start',1); ?>>
       <a href="/beheer/page/list/"><img style="vertical-align: bottom;" src="/icons/fugues/icons/application-blue.png" alt="Overzicht icon" /> Overzicht weergeven</a>
    </li>
    <li<?php doActive('new',1); ?>>
        <a href="/beheer/page/new/"><img style="vertical-align: bottom;" src="/icons/fugues/icons/plus-circle.png" alt="Nieuwe pagina icon" /> Nieuwe pagina</a>
    </li>
    <li<?php doActive('trash',1); ?>>
        <a href="/beheer/page/trash/"><img style="vertical-align: bottom;" src="/icons/fugues/icons/bin.png" alt="Prullenbak icon" /> Prullenbak</a>
    </li>
<?php
$quickMenuContent = ob_get_clean();
QuickMenu::add($quickMenuContent);
?>