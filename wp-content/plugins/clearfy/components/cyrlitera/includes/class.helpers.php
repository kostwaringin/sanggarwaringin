<?php

	/**
	 * Helpers functions
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 2017 Webraftic Ltd
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	class WCTR_Helper {

		public static function transliterate($title, $ignore_special_symbols = false)
		{
			$origin_title = $title;
			$iso9_table = self::getSymbolsPack();

			$title = urldecode($title);
			$title = strtr($title, $iso9_table);

			if( function_exists('iconv') ) {
				$title = iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $title);
			}

			if( !$ignore_special_symbols ) {
				$title = preg_replace("/[^A-Za-z0-9'_\-\.]/", '-', $title);
				$title = preg_replace('/\-+/', '-', $title);
				$title = preg_replace('/^-+/', '', $title);
				$title = preg_replace('/-+$/', '', $title);
			}

			return apply_filters('wbcr_cyrlitera_transliterate', $title, $origin_title, $iso9_table);
		}

		/**
		 * @param string $title обработанный заголовок
		 * @return mixed|void
		 */
		public static function sanitizeTitle($title)
		{
			global $wpdb;

			$origin_title = $title;

			$is_term = false;
			$backtrace = debug_backtrace();
			foreach($backtrace as $backtrace_entry) {
				if( $backtrace_entry['function'] == 'wp_insert_term' ) {
					$is_term = true;
					break;
				}
			}

			$term = $is_term ? $wpdb->get_var($wpdb->prepare("SELECT slug FROM {$wpdb->terms} WHERE name = '%s'", $title)) : '';

			if( empty($term) ) {
				$title = self::transliterate($title);
			} else {
				$title = $term;
			}

			return apply_filters('wbcr_cyrlitera_sanitize_title', $title, $origin_title);
		}

		/**
		 * @return array
		 */
		public static function getSymbolsPack()
		{
			$loc = get_locale();

			$ret = array(
				// russian
				'А' => 'A',
				'а' => 'a',
				'Б' => 'B',
				'б' => 'b',
				'В' => 'V',
				'в' => 'v',
				'Г' => 'G',
				'г' => 'g',
				'Д' => 'D',
				'д' => 'd',
				'Е' => 'E',
				'е' => 'e',
				'Ё' => 'Jo',
				'ё' => 'jo',
				'Ж' => 'Zh',
				'ж' => 'zh',
				'З' => 'Z',
				'з' => 'z',
				'И' => 'I',
				'и' => 'i',
				'Й' => 'J',
				'й' => 'j',
				'К' => 'K',
				'к' => 'k',
				'Л' => 'L',
				'л' => 'l',
				'М' => 'M',
				'м' => 'm',
				'Н' => 'N',
				'н' => 'n',
				'О' => 'O',
				'о' => 'o',
				'П' => 'P',
				'п' => 'p',
				'Р' => 'R',
				'р' => 'r',
				'С' => 'S',
				'с' => 's',
				'Т' => 'T',
				'т' => 't',
				'У' => 'U',
				'у' => 'u',
				'Ф' => 'F',
				'ф' => 'f',
				'Х' => 'H',
				'х' => 'h',
				'Ц' => 'C',
				'ц' => 'c',
				'Ч' => 'Ch',
				'ч' => 'ch',
				'Ш' => 'Sh',
				'ш' => 'sh',
				'Щ' => 'Shh',
				'щ' => 'shh',
				'Ъ' => '',
				'ъ' => '',
				'Ы' => 'Y',
				'ы' => 'y',
				'Ь' => '',
				'ь' => '',
				'Э' => 'Je',
				'э' => 'je',
				'Ю' => 'Ju',
				'ю' => 'ju',
				'Я' => 'Ja',
				'я' => 'ja',
				// global
				'Ґ' => 'G',
				'ґ' => 'g',
				'Є' => 'Ie',
				'є' => 'ie',
				'І' => 'I',
				'і' => 'i',
				'Ї' => 'I',
				'ї' => 'i',
				'Ї' => 'i',
				'ї' => 'i',
				'Ё' => 'Jo',
				'ё' => 'jo',
				'й' => 'i',
				'Й' => 'I'
			);

			// ukrainian
			if( $loc == 'uk' ) {
				$ret = array_merge($ret, array(
					'Г' => 'H',
					'г' => 'h',
					'И' => 'Y',
					'и' => 'y',
					'Х' => 'Kh',
					'х' => 'kh',
					'Ц' => 'Ts',
					'ц' => 'ts',
					'Щ' => 'Shch',
					'щ' => 'shch',
					'Ю' => 'Iu',
					'ю' => 'iu',
					'Я' => 'Ia',
					'я' => 'ia',

				));
				//bulgarian
			} elseif( $loc == 'bg' || $loc == 'bg_BG' ) {
				$ret = array_merge($ret, array(
					'Щ' => 'Sht',
					'щ' => 'sht',
					'Ъ' => 'a',
					'ъ' => 'a'
				));
			}

			if( $loc == 'ka_GE' ) {
				$ret = array(
					'ა' => 'a',
					'ბ' => 'b',
					'გ' => 'g',
					'დ' => 'd',
					'ე' => 'e',
					'ვ' => 'v',
					'ზ' => 'z',
					'თ' => 'th',
					'ი' => 'i',
					'კ' => 'k',
					'ლ' => 'l',
					'მ' => 'm',
					'ნ' => 'n',
					'ო' => 'o',
					'პ' => 'p',
					'ჟ' => 'zh',
					'რ' => 'r',
					'ს' => 's',
					'ტ' => 't',
					'უ' => 'u',
					'ფ' => 'ph',
					'ქ' => 'q',
					'ღ' => 'gh',
					'ყ' => 'qh',
					'შ' => 'sh',
					'ჩ' => 'ch',
					'ც' => 'ts',
					'ძ' => 'dz',
					'წ' => 'ts',
					'ჭ' => 'tch',
					'ხ' => 'kh',
					'ჯ' => 'j',
					'ჰ' => 'h'
				);
			}

			$custom_rules = WCTR_Plugin::app()->getPopulateOption('custom_symbols_pack');

			if( !empty($custom_rules) ) {
				$split_rules = explode(',', $custom_rules);
				$split_rules = array_map('trim', $split_rules);

				foreach($split_rules as $rule) {
					$split_symbols = explode('=', $rule);

					if( sizeof($split_symbols) === 2 ) {
						if( empty($split_symbols[0]) ) {
							continue;
						}

						$ret[$split_symbols[0]] = $split_symbols[1];
					}
				}
			}

			return apply_filters('wbcr_cyrlitera_default_symbols_pack', $ret);
		}

		/**
		 * Делает откат изменений после выполнения метода convertExistingSlugs,
		 * этот метод не восстановливает вновь конвертированные слаги.
		 */
		public static function rollbackUrlChanges()
		{
			global $wpdb;

			$posts = $wpdb->get_results("SELECT p.ID, p.post_name, m.meta_value as old_post_name FROM {$wpdb->posts} p
						LEFT JOIN {$wpdb->postmeta} m
						ON p.ID = m.post_id
						WHERE p.post_status
						IN ('publish', 'future', 'private') AND m.meta_key='wbcr_wp_old_slug' AND m.meta_value IS NOT NULL");

			foreach((array)$posts as $post) {
				if( $post->post_name != $post->old_post_name ) {
					$wpdb->update($wpdb->posts, array('post_name' => $post->old_post_name), array('ID' => $post->ID), array('%s'), array('%d'));
					delete_post_meta($post->ID, 'wbcr_wp_old_slug');
				}
			}

			$terms = $wpdb->get_results("SELECT t.term_id, t.slug, o.option_value as old_term_slug FROM {$wpdb->terms} t
						LEFT JOIN {$wpdb->options} o
						ON o.option_name=concat('wbcr_wp_term_',t.term_id, '_old_slug')
						WHERE o.option_value IS NOT NULL");

			foreach((array)$terms as $term) {
				if( $term->slug != $term->old_term_slug ) {
					$wpdb->update($wpdb->terms, array('slug' => $term->old_term_slug), array('term_id' => $term->term_id), array('%s'), array('%d'));
					delete_option('wbcr_wp_term_' . $term->term_id . '_old_slug');
				}
			}
		}

		/**
		 * Массово конвертирует слаги для страниц, записей, терминов и т.д.
		 * Делает бекап старого слага, чтобы можно было восстановить его. А также использовать в других плагинах.
		 */
		public static function convertExistingSlugs()
		{
			global $wpdb;

			$posts = $wpdb->get_results("SELECT ID, post_name FROM {$wpdb->posts}
 					WHERE post_name REGEXP('[^_A-Za-z0-9\-]+') AND post_status IN ('publish', 'future', 'private')");

			foreach((array)$posts as $post) {
				$sanitized_name = WCTR_Helper::sanitizeTitle(urldecode($post->post_name));

				if( $post->post_name != $sanitized_name ) {
					add_post_meta($post->ID, 'wbcr_wp_old_slug', $post->post_name);

					$wpdb->update($wpdb->posts, array('post_name' => $sanitized_name), array('ID' => $post->ID), array('%s'), array('%d'));
				}
			}

			$terms = $wpdb->get_results("SELECT term_id, slug FROM {$wpdb->terms} WHERE slug REGEXP('[^_A-Za-z0-9\-]+')");

			foreach((array)$terms as $term) {
				$sanitized_slug = WCTR_Helper::sanitizeTitle(urldecode($term->slug));

				if( $term->slug != $sanitized_slug ) {
					update_option('wbcr_wp_term_' . $term->term_id . '_old_slug', $term->slug, false);
					$wpdb->update($wpdb->terms, array('slug' => $sanitized_slug), array('term_id' => $term->term_id), array('%s'), array('%d'));
				}
			}
		}
	}
