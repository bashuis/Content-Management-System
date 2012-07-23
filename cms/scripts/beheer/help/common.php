<?php

$output->addTitle("Hulp & Ondersteuning");

ob_start();
?>
        <li<?php doActive('contact',1); doActive('start',1); ?>>
            <a href="/beheer/help/contact/">Contactgegevens</a>
        </li>
        <li<?php doActive('handleiding',1); ?>>
            <a href="/beheer/help/handleiding/">Handleiding</a>
        </li>

<?php
$quickMenuContent = ob_get_clean();
QuickMenu::add($quickMenuContent);

?>