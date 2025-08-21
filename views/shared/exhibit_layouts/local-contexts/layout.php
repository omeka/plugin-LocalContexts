<?php 
if (!array_key_exists('lc-project-id', $options)) {
    return;
}

$project = json_decode($options['lc-project-id'], true);
$projectArray = array();
foreach ($project as $key => $content) {
    if (is_int($key)) {
        // Only print content in selected language. If 'All', print everything
        if ((isset($content['language']) && $content['language'] == $options['lc-project-language'])
        || (!isset($content['language']) && $options['lc-project-language'] == 'English')
        || $options['lc-project-language'] == 'All') {
            $projectArray[] = $content;
        }
    }
}

// Don't print project URL if element value array is empty
if (isset($project['project_url']) && $projectArray) {
    $projectArray['project_url'] = $project['project_url'];
    $projectArray['project_title'] = $project['project_title'];
}

$lcArray = array();
if ($projectArray) {
    $lcHtml = LocalContextsPlugin::renderLCNoticeHtml($projectArray, null, $isPublic = true, null);
    $lcArray['label'] = $lcHtml;
}
?>

<?php if (count($lcArray) > 0): ?>
<div id="local-contexts-content" class="default">
    <?php echo $lcArray['label']; ?>
</div>
<?php endif; ?>
