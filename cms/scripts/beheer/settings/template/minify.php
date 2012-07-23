<?php
if (isset($_GET['id']) && is_numeric($_GET['id']))
{
    if (Template::minify(intval($_GET['id'])))
    {        
        Messager::ok('De template bestanden zijn geoptimaliseerd.', false, true);
        redirect('?do=list');
    }
    else
    {
        Messager::error('Het door u ingegeven id bestaat niet.', false, true);
        redirect('?do=list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingegeven.', false, true);
    redirect('?do=list');
}