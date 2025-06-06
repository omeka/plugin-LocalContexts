<?php
/**
 * LocalContexts Controller
 */

class LocalContexts_LocalContextsController extends Omeka_Controller_AbstractActionController
{
    protected $_autoCsrfProtection = true;

    public function indexAction()
    {
        $form = new Omeka_Form_Admin(array('type'=>'lc_api_settings'));

        $projectID = get_option('lc_project_id');

        $form->setAction(url('local-contexts/assign'))
             ->setMethod('post');
     
        $form->addElementToEditGroup('password', 'lc_api_key', array(
            'label'       => __('API Key'),
            'description' => __('Optional. To retrieve project notices from Local Contexts Hub, enter user API key. To edit/remove existing notices, leave blank.'),
            'class'       => 'textinput',
            'size'        => '40',
        ));

        $form->addElementToEditGroup('text', 'lc_project_id', array(
            'label'       => __('Local Contexts Project ID'),
            'value'       => $projectID,
            'description' => __('Optional. Input Project IDs to retrieve from Local Contexts Hub. Add multiple IDs separated by "," to return multiple projects.'),
            'class'       => 'textinput',
            'size'        => '40',
        ));

        $this->view->assign('form', $form);
    }

    public function assignAction()
    {    
        if (isset($_POST['lc_project_id'])) {
            set_option('lc_project_id', $_POST['lc_project_id']);
        }

        $assignForm = new Omeka_Form;

        // If LC remove content selected, remove from general settings
        if (isset($_POST['lc-remove'])) {
            foreach ($_POST['lc-remove'] as $remove) {
                $removeArray[] = json_decode($remove, true);
            }
            $this->removeLCcontent($removeArray);
        }

        // Get existing LC content from database
        $existingProjectArray = get_option('lc_notices') ? unserialize(get_option('lc_notices')) : [];

        // If LC assign content selected, add to general settings
        if (isset($_POST['lc-notice'])) {
            foreach ($_POST['lc-notice'] as $notice) {
                $noticeArray[] = json_decode($notice, true);
            }
            
            // Add notices to general options for site/item/exhibit access
            if (isset($existingProjectArray)) {
                $existingProjectArray = array_unique(array_merge($existingProjectArray, $noticeArray), SORT_REGULAR);
            }
            set_option('lc_notices', serialize($existingProjectArray));
        }

        // Retrieve project data from Local Contexts API
        $newProjectArray = [];
        // Only retrieve API content if given API key
        if (!empty($_POST['lc_api_key'])) {
            if ( get_option('lc_project_id') ) {
                $projects = explode(',', get_option('lc_project_id'));
                // Display 'Open to Collaborate' notice along with all given projects
                $newProjectArray[] = $this->fetchAPIdata($_POST['lc_api_key']);
                foreach ($projects as $projectID) {
                    $newProjectArray[] = $this->fetchAPIdata($_POST['lc_api_key'], trim($projectID));
                }
            } else {
                // Display 'Open to Collaborate' notice along with user's projects
                $newProjectArray[] = $this->fetchAPIdata($_POST['lc_api_key']);
                $projectsURL = 'https://sandbox.localcontextshub.org/api/v2/projects/';
                $this->client->setUri($projectsURL);
                $this->client->setHeaders(['x-api-key' => $_POST['lc_api_key']]);
                $response = $this->client->request('GET');

                if ($response->isSuccessful()) {
                    $projectsMetadata = json_decode($response->getBody(), true);
                    foreach ($projectsMetadata['results'] as $project) {
                        $newProjectArray[] = $this->fetchAPIdata($_POST['lc_api_key'], $project['unique_id']);
                    }
                }

            }
            $newProjectArray = array_filter($newProjectArray);
            // Pass API key to assign form to retain assign content after submission
            $this->view->lc_api_key = $_POST['lc_api_key'];
        }

        // Remove LC content that is already assigned
        $newProjectArray = array_udiff($newProjectArray, $existingProjectArray, function($a, $b) { return $a <=> $b; });

        $contentArray = [];
        foreach ($newProjectArray as $project) {
            // Collapse many projects for ease of viewing
            $collapse = (count($newProjectArray) >= 3) ? true : false;
            $lcHtml = LocalContextsPlugin::renderLCNoticeHtml($project, $collapse);
            $lcArray['label'] = $lcHtml;
            $lcArray['value'] = json_encode($project);
            $contentArray[] = $lcArray;
        }

        $assignedArray = [];
        foreach ($existingProjectArray as $project) {
            // Collapse many projects for ease of viewing
            $collapse = (count($existingProjectArray) >= 3) ? true : false;
            $lcHtml = LocalContextsPlugin::renderLCNoticeHtml($project, $collapse);
            $lcArray['label'] = $lcHtml;
            $lcArray['value'] = json_encode($project);
            $assignedArray[] = $lcArray;
        }

        // Redirect to index page if no content to display
        if (empty($contentArray) && empty($assignedArray)) {
            $this->_helper->flashMessenger(__('No Local Contexts content to display!'));
            $this->_helper->redirector->goto('index');
        } else if (empty($contentArray) && !empty($assignedArray)) {
            $this->view->lc_assigned = $assignedArray;
        } else if (!empty($contentArray) && empty($assignedArray)) {
            $this->view->lc_content = $contentArray;
        } else {
            $this->view->lc_content = $contentArray;
            $this->view->lc_assigned = $assignedArray;
        }

        $assignForm->setAction($this->_helper->url('assign', 'local-contexts'))
             ->setMethod('post');
        $this->view->assign('form', $assignForm);
    }

