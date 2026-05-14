<?php

/* Taken from madx CDN off-linker, a plugin by W-Mark Kubacki (http://mark.madx.de/) and used with permission */

if ( ! isset( $madxcdn ) ) {
	$madxcdn = 1; // have to default to on for existing users.
}

if ( 1 === $madxcdn && ! is_admin() ) {
	add_action( 'init', 'do_scmadx_off_ob_start' );
}

/**
 * Set up some defaults.
 *
 * @global string $madx_off_blog_url
 * @global string $madx_off_cdn_url
 * @global string $madx_cname
 * @global int    $madx_https
 * @global array  $madx_off_include_dirs
 * @global array  $madx_off_excludes
 * @global array  $madx_arr_of_cnames
 *
 * @return void
 */
function scmadx_off_get_options() {
	global $madx_off_blog_url, $madx_off_cdn_url, $madx_cname, $madx_https;
	global $madx_off_include_dirs, $madx_off_excludes, $madx_arr_of_cnames;

	$madx_off_blog_url = get_option( 'madx_off_blog_url' );
	if ( false === $madx_off_blog_url ) {
		$madx_off_blog_url = untrailingslashit( get_site_url() );
		add_option( 'madx_off_blog_url', $madx_off_blog_url );
	}

	if ( has_filter( 'madx_off_blog_url' ) ) {
		$madx_off_blog_url = untrailingslashit( apply_filters( 'madx_off_blog_url', $madx_off_blog_url ) );
	}

	$madx_off_cdn_url = get_option( 'madx_off_cdn_url' );
	if ( false === $madx_off_cdn_url ) {
		$madx_off_cdn_url = untrailingslashit( get_site_url() );
		add_option( 'madx_off_cdn_url', $madx_off_cdn_url );
	}

	$include_dirs = get_option( 'madx_off_include_dirs' );
	if ( false !== $include_dirs ) {
		$madx_off_include_dirs = array_filter( array_map( 'trim', explode( ',', $include_dirs ) ) );
	} else {
		$madx_off_include_dirs = scmadx_off_default_inc_dirs();
		add_option( 'madx_off_include_dirs', implode( ',', $madx_off_include_dirs ) );
	}

	$exclude = get_option( 'madx_off_exclude' );
	if ( false !== $exclude ) {
		$madx_off_excludes = array_filter( array_map( 'trim', explode( ',', $exclude ) ) );
	} else {
		$madx_off_excludes = array( '.php' );
		add_option( 'madx_off_exclude', implode( ',', $madx_off_excludes ) );
	}

	$madx_cname = get_option( 'madx_cname' );
	if ( false !== $madx_cname ) {
		$madx_cname = trim( $madx_cname );
	} else {
		$madx_cname = '';
		add_option( 'madx_cname', $madx_cname );
	}
	$madx_arr_of_cnames = array_filter( array_map( 'trim', explode( ',', $madx_cname ) ) );

	$madx_https = intval( get_option( 'madx_https' ) );
}

/**
 * Get default directories.
 *
 * @return array
 */
function scmadx_off_default_inc_dirs() {

	$home_path = trailingslashit( (string) parse_url( get_option( 'siteurl' ), PHP_URL_PATH ) );
	$inc_dirs  = array();

	foreach ( array( content_url(), includes_url() ) as $dir ) {
		$dir        = wp_make_link_relative( $dir );
		$dir        = preg_replace( '`^' . preg_quote( $home_path, '`' ) . '`', '', $dir );
		$inc_dirs[] = trim( $dir, '/' );
	}

	return $inc_dirs;
}

/**
 * Determines whether to exclude a match.
 *
 * @param string $match    URI to examine.
 * @param array  $excludes Array of "badwords".
 *
 * @return boolean true if to exclude given match from rewriting.
 */
