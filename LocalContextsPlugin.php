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
        'before_save_item',
        'public_footer'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'admin_items_form_tabs',
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

    // Save LC content selected in admin item form (below)
    public function hookBeforeSaveItem($args)
    {
        if (!($post = $args['post'])) {
            return;
        }

        $item = $args['record'];

        $lcContentArray = isset($post['lc-content']) ? $post['lc-content'] : [];
        $lcLanguage = isset($post['lc_content_language']) ? $post['lc_content_language'] : '';

        $lcElementValueArray = [];
        if ($lcContentArray) {
            $elementID = $post['lc_content_element'];
            $element = get_db()->getTable('Element')->find($elementID);
            $elementSet = get_db()->getTable('ElementSet')->find($element->element_set_id);
            foreach ($lcContentArray as $project) {
                $project = json_decode($project, true);

                foreach($project as $key => $content) {
                    if (is_int($key)) {
                        if (isset($content['language'])) {
                            if ($content['language'] == $lcLanguage || $lcLanguage == 'All') {
                                $lcElementValue = self::lcContentHtml($content);
                                $lcElementValueArray[] = ['text' => $lcElementValue, 'html' => true];
                            }
                        } else if ($lcLanguage == 'English' || $lcLanguage == 'All') {
                            // English LC content doesn't have language element
                            $lcElementValue = self::lcContentHtml($content);
                            $lcElementValueArray[] = ['text' => $lcElementValue, 'html' => true];
                        }
                    }
                }

                // Don't print project URL if element value array is empty
                if (isset($project['project_url']) && $lcElementValueArray) {
                    $projectLink = '<a href=' . $project['project_url'] . '>' . $project['project_title'] . '</a>';
                    $lcElementValueArray[] = ['text' => $projectLink, 'html' => true];
                }
            }

            $elementTexts = array(
                $elementSet->name => array(
                    $element->name => $lcElementValueArray
                )
            );

            $item->addElementTextsByArray($elementTexts);

        } else {
            // Do nothing if no lc content selected
            return;
        }
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

    // Provide LC content to item add/update form
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];
        $tabs['Local Contexts'] = [];

        $view = get_view();
        if (get_option('lc_content_site')) {
			$projects = unserialize(get_option('lc_notices'));
            foreach ($projects as $project) {
                $contentArray[] = $project;
            }

            $elementTable = get_db()->getTable('Element');
            $elementData = $elementTable->findPairsForSelectForm();

            $languageData = [
                'All' => __('All available languages'),
                'English' => __('English'),
                'French' => __('French'),
                'Spanish' => __('Spanish'),
                'Māori' => __('Māori'),
            ];

			$tabs['Local Contexts'] = $view->partial('lc-item-edit.phtml', [
	            'item' => $item,
                'lc_content' => $contentArray,
                'element_data' => $elementData,
                'language_data' => $languageData,
	        ]);
        }

        return $tabs;
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

    public function lcContentHtml($content)
    {
        if ($content['image_url']) {
            $html = sprintf(
                "<div class='label'><img class='column image' src='%s'><div class='column text'>
                <div class='name'>%s</div><div class='description'>%s</div></div></div>",
                __($content['image_url']),
                __($content['name']),
                __($content['text']),
            );
        } else {
            $html = sprintf(
                "<div class='column text'><div class='name'>%s</div><div class='description'>%s</div></div>",
                __($content['name']),
                __($content['text']),
            );
        }
        return $html;
    }
}
