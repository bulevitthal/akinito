<?php
//Recent Posts
if (!function_exists('shortcode_recent_posts')) {

	function shortcode_recent_posts($atts, $content = null) {
		extract(shortcode_atts(array(
				'type'             => 'post',
				'category'         => '',
				'custom_category'  => '',
				'post_format'      => 'standard',
				'num'              => '5',
				'meta'             => 'true',
				'thumb'            => 'true',
				'thumb_width'      => '120',
				'thumb_height'     => '120',
				'more_text_single' => '',
				'excerpt_count'    => '0',
				'custom_class'     => ''
		), $atts));

		$output = '<ul class="recent-posts '.$custom_class.' unstyled">';

		global $post;
		global $my_string_limit_words;

		// WPML filter
		$suppress_filters = get_option('suppress_filters');
		
		if($post_format == 'standard') {

			$args = array(
						'post_type'         => $type,
						'category_name'     => $category,
						$type . '_category' => $custom_category,
						'numberposts'       => $num,
						'orderby'           => 'post_date',
						'order'             => 'DESC',
						'tax_query'         => array(
						'relation'          => 'AND',
							array(
								'taxonomy' => 'post_format',
								'field'    => 'slug',
								'terms'    => array('post-format-aside', 'post-format-gallery', 'post-format-link', 'post-format-image', 'post-format-quote', 'post-format-audio', 'post-format-video'),
								'operator' => 'NOT IN'
							)
						),
						'suppress_filters' => $suppress_filters
					);
		
		} else {
		
			$args = array(
				'post_type'         => $type,
				'category_name'     => $category,
				$type . '_category' => $custom_category,
				'numberposts'       => $num,
				'orderby'           => 'post_date',
				'order'             => 'DESC',
				'tax_query'         => array(
				'relation'          => 'AND',
					array(
						'taxonomy' => 'post_format',
						'field'    => 'slug',
						'terms'    => array('post-format-' . $post_format)
					)
				),
				'suppress_filters' => $suppress_filters
			);
		}

		$latest = get_posts($args);
		
		foreach($latest as $k => $post) {
				// Unset not translated posts
				if ( function_exists( 'wpml_get_language_information' ) ) {
					global $sitepress;

					$check              = wpml_get_language_information( $post->ID );
					$language_code      = substr( $check['locale'], 0, 2 );
					if ( $language_code != $sitepress->get_current_language() ) unset( $latest[$k] );

					// Post ID is different in a second language Solution
					if ( function_exists( 'icl_object_id' ) ) $post = get_post( icl_object_id( $post->ID, $type, true ) );
				}
				setup_postdata($post);
				$excerpt        = get_the_excerpt();
				$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
				$url            = $attachment_url['0'];
				$image          = aq_resize($url, $thumb_width, $thumb_height, true);
				
				$post_classes = get_post_class();
				foreach ($post_classes as $key => $value) {
					$pos = strripos($value, 'tag-');
					if ($pos !== false) {
						unset($post_classes[$key]);
					}
				}
				$post_classes = implode(' ', $post_classes);

				$output .= '<li class="recent-posts_li ' . $post_classes . '">';
				
				//Aside
				if($post_format == "aside") {
					
					$output .= the_content($post->ID);
				
				} elseif ($post_format == "link") {
				
					$url =  get_post_meta(get_the_ID(), 'tz_link_url', true);
				
					$output .= '<a target="_blank" href="'. $url . '">';
					$output .= get_the_title($post->ID);
					$output .= '</a>';
				
				//Quote
				} elseif ($post_format == "quote") {
				
					$quote =  get_post_meta(get_the_ID(), 'tz_quote', true);
					
					$output .= '<div class="quote-wrap clearfix">';
							
							$output .= '<blockquote>';
								$output .= $quote;
							$output .= '</blockquote>';
							
					$output .= '</div>';
				
				//Image
				} elseif ($post_format == "image") {
				
				if (has_post_thumbnail() ) :
				
					// $lightbox = get_post_meta(get_the_ID(), 'tz_image_lightbox', TRUE);
					
					$src      = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), array( '9999','9999' ), false, '' );
					
					$thumb    = get_post_thumbnail_id();
					$img_url  = wp_get_attachment_url( $thumb,'full'); //get img URL
					$image    = aq_resize( $img_url, 200, 120, true ); //resize & crop img
					
					
					$output .= '<figure class="thumbnail featured-thumbnail large">';
						$output .= '<a class="image-wrap" rel="prettyPhoto" title="' . get_the_title($post->ID) . '" href="' . $src[0] . '">';
						$output .= '<img src="' . $image . '" alt="' . get_the_title($post->ID) .'" />';
						$output .= '<span class="zoom-icon"></span></a>';
					$output .= '</figure>';
				
				endif;
				
				
				//Audio
				} elseif ($post_format == "audio") {
				
					$template_url = get_template_directory_uri();
					$id           = $post->ID;
					
					// get audio attribute
					$audio_title  = get_post_meta(get_the_ID(), 'tz_audio_title', true);
					$audio_artist = get_post_meta(get_the_ID(), 'tz_audio_artist', true);
					$audio_format = get_post_meta(get_the_ID(), 'tz_audio_format', true);
					$audio_url    = get_post_meta(get_the_ID(), 'tz_audio_url', true);

					$content_url = content_url();
					$content_str = 'wp-content';
					
					$pos    = strpos($audio_url, $content_str);
					if ($pos === false) {
						$file = $audio_url;
					} else {
						$audio_new = substr($audio_url, $pos+strlen($content_str), strlen($audio_url) - $pos);
						$file      = $content_url.$audio_new;
					}
						
					$output .= '<script type="text/javascript">
						$(document).ready(function(){
							var myPlaylist_'. $id.'  = new jPlayerPlaylist({
							jPlayer: "#jquery_jplayer_'. $id .'",
							cssSelectorAncestor: "#jp_container_'. $id .'"
							}, [
							{
								title:"'. $audio_title .'",
								artist:"'. $audio_artist .'",
								'. $audio_format .' : "'. stripslashes(htmlspecialchars_decode($file)) .'"}
							], { 
								playlistOptions: {enableRemoveControls: false},
								ready: function () {$(this).jPlayer("setMedia", {'. $audio_format .' : "'. stripslashes(htmlspecialchars_decode($file)) .'", poster: "'. $image .'"});
							},
							swfPath: "'. $template_url .'/flash",
							supplied: "'. $audio_format .', all",
							wmode:"window"
							});
						});
						</script>';
						
					$output .= '<div id="jquery_jplayer_'.$id.'" class="jp-jplayer"></div>
								<div id="jp_container_'.$id.'" class="jp-audio">
									<div class="jp-type-single">
										<div class="jp-gui">
											<div class="jp-interface">
												<div class="jp-progress">
													<div class="jp-seek-bar">
														<div class="jp-play-bar"></div>
													</div>
												</div>
												<div class="jp-duration"></div>
												<div class="jp-time-sep"></div>
												<div class="jp-current-time"></div>
												<div class="jp-controls-holder">
													<ul class="jp-controls">
														<li><a href="javascript:;" class="jp-previous" tabindex="1" title="'.theme_locals("prev").'"><span>'.theme_locals("prev").'</span></a></li>
														<li><a href="javascript:;" class="jp-play" tabindex="1" title="'.theme_locals("play").'"><span>'.theme_locals("play").'</span></a></li>
														<li><a href="javascript:;" class="jp-pause" tabindex="1" title="'.theme_locals("pause").'"><span>'.theme_locals("pause").'</span></a></li>
														<li><a href="javascript:;" class="jp-next" tabindex="1" title="'.theme_locals("next").'"><span>'.theme_locals("next").'</span></a></li>
														<li><a href="javascript:;" class="jp-stop" tabindex="1" title="'.theme_locals("stop").'"><span>'.theme_locals("stop").'</span></a></li>
													</ul>
													<div class="jp-volume-bar">
														<div class="jp-volume-bar-value"></div>
													</div>
													<ul class="jp-toggles">
														<li><a href="javascript:;" class="jp-mute" tabindex="1" title="'.theme_locals("mute").'"><span>'.theme_locals("mute").'</span></a></li>
														<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="'.theme_locals("unmute").'"><span>'.theme_locals("unmute").'</span></a></li>
													</ul>
												</div>
											</div>
											<div class="jp-no-solution">
												'.theme_locals("update_required").'
											</div>
										</div>
									</div>
									<div class="jp-playlist">
										<ul>
											<li></li>
										</ul>
									</div>
								</div>';
				
				
				$output .= '<div class="entry-content">';
					$output .= get_the_content($post->ID);
				$output .= '</div>';
				
				//Video
				} elseif ($post_format == "video") {
					
					$template_url = get_template_directory_uri();
					$id           = $post->ID;
				
					// get video attribute
					$video_title  = get_post_meta(get_the_ID(), 'tz_video_title', true);
					$video_artist = get_post_meta(get_the_ID(), 'tz_video_artist', true);
					$embed        = get_post_meta(get_the_ID(), 'tz_video_embed', true);
					$m4v_url      = get_post_meta(get_the_ID(), 'tz_m4v_url', true);
					$ogv_url      = get_post_meta(get_the_ID(), 'tz_ogv_url', true);

					$content_url = content_url();
					$content_str = 'wp-content';
					
					$pos1 = strpos($m4v_url, $content_str);
					if ($pos1 === false) {
						$file1 = $m4v_url;
					} else {
						$m4v_new  = substr($m4v_url, $pos1+strlen($content_str), strlen($m4v_url) - $pos1);
						$file1    = $content_url.$m4v_new;
					}

					$pos2 = strpos($ogv_url, $content_str);
					if ($pos2 === false) {
						$file2 = $ogv_url;
					} else {
						$ogv_new  = substr($ogv_url, $pos2+strlen($content_str), strlen($ogv_url) - $pos2);
						$file2    = $content_url.$ogv_new;
					}
					
					// get thumb
					if(has_post_thumbnail()) {
						$thumb   = get_post_thumbnail_id();
						$img_url = wp_get_attachment_url( $thumb,'full'); //get img URL
						$image   = aq_resize( $img_url, 770, 380, true ); //resize & crop img
					}

					if ($embed == '') {
						$output .= '<script type="text/javascript">
							$(document).ready(function(){
								$("#jquery_jplayer_'. $id.'").jPlayer({
									ready: function () {
										$(this).jPlayer("setMedia", {
											m4v: "'. stripslashes(htmlspecialchars_decode($file1)) .'",
											ogv: "'. stripslashes(htmlspecialchars_decode($file2)) .'",
											poster: "'. $image .'"
										});
									},
									swfPath: "'. $template_url .'/flash",
									solution: "flash, html",
									supplied: "ogv, m4v, all",
									cssSelectorAncestor: "#jp_container_'. $id.'",
									size: {
										width: "100%",
										height: "100%"
									}
								});
							});
							</script>';
							$output .= '<div id="jp_container_'. $id .'" class="jp-video fullwidth">';
							$output .= '<div class="jp-type-list-parent">';
							$output .= '<div class="jp-type-single">';
							$output .= '<div id="jquery_jplayer_'. $id .'" class="jp-jplayer"></div>';
							$output .= '<div class="jp-gui">';
							$output .= '<div class="jp-video-play">';
							$output .= '<a href="javascript:;" class="jp-video-play-icon" tabindex="1" title="'.theme_locals("play").'">'.theme_locals("play").'</a></div>';
							$output .= '<div class="jp-interface">';
							$output .= '<div class="jp-progress">';
							$output .= '<div class="jp-seek-bar">';
							$output .= '<div class="jp-play-bar">';
							$output .= '</div></div></div>';
							$output .= '<div class="jp-duration"></div>';
							$output .= '<div class="jp-time-sep">/</div>';
							$output .= '<div class="jp-current-time"></div>';
							$output .= '<div class="jp-controls-holder">';
							$output .= '<ul class="jp-controls">';
							$output .= '<li><a href="javascript:;" class="jp-play" tabindex="1" title="'.theme_locals("play").'"><span>'.theme_locals("play").'</span></a></li>';
							$output .= '<li><a href="javascript:;" class="jp-pause" tabindex="1" title="'.theme_locals("pause").'"><span>'.theme_locals("pause").'</span></a></li>';
							$output .= '<li class="li-jp-stop"><a href="javascript:;" class="jp-stop" tabindex="1" title="'.theme_locals("stop").'"><span>'.theme_locals("stop").'</span></a></li>';
							$output .= '</ul>';
							$output .= '<div class="jp-volume-bar">';
							$output .= '<div class="jp-volume-bar-value">';
							$output .= '</div></div>';
							$output .= '<ul class="jp-toggles">';
							$output .= '<li><a href="javascript:;" class="jp-mute" tabindex="1" title="'.theme_locals("mute").'"><span>'.theme_locals("mute").'</span></a></li>';
							$output .= '<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="'.theme_locals("unmute").'"><span>'.theme_locals("unmute").'</span></a></li>';
							$output .= '</ul>';
							$output .= '</div></div>';
							$output .= '<div class="jp-no-solution">';
							$output .= theme_locals("update_required");
							$output .= '</div></div></div></div>';
							$output .= '</div>';
					} else {
						$output .= '<div class="video-wrap">' . stripslashes(htmlspecialchars_decode($embed)) . '</div>';
					}
					
					if($excerpt_count >= 1){
						$output .= '<div class="excerpt">';
							$output .= my_string_limit_words($excerpt,$excerpt_count);
						$output .= '</div>';
				}
				
				//Standard
				} else {
				
					if ($thumb == 'true') {
						if ( has_post_thumbnail($post->ID) ){
							$output .= '<figure class="thumbnail featured-thumbnail"><a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
							$output .= '<img  src="'.$image.'"/>';
							$output .= '</a></figure>';
						}
					}
					$output .= '<h5><a href="'.get_permalink($post->ID).'" title="'.get_the_title($post->ID).'">';
							$output .= get_the_title($post->ID);
					$output .= '</a></h5>';
					if ($meta == 'true') {
							$output .= '<span class="meta">';
									$output .= '<span class="post-date">';
										$output .= get_the_time( get_option( 'date_format' ) );
									$output .= '</span>';
									$output .= '<span class="post-comments">';
										$output .= '<a href="'.get_comments_link($post->ID).'">';
											$output .= get_comments_number($post->ID);
										$output .= '</a>';
									$output .= '</span>';
							$output .= '</span>';
					}
					if ($excerpt_count >= 1) {
						$output .= '<div class="excerpt">';
							$output .= my_string_limit_words($excerpt,$excerpt_count);
						$output .= '</div>';
					}
					if ($more_text_single!="") {
						$output .= '<a href="'.get_permalink($post->ID).'" class="btn btn-primary" title="'.get_the_title($post->ID).'">';
						$output .= $more_text_single;
						$output .= '</a>';
					}
				}
			$output .= '<div class="clear"></div>';
			$output .= '</li><!-- .entry (end) -->';
		}
		$output .= '</ul><!-- .recent-posts (end) -->';
		return $output;
	}
	add_shortcode('recent_posts', 'shortcode_recent_posts');
}


