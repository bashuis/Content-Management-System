<h2>Nieuwe gebruiker aanmaken</h2>
<?php

if (isset($_POST['submit']))
{
    $input = array();
    $errors = array();

    //	Check the name
    if (!empty($_POST['name']))
    {
        $db->prepare("SELECT 1
                     FROM   `cms_user`
                     WHERE  `u_name` = :name
                     LIMIT 1")
           ->bindValue('name', $_POST['name'])
           ->execute();

        if ($db->num_rows() == 1 || Security::checkSetting("check-username&usr=" . $_POST['name']) == '0')
        {
            Messager::error('Helaas, er is al een gebruiker met de naam &quot;' . htmlentities($_POST['name']) . '&quot;, je zult een andere naam moeten kiezen.', false);
            $errors['name'] = true;
            $input['name'] = $_POST['name'];
        }
        else
        {
            $input['name'] = $_POST['name'];
        }
    }
    else
    {
        Messager::error('U heeft geen gebruikersnaam ingevuld.', false);
        $errors['name'] = true;
    }

    //	Check the fullname
    if (!empty($_POST['fullname']))
    {
        $db->prepare("SELECT 1
                     FROM   `cms_user`
                     WHERE  `u_fullname` = :fullname
                     LIMIT 1")
           ->bindValue('fullname', $_POST['fullname'])
           ->execute();
        
        if($db->num_rows() == 1 )
        {
            Messager::error('Helaas, er is al een gebruiker met de volledige naam &quot;' . htmlentities($_POST['fullname']) . '&quot;, je zult een andere volledige naam moeten kiezen.', false);
            $errors['fullname'] = true;
            $input['fullname'] = $_POST['fullname'];
        }
        else
        {
            $input['fullname'] = $_POST['fullname'];
        }
    }
    else
    {
        Messager::error('U heeft geen volledige naam ingevuld.', false);
        $errors['fullname'] = true;
    }

    //	Check the email address
    if (!empty($_POST['email']))
    {
        if (!Security::validateEmailAddress($_POST['email']))
        {
            Messager::error('U heeft geen geldig e-mailadres ingevuld.', false);
            $errors['email'] = true;
            $input['email'] = $_POST['email'];
        }
        else
        {
            $input['email'] = $_POST['email'];
        }
    }
    else
    {
        Messager::error('U heeft geen e-mailadres ingevuld.', false);
        $errors['email'] = true;
    }

    //	Everybody is in group 1 "Iedereen"
    $_POST['g_1'] = true;

    //	Check if there is a group to put the user in
    $groupWasSelected = false;
    $input['groups'] = array();

    $groups = $db->query("SELECT `g_id` AS id
                          FROM `cms_group`");

    while ($group = $db->fetch_assoc($groups))
    {
        if (isset($_POST['g_'.$group['id']]))
        {
            $groupWasSelected = true;
            $input['groups'][] = $group['id'];
        }
    }

    if (!$groupWasSelected)
    {
        Messager::error('Iedere gebruiker moet lid zijn van minimaal 1 groep.', false);
        $errors['group'] = true;
    }

    $input['active'] = isset($_POST['active']);
    $input['admin'] = isset($_POST['admin']);

    if (sizeof($errors) == 0)
    {
        $password = Generic::generateRandomString( 6 );
        $hash = sha1($password);

        //	Insert the user
        $db->prepare("INSERT INTO `cms_user` (`u_name`,
                                              `u_fullname`,
                                              `u_email`,
                                              `u_password`,
                                              `u_active`,
                                              `u_admin`)
                      VALUES (:name,
                              :fullname,
                              :email,
                              :password,
                              :active,
                              :admin)")
           ->bindValue('name',     $input['name'])
           ->bindValue('fullname', $input['fullname'])
           ->bindValue('email',    $input['email'])
           ->bindValue('password', $hash)
           ->bindValue('active',   ($input['active'] ? 1 : 0))
           ->bindValue('admin',    ($input['admin'] ? 1 : 0))
           ->execute();

        $userId = $db->insert_id();

        $inserts = array();

        foreach ($input['groups'] as $group)
        {
            $db->query("INSERT INTO `cms_user_group` (`u_id`,
                                                      `g_id`)
                        VALUES (" . intval($userId) . ",
                                " . intval($group) . ")");
        }


        $replacements = array();
        $replacements['%fullname%'] = $input['fullname'];
        $replacements['%username%'] = $input['name'];
        $replacements['%password%'] = $password;
        $replacements['%sitename%'] = $cms_settings['sitename'];

        if (User::isAdmin($userId) || User::isInAdminGroup($userId))
        {
            $replacements['%domainname%'] = $_SERVER['HTTP_HOST'].'/beheer/';
        }
        else
        {
            $replacements['%domainname%'] = $_SERVER['HTTP_HOST'];
        }

        $replacements['%style%'] = '<style type="text/css" media="screen">'.file_get_contents('http://huizinga.nl/mail.css').'</style>';

        $message = str_ireplace(array_keys($replacements), $replacements, file_get_contents(MESSAGES . 'email/account-created-admin-nl.htm'));

        if (Email::sendEmail($smtpMailSettings, $input['email'], 'Welkom bij ' . $cms_settings['sitename'], $message, $cms_settings['system-mail']))
        {
            Messager::ok('De gebruiker <strong>' . $input['name'] . '</strong> is aangemaakt met als wachtwoord <strong>' . $password . '</strong> en er is een bericht gestuurd naar <strong>' . $input['email'] . '</strong>.', false, true);
            redirect('/beheer/users-and-groups/list/');
        }
        else
        {
            Messager::warning('De gebruiker <strong>' . $input['name'] . '</strong> is aangemaakt, maar er kon geen bericht worden verstuurd naar <strong>' . $input['email'] . '</strong>. Je zult ' . $input['name'] . ' daarom zelf op de hoogte moeten stellen, zijn / haar wachtwoord is <strong>' . $password . '</strong>', false);
        }
    }
    else
    {
        showNewUserForm($input, $errors, 'Maak de gebruiker aan');
    }
}
else
{
    showNewUserForm( array('active'=>true,'admin'=>false,'groups'=>array(1)), array(), 'Maak de gebruiker aan');
}
?>