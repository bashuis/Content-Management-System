<?php

if (!isset($_GET['do']))
    $_GET['do'] = 'list';

if (file_exists(dirname(__FILE__) . DS . 'template' . DS . $_GET['do'] . '.php'))
{
    require_once dirname(__FILE__) . DS . 'template' . DS . $_GET['do'] . '.php';
}
else
{
    Messager::error('De pagina die u probeert te bezoeken bestaat niet.');
    require_once dirname(__FILE__) . DS . 'template' . DS . 'list.php';
}