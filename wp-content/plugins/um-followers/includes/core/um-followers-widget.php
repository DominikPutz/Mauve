<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class um_my_following
 */
class um_my_following extends WP_Widget {


	/**
	 * um_my_following constructor.
	 */
	function __construct() {
		parent::__construct(
		
		// Base ID of your widget
		'um_my_following', 

		// Widget name will appear in UI
		__( 'Ultimate Member - Following', 'um-followers' ),

		// Widget description
		array( 'description' => __( 'Shows users they follow in a widget.', 'um-followers' ), ) 
		);
	}


	/**
	 * Creating widget front-end
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$max = $instance['max'];
		
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style( 'um_followers' );
		wp_enqueue_script( 'um_followers' );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		
		// This is where you run the code and display the output
		echo do_shortcode( '[ultimatemember_following style=avatars max="' . $max . '"]' );
		
		echo $args['after_widget'];
	}


	/**
	 * Widget Backend
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Following', 'um-followers' );
		}
		
		if ( isset( $instance[ 'max' ] ) ) {
			$max = $instance[ 'max' ];
		} else {
			$max = '';
		}
		
		// Widget admin form
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'um-followers' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>"><?php _e( 'Maximum number of users in first view:', 'um-followers' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max' ) ); ?>" type="text" value="<?php echo esc_attr( $max ); ?>" />
		</p>
		
		<?php 
	}


	/**
	 * Updating widget replacing old instances with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['max'] = ( ! empty( $new_instance['max'] ) ) ? strip_tags( $new_instance['max'] ) : '';
		return $instance;
	}

}


/**
 * Class um_my_followers
 */
class um_my_followers extends WP_Widget {


	/**
	 * um_my_followers constructor.
	 */
	function __construct() {
		parent::__construct(
		
		// Base ID of your widget
		'um_my_followers', 

		// Widget name will appear in UI
		__( 'Ultimate Member - Followers', 'um-followers' ),

		// Widget description
		array( 'description' => __( 'Shows user followers in a widget.', 'um-followers' ), ) 
		);
	}


	/**
	 * Creating widget front-end
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$max = $instance['max'];
		
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style( 'um_followers' );
		wp_enqueue_script( 'um_followers' );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		
		// This is where you run the code and display the output
		echo do_shortcode('[ultimatemember_followers style=avatars max="' . $max . '"]');
		
		echo $args['after_widget'];
	}


	/**
	 * Widget Backend
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Followers', 'um-followers' );
		}
		
		if ( isset( $instance[ 'max' ] ) ) {
			$max = $instance[ 'max' ];
		} else {
			$max = '';
		}
		
		// Widget admin form
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'um-followers' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>"><?php _e( 'Maximum number of users in first view:', 'um-followers' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max' ) ); ?>" type="text" value="<?php echo esc_attr( $max ); ?>" />
		</p>
		
		<?php 
	}


	/**
	 * Updating widget replacing old instances with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['max'] = ( ! empty( $new_instance['max'] ) ) ? strip_tags( $new_instance['max'] ) : '';
		return $instance;
	}
}