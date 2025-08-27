<?php
/**
 * View for Local Contexts content assign.
 */

$head = array('bodyclass' => 'edit', 
              'title' => html_escape(__('Local Contexts | Assign Projects')));
echo head($head);
?>

<ul id="section-nav" class="navigation tabs">
    <?php if (isset($lc_content)): ?>
    <li><a href="#assign"><?php echo html_escape(__('Assign')); ?></a></li>
    <?php endif; ?>
    <?php if (isset($lc_assigned)): ?>
    <li><a href="#remove"><?php echo html_escape(__('Remove')); ?></a></li>
    <?php endif; ?>
</ul>

<?php echo flash(); ?>

<form method="post" enctype="multipart/form-data" action="">
<?php echo js_tag('tabs'); ?>
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function () {
    Omeka.Tabs.initialize();
});
</script>
<section class="seven columns alpha" id="lc-assign-form">
    <?php echo flash(); ?>
    
    <?php if (isset($lc_content)): ?>
    <div id="assign">
    <fieldset class="set">
        <div class="instructions"><?php echo __('Select content to make available for assignment to Site, Items & Exhibits:'); ?></div>
        <div id="assign-drawer-list" class="local-contexts-multicheckbox">
            <?php
            foreach ($lc_content as $assign_content) {
                echo $assign_content;
            }
            ?>
        </div>
    </fieldset>
    </div>
    <?php endif; ?>

    <?php if (isset($lc_assigned)): ?>
    <div id="remove">
    <fieldset class="set">
        <div class="instructions"><?php echo __('Select existing content to remove from assignment list (content already assigned to Sites, Items & Exhibits must be manually removed):'); ?></div>
        <div id="remove-drawer-list" class="local-contexts-multicheckbox">
            <?php
            foreach ($lc_assigned as $remove_content) {
                echo $remove_content;
            }
            ?>
        </div>
    </fieldset>
    </div>
    <?php endif; ?>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" name="submit" id="submit" value="Submit" class="big green button">
        <?php if (isset($lc_api_key)): ?>
        <input type="hidden" name="lc_api_key" value="<?php echo html_escape($lc_api_key); ?>"/>
        <?php endif; ?>
    </div>
</section>
</form>

<script>
    Omeka.manageDrawers('#assign-drawer-list', '.local-contexts-multicheckbox-row');
    Omeka.manageDrawers('#remove-drawer-list', '.local-contexts-multicheckbox-row');
    Omeka.addReadyCallback(Omeka.LocalContexts.manageDrawerToggleLabels);
</script>

<?php echo foot(); ?>