// Recent Comments
if (!function_exists('shortcode_recent_comments')) {

	function shortcode_recent_comments($atts, $content = null) {
		extract(shortcode_atts(array(
			'num'          => '5',
			'custom_class' => ''
		), $atts));

		global $wpdb;

		if ( function_exists( 'wpml_get_language_information' ) ) {
			global $sitepress;
			$sql = "
				SELECT * FROM {$wpdb->comments}
				JOIN {$wpdb->prefix}icl_translations 
				ON {$wpdb->comments}.comment_post_id = {$wpdb->prefix}icl_translations.element_id 
				AND {$wpdb->prefix}icl_translations.element_type='post_post' 
				WHERE comment_approved = '1' 
				AND language_code = '".$sitepress->get_current_language()."' 
				ORDER BY comment_date_gmt DESC LIMIT {$num}";
		} else {
			$sql = "
				SELECT * FROM $wpdb->comments
				LEFT OUTER JOIN $wpdb->posts 
				ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
				WHERE comment_approved = '1' 
				AND comment_type = '' 
				AND post_password = ''
				ORDER BY comment_date_gmt DESC LIMIT {$num}";
		}
		
		$comment_len = 100;
		$comments = $wpdb->get_results($sql);

		$output = '<ul class="recent-comments unstyled">';

		foreach ($comments as $comment) {
			$output .= '<li>';
				$output .= '<a href="'.get_comment_link($comment->comment_ID).'" title="on '.get_the_title($comment->comment_post_ID).'">';
					$output .= strip_tags($comment->comment_author).' : ' . strip_tags(substr(apply_filters('get_comment_text', $comment->comment_content), 0, $comment_len)); 
					if (strlen($comment->comment_content) > $comment_len) $output .= '...';
				$output .= '</a>';
			$output .= '</li>';
		}

		$output .= '</ul>';
		return $output;
	}
	add_shortcode('recent_comments', 'shortcode_recent_comments');
}


