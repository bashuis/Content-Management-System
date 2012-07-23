<?php

$db->query("SELECT i.`ifr_height` AS height,
                   i.`ifr_width` AS width,
                   i.`ifr_allowtransparency` AS allowtransparency,
                   t.`url`,
                   t.`name`
            FROM  `cms_iframe` AS i
            JOIN  `cms_iframe_translation` AS t
                ON (i.`ifr_id` = t.`ifr_id`)
            WHERE i.`ifr_id` = " . $requestedPage['id'] . "
            AND	  t.`lang_id` = " . LANG . "
            LIMIT 1");

if ($db->num_rows() == 1)
{
    $iframe = $db->fetch_assoc();

    ob_start();
    Messager::getMessages();
    $msgHTML = ob_get_clean();

    ob_start();
    ?>

    <iframe frameborder="0" marginheight="0" marginwidth="0" scrolling="auto" src="<?php echo $iframe['url']; ?>" height="<?php echo $iframe['height']; ?>" width="<?php echo $iframe['width']; ?>" <?php
        if($iframe['allowtransparency'] == '1')
        {
            echo ' allowtransparency="allowtransparency"';
        }
        ?>>
    </iframe>

    <?php
    $iframeOutput = ob_get_clean();
    $output->setContent( $iframeOutput, $msgHTML );
}
else
{
    Registry::get('error')->showError('iframe not found', 'Het iframe kan niet worden gevonden.', true);
}