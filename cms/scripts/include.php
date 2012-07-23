<?php

$include = $db->query("SELECT `inc_file` as file
                       FROM   `cms_include`
                       WHERE  `inc_id` = " . intval($requestedPage['id']) . "
                       LIMIT 1");

if ($db->num_rows($include) == 1)
{
    ob_start();
    Messager::getMessages();
    $msgHTML = ob_get_clean();

    $include = $db->fetch_assoc($include);

    ob_start();
    require_once $include['file'];
    $includeOutput = ob_get_clean();
    
    $output->setContent($includeOutput, $msgHTML);
}
else
{
    $output->showError('include_not_found');
}