//Recent Testimonials
if (!function_exists('shortcode_recenttesti')) {

	function shortcode_recenttesti($atts, $content = null) {
		extract(shortcode_atts(array(
				'num'           => '5',
				'thumb'         => 'true',
				'excerpt_count' => '30',
				'custom_class'  => '',
		), $atts));

		// WPML filter
		$suppress_filters = get_option('suppress_filters');

		$args = array(
				'post_type'        => 'testi',
				'numberposts'      => $num,
				'orderby'          => 'post_date',
				'suppress_filters' => $suppress_filters
			);
		$testi = get_posts($args);

		$output = '<div class="testimonials '.$custom_class.'">';
		
		global $post;
		global $my_string_limit_words;

		foreach ($testi as $k => $post) {
			// Unset not translated posts
			if ( function_exists( 'wpml_get_language_information' ) ) {
				global $sitepress;

				$check              = wpml_get_language_information( $post->ID );
				$language_code      = substr( $check['locale'], 0, 2 );
				if ( $language_code != $sitepress->get_current_language() ) unset( $testi[$k] );

				// Post ID is different in a second language Solution
				if ( function_exists( 'icl_object_id' ) ) $post = get_post( icl_object_id( $post->ID, 'testi', true ) );
			}
			setup_postdata($post);
			$excerpt        = get_the_excerpt();
			$testiname      = get_post_meta(get_the_ID(), 'my_testi_caption', true);
			$testiurl       = get_post_meta(get_the_ID(), 'my_testi_url', true);
			$testiinfo      = get_post_meta(get_the_ID(), 'my_testi_info', true);
			$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
			$url            = $attachment_url['0'];
			$image          = aq_resize($url, 100, 100, true);

			$output .= '<div class="testi-item">';
				$output .= '<blockquote class="testi-item_blockquote">';
					if ($thumb == 'true') {
						if ( has_post_thumbnail($post->ID) ){
							$output .= '<figure class="featured-thumbnail">';
							$output .= '<img  src="'.$image.'"/>';
							$output .= '</figure>';
						}
					}
					$output .= '<small class="testi-meta">';
					if( isset($testiname) ) { 
						$output .= '<span class="user">';
							$output .= $testiname;
						$output .= '</span>';
					}
					
					if( isset($testiinfo) ) { 
						$output .= ', <span class="info">';
							$output .= $testiinfo;
						$output .= '</span><br>';
					}
					
					if( isset($testiurl) ) { 
						$output .= '<a href="'.$testiurl.'">';
							$output .= $testiurl;
						$output .= '</a>';
					}
					
				$output .= '</small><div class="clear"></div>';
					$output .= '<a href="'.get_permalink($post->ID).'">"... ';
						$output .= my_string_limit_words($excerpt,$excerpt_count);
					$output .= '"</a>';

				$output .= '</blockquote>';

				
					
			$output .= '</div>';

		}
		$output .= '</div>';
		return $output;
	}
	add_shortcode('recenttesti', 'shortcode_recenttesti');

}


