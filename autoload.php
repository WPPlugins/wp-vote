<?php
/**
 * an example of a project-specific implementation.
 *
 * @param   string $class The fully-qualified class name.
 *
 * @return  void
 */

namespace WP_Vote;

spl_autoload_register( function ( $class ) {

	/* class prefix */
	$prefix = __NAMESPACE__;

	/* base directory class files. */
	$base_dir = __DIR__ . '/';

	/* check class prefix. */
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {

		return;
	}

	/* get the relative class name. */
	$relative_class = substr( $class, $len );

	$relative_class = strtolower( $relative_class );

	$relative_class = str_replace( '_', '-', $relative_class );

	$relative_class = str_replace( '//', '/', $relative_class );

	$relative_class = str_replace( '\\', '\class-', $relative_class );

	/* get class file. */
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	/* if the file exists, require it. */
	if ( file_exists( $file ) ) {
		require $file;

		return;
	}

	$possible_folders = array( 'post-types', 'includes', 'question-types', 'voter-types', 'public', 'admin' );

	foreach ( $possible_folders as $possible_folder ) {
		$file = $base_dir . $possible_folder . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require $file;

			return;
		}
	}
} );
