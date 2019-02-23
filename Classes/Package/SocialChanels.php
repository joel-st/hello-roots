<?php

namespace SayHello\Theme\Package;

use SayHello\Theme\Package\SVG;

/**
 * Social-Channels and Social-Icons under Theme Settings
 *
 * @author Joel Stüdle <joel@sayhello.ch>
 */
class SocialChanels
{

	public $pfx;
	public $main_slug = '';
	public $general_slug = '';
	public $icon_dir = '';
	public $choices = [];

	public function __construct()
	{
		$this->pfx       = sht_theme()->prefix . '-social-icons';
		$this->main_slug = sht_theme()->prefix . '-settings';
		$this->general_slug = $this->main_slug . '-general';
		$this->icon_dir = get_template_directory() . "/assets/img/icons/social-icons/";
		$this->choices = $this->createOptions();
	}

	/**
	 * Hooks the acf/init action to add acf fields to theme settings page.
	 *
	 * @return void
	 */
	public function run()
	{
		add_action('acf/init', [$this, 'acfSocialChannels']);
	}

	/**
	 * This function scans the social-icon folder for icons and generates
	 * the option for the ACF channel icon select field
	 *
	 * @since    0.1.0
	 *
	 * @return Array
	 */
	public function createOptions()
	{
		if (is_dir($this->icon_dir)) {
			$dir_list = scandir($this->icon_dir);

			if (!empty($dir_list)) {
				$choices = [];

				foreach ($dir_list as $key => $item) {
					if (strpos($item, '.min.svg') !== false) {
						// extract channel name
						$item_name = str_replace('.min.svg', '', $item);

						// add to array if channel not already exist
						if (!$choices[$item_name]) {
							$choices[$item_name] = ucwords(str_replace('-', ' ', $item_name));
						}
					}
				}

				return $choices;
			}
		};
	}

	/**
	 * This function returns a list element built with the data from the acf repeater
	 *
	 * @since    0.1.0
	 *
	 * @param Array $channels empty for all channels or specific channel byname
	 * @param bool $echo echo the html or return array
	 * @param Array $classes classes to add to the <ul> element
	 *
	 * @return Array/String/HTML
	 */

	public function getSocialChannels($channels = [], $echo = false, $classes = [])
	{
		$prefix = $this->pfx;
		$social_channels = [];

		if (have_rows("$prefix-channels", 'options')) {
			while (have_rows("$prefix-channels", 'options')) {
				the_row();

				$channel_url = get_sub_field("$prefix-channel-url");
				$channel_icon = get_sub_field("$prefix-channel-icon");

				if (empty($channels) || in_array($channel_icon, $channels)) {
					array_push($social_channels, ['url' => $channel_url, 'icon' => $channel_icon]);
				}
			}
		}

		if (!empty($social_channels)) {
			if (!$echo) {
				return $social_channels;
			} else {
				$c = array_merge(['social-channels'], $classes);

				$output = '<ul class="' . implode(' ', $c) . '">';

				foreach ($social_channels as $key => $channel) {
					$output .= '<li class="social-channels__channel social-channel social-channel--' . $channel['icon'] . '">';
					$output .= '<a class="social-channel__link" href="' . $channel['url'] . '" target="_blank" title="'.$this->choices[$channel['icon']].'">'.(new SVG)->getIcon("social-icons/" . $channel['icon'], ['social-channel__icon']).'</a>';
					$output .= '</li>';
				}

				$output .= '</ul>';

				echo $output;
			}
		} else {
			if (!$echo) {
				return 'no channels found';
			} else {
				echo 'no channels found';
			}
		}
	}

	/**
	 * This function adds the acf fields to the theme settings page
	 *
	 * @since    0.1.0
	 *
	 * @return void
	 */
	public function acfSocialChannels()
	{
		if (function_exists('acf_add_local_field_group') && !empty($this->choices)) {
			$prefix = $this->pfx;

			acf_add_local_field_group(
				[
					'key' => "$prefix-group",
					'title' => __('Social Media Channels', 'sha'),
					'fields' => [
						[
							'key' => "$prefix-channels",
							'name' => "$prefix-channels",
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __('Channel hinzufügen', 'sha'),
							'sub_fields'   => [
								[
									'key' => "$prefix-channel-icon",
									'name' => "$prefix-channel-icon",
									'label' => __('Icon', 'sha'),
									'required' => true,
									'type' => 'select',
									'choices' => $this->choices,
									'allow_null' => 1,
									'mime_type' => 'svg',
									'wrapper' => [
										'width' => 30,
									]
								],
								[
									'key' => "$prefix-channel-url",
									'name' => "$prefix-channel-url",
									'label' => __('Url', 'sha'),
									'required' => true,
									'type' => 'url',
									'wrapper' => [
										'width' => 70,
									]
								]
							],
						],
					],
					'menu_order' => 20,
					'location' => [
						[
							[
								'param' => 'options_page',
								'operator' => '==',
								'value' => $this->general_slug,
							],
						],
					],
				]
			);
		}
	}
}
