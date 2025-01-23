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
        'install',
        'uninstall',
        'admin_head',
        'public_head',
        'initialize',
        'define_acl',
        'define_routes',
        'admin_appearance_settings_form',
        'public_footer'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'exhibit_layouts'
    );

        public function hookInstall()
    {
        // set_option('geolocation_default_latitude', '38');
    }

    public function hookUninstall()
    {
        // Delete the plugin options
        delete_option('lc_notices');
        delete_option('lc_project_id');
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

    /**
     * Define the ACL.
     * 
     * @param Omeka_Acl
     */
    public function hookDefineAcl($args)
    {
        // $acl = $args['acl'];
        // $acl->addResource('LocalContexts_LocalContexts');
    
        // Allow everyone access to browse and show.
        // $acl->allow(null, 'LocalContexts_LocalContexts', array('show', 'browse'));
        // $acl->allow('researcher', 'LocalContexts_LocalContexts', 'showNotPublic');
        // $acl->allow('contributor', 'LocalContexts_LocalContexts', array('add', 'editSelf', 'querySelf', 'deleteSelf', 'showNotPublic'));
        // $acl->allow(array('super', 'admin', 'contributor', 'researcher'), 'LocalContexts_LocalContexts', array('edit', 'query', 'delete'), new Omeka_Acl_Assert_Ownership);
    
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

    /**
     * Add LocalContext content to site appearance settings
     * @param Omeka_Form $form
     */
    public function hookAdminAppearanceSettingsForm($args)
    {
        $logger = Zend_Registry::get('bootstrap')->getResource('Logger');
        // $view = $args['view'];
        // $form = $view->formInput();
        $form = $args['form'];
        // $logger->log('testtest', Zend_Log::ERR);
        
        // Combine available general settings projects with existing site settings projects
        $projects = get_option('lc_notices') ? unserialize(get_option('lc_notices')) : [];
        if (get_option('lc_content_sites')) {
            foreach(unserialize(get_option('lc_content_sites')) as $siteProject) {
                $projects[] = json_decode($siteProject, true);
            }
        }

        $lcArray = array();
        foreach (array_unique($projects, SORT_REGULAR) as $key => $project) {
            // Save each project's content as single select value
            $lcHtml = '<div class="column content">';
            if (isset($project['project_url'])) {
                $lcHtml .= "<a class='name' target='_blank' href=" . $project['project_url'] . ">" . $project['project_title'] . "</a>";
            }
            foreach($project as $key => $content) {
                if (is_int($key)) {
                    $lcHtml .= '<div class="column description"><img class="column image" src="' . $content['image_url'] .
                                     '"><div class="column text"><div class="name">' . $content['name'] .
                                     (isset($content['language']) ? '<span class="language"> (' . $content['language'] . ')</span>' : '') . '</div>' .
                                     '<div class="description">' . $content['text'] . '</div></div></div>';
                }
            }
            $lcHtml .= '</div>';
            // $lcArray['label'] = $lcHtml;
            // $lcArray['value'] = json_encode($project);
            // $optionArray[] = $lcArray;
            $optionArray[json_encode($project)] = html_entity_decode($lcHtml);
        }
        
        $logger->log(print_r($optionArray, 1), Zend_Log::ERR);

        $form->addElement('select', 'lc_language', array(
            'label' => __('Local Contexts Language'),
            'description' => __('Only display content in selected language (Note: must already be generated and retrieved from LC Hub).'),
            'multiOptions' => [
                'English' => __('English'),
                'French' => __('French'),
                'Spanish' => __('Spanish'),
                'Māori' => __('Māori'),
            ],
        ));

        $form->addElement('multiCheckbox', 'lc_content_sites', array(
            'label' => __('Local Contexts value(s)'),
            'description' => __('Only display content in selected language (Note: must already be generated and retrieved from LC Hub).'),
            'multiOptions' => $optionArray,
            'label_class' => 'label admin',
            // 'filters' => [
            //     'HTMLEntities',
            // ],
            // 'label_options' => [
            //     'disable_html_escape' => 'disable_html_escape',
            // ],
            // 'attributes' => [
            //     'class' => 'label admin',
            // ],
        ));
        
        $form->getElement('lc_content_sites')->getDecorator('label')->setOption('escape', false);
        
        // $form->add([
        //     'name' => 'lc_content_sites',
        //     'type' => MultiCheckbox::class,
        //     'options' => [
        //         'element_group' => 'local_contexts',
        //         'label' => 'Local Contexts value(s)', // @translate
        //         'info' => 'Local Contexts value(s) to apply to site footer.', // @translate
        //         'multiOptions' => $optionArray,
        //         'label_options' => [
        //             'disable_html_escape' => true,
        //         ],
        //         'label_attributes' => [
        //             'class' => 'label admin',
        //         ],
        //     ],
        //     'attributes' => [
        //         'value' => get_option('lc_content_sites'),
        //         'class' => 'column check',
        //         'required' => false,
        //     ],
        // ]);

        $form->addDisplayGroup(
            array(
                'lc_language', 'lc_content_sites',
            ),
            'local-contexts', array('legend' => __('Local Contexts Content'))
        );

        echo $form->getDisplayGroup('local-contexts')->render();
    }

    public function hookPublicFooter()
    {
        echo "Henlo";
        if ( get_option('lc_notices') ) {
            $LCContent = unserialize(get_option('lc_notices'));
            
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