//Tag Cloud
if (!function_exists('shortcode_tags')) {

	function shortcode_tags($atts, $content = null) {
		$output = '<div class="tags-cloud clearfix">';
		$tags = wp_tag_cloud('smallest=8&largest=8&format=array');

		foreach($tags as $tag){
			$output .= $tag.' ';
		}

		$output .= '</div><!-- .tags-cloud (end) -->';
		return $output;
	}
	add_shortcode('tags', 'shortcode_tags');

}


// Audio Player
if (!function_exists('shortcode_audio')) {

	function shortcode_audio($atts, $content = null) {
		extract(shortcode_atts(array(
			'type' => '',
			'file' => '',
			'title' => ''
		), $atts));
		
		$template_url = get_template_directory_uri();
		$id = rand();

		// get audio attribute
		$content_url = content_url();
		$content_str = 'wp-content';
		
		$pos = strpos($file, $content_str);
		if ($pos !== false) {
			$audio_new = substr($file, $pos+strlen($content_str), strlen($file) - $pos);
			$file      = $content_url.$audio_new;
		}
		
		$output = '<div class="audio-wrap">';
		$output .= '<script type="text/javascript">
						jQuery(document).ready(function(){
							if(jQuery().jPlayer) {
								jQuery("#jquery_jplayer_'. $id .'").jPlayer( {
									ready: function () {
										jQuery(this).jPlayer("setMedia", {'.
											$type .': "'. $file .'",
											end: ""
										});
									},
									play: function() {
										jQuery(this).jPlayer("pauseOthers");
									},
									swfPath: "'. $template_url .'/flash",
									wmode: "window",
									cssSelectorAncestor: "#jp_container_'. $id .'",
									supplied: "'. $type .',  all"
								});
							}
						});
					</script>';

		$output .= '<div id="jquery_jplayer_'. $id .'" class="jp-jplayer"></div>';
		$output .= '<div id="jp_container_'. $id .'" class="jp-audio">';
		$output .= '<div class="jp-type-single">';
		$output .= '<div class="jp-gui">';
		$output .= '<div class="jp-interface">';
		$output .= '<div class="jp-progress">';
		$output .= '<div class="jp-seek-bar"><div class="jp-play-bar"></div></div>';
		$output .= '</div>';
		$output .= '<div class="jp-duration"></div><div class="jp-time-sep"></div><div class="jp-current-time"></div>';
		$output .= '<div class="jp-controls-holder">';
		$output .= '<ul class="jp-controls">';
		$output .= '<li><a href="javascript:;" class="jp-play" tabindex="1" title="'.theme_locals("play").'"><span>'.theme_locals("play").'</span></a></li>';
		$output .= '<li><a href="javascript:;" class="jp-pause" tabindex="1" title="'.theme_locals("pause").'"><span>'.theme_locals("pause").'</span></a></li>';
		$output .= '<li><a href="javascript:;" class="jp-stop" tabindex="1" title="'.theme_locals("stop").'"><span>'.theme_locals("stop").'</span></a></li>';
		$output .= '</ul>';
		$output .= '<div class="jp-volume-bar">';
		$output .= '<div class="jp-volume-bar-value">';
		$output .= '</div></div>';
		$output .= '<ul class="jp-toggles">';
		$output .= '<li><a href="javascript:;" class="jp-mute" tabindex="1" title="'.theme_locals("mute").'"><span>'.theme_locals("mute").'</span></a></li>';
		$output .= '<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="'.theme_locals("unmute").'""><span>'.theme_locals("unmute").'</span></a></li>';
		$output .= '</ul>';
		$output .= '<div class="jp-title"><ul><li>'. $title .'</li></ul></div></div>';
		$output .= '</div>';
		$output .= '<div class="jp-no-solution">';
		$output .= theme_locals("update_required");
		$output .= '</div></div></div></div>';
		
		$output .= '</div><!-- .audio-wrap (end) -->';

		return $output;

	}
	add_shortcode('audio', 'shortcode_audio');

}


