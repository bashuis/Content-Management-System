<?php
/**
 * @ignore
 */

// Define some constants.
define('DS',                DIRECTORY_SEPARATOR);
define('ROOT',              dirname(__FILE__) . DS);
define('SYSTEMPATH',        dirname(__FILE__) . DS . 'cms' . DS);

// Include the CMS configuration file.
require_once ROOT . 'cms_config.php';

// Include the bootstrap file, wich handles everything else for us.
require_once SYSTEMPATH . 'bootstrap.php';
require_once SYSTEMPATH . 'frontend.php';

$content = "";

if (!is_null(Lang::tagToId($request[0])))
{
    if (isset($request[1]))
    {		   
		if(isset($activePage['templid']) && !empty($activePage['templid'])){
			$db->prepare('SELECT cm.`template_id`,
							 ct.`identifier`
					  FROM   `cms_menuitem` cm, `cms_template` ct
					  WHERE cm.`template_id` = ct.`template_id`
					  AND cm.`template_id` = :templid
					  LIMIT 1')
		   ->bindValue('templid', $activePage['templid'])
		   ->execute();
		}
		else{
			$db->prepare('SELECT ct.`template_id`,
							 ct.`identifier`
					  FROM   `cms_template` ct
					  WHERE ct.`default` = :default
					  LIMIT 1')
		   ->bindValue('default', 1)
		   ->execute();			
		} 
		   
        if ($db->num_rows() == 1 || $request[1] == 'page' || $request[1] == 'module')
        {
            ob_start();

            $currentMenu = $db->fetch_assoc();
						 
            if (!is_null($currentMenu['template_id']) && !is_null($currentMenu['identifier']))
            {
                if (file_exists(TEMPLATES . $currentMenu['identifier'] .  DS . 'index.php'))
                {
                    if (file_exists(TEMPLATES . $currentMenu['identifier'] .  DS . 'style_minified.css'))
                        $output->addStylesheet('/cms/templates/' . $currentMenu['identifier'] . '/style_minified.css');

                    if (file_exists(TEMPLATES . $currentMenu['identifier'] .  DS . 'js_minified.js'))
                        $output->addJavascript('/cms/templates/' . $currentMenu['identifier'] . '/js_minified.js');

                    require_once TEMPLATES . $currentMenu['identifier'] .  DS . 'index.php';
                }
                else
                {
                    Registry::get('error')->showError('index not found', 'De index pagina van deze template bestaat niet.', true);
                }
            }
            else
            {
                $db->query("SELECT `identifier`
                            FROM   `cms_template`
                            WHERE  `default` = 1
                            LIMIT 1");
                if ($db->num_rows() == 1)
                {
                    $template = $db->fetch_assoc();

                    if (file_exists(TEMPLATES . $template['identifier'] .  DS . 'style_minified.css'))
                        $output->addStylesheet('/cms/templates/' . $template['identifier'] . '/style_minified.css');

                    if (file_exists(TEMPLATES . $template['identifier'] .  DS . 'js_minified.js'))
                        $output->addJavascript('/cms/templates/' . $template['identifier'] . '/js_minified.js');

                    require_once TEMPLATES . $template['identifier'] .  DS . 'index.php';
                }
                else
                {
                    Registry::get('error')->showError('no template', 'Er is een fout opgetreden tijdens het selecteren van de template.', true);
                }
            }

            $content = ob_get_clean();
        }
        else
        {
            Registry::get('error')->showError('menuitem not found', 'Er is een fout opgetreden tijdens het ophalen van de pagina.', true);
        }
    }
    else
    {
        Registry::get('error')->showError('menuitem not found', 'Er is een fout opgetreden tijdens het ophalen van de pagina.', true);
    }
}
else
{
    Error::notFound();
}


$mysqlTime  = Registry::get('database')->fb();
$totalTime  = microtime(true) - $timeStarted;
$phpTime    = $totalTime - $mysqlTime;

Registry::get('firephp')->info(round($phpTime, 3) * 1000 . 'ms', 'PHP loadtime');
Registry::get('firephp')->info(round($totalTime, 3) * 1000 . 'ms', 'PHP + MySQL loadtime');

echo $content;