<?php
/**
 * Fix Image Rotation
 *
 * Automatically corrects image orientation based on EXIF data.
 * Fixes images uploaded from mobile phones and cameras that store
 * orientation metadata in EXIF rather than rotating the actual pixels.
 *
 * Integrated from the "Fix Image Rotation" plugin by Gagan Deep Singh
 * (https://wordpress.org/plugins/fix-image-rotation/), licensed GPLv2.
 *
 * @package Surbma_WP_Control
 */

defined( 'ABSPATH' ) || die;

if ( ! class_exists( 'Surbma_WP_Control_Fix_Image_Rotation' ) ) {

	/**
	 * Handles automatic EXIF-based image orientation correction on upload.
	 *
	 * @since 24.0
	 */
	class Surbma_WP_Control_Fix_Image_Rotation {

		/**
		 * Tracks file paths that have already been processed, to prevent
		 * double-processing (pre-filter and post-upload both fire).
		 *
		 * @since 24.0
		 * @access private
		 * @var array
		 */
		private $orientation_fixed = array();

		/**
		 * Stores image metadata before GD processing so it can be restored
		 * afterwards (GD strips metadata on rotate/flip; Imagick does not).
		 *
		 * @since 24.0
		 * @access private
		 * @var array
		 */
		private $previous_meta = array();

		/**
		 * Singleton instance.
		 *
		 * @since 24.0
		 * @access protected
		 * @var Surbma_WP_Control_Fix_Image_Rotation|null
		 */
		protected static $instance = null;

		/**
		 * Returns the singleton instance.
		 *
		 * @since 24.0
		 * @return Surbma_WP_Control_Fix_Image_Rotation
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Registers WordPress upload hooks (only when PHP EXIF extension is available).
		 *
		 * @since 24.0
		 * @return void
		 */
		public function register_hooks() {
			/* function_exists() is used because it is faster and also detects disabled functions. */
			if ( extension_loaded( 'exif' ) && function_exists( 'exif_read_data' ) ) {
				add_filter( 'wp_handle_upload_prefilter', array( $this, 'filter_wp_handle_upload_prefilter' ), 10, 1 );
				add_filter( 'wp_handle_upload', array( $this, 'filter_wp_handle_upload' ), 1, 3 );
			}
			// Silently skip if EXIF is unavailable — no admin UI or notices needed.
		}

		/**
		 * Pre-upload filter: runs on the temporary file before WordPress moves it.
		 *
		 * @since 24.0
		 * @param array $file An array of data for a single file.
		 * @return array An array of data for a single file.
		 */
		public function filter_wp_handle_upload_prefilter( $file ) {
			$suffix = substr( $file['name'], strrpos( $file['name'], '.', -1 ) + 1 );
			if ( in_array( strtolower( $suffix ), array( 'jpg', 'jpeg', 'tiff' ), true ) ) {
				$this->fix_image_orientation( $file['tmp_name'] );
			}
			return $file;
		}

		/**
		 * Post-upload filter: runs on the final file path after WordPress moves it.
		 *
		 * @since 24.0
		 * @param array $file Array of upload data (file, url, type).
		 * @return array Array of upload data.
		 */
		public function filter_wp_handle_upload( $file ) {
			$suffix = substr( $file['file'], strrpos( $file['file'], '.', -1 ) + 1 );
			if ( in_array( strtolower( $suffix ), array( 'jpg', 'jpeg', 'tiff' ), true ) ) {
				$this->fix_image_orientation( $file['file'] );
			}
			return $file;
		}

		/**
		 * Fixes the orientation of a single image file based on its EXIF data.
		 *
		 * @since 24.0
		 * @param string $file Absolute path to the image file.
		 * @return void
		 */
		public function fix_image_orientation( $file ) {
			if ( isset( $this->orientation_fixed[ $file ] ) ) {
				return;
			}

			$exif = exif_read_data( $file );

			if ( isset( $exif['Orientation'] ) && $exif['Orientation'] > 1 ) {
				// Ensure WordPress image editors are available.
				include_once ABSPATH . 'wp-admin/includes/image-edit.php';

				$operations = $this->calculate_flip_and_rotate( $file, $exif );

				if ( false !== $operations ) {
					$this->do_flip_and_rotate( $file, $operations );
				}
			}
		}

		/**
		 * Calculates the rotation and flip operations required to normalise orientation.
		 *
		 * @since 24.0
		 * @access private
		 * @param string $file Absolute path to the image file.
		 * @param array  $exif EXIF data array from exif_read_data().
		 * @return array|false Operations array, or false if no correction needed.
		 */
		private function calculate_flip_and_rotate( $file, $exif ) {
			$rotator     = false;
			$flipper     = false;
			$orientation = 0;

			switch ( $exif['Orientation'] ) {
				case 1:
					// Already correct — mark as done and bail.
					$this->orientation_fixed[ $file ] = true;
					return false;
				case 2:
					$flipper = array( false, true );
					break;
				case 3:
					$orientation = -180;
					$rotator     = true;
					break;
				case 4:
					$flipper = array( true, false );
					break;
				case 5:
					$orientation = -90;
					$rotator     = true;
					$flipper     = array( false, true );
					break;
				case 6:
					$orientation = -90;
					$rotator     = true;
					break;
				case 7:
					$orientation = -270;
					$rotator     = true;
					$flipper     = array( false, true );
					break;
				case 8:
				case 9:
					$orientation = -270;
					$rotator     = true;
					break;
				default:
					$orientation = 0;
					$rotator     = true;
					break;
			}

			return compact( 'orientation', 'rotator', 'flipper' );
		}

		/**
		 * Applies the calculated rotation and flip operations to the image file.
		 *
		 * GD Library strips metadata on rotate/flip, so it is saved beforehand
		 * and restored via the wp_read_image_metadata filter. Imagick preserves
		 * metadata natively and requires no special handling.
		 *
		 * @since 24.0
		 * @access private
		 * @param string $file       Absolute path to the image file.
		 * @param array  $operations Operations array from calculate_flip_and_rotate().
		 * @return bool True on success, false on failure.
		 */
		private function do_flip_and_rotate( $file, $operations ) {
			$editor = wp_get_image_editor( $file );

			// Save metadata before GD processes the image (GD strips it on rotate/flip).
			if ( 'WP_Image_Editor_GD' === get_class( $editor ) ) {
				include_once ABSPATH . 'wp-admin/includes/image.php';
				$this->previous_meta[ $file ] = wp_read_image_metadata( $file );
			}

			if ( is_wp_error( $editor ) ) {
				return false;
			}

			if ( true === $operations['rotator'] ) {
				$editor->rotate( $operations['orientation'] );
			}
			if ( false !== $operations['flipper'] ) {
				$editor->flip( $operations['flipper'][0], $operations['flipper'][1] );
			}

			$editor->save( $file );
			$this->orientation_fixed[ $file ] = true;

			add_filter( 'wp_read_image_metadata', array( $this, 'restore_meta_data' ), 10, 2 );

			return true;
		}

		/**
		 * Restores image metadata after GD-based processing.
		 *
		 * Hooked onto wp_read_image_metadata only when GD is the active editor
		 * and only after a successful rotation/flip. Sets orientation to 1 to
		 * reflect the now-corrected pixel data.
		 *
		 * @since 24.0
		 * @param array  $meta Image metadata array.
		 * @param string $file Absolute path to the image file.
		 * @return array Possibly-restored image metadata.
		 */
		public function restore_meta_data( $meta, $file ) {
			if ( isset( $this->previous_meta[ $file ] ) ) {
				$meta                = $this->previous_meta[ $file ];
				$meta['orientation'] = 1;
				return $meta;
			}
			return $meta;
		}
	}
}

// Instantiate and register hooks immediately — no options or admin UI required.
Surbma_WP_Control_Fix_Image_Rotation::get_instance()->register_hooks();
