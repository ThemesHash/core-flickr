<?php

/*-----------------------------------------------------------------------------------

	Plugin Name: Core Flickr
	Plugin URI: http://themeshash.com/wordpress-plugins/
	Description: A widget for showing latest Flickr photos via widget.
	Version: 1.0
	Author: Muhammad Faisal
	Author URI: http://themeshash.com/

-----------------------------------------------------------------------------------*/


// Add function to widgets_init that'll load our widget
add_action( 'widgets_init', 'th_flickr_widget_init' );

// Register widget
function th_flickr_widget_init() {
	register_widget( 'th_flickr_widget' );
}

// Widget class
class th_flickr_widget extends WP_Widget {


	#-------------------------------------------------------------------------------#
	#  Widget Setup
	#-------------------------------------------------------------------------------#
	
	function __construct() {

		// Widget settings
		$widget_ops = array(
			'classname' => 'widget-instagram',
			'description' => esc_html__('A widget for showing your latest Flickr photos.', 'themeshash')
		);

		// Widget control settings
		$control_ops = array(
			'width' => 300,
			'height' => 350,
			'id_base' => 'th_flickr_widget'
		);

		// Create the widget
		parent::__construct( 'th_flickr_widget', esc_html__('Flickr Photostream', 'themeshash'), $widget_ops, $control_ops );
		
	}


	#-------------------------------------------------------------------------------#
	#  Widget Display
	#-------------------------------------------------------------------------------#
	
	public function widget( $args, $instance ) {
		extract( $args );

		// Our variables from the widget settings
		$title = apply_filters('widget_title', $instance['title'] );
		$id = $instance['id'];
		$limit = $instance['number'];


		// Before widget (defined by theme functions file)
		echo wp_kses_post( $before_widget );

		// Display the widget title if one was input
		if ( $title )
			echo wp_kses_post( $before_title . $title . $after_title );

		?>
	     
        <div class="widget-content">
            
	    	<?php do_action( 'th_before_flickr_widget' ); ?>

			<?php echo wp_kses_post( $this->parseFlickrFeed($id, $limit) ); ?>

	    	<?php do_action( 'th_after_flickr_widget' ); ?>
                        
        </div>

		<?php

		// After widget (defined by theme functions file)
		echo wp_kses_post( $after_widget );
		
	}

	#-------------------------------------------------------------------------------#
	#  Widget Update
	#-------------------------------------------------------------------------------#
	
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags to remove HTML (important for text inputs)
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['id'] = strip_tags( $new_instance['id'] );
		$instance['number'] = strip_tags( $new_instance['number'] );

		// No need to strip tags
		return $instance;
	}

	#-------------------------------------------------------------------------------#
	#  Widget Form
	#-------------------------------------------------------------------------------#
		 
	public function form( $instance ) {

		// Set up some default widget settings
		$defaults = array(
			'title' => __('Flickr Stream', 'themeshash'),
			'id' => '',
			'number' => '9',
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Title: Text Input -->	
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title', 'themeshash') ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<!-- User Name: Text Input -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php esc_html_e('Flickr ID', 'themeshash') ?>: (<a href="<?php echo esc_url( esc_html__( 'http://idgettr.com/', 'themeshash' ) ); ?>">idGettr</a>)</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['id'] ); ?>" />
		</p>

		<!-- Number: Text Input -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e('Number of Photos', 'themeshash') ?>:</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['number'] ); ?>" />
		</p>

		<?php

	}

	#-------------------------------------------------------------------------------#
	#  Flickr Scarpper
	#-------------------------------------------------------------------------------#

	public function attr($s,$attrname) { // return html attribute
		preg_match_all('#\s*('.$attrname.')\s*=\s*["|\']([^"\']*)["|\']\s*#i', $s, $x);
		if (count($x)>=3) return $x[2][0]; else return "";
	}
	 
	// id = id of the feed
	// n = number of thumbs
	public function parseFlickrFeed($id,$n) {
		$url = "http://api.flickr.com/services/feeds/photos_public.gne?id={$id}&lang=it-it&format=rss_200";
		$s = file_get_contents($url);
		preg_match_all('#<item>(.*)</item>#Us', $s, $items);

		// filters for custom classes
		$ul_class = esc_attr( apply_filters( 'class', 'flickr-pics ' ) );

		$out = "";
		$out.= "<ul class=". esc_attr( $ul_class ) .">";
		for($i=0;$i<count($items[1]);$i++) {
			if($i>=$n) break;
			$item = $items[1][$i];
			preg_match_all('#<link>(.*)</link>#Us', $item, $temp);
			$link = $temp[1][0];
			$title = 'Flickr Photo';
			preg_match_all('#<media:thumbnail([^>]*)>#Us', $item, $temp);
			$thumb = $this->attr($temp[0][0],"url");
			
			$out.= "<li><a href='$link' target='_blank' title=\"".str_replace('"','',$title)."\"><img src='$thumb' alt=\"".str_replace('"','',$title)."\" /></a></li>";
		}
		$out.= "</ul>";
		
		return $out;
	}	


}