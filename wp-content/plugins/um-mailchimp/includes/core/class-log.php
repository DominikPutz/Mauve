<?php
namespace um_ext\um_mailchimp\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class for MailChimp API requests logging
 *
 * @example UM()->classes['um_mailchimp_log']
 * @example UM()->Mailchimp()->log()
 */
class Log {

	/**
	 * A file system pointer
	 * @var resource
	 */
	private $file;

	/**
	 * Path to file
	 * @var string
	 */
	private $file_path;

	/**
	 * Max size of the log file
	 * @var int
	 */
	private $max_filesize = 4 * 1024 * 1024;


	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->file_path = UM()->files()->upload_basedir . 'mailchimp.log';
		$this->archive();
		$this->file = fopen( $this->file_path, 'ab+' );
	}


	/**
	 * Get log content
	 *
	 * @return string
	 */
	public function get() {
		$size = filesize( $this->file_path );
		$content = $size ? fread( $this->file, $size ) : '';
		return $content;
	}


	/**
	 * Get log content as HTML
	 *
	 * @return string
	 */
	public function get_html() {

		if( !file_exists( $this->file_path ) ) {
			return _e( 'No file "mailchimp.log".', 'um-mailchimp' );
		}

		$log_arr = file( $this->file_path );
		foreach( $log_arr as $key => $value ) {
			if( substr_count( $value, '-> error' ) ) {
				$log_arr[ $key ] = '<span style="color:darkred;">' . $log_arr[ $key ] . '</span>';
			}
			if( substr_count( $value, '-> success' ) ) {
				$log_arr[ $key ] = '<span style="color:darkgreen;">' . $log_arr[ $key ] . '</span>';
			}
			if( substr_count( $value, '-> warning' ) ) {
				$log_arr[ $key ] = '<span style="color:darkgoldenrod;">' . $log_arr[ $key ] . '</span>';
			}
		}

		$content = implode( '</br>', $log_arr );

		return $content;
	}


	/**
	 * Add new record to the log
	 *
	 * @param array $data
	 */
	public function add( $data ) {
		$content = date( 'm/d/Y H:i:s' );

		if( !empty( $data[ 'method' ] ) ) {
			$content .= ' [' . strtoupper( $data[ 'method' ] ) . ']';
		}

		if( !empty( $data[ 'url' ] ) ) {
			$content .= ' ' . $data[ 'url' ];
		}

		if ( isset( $data[ 'status' ] ) ) {
			if ( $data[ 'status' ] ) {
				$content .= ' -> success';
			}
			elseif ( isset( $data[ 'response' ][ 'title' ] ) && $data[ 'response' ][ 'title' ] === "Resource Not Found" ) {
				$content .= ' -> warning';
			}
			else {
				$content .= ' -> error';
			}
		}

		if( !empty( $data[ 'args' ] ) ) {
			$content .= PHP_EOL;
			$content .= 'ARGS: ' . json_encode( $data[ 'args' ] );
		}

		/**
		 * @see option "Log response for all requests"
		 */
		if( empty( $data[ 'status' ] ) || UM()->options()->get( 'mailchimp_enable_log_response' ) ) {

			if( isset( $data[ 'response' ] ) ) {
				$content .= PHP_EOL;
				$content .= 'RESPONSE: ' . json_encode( $this->remove_links( $data[ 'response' ] ) );
			}

			if( isset( $data[ 'trace' ] ) && defined( 'UM_DEBUG' ) ) {
				$content .= PHP_EOL;
				$content .= 'TRACE: ' . $data[ 'trace' ];
			}
		}

		$content .= PHP_EOL . PHP_EOL;

		fwrite( $this->file, $content );
	}


	/**
	 * Archive log if it is too big
	 */
	public function archive() {
		if ( is_file( $this->file_path ) && filesize( $this->file_path ) > $this->max_filesize ) {
			if ( copy( $this->file_path, UM()->files()->upload_basedir . 'mailchimp ' . date('Y-m-d H:i:s') . '.log' ) ) {
				$this->clear();
			}
		}
	}


	/**
	 * Clear log file
	 */
	public function clear() {
		if( file_exists( $this->file_path ) ) {
			file_put_contents( $this->file_path, '' );
		}
	}


	/**
	 * Recursive remove links
	 *
	 * @param mixed $arr
	 * @return mixed
	 */
	public function remove_links( $arr ) {
		if( is_array( $arr ) ) {
			foreach( $arr as $k => $v ) {
				if( $k === '_links' ) {
					unset( $arr[ $k ] );
				} else {
					$arr[ $k ] = $this->remove_links( $v );
				}
			}
		}

		return $arr;
	}

}