    /**
     * retrieve and display content from Local Contexts API
     *
     * @param string $apiKey
     * @param string $projectID
     */
    protected function fetchAPIdata($apiKey, $projectID = null)
    {
        $this->client = new Omeka_Http_Client();
        
        if (!empty($projectID)) {
            // If project ID(s) given, retrieve specific project notices
            $APIProjectURL = 'https://sandbox.localcontextshub.org/api/v2/projects/' . $projectID . '/';
        } else {
            // If not, retrieve generic 'Open to Collaborate' notice
            $collaborateURL = 'https://sandbox.localcontextshub.org/api/v2/notices/open_to_collaborate/';

            $this->client->setUri($collaborateURL);
            $this->client->setHeaders(['x-api-key' => $apiKey]);

            $response = $this->client->request('GET');
            if (!$response->isSuccessful()) {
                return;
            }

            $collaborateMetadata = json_decode($response->getBody(), true);
            // Set institution/researcher name and profile page to display as linked 'project' metadata
            if (isset($collaborateMetadata['institution'])) {
                $newProjectArray['project_url'] = $collaborateMetadata['institution']['profile_url'];
                $newProjectArray['project_title'] = $collaborateMetadata['institution']['name'] . ' (institution)';
            } else if (isset($collaborateMetadata['researcher'])) {
                $newProjectArray['project_url'] = $collaborateMetadata['researcher']['profile_url'];
                $newProjectArray['project_title'] = $collaborateMetadata['researcher']['name'] . ' (researcher)';
            } else {
                $newProjectArray['project_url'] = null;
                $newProjectArray['project_title'] = null;
            }
            $noticeArray['name'] = isset($collaborateMetadata['notice']['name']) ? $collaborateMetadata['notice']['name'] : null;
            $noticeArray['image_url'] = isset($collaborateMetadata['notice']['img_url']) ? $collaborateMetadata['notice']['img_url'] : null;
            $noticeArray['text'] = isset($collaborateMetadata['notice']['default_text']) ? $collaborateMetadata['notice']['default_text'] : null;
            $newProjectArray[] = $noticeArray;
            return $newProjectArray;
        }

        $this->client->setUri($APIProjectURL);
        $this->client->setHeaders(['x-api-key' => $apiKey]);

        $response = $this->client->request('GET');
        if (!$response->isSuccessful()) {
            return;
        }
        
        $projectMetadata = json_decode($response->getBody(), true);

        $assignArray['project_url'] = isset($projectMetadata['project_page']) ? $projectMetadata['project_page'] : null;
        $assignArray['project_title'] = isset($projectMetadata['title']) ? $projectMetadata['title'] . ' (project)' : null;
        if (isset($projectMetadata['notice'])) {
            foreach ($projectMetadata['notice'] as $notice) {
                $noticeArray['name'] = $notice['name'];
                $noticeArray['image_url'] = $notice['img_url'];
                $noticeArray['text'] = $notice['default_text'];
                $assignArray[] = $noticeArray;
                if ($notice['translations']) {
                    foreach ($notice['translations'] as $translation) {
                        $noticeArray['name'] = $translation['translated_name'];
                        $noticeArray['image_url'] = $notice['img_url'];
                        $noticeArray['text'] = $translation['translated_text'];
                        $noticeArray['language'] = $translation['language'];
                        $assignArray[] = $noticeArray;
                        $noticeArray = array();
                    }
                }
            }
        }
        return $assignArray;
    }

    /**
     * remove Local Contexts content from settings
     *
     * @param array $removeArray
     */
    protected function removeLCcontent($removeArray)
    {
        // Get existing LC content
        $currentLCcontent = get_option('lc_notices') ? unserialize(get_option('lc_notices')) : [];

        // Build new array without removeArray content and save to settings
        $diff = array_diff(array_map('json_encode', $currentLCcontent), array_map('json_encode', $removeArray));
        $newLCcontent = array_map(function ($json) { return json_decode($json, true); }, $diff);
        set_option('lc_notices', serialize($newLCcontent));
    }
}
