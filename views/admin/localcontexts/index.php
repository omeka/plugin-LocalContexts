<?php
/**
 * View for Local Contexts API Settings.
 */
$head = array('bodyclass' => 'primary', 
              'title' => html_escape(__('Local Contexts | API Settings')));
echo head($head);
?>

<?php echo flash(); ?>
<?php echo $this->form; ?>

<?php echo foot(); ?>
