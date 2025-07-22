<?php
$lcLanguage = get_option('lc_site_language') ?: 'English';
$lcSiteChecked = get_option('lc_content_site') ? unserialize(get_option('lc_content_site')) : [];
$lcLanguageOptions = [
    'All' => __('All available languages'),
    'English' => __('English'),
    'French' => __('French'),
    'Spanish' => __('Spanish'),
    'Māori' => __('Māori'),
];

// Combine available general settings projects with existing site settings projects
$projects = get_option('lc_notices') ? unserialize(get_option('lc_notices')) : [];
foreach($lcSiteChecked as $siteProject) {
    $siteProject = json_decode($siteProject, true);
    $projects[] = $siteProject;
    // Parse project IDs from URLs to locate checked form values in jQuery below
    $projectUrl = $siteProject['project_url'];
    $projectID = trim(substr($projectUrl, strrpos($projectUrl, '/') + 1));
    $lcCheckedProjects[] = $projectID;
}

foreach (array_unique($projects, SORT_REGULAR) as $key => $project) {
    // Collapse many projects for ease of viewing
    $collapse = (count($projects) >= 3) ? true : false;
    // Save each project's content as single select value
    $lcHtml = LocalContextsPlugin::renderLCNoticeHtml($project, $key, false, $collapse);
    $contentArray[] = $lcHtml;
}
?>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Local Contexts language'); ?></label>    
    </div>
    <div class="inputs five columns omega" >
        <p class='explanation'><?php echo __('Only display content in selected language (Note: must already be generated and retrieved from LC Hub).'); ?>
        </p>
        <div class="input-block">
            <?php echo $view->formSelect('lc_site_language', $lcLanguage, null, $lcLanguageOptions); ?>
        </div>
    </div>
</div>

<?php if (isset($contentArray)): ?>
<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Local Contexts public site value(s)'); ?></label>    
    </div>
    <div class="inputs five columns omega">
        <p class='explanation'><?php echo __('Local Contexts value(s) to apply to public site footer.'); ?></p>
        <div class="local-contexts-multicheckbox">
            <?php
            foreach ($contentArray as $project) {
                echo $project;
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function () {
    Omeka.addReadyCallback(Omeka.LocalContexts.addHideButtons);
    lcCheckedProjects = <?php echo js_escape($lcCheckedProjects); ?>;
    $.each(lcCheckedProjects, function(key, projectID) {
        $("[value*='" + projectID + "']").attr('checked', 'true');
    });
});
</script>
