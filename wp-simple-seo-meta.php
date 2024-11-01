<?php
/**
 * Plugin Name: WP Simple SEO Meta
 * Plugin URI: https://www.epigrade.com/wp-simple-seo-meta/
 * Description: Add page title, meta description, keywords and robots to all post types and taxonomies. This plugin is mostly intended to be used by other plugins and themes as by itself provides no public functionality. Keep in mind this plugin removes taxonomy description column from listing table.
 * Version: 1.1.0
 * Author: Epigrade
 * Author URI: https://www.epigrade.com/
 * Text Domain: wpsimpleseometa
 *
 * Copyright (c) 2019 Epigrade.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if ( ! class_exists( 'EpigradeWPSimpleSEOMeta' ) ) {

	/**
	 * Plugin class.
	 *
	 * Class EpigradeWPSimpleSEOMeta
	 */
	class EpigradeWPSimpleSEOMeta {

		/**
		 * Plugin instance.
		 *
		 * @var null|EpigradeWPSimpleSEOMeta
		 */
		private static $instance = null;

		/**
		 * Registers actions and filters.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'registerTaxonomies' ], 100 );
			add_action( 'save_post', [ $this, 'postSave' ], 100, 2 );
			add_action( 'add_meta_boxes', [ $this, 'postRegister' ] );
			add_filter( 'document_title_parts', [ $this, 'filterTitle' ], 100, 1 );
			add_filter( 'single_cat_title', [ $this, 'filterTermTitle' ], 100, 1 );
			add_filter( 'single_tag_title', [ $this, 'filterTermTitle' ], 100, 1 );
			add_filter( 'single_term_title', [ $this, 'filterTermTitle' ], 100, 1 );
			add_action( 'wp_head', [ $this, 'injectMeta' ], 1 );
		}

		/**
		 * Returns plugin instance.
		 *
		 * @return EpigradeWPSimpleSEOMeta
		 */
		public static function getInstance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Render form fields for taxonomy term add form.
		 *
		 * @param WP_Taxonomy $taxonomy
		 */
		public function taxonomyAdd( $taxonomy ) {
			?>
            <div class="form-field">
                <label for="wpsimpleseometa-title">
					<?php _e( 'Page Title', 'wpsimpleseometa' ); ?>
                </label>
                <input type="text" id="wpsimpleseometa-title" name="wpsimpleseometa_title">
            </div>
            <div class="form-field">
                <label for="wpsimpleseometa-description">
					<?php _e( 'Meta Description', 'wpsimpleseometa' ); ?>
                </label>
                <textarea id="wpsimpleseometa-description" name="wpsimpleseometa_description"></textarea>
            </div>
            <div class="form-field">
                <label for="wpsimpleseometa-keywords">
					<?php _e( 'Meta Keywords', 'wpsimpleseometa' ); ?>
                </label>
                <textarea id="wpsimpleseometa-keywords" name="wpsimpleseometa_keywords"></textarea>
            </div>
            <div class="form-field">
                <label for="wpsimpleseometa-robots">
					<?php _e( 'Robots', 'wpsimpleseometa' ); ?>
                </label>
                <input type="text" id="wpsimpleseometa-robots" name="wpsimpleseometa_robots">
            </div>
			<?php
		}

		/**
		 * Render form fields for taxonomy term edit form.
		 *
		 * @param WP_Term $term
		 * @param WP_Taxonomy $taxonomy
		 */
		public function taxonomyEdit( $term, $taxonomy ) {
			?>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="wpsimpleseometa-title"><?php _e( 'Page Title', 'wpsimpleseometa' ); ?></label>
                </th>
                <td>
                    <input
                            type="text"
                            id="wpsimpleseometa-title"
                            name="wpsimpleseometa_title"
                            value="<?php echo esc_attr( ! is_null( $term ) ? get_term_meta( $term->term_id, '_page_title', true ) : '' ); ?>"
                    >
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="wpsimpleseometa-description"><?php _e( 'Meta Description', 'wpsimpleseometa' ); ?></label>
                </th>
                <td>
                    <textarea id="wpsimpleseometa-description" name="wpsimpleseometa_description"><?php
	                    echo esc_html( ! is_null( $term ) ? get_term_meta( $term->term_id, '_meta_description', true ) : '' );
	                    ?></textarea>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="wpsimpleseometa-keywords"><?php _e( 'Meta Keywords', 'wpsimpleseometa' ); ?></label>
                </th>
                <td>
                    <textarea id="wpsimpleseometa-keywords" name="wpsimpleseometa_keywords"><?php
	                    echo esc_html( ! is_null( $term ) ? get_term_meta( $term->term_id, '_meta_keywords', true ) : '' );
	                    ?></textarea>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="wpsimpleseometa-robots"><?php _e( 'Robots', 'wpsimpleseometa' ); ?></label>
                </th>
                <td>
                    <input
                            type="text"
                            id="wpsimpleseometa-robots"
                            name="wpsimpleseometa_robots"
                            value="<?php echo esc_attr( ! is_null( $term ) ? get_term_meta( $term->term_id, '_meta_robots', true ) : '' ); ?>"
                    >
                </td>
            </tr>
			<?php
		}

		/**
		 * Save taxonomy term meta fields on add form submit.
		 *
		 * @param int $term_id
		 * @param int $taxonomy_id
		 */
		public function taxonomySave( $term_id, $taxonomy_id ) {

			if (
				isset( $_POST['wpsimpleseometa_title'] ) &&
				( $title = sanitize_text_field( $_POST['wpsimpleseometa_title'] ) ) != ''
			) {
				add_term_meta( $term_id, '_page_title', $title, true );
			}
			if (
				isset( $_POST['wpsimpleseometa_description'] ) &&
				( $description = sanitize_text_field( $_POST['wpsimpleseometa_description'] ) ) != ''
			) {
				add_term_meta( $term_id, '_meta_description', $description, true );
			}
			if (
				isset( $_POST['wpsimpleseometa_keywords'] ) &&
				( $keywords = sanitize_text_field( $_POST['wpsimpleseometa_keywords'] ) ) != ''
			) {
				add_term_meta( $term_id, '_meta_keywords', $keywords, true );
			}
			if (
				isset( $_POST['wpsimpleseometa_robots'] ) &&
				( $robots = sanitize_text_field( $_POST['wpsimpleseometa_robots'] ) ) != ''
			) {
				add_term_meta( $term_id, '_meta_robots', $robots, true );
			}
		}

		/**
		 * Save taxonomy term meta fields on edit form submit.
		 *
		 * @param int $term_id
		 * @param int $taxonomy_id
		 */
		public function taxonomyUpdate( $term_id, $taxonomy_id ) {

			$page_title  = sanitize_text_field( $_POST['wpsimpleseometa_title'] );
			$description = sanitize_text_field( $_POST['wpsimpleseometa_description'] );
			$keywords    = sanitize_text_field( $_POST['wpsimpleseometa_keywords'] );
			$robots      = sanitize_text_field( $_POST['wpsimpleseometa_robots'] );

			if ( $page_title != '' && get_term_meta( $term_id, '_page_title', false ) ) {
				update_term_meta( $term_id, '_page_title', $page_title );
			} elseif ( $page_title != '' ) {
				add_term_meta( $term_id, '_page_title', $page_title );
			} else {
				delete_term_meta( $term_id, '_page_title' );
			}

			if ( $description != '' && get_term_meta( $term_id, '_meta_description', false ) ) {
				update_term_meta( $term_id, '_meta_description', $description );
			} elseif ( $description != '' ) {
				add_term_meta( $term_id, '_meta_description', $description );
			} else {
				delete_term_meta( $term_id, '_meta_description' );
			}

			if ( $keywords != '' && get_term_meta( $term_id, '_meta_keywords', false ) ) {
				update_term_meta( $term_id, '_meta_keywords', $keywords );
			} elseif ( $keywords != '' ) {
				add_term_meta( $term_id, '_meta_keywords', $keywords );
			} else {
				delete_term_meta( $term_id, '_meta_keywords' );
			}

			if ( $robots != '' && get_term_meta( $term_id, '_meta_robots', false ) ) {
				update_term_meta( $term_id, '_meta_robots', $robots );
			} elseif ( $robots != '' ) {
				add_term_meta( $term_id, '_meta_robots', $robots );
			} else {
				delete_term_meta( $term_id, '_meta_robots' );
			}
		}

		/**
		 * Add meta field columns and remove description column from taxonomy terms listing/table.
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		public function taxonomyColumns( $columns ) {

			unset( $columns['description'] );
			$columns['page_title']       = __( 'Title', 'wpsimpleseometa' );
			$columns['meta_description'] = __( 'Description', 'wpsimpleseometa' );
			$columns['meta_keywords']    = __( 'Keywords', 'wpsimpleseometa' );
			$columns['meta_robots']      = __( 'Robots', 'wpsimpleseometa' );

			return $columns;

		}

		/**
         * Handle rendering of meta fields details in the taxonomy terms listing/table.
         *
		 * @param $content
		 * @param $column_name
		 * @param $term_id
		 *
		 * @return string
		 */
		public function taxonomyColumnContent( $content, $column_name, $term_id ) {

			if ( $column_name == 'page_title' ) {
				$value = get_term_meta( $term_id, '_page_title', true );
			} elseif ( $column_name == 'meta_description' ) {
				$value = get_term_meta( $term_id, '_meta_description', true );
			} elseif ( $column_name == 'meta_keywords' ) {
				$value = get_term_meta( $term_id, '_meta_keywords', true );
			} elseif ( $column_name == 'meta_robots' ) {
				$value = get_term_meta( $term_id, '_meta_robots', true );
			} else {
				return $content;
			}

			if ( $column_name == 'meta_robots' ) {
				$content .= $value;
			} else {
				if ( trim( $value ) == '' ) {
					$style = 'color: red;';
				} else {
					$style = 'color: green;';
				}

				$content .= '<span style="cursor: help;' . $style . '" title="' . esc_attr( $value ) . '">' . strlen( $value ) . '</div>';
			}

			return $content;

		}

		/**
		 * Register taxonomies actions and filters for term CRUD.
		 */
		public function registerTaxonomies() {

			$taxonomies = get_taxonomies( [
				'public'       => true,
				'show_in_menu' => true,
			], 'objects' );
			foreach ( $taxonomies as $taxonomy ) {
				add_action( $taxonomy->name . '_add_form_fields', [ $this, 'taxonomyAdd' ], 100, 2 );
				add_action( $taxonomy->name . '_edit_form_fields', [ $this, 'taxonomyEdit' ], 100, 2 );
				add_action( 'created_' . $taxonomy->name, [ $this, 'taxonomySave' ], 100, 2 );
				add_action( 'edited_' . $taxonomy->name, [ $this, 'taxonomyUpdate' ], 100, 2 );
				add_filter( 'manage_edit-' . $taxonomy->name . '_columns', [ $this, 'taxonomyColumns' ] );
				add_filter( 'manage_' . $taxonomy->name . '_custom_column', [
					$this,
					'taxonomyColumnContent'
				], 100, 3 );
			}

		}

		/**
		 * Display post meta box contents.
		 */
		public function postDisplay() {
			global $post;
			wp_nonce_field( basename( __FILE__ ), 'wpsimpleseometa_post_nonce' );
			?>
            <div class="form-field">
                <label class="components-base-control__label" for="wpsimpleseometa-post-title">
					<?php _e( 'Page Title', 'wpsimpleseometa' ); ?>:
                </label>
                <input
                        type="text"
                        id="wpsimpleseometa-post-title"
                        name="wpsimpleseometa_post_title"
                        value="<?php echo esc_attr( get_post_meta( $post->ID, '_page_title', true ) ); ?>"
                >
            </div>
            <div class="form-field">
                <label class="components-base-control__label" for="wpsimpleseometa-post-description">
					<?php _e( 'Meta Description', 'wpsimpleseometa' ); ?>
                </label>
                <textarea id="wpsimpleseometa-post-description" name="wpsimpleseometa_post_description"><?php
					echo esc_html( get_post_meta( $post->ID, '_meta_description', true ) );
					?></textarea>
            </div>
            <div class="form-field">
                <label class="components-base-control__label" for="wpsimpleseometa-post-keywords">
					<?php _e( 'Meta Keywords', 'wpsimpleseometa' ); ?>
                </label>
                <textarea id="wpsimpleseometa-post-keywords" name="wpsimpleseometa_post_keywords"><?php
					echo esc_html( get_post_meta( $post->ID, '_meta_keywords', true ) );
					?></textarea>
            </div>
            <div class="form-field">
                <label class="components-base-control__label" for="wpsimpleseometa-post-robots">
					<?php _e( 'Robots', 'wpsimpleseometa' ); ?>:
                </label>
                <input
                        type="text"
                        id="wpsimpleseometa-post-robots"
                        name="wpsimpleseometa_post_robots"
                        value="<?php echo esc_attr( get_post_meta( $post->ID, '_meta_robots', true ) ); ?>"
                >
            </div>
			<?php
		}

		/**
         * Save post meta fields values.
         *
		 * @param int $post_id
		 * @param WP_Post $post
		 *
		 * @return mixed
		 */
		public function postSave( $post_id, $post ) {
			// Return if the user doesn't have edit permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			// Verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times.
			if (
				! (
					isset( $_POST['wpsimpleseometa_post_title'] ) &&
					isset( $_POST['wpsimpleseometa_post_description'] ) &&
					isset( $_POST['wpsimpleseometa_post_keywords'] ) &&
					isset( $_POST['wpsimpleseometa_post_robots'] )
				) ||
				! wp_verify_nonce( $_POST['wpsimpleseometa_post_nonce'], basename( __FILE__ ) )
			) {
				return $post_id;
			}

			// Don't store custom data twice
			if ( 'revision' === $post->post_type ) {
				return $post_id;
			}

			$page_title  = sanitize_text_field( $_POST['wpsimpleseometa_post_title'] );
			$description = sanitize_text_field( $_POST['wpsimpleseometa_post_description'] );
			$keywords    = sanitize_text_field( $_POST['wpsimpleseometa_post_keywords'] );
			$robots      = sanitize_text_field( $_POST['wpsimpleseometa_post_robots'] );

			if ( $page_title != '' && get_post_meta( $post_id, '_page_title', false ) ) {
				update_post_meta( $post_id, '_page_title', $page_title );
			} elseif ( $page_title != '' ) {
				add_post_meta( $post_id, '_page_title', $page_title );
			} else {
				delete_post_meta( $post_id, '_page_title' );
			}

			if ( $description != '' && get_post_meta( $post_id, '_meta_description', false ) ) {
				update_post_meta( $post_id, '_meta_description', $description );
			} elseif ( $description != '' ) {
				add_post_meta( $post_id, '_meta_description', $description );
			} else {
				delete_post_meta( $post_id, '_meta_description' );
			}

			if ( $keywords != '' && get_post_meta( $post_id, '_meta_keywords', false ) ) {
				update_post_meta( $post_id, '_meta_keywords', $keywords );
			} elseif ( $keywords != '' ) {
				add_post_meta( $post_id, '_meta_keywords', $keywords );
			} else {
				delete_post_meta( $post_id, '_meta_keywords' );
			}

			if ( $robots != '' && get_post_meta( $post_id, '_meta_robots', false ) ) {
				update_post_meta( $post_id, '_meta_robots', $robots );
			} elseif ( $robots != '' ) {
				add_post_meta( $post_id, '_meta_robots', $robots );
			} else {
				delete_post_meta( $post_id, '_meta_robots' );
			}

			return $post_id;
		}

		/**
		 * Add meta box to posts.
		 */
		public function postRegister() {
			$post_types = get_post_types( [ 'public' => true, 'show_in_menu' => true ], 'objects' );
			foreach ( $post_types as $post_type ) {
				add_meta_box(
					'wpsimpleseometa-post',
					__( 'SEO Meta', 'wpsimpleseometa' ),
					[ $this, 'postDisplay' ],
					$post_type->name,
					'side',
					'default'
				);
			}
		}

		/**
         * Change page title for posts and pages.
         *
		 * @param array $title_parts
		 *
		 * @return array
		 */
		public function filterTitle( $title_parts ) {

			if ( is_single() || is_page() ) {
				$meta_title = get_post_meta( get_the_ID(), '_page_title', true );
				if ( ! is_null( $meta_title ) && $meta_title != '' ) {
					$title_parts['title'] = $meta_title;
				}
			}

			return $title_parts;
		}

		/**
         * Change page title for taxonomy terms.
         *
		 * @param array $term_name
		 *
		 * @return array
		 */
		public function filterTermTitle( $term_name ) {

			if ( is_category() || is_tag() || is_tax() ) {
				$term       = get_queried_object();
				$meta_title = get_term_meta( $term->term_id, '_page_title', true );
				if ( $meta_title != '' ) {
					$term_name = $meta_title;
				}
			}

			return $term_name;
		}

		/**
		 * Inject meta fields into template <head> section.
		 */
		public function injectMeta() {
			if ( is_single() || is_page() ) {
				$id = get_the_ID();

				$description = get_post_meta( $id, '_meta_description', true );
				if ( $description != '' ) {
					echo '    <meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
				}

				$keywords = get_post_meta( $id, '_meta_keywords', true );
				if ( $keywords != '' ) {
					echo '    <meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
				}

				$robots = get_post_meta( $id, '_meta_robots', true );
				if ( $robots != '' ) {
					echo '    <meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
				}
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$term = get_queried_object();

				$description = get_term_meta( $term->term_id, '_meta_description', true );
				if ( $description != '' ) {
					echo '    <meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
				}

				$keywords = get_term_meta( $term->term_id, '_meta_keywords', true );
				if ( $keywords != '' ) {
					echo '    <meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
				}

				$robots = get_term_meta( $term->term_id, '_meta_robots', true );
				if ( $robots != '' ) {
					echo '    <meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
				}
			}
		}
	}

	EpigradeWPSimpleSEOMeta::getInstance();
}