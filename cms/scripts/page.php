<?php

$db->prepare("SELECT `name`, `text`
              FROM   `cms_page_translation`
              WHERE  `p_id` = :p_id
              AND    `lang_id` = :lang_id
              LIMIT 1")
   ->bindValue('p_id', $requestedPage['id'])
   ->bindValue('lang_id', LANG)
   ->execute();

if ($db->num_rows() == 1 )
{
    $page = $db->fetch_assoc();

    ob_start();
    Messager::getMessages();
    $msgHTML = ob_get_clean();
    
    $output->setContent(stripslashes($page['text']), $msgHTML);
}
else
{
    $output->showError('page_translation_not_found');
}