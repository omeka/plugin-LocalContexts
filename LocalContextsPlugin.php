<?php

if (!defined('LOCALCONTEXTS_PLUGIN_DIR')) {
    define('LOCALCONTEXTS_PLUGIN_DIR', dirname(__FILE__));
}

if (!defined('LOCALCONTEXTS_HELPERS_DIR')) {
    define('LOCALCONTEXTS_HELPERS_DIR', LOCALCONTEXTS_PLUGIN_DIR . '/helpers');
}

if (!defined('LOCALCONTEXTS_FORMS_DIR')) {
    define('LOCALCONTEXTS_FORMS_DIR', LOCALCONTEXTS_PLUGIN_DIR . '/forms');
}

require_once LOCALCONTEXTS_PLUGIN_DIR . '/LocalContextsPlugin.php';

/**
 * LocalContexts plugin.
 * 
 * @package Omeka\Plugins\LocalContexts
 */
class LocalContextsPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'uninstall',
        'admin_head',
        'public_head',
        'initialize',
        'define_routes',
        'config',
        'config_form',
        'public_footer'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'exhibit_layouts'
    );

    public function hookUninstall()
    {
        // Delete the plugin options
        delete_option('lc_project_id');
        delete_option('lc_notices');
        delete_option('lc_language');
        delete_option('lc_content_site');
    }
    
    public function hookAdminHead()
    {
        queue_css_file('local-contexts');
    }

    public function hookPublicHead()
    {
        queue_css_file('local-contexts');
    }

    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $indexRoute = new Zend_Controller_Router_Route('local-contexts',
            array(
                'module'        => 'local-contexts',
                'controller'    => 'localcontexts',
                'action'        => 'index'
                )
            );
        $router->addRoute('localContextsIndex', $indexRoute);

        $assignRoute = new Zend_Controller_Router_Route('local-contexts/assign',
            array(
                'module'        => 'local-contexts',
                'controller'    => 'localcontexts',
                'action'        => 'assign'
                )
            );
        $router->addRoute('localContextsAssign', $assignRoute);
    }

    public function hookConfig($args)
    {
        $post = $args['post'];
        set_option('lc_language', $post['lc_language']);
        if ($post['lc_content_site']) {
            set_option('lc_content_site', serialize($post['lc_content_site']));
        } else {
            delete_option('lc_content_site');
        }
    }

    public function hookConfigForm()
    {
        $view = get_view();
        include 'config_form.php';
    }

    public function hookPublicFooter()
    {
        if (get_option('lc_content_site')) {
            $projects = unserialize(get_option('lc_content_site'));
            $localContextLanguage = get_option('lc_language');
            
            $contentArray = array();
            foreach ($projects as $project) {
                $project = json_decode($project, true);
                $projectArray = array();

                if (isset($project['project_title'])) {
                    $projectArray['project_title'] = $project['project_title'];
                }
                if (isset($project['project_url'])) {
                    $projectArray['project_url'] = $project['project_url'];
                }

                foreach ($project as $key => $notice) {
                    if (is_int($key)) {
                        if (isset($notice['language']) && ($notice['language'] == $localContextLanguage)) {
                            $projectArray[] = $notice;
                        } elseif (!isset($notice['language']) && $localContextLanguage == 'English') {
                            $projectArray[] = $notice;
                        }
                    }
                }
                $contentArray[] = $projectArray;
            }
            $view = get_view();
            echo $view->partial('site-footer.phtml', [
                'lc_content' => $contentArray,
            ]);
        }
    }

    /**
     * LocalContexts admin_navigation_main filter.
     *
     * Adds a button to the admin's main navigation.
     *
     * @param array $nav
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Local Contexts'),
            'uri' => url('local-contexts'),
        );
        return $nav;
    }

    /**
     * Register an exhibit layout for displaying Local Contexts content.
     *
     * @param array $layouts Exhibit layout specs.
     * @return array
     */
    public function filterExhibitLayouts($layouts)
    {
        $layouts['local-contexts'] = array(
            'name' => __('Local Contexts'),
            'description' => __('Embed Local Contexts content.')
        );
        return $layouts;
    }
}
