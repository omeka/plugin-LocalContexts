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
    $projects[] = json_decode($siteProject, true);
}

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
    $lcSiteOptions[json_encode($project)] = $lcHtml;
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

<?php if (isset($lcSiteOptions)): ?>
<div class="field">
    <div class="seven columns alpha">
        <label><?php echo __('Local Contexts public site value(s)'); ?></label>    
    </div>
    <div class="inputs seven columns omega" >
        <p class='explanation'><?php echo __('Local Contexts value(s) to apply to public site footer.'); ?>
        </p>
        <div class="input-block">
            <?php echo $view->formMultiCheckbox('lc_content_site', $lcSiteChecked, ['class' => 'column check', 'escape' => false], $lcSiteOptions, ''); ?>
        </div>

    </div>
</div>
<?php endif; ?>
