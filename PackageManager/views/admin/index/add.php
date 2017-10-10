<?php
$head = array('bodyclass' => 'package-manager primary',
              'title' => html_escape(__('Add a Package')),
              'content_class' => 'horizontal-nav');
echo head($head);
echo flash();
echo $form;
echo foot();
