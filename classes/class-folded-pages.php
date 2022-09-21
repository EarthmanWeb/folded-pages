<?php
/**
 * Plugin Class
 *
 * @package Folded_Pages
 */

namespace EMMEDIA;

if ( ! class_exists( 'Folded_Pages' ) ) {
	/**
	 * Folded Pages Class
	 */
	class Folded_Pages {

		/**
		 * Registers hook functions
		 *
		 * @return void
		 */
		public function init() {

			add_action( 'pre_get_posts', array( $this, 'folded_pages_list_show_parent_pages_only' ) );
			add_action( 'restrict_manage_posts', array( $this, 'folded_pages_list_filter_by_parent_page' ) );
			add_action( 'manage_posts_extra_tablenav', array( $this, 'folded_pages_clear_filters_button' ), 100000 );
			add_filter( 'page_row_actions', array( $this, 'folded_pages_add_child_page_links' ), 100, 2 );

			add_action( 'manage_page_posts_custom_column', array( $this, 'folded_pages_child_pages_column' ), 100, 2 );
			add_filter( 'manage_page_posts_columns', array( $this, 'folded_pages_columns_order' ) );
			add_action( 'admin_head', array( $this, 'folded_pages_child_pages_column_css' ) );

		}


		/**
		 * Restricts pages list to top level or children of parent page
		 *
		 * @param Object $query WP_Query.
		 * @return void
		 */
		public function folded_pages_list_show_parent_pages_only( $query ) {
			global $pagenow;
			$parent_page = ! empty( sanitize_text_field( $_REQUEST['parent_page'] ) ) ? sanitize_text_field( $_REQUEST['parent_page'] ) : null;
			$post_status = ! empty( sanitize_text_field( $_REQUEST['post_status'] ) ) ? sanitize_text_field( $_REQUEST['post_status'] ) : null;
			$author      = ! empty( sanitize_text_field( $_REQUEST['author'] ) ) ? sanitize_text_field( $_REQUEST['author'] ) : null;
			$orderby     = ! empty( sanitize_text_field( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : null;
			$order       = ! empty( sanitize_text_field( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : null;

			if ( 'edit.php' !== $pagenow ||
			empty( $query->query_vars['post_type'] ) ||
			'page' !== $query->query_vars['post_type'] ||
			! empty( $query->query_vars['post_parent'] ) ||
			( ! empty( $post_status ) && 'trash' === $post_status ) ||
			! empty( $author )
				) {
					return;
			}

			if ( current_user_can( 'edit_others_pages' ) && empty( $query->query_vars['post_parent'] ) && empty( $parent_page ) ) {
				$query->set( 'post_parent', 0 );
				$query->set( 'parent_page', 'root' );
				$parent_page = 'root';
			}

			if ( 'root' === $parent_page ) {
				$query->set( 'post_parent', 0 );
			}

			if ( 'all' === $parent_page ) {
				$query->unset( 'post_parent' );
			}

			if ( ! empty( $parent_page ) && is_numeric( $parent_page ) ) {
				$query->set( 'post_parent', $parent_page );
			}

			// set default sort options.
			if ( empty( $orderby ) ) {
				$query->set( 'orderby', 'title' );
			}

			if ( empty( $order ) ) {
				$query->set( 'order', 'ASC' );
			}
		}

		/**
		 * Adds a select dropdown to filter the pages in the wp-admin
		 *
		 * @param String $post_type The post type being edited.
		 * @return output
		 */
		public function folded_pages_list_filter_by_parent_page( $post_type ) {
			global $pagenow;
			$parent_page = ! empty( sanitize_text_field( $_REQUEST['parent_page'] ) ) ? sanitize_text_field( $_REQUEST['parent_page'] ) : null;

			if ( 'edit.php' !== $pagenow || 'page' !== $post_type ) {
				return;
			}
			global $wpdb;
			$post_parent    = null;
			$option_current = '';
			$option_up      = '';
			$parent_id      = 0;
			if ( isset( $parent_page ) ) {
				$current   = $parent_page;
				$parent_id = $current;
				if ( 'root' !== $current && 'all' !== $current ) {
					$current_page   = get_post( $current );
					$option_up      = ' <option value="' . $current_page->post_parent . '">' . __( 'Up One Level', 'folded-pages' ) . '</option>';
					$option_current = ' <option value="' . $current . '" selected>- ' . $current_page->post_title . '</option>';
				}
			} else {
				$current = 'root';
			}

			$default_text  = __( 'Any parent (all pages)', 'folded-pages' );
			$all_selected  = 'all' === $current ? ' selected' : '';
			$root_selected = 'root' === $current ? ' selected' : '';
			$select        = '
			<select id="folded-pages-filter" name="parent_page" onChange="this.form.submit();" >
			<option value="" disabled>' . __( 'Filter pages by page parent:', 'folded-pages' ) . '</option >
			<option value="all"' . $all_selected . '>' . $default_text . '</option >
			<option value="root"' . $root_selected . ' > ' . __( 'Root Level pages', 'folded-pages' ) . '</option>' . $option_up . $option_current;

			$pages = get_pages(
				'parent=' . $parent_id . ' & order_by=ID,
			post_parent'
			);

			$tree = array();
			foreach ( $pages as $page ) {
				$indent  = '-';
				$parent  = $page->post_parent;
				$page_id = $page->ID;
				if ( ! $parent ) {
					$tree[ $page_id ] = $page_id;
				} elseif ( ! empty( array_keys( $tree ) ) && in_array( $parent, array_keys( $tree ) ) ) {
					$indent .= '--';
					if ( ! is_array( $tree[ $parent ] ) ) {
						$tree[ $parent ] = array();
					}
					$tree[ $parent ][ $page_id ] = $page_id;
				}
				$children     = get_pages(
					'parent=' . $page_id . '&order=ID,post_parent'
				);
				$num_children = count( $children );
				if ( $num_children ) {

					$option  = '<option value="' . $page_id . '" ';
					$option .= ( $current === $page_id ) ? ' selected="selected"' : '';
					$option .= '>';
					$option .= $indent . ' ' . $page->post_title;
					if ( current_user_can( 'manage_options' ) ) {
						$option .= ' ( ' . $num_children . ' )';
					}
					$option .= '</option>';
					$select .= $option;
				}
			}

				$select      .= '
				</select>';
				$output       = apply_filters( 'folded_pages_list_filter_by_parent_page_output', $select );
				$allowed_html = array(
					'select' => array(
						'id'       => array(),
						'name'     => array(),
						'onChange' => array(),
					),
					'option' => array(
						'value'    => array(),
						'selected' => array(),
						'disabled' => array(),
					),
					'script' => array(
						'type' => array(),
						'id'   => array(),
					),
				);
				$output      .= '<script type="text/javascript" id="folded-pages-list-filter-activate">jQuery( "#folded-pages-filter" ).change(function() {  jQuery( "#posts-filter").submit(); });</script>';
				echo wp_kses( $output, $allowed_html );
		}

		/**
		 * Adds a button to clear the filters from the pages list in the wp-admin
		 *
		 * @param String $which The location of the extra table nav markup: 'top' or 'bottom'.
		 * @return void
		 */
		public function folded_pages_clear_filters_button( $which ) {
			global $pagenow;

			$post_type     = ! empty( sanitize_text_field( $_REQUEST['post_type'] ) ) ? sanitize_text_field( $_REQUEST['post_type'] ) : null;
			$s             = ! empty( sanitize_text_field( $_REQUEST['s'] ) ) ? sanitize_text_field( $_REQUEST['s'] ) : null;
			$content_group = ! empty( sanitize_text_field( $_REQUEST['content_group'] ) ) ? sanitize_text_field( $_REQUEST['content_group'] ) : null;

			if ( 'edit.php' !== $pagenow || 'top' !== $which ) {
				return;
			}
			if ( empty( $post_type ) ) {
				$post_type = 'post';
			} else {
				$post_type = $post_type;
			}
			$this_url = '/wp-admin/edit.php?post_type=' . $post_type;
			$class    = ( isset( $s ) || isset( $content_group ) ) ? 'button-primary' : 'button';

			$output_html = '<div class="alignleft actions"><a type="button" name="filter_clear_all" id="post-query-clear" class="' . $class . '" value="Clear All" href="' . $this_url . '">Clear All Filters</a></div>';
			$output      = apply_filters( 'folded_pages_clear_filter_button_output', $output_html );
			echo wp_kses_post( $output );
		}

		/**
		 * Adds a view link to the page list for any row with child pages
		 *
		 * @param Array   $actions page row actions available-see: https://developer.wordpress.org/reference/hooks/page_row_actions/ .
		 * @param WP_Post $post The post being edited.
		 * @return Array $actions
		 */
		public function folded_pages_add_child_page_links( $actions, $post ) {
			if ( ! empty( $actions['edit'] ) ) {
				global $wpdb;
				// check if there are children to this page...
				$child_page_count = $this->folded_pages_get_child_page_count( $post->ID );
				if ( $child_page_count > 0 ) {
					// Children exist-Show the link...
					$link_params          = ' ? s & post_status=all & post_type=page & parent_page=' . $post->ID;
					$child_link_text      = __( 'View Child Page(s)', 'folded-pages' );
					$actions['view-tree'] = apply_filters( 'folded_pages_add_child_page_links_output', "<a href='/wp-admin/edit.php$link_params' title='$child_link_text'>$child_link_text</a>" );
				}
			}
			return $actions;
		}

		/**
		 * Create the column for our links, make it appear first, at the left
		 *
		 * @param Array $columns An associative array of column headings.
		 * @return Array the altered columns.
		 */
		public function folded_pages_columns_order( $columns ) {
			$parent_page      = ! empty( sanitize_text_field( $_REQUEST['parent_page'] ) ) ? sanitize_text_field( $_REQUEST['parent_page'] ) : null;
			$parent_page_base = ! empty( sanitize_text_field( $_REQUEST['parent_page_base'] ) ) ? sanitize_text_field( $_REQUEST['parent_page_base'] ) : null;

			if ( isset( $parent_page ) && 'all' !== $parent_page && 'root' !== $parent_page ) {
				if ( 0 === $parent_page || ! empty( $parent_page_base ) ) {
					$page_up_link = add_query_arg( 'parent_page', 'all' );
				} else {
					$grand_parent_page_id = wp_get_post_parent_id( $parent_page );
					$page_up_link         = add_query_arg( 'parent_page', $grand_parent_page_id );
				}
				$column_header = "<a href='$page_up_link' title='" . __( 'Move up one level', 'folded-pages' ) . "'><i class='dashicons dashicons-exit'></i></a>";
			} else {
				$column_header = "<a><i class='dashicons dashicons-admin-page' title='" . __( 'Child pages count + filter', 'folded-pages' ) . "'></i></a>";
			}
			$before    = 'cb'; // move before this..
			$n_columns = array();
			foreach ( $columns as $key => $value ) {
				if ( $key == $before ) {
					$n_columns['children'] = $column_header;
				}
				$n_columns[ $key ] = $value;
			}
			return $n_columns;
		}

		/**
		 * Adds the column details in each row of the wp-admin list.
		 *
		 * @param String  $column_key The key for the column to display.
		 * @param Integer $post_id The ID for the post being displayed.
		 * @return void
		 */
		public function folded_pages_child_pages_column( $column_key, $post_id ) {
			if ( 'children' === $column_key ) {
				global $wpdb;
				// check if there are children to this page...
				$child_pages_count = $this->folded_pages_get_child_page_count( $post_id );
				if ( $child_pages_count > 0 ) {
					$link_params = '?s&post_status=all&post_type=page&parent_page=' . $post_id;
					$title_text  = __( 'View Child Page(s)', 'folded-pages' );
					$output      = "<a href='/wp-admin/edit.php$link_params' title='$title_text'>";
					$output     .= $child_pages_count . '<br/> ';
					$output     .= "<i class='dashicons dashicons-arrow-down-alt2' /></a>";
				} else {
					$output = '';
				}
				echo wp_kses_post( $output );
			}
		}

		/**
		 * Add css to make the columns look purdy.
		 *
		 * @return output
		 */
		public function folded_pages_child_pages_column_css() {
			global $pagenow;
			$post_type = ! empty( sanitize_text_field( $_REQUEST['post_type'] ) ) ? sanitize_text_field( $_REQUEST['post_type'] ) : null;

			if ( 'edit.php' !== $pagenow || empty( $post_type ) || 'page' !== $post_type ) {
				return;
			}
			echo '<style id="child-pages-column-css">
				.manage-column.column-children {
					text-align: center;
					width: 2.2em;
				}
				td.children.column-children {
					text-align: center;
					vertical-align: top;
					line-height: 1.3em;
				}
			</style>';
		}


		/**
		 * Get the count of ALL sub-pages, not just first level
		 *
		 * @param Integer $post_id The post to check.
		 * @param string  $post_type The post type (in case this isn't a page list.
		 * @return Int count of child pages for the current post.
		 */
		public function folded_pages_get_child_page_count_recursive( $post_id, $post_type = 'page' ) {
			global $wpdb;
			$child_pages = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_parent=%d;", $post_type, $post_id ) );
			if ( count( $child_pages ) > 0 ) {
				$child_page_count = count( $child_pages );
				foreach ( $child_pages as $post_id ) {
					$child_page_count += $this->folded_pages_get_child_page_count_recursive( $post_id, $post_type );
				}
			} else {
				$child_page_count = 0;
			}
			return $child_page_count;
		}

		/**
		 * Get the count of the direct sub-pages, first level only.
		 *
		 * @param Integer $post_id The post to check.
		 * @param string  $post_type The post type (in case this isn't a page list).
		 * @return Int count of 1st level child pages for the current post.
		 */
		public function folded_pages_get_child_page_count( $post_id, $post_type = 'page' ) {
			global $wpdb;
			$child_page_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type=%s  AND post_parent=%d ORDER BY post_title ASC;", $post_type, $post_id ) );
			return $child_page_count;
		}
	}
}
