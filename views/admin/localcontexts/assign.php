<?php
/**
 * View for Local Contexts content assign.
 */

queue_css_file('local-contexts');
// $contentClass = isset($lc_content) ? "active" : "";
// $assignedClass = isset($lc_assigned) ? "active" : "";
// // If new content AND assigned content, make new content active tab
// if (isset($lc_content) && isset($lc_assigned)) {
//     $assignedClass = "";
// }

$head = array('bodyclass' => 'edit', 
              'title' => html_escape(__('Local Contexts | Assign Notices')));
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
        <div class="label"><?php echo __('Select content to make available for assignment to Sites, Items & Exhibits:'); ?></div>
            <?php foreach($lc_content as $notice_content): ?>
            <div class="label admin">
                <div class="column check">
                    <input name="lc-notice[]" type="checkbox" value="<?php echo html_escape(json_encode($notice_content)); ?>">
                </div>
                <div class="column content">
                    <?php if (isset($notice_content['project_url'])): ?>
                        <a class="name" target="_blank" href="<?php echo html_escape($notice_content['project_url']); ?>"><?php echo html_escape($notice_content['project_title']); ?></a>
                    <?php endif; ?>
                    <?php foreach($notice_content as $key => $content): ?>
                        <?php if (is_int($key)): ?>
                            <div class="description">
                                <img class="column image" src="<?php echo html_escape($content['image_url']); ?>">
                                <div class="column text">
                                    <div class="name">
                                        <?php
                                        echo($content['name']);
                                        if (isset($content['language'])) {
                                            echo '<span class="language"> (' . html_escape($content['language']) . ')</span>';
                                        }
                                        ?></div>
                                    <div class="description"><?php echo html_escape($content['text']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
    </fieldset>
    </div>
    <?php endif; ?>

    <?php if (isset($lc_assigned)): ?>
    <div id="remove">
    <fieldset class="set">
        <div class="label"><?php echo __('Select existing content to remove from assignment list (content already assigned to Sites, Items & Exhibits must be manually removed):'); ?></div>
        <?php foreach($lc_assigned as $remove_content): ?>
        <div class="label admin">
            <div class="column check">
                <input name="lc-remove[]" type="checkbox" value="<?php echo html_escape(json_encode($remove_content)); ?>">
            </div>
            <div class="column content">
                <?php if (isset($remove_content['project_url'])): ?>
                    <a class="name" target="_blank" href="<?php echo html_escape($remove_content['project_url']); ?>"><?php echo html_escape($remove_content['project_title']); ?></a>
                <?php endif; ?>
                <?php foreach($remove_content as $key => $content): ?>
                    <?php if (is_int($key)): ?>
                        <div class="description">
                            <img class="column image" src="<?php echo html_escape($content['image_url']); ?>">
                            <div class="column text">
                                <div class="name">
                                    <?php
                                    echo html_escape($content['name']);
                                    if (isset($content['language'])) {
                                        echo '<span class="language"> (' . html_escape($content['language']) . ')</span>';
                                    }
                                    ?></div>
                                <div class="description"><?php echo html_escape($content['text']); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </fieldset>
    </div>
    <?php endif; ?>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" name="submit" id="submit" value="Submit" class="big green button">
        <input type="hidden" name="lc_api_key" value="<?php echo html_escape($lc_api_key); ?>"/>
    </div>
</section>
</form>

<?php echo foot(); ?>
