<?php
$head = array('bodyclass' => 'package-manager primary',
              'title' => __('Update package "%s"', metadata('package', 'name')),
              'content_class' => 'horizontal-nav');
echo head($head);

echo flash();
echo $form;
echo foot();
