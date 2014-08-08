<?php

/**
 * WordPress Settings Framework
 *
 */

if(!class_exists('example_plugin_settings')) :
	/**
	 * example_plugin_settings class
	 */
	class example_plugin_settings {

		/**
		 * @access private
		 * @var array
		 */
		private $settings;

		/**
		 * @access private
		 * @var string
		 */
		private $option_group;

		/**
		 * @access protected
		 * @var array
		 */
		protected $setting_defaults = array(
			'id'          => 'default_field',
			'title'       => 'Default Field',
			'desc'        => '',
			'std'         => '',
			'type'        => 'text',
			'placeholder' => '',
			'choices'     => array(),
			'class'       => ''
		);

		/**
		 * @var bool
		 */
		public $show_reset_button = TRUE;

		/**
		 * @var bool
		 */
		public $show_uninstall_button = TRUE;

		/**
		 * Constructor
		 *
		 * @param $settings_file string path to settings page file
		 * @param $option_group  string optional "option_group" override
		 */
		public function __construct($settings_file, $option_group = '') {
			global $example_plugin;
			if(!is_file($settings_file)) {
				exit(__('Settings file could not be found.', 'example-plugin'));
			}
			require_once($settings_file);

			// use the manually specified option_group name or generate one based on the filename
			if($option_group) {
				$this->option_group = $option_group;
			} else {
				$this->option_group = $example_plugin->get_option_group(basename($settings_file, '.php'));
			}

			$this->settings = array();
			$this->settings = apply_filters('example_plugin_register_settings', $this->settings);
			if(!is_array($this->settings)) {
				exit(__('Settings framework must be an array', 'example-plugin'));
			}

			add_action('admin_init', array($this, 'admin_init'));
			//add_action('admin_notices', array($this, 'admin_notices'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
		}

		/**
		 * Get the option group for this instance
		 *
		 * @return string the "option_group"
		 */
		public function get_option_group() {
			return $this->option_group;
		}

		/**
		 * Registers the internal WordPress settings
		 */
		public function admin_init() {
			register_setting($this->option_group, $this->option_group, array($this, 'settings_validate'));
			$this->process_settings();
		}

		/**
		 * Displays any errors from the WordPress settings API
		 */
		public function admin_notices() {
			settings_errors();
		}

		/**
		 * Enqueue scripts and styles
		 */
		public function admin_enqueue_scripts() {
			wp_enqueue_style('farbtastic');
			wp_enqueue_style('thickbox');

			wp_enqueue_script('jquery');
			wp_enqueue_script('farbtastic');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}

		/**
		 * Adds a filter for settings validation
		 *
		 * @param $input array the un-validated settings
		 *
		 * @return array the validated settings
		 */
		public function settings_validate($input) {
			return apply_filters($this->option_group.'_validate', $input);
		}

		/**
		 * Displays the "section_description" if specified in $this->settings
		 *
		 * @param array callback args from add_settings_section()
		 */
		public function section_intro($args) {
			if(!empty($this->settings)) {
				foreach($this->settings as $section) {
					if($section['section_id'] == $args['id']) {
						if(isset($section['section_description']) && $section['section_description']) {
							echo '<p>'.$section['section_description'].'</p>';
						}
						break;
					}
				}
			}
		}

		/**
		 * Processes $this->settings and adds the sections and fields via the WordPress settings API
		 */
		private function process_settings() {
			if(!empty($this->settings)) {
				usort($this->settings, array($this, 'sort_array'));
				foreach($this->settings as $section) {
					if(isset($section['section_id']) && $section['section_id'] && isset($section['section_title'])) {
						add_settings_section($section['section_id'], $section['section_title'], array($this, 'section_intro'), $this->option_group);
						if(isset($section['fields']) && is_array($section['fields']) && !empty($section['fields'])) {
							foreach($section['fields'] as $field) {
								if(isset($field['id']) && $field['id'] && isset($field['title'])) {
									add_settings_field($field['id'], $field['title'], array(
										$this,
										'generate_setting'
									), $this->option_group, $section['section_id'], array(
										'section' => $section,
										'field'   => $field
									));
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Usort callback. Sorts $this->settings by "section_order"
		 *
		 * @param  $a mixed section order a
		 * @param  $b mixed section order b
		 *
		 * @return int order
		 */
		public function sort_array($a, $b) {
			return $a['section_order'] > $b['section_order'];
		}

		/**
		 * Generates the HTML output of the settings fields
		 *
		 * @param array callback args from add_settings_field()
		 */
		public function generate_setting($args) {
			$section = $args['section'];
			$this->setting_defaults = apply_filters('example_plugin_defaults', $this->setting_defaults);
			extract(wp_parse_args($args['field'], $this->setting_defaults));

			$options = get_option($this->option_group);
			$field_id = $this->option_group.'_'.$section['section_id'].'_'.$id;
			$val = (isset($options[$field_id])) ? $options[$field_id] : $std;

			do_action('example_plugin_before_field');
			do_action('example_plugin_before_field_'.$field_id);
			switch($type) {
				case 'text':
					$val = esc_attr(stripslashes($val));
					echo '<input type="text" name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" value="'.$val.'" placeholder="'.$placeholder.'" class="regular-text '.$class.'" />';
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'password':
					$val = esc_attr(stripslashes($val));
					echo '<input type="password" name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" value="'.$val.'" placeholder="'.$placeholder.'" class="regular-text '.$class.'" />';
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'textarea':
					$val = esc_html(stripslashes($val));
					echo '<textarea name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" placeholder="'.$placeholder.'" rows="5" cols="60" class="'.$class.'">'.$val.'</textarea>';
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'select':
					$val = esc_html(esc_attr($val));
					echo '<select name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" class="'.$class.'">';
					foreach($choices as $ckey => $cval) {
						echo '<option value="'.$ckey.'"'.(($ckey == $val) ? ' selected="selected"' : '').'>'.$cval.'</option>';
					}
					echo '</select>';
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'radio':
					$val = esc_html(esc_attr($val));
					foreach($choices as $ckey => $cval) {
						echo '<label><input type="radio" name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'_'.$ckey.'" value="'.$ckey.'" class="'.$class.'"'.(($ckey == $val) ? ' checked="checked"' : '').' /> '.$cval.'</label><br />';
					}
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'checkbox':
					$val = esc_attr(stripslashes($val));
					echo '<input type="hidden" name="'.$this->option_group.'['.$field_id.']" value="0" />';
					echo '<label><input type="checkbox" name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" value="1" class="'.$class.'"'.(($val) ? ' checked="checked"' : '').' /> '.$desc.'</label>';
					break;
				case 'checkboxes':
					foreach($choices as $ckey => $cval) {
						$val = '';
						if(isset($options[$field_id.'_'.$ckey])) {
							$val = $options[$field_id.'_'.$ckey];
						} elseif(is_array($std) && in_array($ckey, $std)) {
							$val = $ckey;
						}
						$val = esc_html(esc_attr($val));
						echo '<input type="hidden" name="'.$this->option_group.'['.$field_id.'_'.$ckey.']" value="0" />';
						echo '<label><input type="checkbox" name="'.$this->option_group.'['.$field_id.'_'.$ckey.']" id="'.$field_id.'_'.$ckey.'" value="'.$ckey.'" class="'.$class.'"'.(($ckey == $val) ? ' checked="checked"' : '').' /> '.$cval.'</label><br />';
					}
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'color':
					$val = esc_attr(stripslashes($val));
					echo '<div style="position:relative;">';
					echo '<input type="text" name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" value="'.$val.'" class="'.$class.'" />';
					echo '<div id="'.$field_id.'_cp" style="position:absolute;top:0;left:190px;background:#fff;z-index:9999;"></div>';
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					echo '<script type="text/javascript">
    		        jQuery(document).ready(function($){
                        var colorPicker = $("#'.$field_id.'_cp");
                        colorPicker.farbtastic("#'.$field_id.'");
                        colorPicker.hide();
                        $("#'.$field_id.'").live("focus", function(){
                            colorPicker.show();
                        });
                        $("#'.$field_id.'").live("blur", function(){
                            colorPicker.hide();
                            if($(this).val() == "") $(this).val("#");
                        });
                    });
                    </script></div>';
					break;
				case 'file':
					$val = esc_attr($val);
					echo '<input type="text" name="'.$this->option_group.'['.$field_id.']" id="'.$field_id.'" value="'.$val.'" class="regular-text '.$class.'" /> ';
					echo '<input type="button" class="button wpsf-browse" id="'.$field_id.'_button" value="Browse" />';
					echo '<script type="text/javascript">
                    jQuery(document).ready(function($){
                		$("#'.$field_id.'_button").click(function() {
                			tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");
                			window.original_send_to_editor = window.send_to_editor;
                        	window.send_to_editor = function(html) {
                        		var imgurl = $("img",html).attr("src");
                        		$("#'.$field_id.'").val(imgurl);
                        		tb_remove();
                        		window.send_to_editor = window.original_send_to_editor;
                        	};
                			return false;
                		});
                    });
                    </script>';
					break;
				case 'editor':
					wp_editor($val, $field_id, array('textarea_name' => $this->option_group.'['.$field_id.']'));
					if($desc) {
						echo '<p class="description">'.$desc.'</p>';
					}
					break;
				case 'custom':
					echo $std;
					break;
				default:
					// action to add custom field types
					do_action('example_plugin_custom_field');
					break;
			}
			do_action('example_plugin_after_field');
			do_action('example_plugin_after_field_'.$field_id);
		}

		/**
		 * Output the settings form
		 */
		public function settings() {

			if(isset($_POST['example_plugin_uninstall'])) {
				check_admin_referer('example-plugin-uninstall', 'example-plugin-uninstall-nonce');
				delete_option($this->option_group);
				?>
				<div class="updated">
					<p><?php _e('All options have been removed from the database.', 'example-plugin'); ?>

						<?php
						if(defined('EXAMPLE_PLUGIN_PLUGIN_SLUG') && EXAMPLE_PLUGIN_PLUGIN_SLUG != '') {
							$deactivate_url = 'plugins.php?action=deactivate&amp;plugin='.EXAMPLE_PLUGIN_PLUGIN_SLUG.'/'.EXAMPLE_PLUGIN_PLUGIN_SLUG.'.php';
							$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_'.EXAMPLE_PLUGIN_PLUGIN_SLUG.'/'.EXAMPLE_PLUGIN_PLUGIN_SLUG.'.php');
						} else {
							$deactivate_url = admin_url('plugins.php');
						}
						?>

						<?php printf(__('To complete the uninstall <a href="%1$s"">deactivate %2$s.</a>', 'example-plugin'), esc_url($deactivate_url), EXAMPLE_PLUGIN_PLUGIN_NAME); ?>
					</p>
				</div>
				<?php
				return;
			}

			if(isset($_POST['example_plugin_reset'])) {
				check_admin_referer('example-plugin-reset', 'example-plugin-reset-nonce');
				delete_option($this->option_group);
				?>
				<div class="updated">
					<p><?php _e('All options have been restored to their default values.', 'example-plugin'); ?></p>
				</div>
			<?php
			}

			do_action('example_plugin_before_settings');
			?>
			<form action="options.php" method="post">
				<?php do_action('example_plugin_before_settings_fields'); ?>
				<?php settings_fields($this->option_group); ?>
				<?php do_settings_sections($this->option_group); ?>
				<?php do_action('example_plugin_after_settings_fields'); ?>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'example-plugin'); ?>" />

					<?php if($this->show_reset_button == TRUE) : ?>
						<input class="button-secondary" type="button" value="<?php _e('Restore Defaults', 'example-plugin'); ?>" onclick="document.getElementById('example-plugin-reset').style.display = 'block';document.getElementById('example-plugin-uninst').style.display = 'none';" />
					<?php endif; ?>

					<?php if($this->show_uninstall_button == TRUE) : ?>
						<input class="button-secondary" type="button" value="<?php _e('Uninstall', 'example-plugin'); ?>" onclick="document.getElementById('example-plugin-uninst').style.display = 'block';document.getElementById('example-plugin-reset').style.display = 'none';" />
					<?php endif; ?>
				</p>
			</form>

			<div id="example-plugin-reset" style="display:none; clear: both;">
				<form method="post" action="">
					<?php wp_nonce_field('example-plugin-reset', 'example-plugin-reset-nonce'); ?>
					<label style="font-weight:normal;">
						<?php printf(__('Do you wish to <strong>completely reset</strong> the default options for', 'example-plugin')); ?> <?php echo EXAMPLE_PLUGIN_PLUGIN_NAME ?>? </label>
					<input class="button-secondary" type="button" name="cancel" value="<?php _e('Cancel', 'example-plugin'); ?>" onclick="document.getElementById('example-plugin-reset').style.display='none';" style="margin-left:20px" />
					<input class="button-primary" type="submit" name="example_plugin_reset" value="Restore Defaults" />
				</form>
			</div>
			<div id="example-plugin-uninst" style="display:none; clear: both;">
				<form method="post" action="">
					<?php wp_nonce_field('example-plugin-uninstall', 'example-plugin-uninstall-nonce'); ?>
					<label style="font-weight:normal;">
						<?php echo sprintf(__('Do you wish to <strong>completely uninstall</strong>', 'example-plugin')); ?> <?php echo EXAMPLE_PLUGIN_PLUGIN_NAME ?>?</label>
					<input class="button-secondary" type="button" name="cancel" value="<?php _e('Cancel', 'example-plugin'); ?>" onclick="document.getElementById('example-plugin-uninst').style.display = 'none';" style="margin-left:20px" />
					<input class="button-primary" type="submit" name="example_plugin_uninstall" value="Uninstall" />
				</form>
			</div>
			<?php
			do_action('example_plugin_after_settings');
		}
	}
endif;
