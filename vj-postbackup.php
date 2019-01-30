<?php
/*
Plugin Name: 輔仁網: 作者文章備份
Description: 我誤判 ‧ 我恐懼
Version: 1.0
Author: <a href="http://www.vjmedia.com.hk">技術組</a>
GitHub Plugin URI: https://github.com/VJMedia/vj-postbackup
*/

defined('WPINC') || (header("location: /") && die());

function vj_postbackup_addmenu(){
    	add_menu_page('文章備份', '文章備份', 'read', 'vj-postbackup', 'vj_postbackup_render','dashicons-arrow-down-alt');
}

function vj_postbackup_ajax(){
	require_once( 'export.inc.php' );
	if ( isset( $_POST['download'] ) ) {
		check_ajax_referer('vj-postbackup-'.get_current_user_id() );
		$args = array();
		$args['content'] = 'post';
		$args['author'] = get_current_user_id();

		if ( $_POST['post_start_date'] || $_POST['post_end_date'] ) {
			$args['start_date'] = $_POST['post_start_date'];
			$args['end_date'] = $_POST['post_end_date'];
		}

		if ( $_POST['post_status'] )
			$args['status'] = $_POST['post_status'];

		if ( $_GET['page_status'] )
			$args['status'] = $_GET['page_status'];
		
		$args = apply_filters( 'export_args', $args );
		export_wp( $args );
		exit();
	}
	
}

add_action( 'wp_ajax_vj_postbackup', 'vj_postbackup_ajax' );
add_action('admin_menu','vj_postbackup_addmenu');
function vj_postbackup_render(){
	
?>
<div class="wrap">
<h2>善始善終 作者文章備份</h2>
<p>我誤判 我恐懼 所以我要Backup文章</p>
<h3>備份我的文章</h3>
<form method="post" id="export-filters" action="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=vj_postbackup">
<input type="hidden" name="download" value="true" />
<p class="description">備份輸出的XML檔可以匯入到自己的Wordpress 網站</p>
<div><img src="<?php echo plugins_url("vj-postbackup.png",__FILE__); ?>" /></div>
<ul id="post-filters" class="export-filters">
	<li>
		<label><?php _e( 'Date range:' ); ?></label>
		<select name="post_start_date">
			<option value="0"><?php _e( 'Start Date' ); ?></option>
			<?php vj_postbackup_date(); ?>
		</select>
		<select name="post_end_date">
			<option value="0"><?php _e( 'End Date' ); ?></option>
			<?php vj_postbackup_date(); ?>
		</select>
	</li>
	<li>
		<label><?php _e( 'Status:' ); ?></label>
		<select name="post_status">
			<option value="0"><?php _e( 'All' ); ?></option>
			<?php $post_stati = get_post_stati( array( 'internal' => false ), 'objects' );
			foreach ( $post_stati as $status ) : ?>
			<option value="<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></option>
			<?php endforeach; ?>
		</select>
	</li>
</ul>

<?php
do_action( 'export_filters' );
?>
<?php wp_nonce_field( 'vj-postbackup-'.get_current_user_id() ); ?>

<?php submit_button("我誤判 我恐懼"); //__('Download Export File')?>
</form>
</div>


<?

}


function vj_postbackup_js(){ ?><?php }

function vj_postbackup_date( $post_type = 'post' ) {
	global $wpdb, $wp_locale;

	$months = $wpdb->get_results( $wpdb->prepare( "
		SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
		FROM $wpdb->posts
		WHERE post_type = %s AND post_status != 'auto-draft' AND post_author = ".get_current_user_id()."
		ORDER BY post_date DESC
	", $post_type ) );

	$month_count = count( $months );
	if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
		return;

	foreach ( $months as $date ) {
		if ( 0 == $date->year )
			continue;

		$month = zeroise( $date->month, 2 );
		echo '<option value="' . $date->year . '-' . $month . '">' . $wp_locale->get_month( $month ) . ' ' . $date->year . '</option>';
	}
}

?>
