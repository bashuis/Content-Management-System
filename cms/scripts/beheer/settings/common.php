<?php

$output->addTitle("Instellingen");

ob_start();
?>
    <li<?php doActive('list',1); ?><?php doActive('start',1); ?>>
        <a href="/beheer/settings/start/">
            <img src="/icons/fugues/icons/wrench-screwdriver.png" alt="Instellingen icon" style="vertical-align: bottom;" />
            Algemene Instellingen
        </a>
    </li>
    <li<?php doActive('lang',1); ?>>
        <a href="/beheer/settings/lang/">
            <img src="/icons/fugues/icons/locale.png" alt="Talen icon" style="vertical-align: bottom;" />
            Talen
        </a>
    </li>
    <li<?php doActive('log',1); ?>>
        <a href="/beheer/settings/log/">
            <img src="/icons/fugues/icons/documents-text.png" alt="Log icon" style="vertical-align: bottom;" />
            Log bekijken
        </a>
    </li>
    <li<?php doActive('fckeditor',1); ?>>
        <a href="/beheer/settings/fckeditor/">
            <img src="/icons/fugues/icons/ui-scroll-pane-image.png" alt="Log icon" style="vertical-align: bottom;" />
            FCKeditor aanpassen
        </a>
    </li>
    <li class="collapsible">
        <a href="#" class="<?php echo $request[1] != 'template' ? 'plus' : 'minus'; ?>">
            <img src="/icons/fugues/icons/layout-design.png" alt="Template icon" style="vertical-align: bottom;" />
            Templates
        </a>
        <ul<?php if ($request[1] != 'template') echo ' class="collapsed"'; ?>>
            <li>
                <a href="/beheer/settings/template/?do=list">
                    <img src="/icons/fugues/icons/application-blue.png" alt="Overzicht icon" style="vertical-align: bottom;" />
                    Overzicht
                </a>
            </li>
            <li>
                <a href="/beheer/settings/template/?do=add">
                    <img src="/icons/fugues/icons/plus-circle.png" alt="Add icon" style="vertical-align: bottom;" />
                    Toevoegen
                </a>
            </li>
        </ul>
    </li>

<?php
$quickMenuContent = ob_get_clean();
QuickMenu::add($quickMenuContent);