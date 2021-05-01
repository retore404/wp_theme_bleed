<?php
/**
 * PhotoSoushi WordPress Theme
 *
 * @package WordPress
 * @subpackage PhotoSoushi
 * @author retore
 * @link https://github.com/retore404/PhotoSoushi
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */

?>

<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="s" id="searchform_label">Search: </label>
	<input type="text" value="" name="s" id="s" style="max-width: 100%;" />
	<input type="submit" id="searchsubmit" value="Search" />
</form>
