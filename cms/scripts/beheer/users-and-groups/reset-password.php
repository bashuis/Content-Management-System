<?php

$output->addTitle("Verwijderen");

if (empty($request[2]))
{
    Messager::warning('U heeft geen gebruiker geselecteerd!');
}
else
{
    $user = $db->query("SELECT `u_id` AS id,
                               `u_name` AS name,
                               `u_email` AS email,
                               `u_fullname` AS fullname
                        FROM   `cms_user`
                        WHERE  `u_id` = " . intval($request[2]) . "
                        LIMIT 1");
	
    if ($db->num_rows($user) == 1)
    {
        $user = $db->fetch_assoc($user);

        if( isset($_POST['confirm']))
        {
            if(isset($_POST['generate_password']))
            {
                $password = Generic::generateRandomString(6);
            }
            else
            {
                $password = $_POST['new_password'];
            }

            $hash = sha1($password);

            $db->query("UPDATE `cms_user`
                        SET    `u_password` = '" . $hash . "'
                        WHERE  `u_id` = " . intval($user['id']) . "
                        LIMIT 1");

            $replacements = array();
            $replacements['%username%'] = $user['name'];
            $replacements['%fullname%'] = $user['fullname'];
            $replacements['%password%'] = $password;
            $replacements['%sitename%'] = $cms_settings['sitename'];
            $replacements['%domainname%'] = $_SERVER['HTTP_HOST'];
            $replacements['%style%'] = '<style type="text/css" media="screen">' . file_get_contents('http://huizinga.nl/mail.css') . '</style>';

            $message = str_ireplace(array_keys($replacements), $replacements, file_get_contents(MESSAGES . 'email/password-changed.htm'));

            Email::cmsMail($user['email'], 'Wachtwoord wijziging op ' . $cms_settings['sitename'], $message);

            Messager::ok('Het wachtwoord van &quot;' . $user['name'] . '&quot; is gewijzig in &quot;' . $password . '&quot;, dit wachtwoord is opgestuurd naar ' . $user['email'] . '.', false, true);
            redirect('/beheer/users-and-groups/list/');
        }
        else
        {
            ?>
            <h1>Wachtwoord wijzigen voor &quot;<?php echo $user['name']; ?>&quot;.</h1>
            <p>Wilt u het wachtwoord automatisch laten genereren, of zelf ingeven?</p>
            <form action="" method="post">
                <div class="normalrow">
                    <label>Genereer wachtwoord:</label>
                    <input type="checkbox" name="generate_password" id="generate_password" checked="checked" onclick="if( this.checked ){ document.getElementById('new_password').value = ''; }" />
                </div>
                <div class="normalrow">
                    <label>Wachtwoord:</label>
                    <input type="text" name="new_password" id="new_password" onfocus="document.getElementById('generate_password').checked = false;" />
                </div>
                <div class="onlyinput">
                    <input type="submit" name="confirm" value="Wijzig wachtwoord" />
                </div>
            </form>
            <?php
        }
    }
    else
    {
        Messager::warning('De gebruiker wiens wachtwoord je wilt wijzigen bestaat niet (meer).');
    }
}

?>