function scmadx_off_exclude_match( $match, $excludes ) {
	foreach ( $excludes as $badword ) {
		if ( false !== stripos( $match, $badword ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Compute string modulo, based on SHA1 hash
 *
 * @param string $str The string.
 * @param int    $mod The divisor.
 *
 * @return int The remainder.
 */
function scmadx_string_mod( $str, $mod ) {
	/**
	 * The full SHA1 is too large for PHP integer types.
	 * This should be enough for our purpose.
	 */
	$num = hexdec( substr( sha1( $str ), 0, 5 ) );

	return $num % $mod;
}

/**
 * Rewriter of URLs, used as replace-callback.
 *
 * Called by #scmadx_off_filter.
 */
function scmadx_off_rewriter( $match ) {
	global $madx_off_blog_url, $madx_https, $madx_off_excludes, $madx_arr_of_cnames;
	static $count_cnames = null, $include_dirs = null;

	// Set up static variables. Run once only.
	if ( ! isset( $count_cnames ) ) {
		$count_cnames = count( $madx_arr_of_cnames );
		$include_dirs = scmadx_off_additional_directories();
	}

	if ( $madx_https && 0 === strpos( $match[0], 'https' ) ) {
		return $match[0];
	}

	if ( scmadx_off_exclude_match( $match[0], $madx_off_excludes ) ) {
		return $match[0];
	}

	if ( preg_match( '`(' . $include_dirs . ')`', $match[0] ) ) {
		$offset = scmadx_string_mod( $match[1], $count_cnames );
		return str_replace( $madx_off_blog_url, $madx_arr_of_cnames[ $offset ], $match[0] );
	}

	return $match[0];
}

/**
 * Creates a regexp compatible pattern from the directories to be included in matching.
 *
 * @return String with the pattern with {@literal |} as prefix, or empty
 */
function scmadx_off_additional_directories() {
	global $madx_off_include_dirs;

	$arr_dirs = array();
	foreach ( $madx_off_include_dirs as $dir ) {
		$arr_dirs[] = preg_quote( trim( $dir ), '`' );
	}

	return implode( '|', $arr_dirs );
}

/**
 * Output filter which runs the actual plugin logic.
 *
 * @param  string $content The content of the output buffer.
 *
 * @return string The rewritten content.
 */
function scmadx_off_filter( $content ) {
	global $madx_off_blog_url, $madx_off_cdn_url;
	global $madx_off_include_dirs, $madx_off_excludes, $madx_arr_of_cnames;

	if ( empty( $content ) || empty( $madx_off_cdn_url ) ||
		$madx_off_blog_url === $madx_off_cdn_url
	) {
		return $content; // no rewrite needed.
	}

	if ( empty( $madx_off_include_dirs ) || ! is_array( $madx_off_include_dirs ) ) {
		$madx_off_include_dirs = scmadx_off_default_inc_dirs();
	}

	if ( empty( $madx_off_excludes ) || ! is_array( $madx_off_excludes ) ) {
		$madx_off_excludes = array();
	}

	if ( ! in_array( $madx_off_cdn_url, (array) $madx_arr_of_cnames, true ) ) {
		$madx_arr_of_cnames = array_merge( array( $madx_off_cdn_url ), (array) $madx_arr_of_cnames );
	}

	$madx_arr_of_cnames = apply_filters( 'wpsc_cdn_urls', $madx_arr_of_cnames );

	$dirs  = scmadx_off_additional_directories();
	$regex = '`(?<=[(\"\'])' . preg_quote( $madx_off_blog_url, '`' ) . '/(?:((?:' . $dirs . ')[^\"\')]+)|([^/\"\']+\.[^/\"\')]+))(?=[\"\')])`';
	return preg_replace_callback( $regex, 'scmadx_off_rewriter', $content );
}

/**
 * Registers scmadx_off_filter as output buffer, if needed.
 */
function do_scmadx_off_ob_start() {
	global $madx_off_blog_url, $madx_off_cdn_url;

	if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'photon' ) ) {
		return;
	}

	scmadx_off_get_options();

	if ( ! empty( $madx_off_cdn_url ) &&
		$madx_off_blog_url !== $madx_off_cdn_url
	) {
		add_filter( 'wp_cache_ob_callback_filter', 'scmadx_off_filter' );
	}
}

/**
 * Update CDN settings to the options database table.
 */
function scmadx_off_update() {

	if ( isset( $_POST['action'], $_POST['_wpnonce'] )
		&& 'update_madx_off' === $_POST['action'] // WPCS: sanitization ok.
		&& wp_verify_nonce( $_POST['_wpnonce'], 'wp-cache' )
	) {
		update_option( 'madx_off_cdn_url', untrailingslashit( wp_unslash( $_POST['madx_off_cdn_url'] ) ) ); // WPSC: sanitization ok.
		update_option( 'madx_off_blog_url', untrailingslashit( wp_unslash( $_POST['madx_off_blog_url'] ) ) ); // WPSC: sanitization ok.

		if ( empty( $_POST['madx_off_include_dirs'] ) ) {
			$include_dirs = implode( ',', scmadx_off_default_inc_dirs() );
		} else {
			$include_dirs = sanitize_text_field( wp_unslash( $_POST['madx_off_include_dirs'] ) ); // WPSC: validation ok,sanitization ok.
		}
		update_option( 'madx_off_include_dirs', $include_dirs );

		update_option( 'madx_off_exclude', sanitize_text_field( wp_unslash( $_POST['madx_off_exclude'] ) ) ); // WPSC: sanitization ok.
		update_option( 'madx_cname', sanitize_text_field( wp_unslash( $_POST['madx_cname'] ) ) ); // WPSC: sanitization ok.

		$madx_https = empty( $_POST['madx_https'] ) ? 0 : 1;
		$madxcdn    = empty( $_POST['madxcdn'] ) ? 0 : 1;

		update_option( 'madx_https', $madx_https );
		wp_cache_setting( 'madxcdn', $madxcdn );
	}
}

/**
 * Show CDN settings.
 */
function scmadx_off_options() {
	global $madxcdn, $madx_off_blog_url, $madx_off_cdn_url, $madx_cname, $madx_https;
	global $madx_off_include_dirs, $madx_off_excludes;

	scmadx_off_update();

	scmadx_off_get_options();

	$example_cdn_uri = ( is_ssl() ? 'https' : 'http' ) . '://cdn.' . preg_replace( '`^(https?:)?//(www\.)?`', '', get_site_url() );
	$example_cnames  = str_replace( '://cdn.', '://cdn1.', $example_cdn_uri );
	$example_cnames .= ',' . str_replace( '://cdn.', '://cdn2.', $example_cdn_uri );
	$example_cnames .= ',' . str_replace( '://cdn.', '://cdn3.', $example_cdn_uri );

	$example_cdn_uri  = ( get_site_url() === $madx_off_cdn_url ) ? $example_cdn_uri : $madx_off_cdn_url;
	$example_cdn_uri .= '/wp-includes/js/jquery/jquery-migrate.js';
	$example_cdn_uri  = esc_url( $example_cdn_uri );
	?>
		<h3><?php _e( 'Jetpack CDN' ); ?></h3>
		<p><?php printf(
			__( 'The free %1$sJetpack plugin%2$s has a %3$sSite Accelerator%2$s feature that is easier to use than the CDN functionality in this plugin. However files will be cached "forever" and will not update if you update the local file. Files will need to be renamed to refresh them. The %3$sJetpack documentation%2$s explains more about this.', 'wp-madx-cache' ),
			'<a href="https://jetpack.com/">',
			'</a>',
			'<a href="https://jetpack.com/support/site-accelerator/">'
		); ?></p>
	<?php
	if ( class_exists( 'Jetpack' ) ) {
		if ( Jetpack::is_module_active( 'photon' ) ) {
			?><p><strong><?php printf(
				__( 'You already have Jetpack installed and %1$sSite Accelerator%2$s enabled on this blog. The CDN here is disabled to avoid conflicts with Jetpack.', 'wp-madx-cache' ),
				'<a href="https://jetpack.com/support/site-accelerator/">',
				'</a>'
			); ?></strong></p><?php
		} else {
			?><p><?php printf(
				__( 'You already have Jetpack installed but %1$sSite Accelerator%2$s is disabled on this blog. Enable it on the %3$sJetpack settings page%2$s.', 'wp-madx-cache' ),
				'<a href="https://jetpack.com/support/site-accelerator/">',
				'</a>',
				'<a href="' . admin_url( 'admin.php?page=jetpack#/settings' ) . '">'
			); ?></p><?php
		}
	} else {
			?><p><strong><?php printf(
				__( '%1$sJetpack%2$s was not found on your site but %3$syou can install it%2$s. The Site Accelerator feature is free to use on any WordPress site and offers the same benefit as other CDN services. You should give it a try!', 'wp-madx-cache' ),
				'<a href="https://jetpack.com/">',
				'</a>',
				'<a href="' . admin_url( 'plugin-install.php?s=jetpack&tab=search&type=term' ) . '">'
			); ?></strong></p><?php
	}
	if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'photon' ) ) {
		return;
	}
	?>
		<h3><?php _e( 'Simple CDN' ); ?></h3>
		<p><?php _e( 'Your website probably uses lots of static files. Image, Javascript and CSS files are usually static files that could just as easily be served from another site or CDN. Therefore, this plugin replaces any links in the <code>wp-content</code> and <code>wp-includes</code> directories (except for PHP files) on your site with the URL you provide below. That way you can either copy all the static content to a dedicated host or mirror the files to a CDN by <a href="https://www.google.com/search?q=cdn+origin+pull" target="_blank">origin pull</a>.', 'wp-madx-cache' ); ?></p>
		<p><?php printf( __( '<strong style="color: red">WARNING:</strong> Test some static urls e.g., %s  to ensure your CDN service is fully working before saving changes.', 'wp-madx-cache' ), '<code>' . esc_html( $example_cdn_uri ) . '</code>' ); ?></p>

	<?php if ( get_home_url() !== $madx_off_blog_url ) { ?>
		<p><?php printf( __( '<strong style="color: red">WARNING:</strong> Your siteurl and homeurl are different. The plugin is using %s as the homepage URL of your site but if that is wrong please use the filter "madx_off_blog_url" to fix it.', 'wp-madx-cache' ), '<code>' . esc_html( $madx_off_blog_url ) . '</code>' ); ?></p>
	<?php } ?>
		<p><?php esc_html_e( 'You can define different CDN URLs for each site on a multsite network.', 'wp-madx-cache' ); ?></p>
		<p><form method="post" action="">
		<?php wp_nonce_field( 'wp-cache' ); ?>
		<table class="form-table"><tbody>
			<tr valign="top">
				<td style='text-align: right'>
					<input id='madxcdn' type="checkbox" name="madxcdn" value="1" <?php checked( $madxcdn ); ?> />
				</td>
				<th scope="row"><label for="madxcdn"><?php esc_html_e( 'Enable CDN Support', 'wp-madx-cache' ); ?></label></th>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="madx_off_cdn_url"><?php esc_html_e( 'Site URL', 'wp-madx-cache' ); ?></label></th>
				<td>
					<input type="text" name="madx_off_blog_url" value="<?php echo esc_attr( untrailingslashit( $madx_off_blog_url ) ); ?>" size="64" class="regular-text code" /><br />
					<span class="description"><?php _e( 'The URL of your site. No trailing <code>/</code> please.', 'wp-madx-cache' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="madx_off_cdn_url"><?php esc_html_e( 'Off-site URL', 'wp-madx-cache' ); ?></label></th>
				<td>
					<input type="text" name="madx_off_cdn_url" value="<?php echo esc_attr( $madx_off_cdn_url ); ?>" size="64" class="regular-text code" /><br />
					<span class="description"><?php printf( __( 'The new URL to be used in place of %1$s for rewriting. No trailing <code>/</code> please.<br />Example: <code>%2$s</code>.', 'wp-madx-cache' ), esc_html( get_site_url() ), esc_html( $example_cdn_uri ) ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="madx_off_include_dirs"><?php esc_html_e( 'Include directories', 'wp-madx-cache' ); ?></label></th>
				<td>
					<input type="text" name="madx_off_include_dirs" value="<?php echo esc_attr( implode( ',', $madx_off_include_dirs ) ); ?>" size="64" class="regular-text code" /><br />
					<span class="description"><?php _e( 'Directories to include in static file matching. Use a comma as the delimiter. Default is <code>wp-content, wp-includes</code>, which will be enforced if this field is left empty.', 'wp-madx-cache' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="madx_off_exclude"><?php esc_html_e( 'Exclude if substring', 'wp-madx-cache' ); ?></label></th>
				<td>
					<input type="text" name="madx_off_exclude" value="<?php echo esc_attr( implode( ',', $madx_off_excludes ) ); ?>" size="64" class="regular-text code" /><br />
					<span class="description"><?php _e( 'Excludes something from being rewritten if one of the above strings is found in the URL. Use a comma as the delimiter like this, <code>.php, .flv, .do</code>, and always include <code>.php</code> (default).', 'wp-madx-cache' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="madx_cname"><?php esc_html_e( 'Additional CNAMES', 'wp-madx-cache' ); ?></label></th>
				<td>
					<input type="text" name="madx_cname" value="<?php echo esc_attr( $madx_cname ); ?>" size="64" class="regular-text code" /><br />
					<span class="description"><?php printf( __( 'These <a href="https://www.wikipedia.org/wiki/CNAME_record">CNAMES</a> will be used in place of %1$s for rewriting (in addition to the off-site URL above). Use a comma as the delimiter. For pages with a large number of static files, this can improve browser performance. CNAMEs may also need to be configured on your CDN.<br />Example: %2$s', 'wp-madx-cache' ), esc_html( get_site_url() ), esc_html( $example_cnames ) ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" colspan='2'><label><input type='checkbox' name='madx_https' value='1' <?php checked( $madx_https ); ?> /><?php esc_html_e( 'Skip https URLs to avoid "mixed content" errors', 'wp-madx-cache' ); ?></label></th>
			</tr>
		</tbody></table>
		<input type="hidden" name="action" value="update_madx_off" />
		<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-madx-cache' ); ?>" /></p>
		</form></p>
		<p><?php _e( 'CDN functionality provided by <a href="https://wordpress.org/plugins/madx-cdn-off-linker/">madx CDN Off Linker</a> by <a href="http://mark.madx.de/">Mark Kubacki</a>', 'wp-madx-cache' ); ?></p>
	<?php
}
