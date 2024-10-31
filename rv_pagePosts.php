<?php
/*
Plugin Name: RVPagePosts
Plugin URI: http://www.richardvenancio.com/category/wordpress
Description: This plugin installs a sidebar widget to display a page, a unique post or posts of categories
Version: 1.0
Author: Richard Venancio
Author URI: http://www.richardvenancio.com/
*/
?>
<?php
/*  Copyright 2011  Richard Venancio  (email : contato@richardvenancio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
class RV_PagePosts_Widget extends WP_Widget {


	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function RV_PagePosts_Widget() {
		$widget_ops = array( 'classname' => 'widget_pageposts_rv', 'description' => __( "Use this widget to show a specific page, a unique post or posts of categories", 'rv_pagepost' ) );
		$this->WP_Widget( 'widget_pageposts_rv', __( 'PagePosts widget', 'rv_pagepost' ), $widget_ops );
		$this->alt_option_name = 'widget_pageposts_rv';	
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void Echoes it's output
	 **/
	function widget( $args, $instance ) {
		ob_start();
		extract( $args, EXTR_SKIP );
		$type = NULL;
		if($instance['page_id']){
			$type = 'page';
			$posts = get_pages('include='.$instance['page_id']); 
		}elseif(count($instance['categories_id']) && !in_array('0', $instance['categories_id'])){
			$type = 'category';
			$qtd_posts = (int)$instance['qtd_posts'];
			if(!$qtd_posts) $qtd_posts = -1;
			$posts = get_posts('category='.implode(',', $instance['categories_id']).'&numberposts='.$qtd_posts);
		}elseif($instance['post_id']){
			$type = 'post';
			$posts = get_posts('include='.$instance['post_id']);
		}else{
			return;
		}

		if ( $posts ) :
			echo $before_widget;
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base);
			if($title){
				echo $before_title;
				echo $title; // Can set this with a widget option, or omit altogether
				echo $after_title;
			}
	
			?>
            <div class="center_pagepost_rv">
				<?php 
                global $post;
                foreach ( (array) $posts as $p): $post = $p; setup_postdata($post); ?>
                    <?php if($instance['show_post_title']):?>
                    <h4 class="title_pagepost_rv"><?php echo $post->post_title?></h4>
                    <?php endif; ?>
                    <div class="content_pagepost_rv">
                        <?php if (has_post_thumbnail()) the_post_thumbnail();?>
                        <?php the_content(__('read more', 'rv_pagepost')); ?>
                    </div>
                <?php endforeach; ?>
                <?php 
                if($type == 'category' && $instance['show_category_link']){
                    $categories = get_categories( 'include='.implode(',', $instance['categories_id']) );
                    echo '<div class="more-link-cat">';
                    foreach((array)$categories as $c){
                        $category_link = get_category_link( $c->cat_ID );
                        echo '<a class="more-pagepost_rv more-category" href="'.esc_url( $category_link ).'">'.__('More', 'rv_pagepost').' '.$c->cat_name.'</a>';
                    }
                    echo '</div>';
                }?>
            </div>
			<?php echo $after_widget;
		endif; #( $posts )
		ob_flush();
		//$cache[$args['widget_id']] = ob_get_flush();
		//wp_cache_set( 'widget_twentyeleven_ephemera', $cache, 'widget' );
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 **/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['page_id'] = (int) $new_instance['page_id'];
		$instance['categories_id'] = $instance['page_id'] || in_array('0', $new_instance['categories_id']) ? array('0') : $new_instance['categories_id'];
		$instance['qtd_posts'] = $new_instance['qtd_posts'];
		$instance['show_category_link'] = $new_instance['show_category_link'];
		$instance['post_id'] = $instance['page_id'] || !in_array('0', $instance['categories_id']) ? '' : strip_tags($new_instance['post_id']);
		$instance['show_post_title'] = (int) $new_instance['show_post_title'];
		
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_pageposts_rv'] ) )
			delete_option( 'widget_pageposts_rv' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_submenu_rv', 'widget' );
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 **/
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : '';
		$post_id = isset( $instance['post_id']) ? esc_attr( $instance['post_id'] ) : '';
		$page_id = isset( $instance['page_id']) ? esc_attr( $instance['page_id'] ) : '';
		$qtd_posts = isset( $instance['qtd_posts']) ? esc_attr( $instance['qtd_posts'] ) : '';
		$show_category_link = isset( $instance['show_category_link']) ? esc_attr( $instance['show_category_link'] ) : '';
		$show_post_title = isset( $instance['show_post_title']) ? esc_attr( $instance['show_post_title'] ) : '';
		$categories_id = $instance['categories_id'];
?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'rv_pagepost' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>" /></p>
            
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'page_id' ) ); ?>"><?php _e( 'Pages:', 'rv_pagepost' ); ?> - <small><?php _e( 'Priority', 'rv_pagepost' ); ?> 1</small></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'page_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'page_id' ) ); ?>" class="widefat">
            	<option value="0"><?php _e( 'Select a page', 'rv_pagepost' ); ?></option>
				 <?php 
                  $pages = get_pages(); 
                  foreach ( $pages as $pagg ) {
						$sel = ($page_id == $pagg->ID ? ' selected="selected"' : '');
                    	echo '<option value="' . $pagg->ID  . '"'.$sel.'>'.$pagg->post_title.'</option>';
                  }
                 ?>
            </select>
            </p>
			<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'categories_id' ) ); ?>"><?php _e( 'Posts of:', 'rv_pagepost' ); ?> - <small><?php _e( 'Priority', 'rv_pagepost' ); ?> 2</small></label><br>
            <select name="<?php echo esc_attr( $this->get_field_name( 'categories_id' ) ); ?>[]" id="<?php echo esc_attr( $this->get_field_id( 'categories_id' ) ); ?>" multiple="multiple" size="4" style="height:auto" class="widefat"> 
             <option value="0"<?php echo (!$categories_id || in_array('0',$categories_id) ? ' selected="selected"' : '') ?>><?php _e( 'No category', 'rv_pagepost' ); ?></option>
			 <?php 
              $categories = get_categories('hide_empty=0'); 
              foreach ($categories as $category) {
				  $sel = (in_array($category->term_id,$categories_id) ? ' selected="selected"' : '');
                  echo '<option value="'.$category->term_id.'"'.$sel.'>'.$category->cat_name.'</option>';
              }
             ?>
            </select><br>
            <small><?php _e( 'Press ctrl to select more than one.', 'rv_pagepost' ); ?></small>
            </p>
			<p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'qtd_posts' ) ); ?>"><?php _e( 'Qtd of posts:', 'rv_pagepost' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'qtd_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'qtd_posts' ) ); ?>" type="text" value="<?php echo esc_attr( $qtd_posts ); ?>" size="3" />
            </p>
			<p>
                <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_category_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_category_link' ) ); ?>" value="1" <?php echo (1 == $show_category_link ? 'checked="checked"' : '') ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'show_category_link' ) ); ?>"><?php _e( "Show category's link:", 'rv_pagepost' ); ?></label>
            </p>
			<p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'post_id' ) ); ?>"><?php _e( 'Post:', 'rv_pagepost' ); ?> - <small><?php _e( 'Priority', 'rv_pagepost' ); ?> 3</small></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_id' ) ); ?>" type="text" value="<?php echo esc_attr( $post_id ); ?>" />
                <small><?php _e( "Post's ID, separeted by commas.", 'rv_pagepost' ); ?></small>
            </p>
			<p>
                <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_post_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_post_title' ) ); ?>" value="1" <?php echo (1 == $show_post_title ? 'checked="checked"' : '') ?> />
                <label for="<?php echo esc_attr( $this->get_field_id( 'show_post_title' ) ); ?>"><?php _e( 'Show post title:', 'rv_pagepost' ); ?></label>
            </p>
		<?php
	}
}

if(function_exists('add_action')){
  add_action('widgets_init', create_function('', 'return register_widget("RV_PagePosts_Widget");'));
  $plugin_dir = basename(dirname(__FILE__)).'/languages';
  load_plugin_textdomain( 'rv_pagepost', false, $plugin_dir);
}
?>