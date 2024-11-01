<?php
/*
 * Plugin Name: vidget
 * Description: use wordpress bulitin video player in widgets
 * Plugin URI: http://wp-master.ir
 * Author: Omid Shamloo
 * Author URI: http://wp-master.ir
 * Version: 1.2
 * Text Domain: vidget
 */
defined('ABSPATH') or die('No script kiddies please!');

if (!defined('ABSPATH')) {
	exit;
}

add_action('plugins_loaded', array('vidget_plugin', 'get_instance'));
// add_action('init', array('vidget_plugin', 'init'));

class vidget_plugin {

	private static $instance = null;

	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_action('widgets_init', array($this, 'register_widget'));
		$this->init();
	}
	public  function init() {
		load_plugin_textdomain('vidget', false, dirname(plugin_basename(__FILE__)) . '/languages');
		__('vidget' , 'vidget');
		__('use wordpress bulitin video player in widgets' , 'vidget');


	}
	public function register_widget() {
		register_widget('vidget_widget');
	}

}

/**
 * Adds vidget_widget widget.
 */
class vidget_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'vidget_widget', // Base ID
			__('video/audio widget', 'vidget'), // Name
			array('description' => __('show yours videos/audios in your sidebars', 'vidget')) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) {
		echo $args['before_widget'];
		if (!empty($instance['title'])) {
			echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}
		extract($instance);
		$extra_attr = '';
		$shortcode = '';
		$poster = empty($poster) ? '' : $poster;
		$autoplay = $autoplay;
		$loop = ($loop == 1) ? 'on' : 'off';
		$preload = ($preload == 1) ? 'auto' : 'metadata';
		$height = empty($height) ? '' : $height;
		$width = empty($width) ? '' : $width;


		if($loop=='on'){
			$extra_attr .= ' loop="on" ';
		}
		if($autoplay==1){
			$extra_attr .= ' autoplay="1" ';
		}
		if($preload =='metadata'){
			$extra_attr .= ' preload="metadata" ';
		}

		if ($type == 'video') {
			$shortcode = '[video src="' . $src . '" poster="' . $poster . '" '.$extra_attr.' height="' . $height . '" width="' . $width . '"]';
		}
		if ($type == 'audio') {
			$shortcode = '[audio src="' . $src . '" '.$extra_attr.' ]';
		}
		echo do_shortcode($shortcode);

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		$title = !empty($instance['title']) ? $instance['title'] : __('Featured Video ','vidget');
		$src = !empty($instance['src']) ? $instance['src'] : '';
		$type = !empty($instance['type']) ? $instance['type'] : 'video';
		$poster = !empty($instance['poster']) ? $instance['poster'] : '';
		$loop = !empty($instance['loop']) ? $instance['loop'] : 'off'; // on - off
		$preload = !empty($instance['preload']) ? $instance['preload'] : '0'; // 1 - 0
		$autoplay = !empty($instance['autoplay']) ? $instance['autoplay'] : 0; //0 - 1
		wp_enqueue_media ();
		?>
		<style type="text/css">
			.vidget_option_toggle_controller
			{
				cursor: pointer;display: block;text-align: center;margin:10px !important;
			}
			.vidget_option_toggle {
				display: none;
				border: 1px solid #eee;
				padding: 10px;
				background: #fefefe;
			}
			.vidget_widget_wrapper label{min-width:110px;display: inline-block;}
		</style>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.vidget_option_toggle_controller').on('click',function(){
					var toToggle = $(this).siblings('.vidget_option_toggle').eq(0);
					toToggle.fadeIn();
				});
			    if ($('.vidget_upload_btn').length > 0) {
			        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
			            $(document).on('click', '.vidget_upload_btn', function(e) {
			                e.preventDefault();
			                var button = $(this);
			                var id = button.prev();
			                wp.media.editor.send.attachment = function(props, attachment) {
			                    id.val(attachment.url);
			                };
			                wp.media.editor.open(button);
			                return false;
			            });
			        }
			    }
			});
		</script>
		<div class="vidget_widget_wrapper">
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title', 'vidget');?>:</label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('src')); ?>"><?php esc_attr_e('URL', 'vidget');?>:</label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('src')); ?>" name="<?php echo esc_attr($this->get_field_name('src')); ?>" type="text" value="<?php echo esc_attr($src); ?>">
		<button class="vidget_upload_btn button"><?php _e('Upload','vidget'); ?></button>
		</p>
		<p class="vidget_option_toggle_controller button show-settings"><?php _e('Options', 'vidget');?></p>
		<div class="vidget_option_toggle">
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('type')); ?>"><?php esc_attr_e('Type', 'vidget');?>:</label>
		<select id="<?php echo esc_attr($this->get_field_id('type')); ?>" name="<?php echo esc_attr($this->get_field_name('type')); ?>">
			<option <?php selected($type, 'viedo', true);?> value="video"><?php _e('Video', 'vidget');?></option>
			<option <?php selected($type, 'audio', true);?> value="audio"><?php _e('Audio', 'vidget');?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('loop')); ?>"><?php esc_attr_e('loop', 'vidget');?>:</label>
		<input <?php checked(1, $loop, 1);?> class="widefat" id="<?php echo esc_attr($this->get_field_id('loop')); ?>" name="<?php echo esc_attr($this->get_field_name('loop')); ?>" type="checkbox" value="1">
		</p>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('autoplay')); ?>"><?php esc_attr_e('autoplay', 'vidget');?>:</label>
		<input <?php checked(1, $autoplay, 1);?> class="widefat" id="<?php echo esc_attr($this->get_field_id('autoplay')); ?>" name="<?php echo esc_attr($this->get_field_name('autoplay')); ?>" type="checkbox" value="1">
		</p>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('preload')); ?>"><?php esc_attr_e('preload', 'vidget');?>:</label>
		<input <?php checked(1, $preload, 1);?> class="widefat" id="<?php echo esc_attr($this->get_field_id('preload')); ?>" name="<?php echo esc_attr($this->get_field_name('preload')); ?>" type="checkbox" value="1">
		</p>
		<p>
			<h4><?php _e(' video options', 'vidget');?></h4>
				<hr>
				<p>
				<label for="<?php echo esc_attr($this->get_field_id('poster')); ?>"><?php esc_attr_e('poster', 'vidget');?>:</label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('poster')); ?>" name="<?php echo esc_attr($this->get_field_name('poster')); ?>" type="text" value="<?php echo esc_attr($poster); ?>">
				</p>
				<p>
				<label for="<?php echo esc_attr($this->get_field_id('width')); ?>"><?php esc_attr_e('width', 'vidget');?>(px):</label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('width')); ?>" name="<?php echo esc_attr($this->get_field_name('width')); ?>" type="text" value="<?php echo esc_attr($poster); ?>">
				</p>
				<p>
				<label for="<?php echo esc_attr($this->get_field_id('height')); ?>"><?php esc_attr_e('height', 'vidget');?>(px):</label>
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('height')); ?>" name="<?php echo esc_attr($this->get_field_name('height')); ?>" type="text" value="<?php echo esc_attr($poster); ?>">
				</p>
		</p>
		</div> <!-- toggle option -->
		</div> <!-- wrapper -->
		<?php
}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['src'] = (!empty($new_instance['src'])) ? strip_tags($new_instance['src']) : '';
		$instance['type'] = (!empty($new_instance['type'])) ? strip_tags($new_instance['type']) : 'video';
		$instance['poster'] = (!empty($new_instance['poster'])) ? strip_tags($new_instance['poster']) : '';
		$instance['loop'] = (!empty($new_instance['loop'])) ? strip_tags($new_instance['loop']) : 'off';
		$instance['preload'] = (!empty($new_instance['preload'])) ? strip_tags($new_instance['preload']) : 0;
		$instance['autoplay'] = (!empty($new_instance['autoplay'])) ? strip_tags($new_instance['autoplay']) : 'off';

		return $instance;
	}

} // class vidget_widget
