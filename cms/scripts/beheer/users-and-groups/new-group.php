<h2>Nieuwe groep aanmaken</h2>
<?php

$input = array();
$errors = array();

if (isset($_POST['submit']))
{
    if( isset( $_POST['name'] ) )
    {
        $input['name'] = $_POST['name'];
        $check = $db->prepare("SELECT 1
                               FROM   `cms_group`
                               WHERE  `g_name` = :name
                               LIMIT  1")
                    ->bindValue('name', $input['name'])
                    ->execute();

        if ($db->num_rows($check) == 1)
        {
            $errors['name'] = 'Er is al een groep met die naam.';
        }
    }
    else
    {
        $errors['name'] = 'Je moet wel een naam aan de groep geven!';
    }

    $input['admin'] = isset($_POST['admin']);

    if (sizeof($errors) == 0)
    {
        $db->query("INSERT INTO `cms_group` (`g_name`,
                                             `g_admin`)
                    VALUES ('" . $input['name'] . "',
                            " . ($input['admin'] ? 1 : 0) . ")");

        Messager::ok('De groep is succesvol aangemaakt.', false, true);
        redirect('/beheer/users-and-groups/list/');
    }
    else
    {
        showNewGroupForm( $input, $errors, 'Maak deze groep aan' );
    }
}
else
{
    showNewGroupForm(array('admin' => false), $errors, 'Maak deze groep aan');
}