// Video Player
if (!function_exists('shortcode_video')) {

	function shortcode_video($atts, $content = null) {
		extract(shortcode_atts(array(
			'file'   => '',
			'm4v'    => '',
			'ogv'    => '',
			'width'  => '600',
			'height' => '350',
		), $atts));

		$template_url = get_template_directory_uri();
		$id = rand();

		$video_url = $file;
		$m4v_url   = $m4v;
		$ogv_url   = $ogv;

		// get content URL
		$content_url = content_url();
		$content_str = 'wp-content';

		$pos1     = strpos($m4v_url, $content_str);
		if ($pos1 === false) {
			$file1 = $m4v_url;
		} else {
			$m4v_new  = substr($m4v_url, $pos1+strlen($content_str), strlen($m4v_url) - $pos1);
			$file1    = $content_url.$m4v_new;
		}

		$pos2     = strpos($ogv_url, $content_str);
		if ($pos2 === false) {
			$file2 = $ogv_url;
		} else {
			$ogv_new  = substr($ogv_url, $pos2+strlen($content_str), strlen($ogv_url) - $pos2);
			$file2    = $content_url.$ogv_new;
		}

		//Check for video format
		$vimeo   = strpos($video_url, "vimeo");
		$youtube = strpos($video_url, "youtu");

		$output = '<div class="video-wrap">';

		//Display video
		if ($file) {
			if($vimeo !== false){

			//Get ID from video url
			$video_id = str_replace( 'http://vimeo.com/', '', $video_url );
			$video_id = str_replace( 'http://www.vimeo.com/', '', $video_id );

			//Display Vimeo video
			$output .= '<iframe src="http://player.vimeo.com/video/'.$video_id.'?title=0&amp;byline=0&amp;portrait=0" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>';

			} elseif($youtube !== false){

			//Get ID from video url
			$video_id = str_replace( 'http://', '', $video_url );
			$video_id = str_replace( 'https://', '', $video_id );
			$video_id = str_replace( 'www.youtube.com/watch?v=', '', $video_id );
			$video_id = str_replace( 'youtube.com/watch?v=', '', $video_id );
			$video_id = str_replace( 'youtu.be/', '', $video_id );
			$video_id = str_replace( '&feature=channel', '', $video_id );

			$output .= '<iframe title="YouTube video player" class="youtube-player" type="text/html" width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$video_id.'" frameborder="0"></iframe>';

			}
		} else {

			$output .= '<script type="text/javascript">
						jQuery(document).ready(function(){
							if(jQuery().jPlayer) {
								jQuery("#jquery_jplayer_'. $id .'").jPlayer( {
									ready: function () {
										jQuery(this).jPlayer("setMedia", {
											m4v: "'. $file1 .'",
											ogv: "'. $file2 .'"
										});
									},
									play: function() {
										jQuery(this).jPlayer("pauseOthers");
									},
									swfPath: "'. $template_url .'/flash",
									wmode: "window",
									cssSelectorAncestor: "#jp_container_'. $id .'",
									solution: "html, flash",
									supplied: "ogv, m4v, all",
									size: {width: "100%", height: "100%"}
								});
							}
						});
					</script>';
			$output .= '<div id="jp_container_'. $id .'" class="jp-video fullwidth">';
			$output .= '<div class="jp-type-single">';
			$output .= '<div id="jquery_jplayer_'. $id .'" class="jp-jplayer"></div>';
			$output .= '<div class="jp-gui">';
			$output .= '<div class="jp-video-play">';
			$output .= '<a href="javascript:;" class="jp-video-play-icon" tabindex="1" title="'.theme_locals("play").'">'.theme_locals("play").'</a></div>';
			$output .= '<div class="jp-interface">';
			$output .= '<div class="jp-progress">';
			$output .= '<div class="jp-seek-bar">';
			$output .= '<div class="jp-play-bar">';
			$output .= '</div></div></div>';
			$output .= '<div class="jp-duration"></div>';
			$output .= '<div class="jp-time-sep">/</div>';
			$output .= '<div class="jp-current-time"></div>';
			$output .= '<div class="jp-controls-holder">';
			$output .= '<ul class="jp-controls">';
			$output .= '<li><a href="javascript:;" class="jp-play" tabindex="1" title="'.theme_locals("play").'"><span>'.theme_locals("play").'</span></a></li>';
			$output .= '<li><a href="javascript:;" class="jp-pause" tabindex="1" title="'.theme_locals("pause").'"><span>'.theme_locals("pause").'</span></a></li>';
			$output .= '<li class="li-jp-stop"><a href="javascript:;" class="jp-stop" tabindex="1" title="'.theme_locals("stop").'"><span>'.theme_locals("stop").'</span></a></li>';
			$output .= '</ul>';
			$output .= '<div class="jp-volume-bar">';
			$output .= '<div class="jp-volume-bar-value">';
			$output .= '</div></div>';
			$output .= '<ul class="jp-toggles">';
			$output .= '<li><a href="javascript:;" class="jp-mute" tabindex="1" title="'.theme_locals("mute").'"><span>'.theme_locals("mute").'</span></a></li>';
			$output .= '<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="'.theme_locals("unmute").'"><span>'.theme_locals("unmute").'</span></a></li>';
			$output .= '</ul>';
			$output .= '</div></div>';
			$output .= '<div class="jp-no-solution">';
			$output .= theme_locals("update_required");
			$output .= '</div></div></div></div>'; 

		}
		$output .= '</div><!-- .video-wrap (end) -->';
		return $output;
	}
	add_shortcode('video', 'shortcode_video');
}
	if (!function_exists('shortcode_video_preview')) {
		function shortcode_video_preview($atts, $content = null) {
			extract(shortcode_atts(
				array(
					'post_url' => '',
					'title' => '',
					'date' => '',
					'author' => '',
					'custom_class' => '',
			), $atts));
			$output_title = '';
			$output_author = '';
			$output_date = '';
			$post_ID = url_to_postid($post_url);
			$post = get_post($post_ID);
			$get_user = get_userdata($post->post_author);
			$user_url = get_bloginfo('url').'/author/'.$get_user->user_nicename;
			$video_url = parser_video_url(get_post_meta($post_ID, 'tz_video_embed', true));
			$get_image_url = video_image($video_url);

			if($title=="yes"){
				$output_title = '<h4><a href="'.$post_url.'" title="'.$post->post_title.'">'.$post->post_title.'</a></h4>';
			}
			if($title=="yes"){
				$output_author = '<span class="post_author">Posts by <a href="'.$user_url.'" title="Posts by '.$get_user->user_nicename.'"  rel="author">'.$get_user->user_nicename.'</a></span>';
			}
			if($title=="yes"){
				$output_date = '<span class="post_date"><time datetime="'.$post->post_date.'"> '.get_the_time('M j, Y', $post_ID).'</time></span>';
			}
			$output ='<figure class="featured-thumbnail thumbnail video_preview '.$custom_class.'"><div><a class="preview_image"  href="'.$post_url.'" title="'.$get_image_url.'"><img src="'.$get_image_url.'" alt=""><span class="icon-play-circle hover"></span></a><figcaption>'.$output_title.$output_author.$output_date.'</figcaption></div></figure>';

			return $output;
		}
		add_shortcode('video_preview', 'shortcode_video_preview');
	}
	if (!function_exists('parser_video_url')) {
		function parser_video_url($video_url){
			$video_url = explode(" ", $video_url);
			foreach ($video_url as $item) {
				if(stripos($item, 'src')!==false){
					$url_array = parse_url($item);
					$video_url = $url_array["path"];
					$video_url = stripcslashes($video_url);
					$video_url = strip_tags($video_url);
					$video_url = str_replace('&quot;', '', $video_url);
					break;
				}
			}
			return $video_url;
		}
	}
	if (!function_exists('video_image')) {
		function video_image($url){
			$image_id = basename($url);
			if(stripos($url, "youtube")!==false){
				return "http://img.youtube.com/vi/".$image_id."/0.jpg";
			} else if(stripos($url, "vimeo")!==false){
				$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$image_id.".php"));
				return $hash[0]["thumbnail_large"];
			}
		}
	}
?>