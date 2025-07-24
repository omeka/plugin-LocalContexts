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
<div class="local-contexts-project">
    <?php
    echo $this->formLabel($formStem . '[options][lc-project-language]',
        __('Select Local Contexts language'));
    echo $this->formSelect($formStem . '[options][lc-project-language]',
        @$options['lc-project-language'], array(),
        $languageArray);
    echo '</br></br>';
    echo $this->formLabel($formStem . '[options][lc-project-id]',
        __('Select Local Contexts project'));
    echo $this->formSelect($formStem . '[options][lc-project-id]',
        @$options['lc-project-id'], array(),
        $contentArray);
    ?>
</div>
