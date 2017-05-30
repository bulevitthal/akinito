<?php
/**
 *
 *
 */

// Fon
function wrap_shortcode($atts, $content = null) {
	
	$output = '<div class="wrap">';
	$output .= do_shortcode($content);
	$output .= '</div> <!-- fon (end) -->';
   
	return $output;
}
add_shortcode('wrap', 'wrap_shortcode');


?>