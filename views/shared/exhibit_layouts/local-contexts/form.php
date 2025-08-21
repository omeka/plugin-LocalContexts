<?php
$formStem = $block->getFormStem();
$options = $block->getOptions();

$languageArray = array(
    'All' => 'All available languages',
    'English' => 'English',
    'French' => 'French',
    'Spanish' => 'Spanish',
    'Māori' => 'Māori'
);

$projects = get_option('lc_notices') ? unserialize(get_option('lc_notices')) : [];
$contentArray = [];
foreach ($projects as $project) {
    $contentArray[json_encode($project)] = $project['project_title'];
}
?>
<div class="local-contexts-options">
    <div class="local-contexts-language-select field">
        <div class="field-meta">
            <?php echo $this->formLabel($formStem . '[options][lc-project-language]', __('Select Local Contexts language')); ?>
        </div>
        <div class="inputs">
            <?php echo $this->formSelect($formStem . '[options][lc-project-language]', @$options['lc-project-language'], array(), $languageArray); ?>
        </div>
    </div>
    <div class="local-contexts-project-select field">
        <div class="field-meta">
            <?php echo $this->formLabel($formStem . '[options][lc-project-id]', __('Select Local Contexts project')); ?>
        </div>
        <div class="inputs">
            <?php echo $this->formSelect($formStem . '[options][lc-project-id]', @$options['lc-project-id'], array(), $contentArray); ?>
        </div>
    </div>
</div>
