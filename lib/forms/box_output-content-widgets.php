<?php
if (is_active_sidebar('slide-in')) {
	echo '<div class="wdsi-slide-columns">';
	dynamic_sidebar('slide-in');
	echo '</div>';
} else {
	$content = apply_filters('wdsi-sidebar-empty_sidebar_text', __('Please, add some widgets to your sidebar.', 'wdsi'));
	if ($content) echo $content;
}