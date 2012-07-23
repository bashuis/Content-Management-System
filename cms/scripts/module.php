<?php
    $db->query("SELECT `m_id` AS id,
                       `m_path` AS path,
                       `m_supports_multiple_instances` AS multi_instance
                FROM   `cms_modules`
                WHERE  `m_id` = " . $requestedPage['id'] . "
                LIMIT 1");

    $module = $db->fetch_assoc();

    if ($module['multi_instance'] == 1 && $requestedPage['mod_instance'] == 0)
    {
        Messager::error('Deze module bestaat uit meerdere instanties. / This module contains more then one instance.', false, true);
        redirect('/');
    }

    try
    {
        Lang::loadTranslationsForModule($module['path'], LANGKEY);
    }
    catch(Exception $exception)
    {
        Lang::loadTranslationsForModule($module['path'], 'nl');
    }

    ob_start();
    Messager::getMessages();
    $msgHTML = ob_get_clean();

    ob_start();

    require MODULES . $module['path'] . '/frontend.php';

    $moduleOutput = ob_get_clean();
    $output->setContent($moduleOutput, $msgHTML);
?>