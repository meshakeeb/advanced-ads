<?php
/**
 * Advanced Ads – form to edit ad groups in the admin
 *
 * @package   Advanced_Ads_Admin
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright since 2013 Thomas Maier, Advanced Ads GmbH
 *
 * @var Advanced_Ads_Group $group        Ad group object.
 * @var array              $ad_form_rows Array with HTML for ad form.
 * @var string             $hints_html   Hints markup.
 */

// Open form if this was the last edited.
$is_last_edited = isset( $_REQUEST['advads-last-edited-group'] ) && $group->id == $_REQUEST['advads-last-edited-group'];
$hidden         = $is_last_edited ? '' : ' hidden';
?><tr id="advads-ad-group-<?php echo absint( $group->id ); ?>" class="advads-ad-group-form<?php echo esc_attr( $hidden ); ?>" <?php echo $is_last_edited ? 'data-touched="true"' : ''; ?>>
	<td colspan="3">

	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the output is already escaped
	echo $hints_html;

	// group name.
	ob_start();
	?>
	<input type="hidden" class="advads-group-id" name="advads-groups[<?php echo absint( $group->id ); ?>][id]" value="<?php echo absint( $group->id ); ?>"/>
	<input type="text" name="advads-groups[<?php echo absint( $group->id ); ?>][name]" value="<?php echo esc_attr( $group->name ); ?>"/>
	<?php
	$option_content = ob_get_clean();

	Advanced_Ads_Admin_Options::render_option(
		'group-name static',
		__( 'Name', 'advanced-ads' ),
		$option_content
	);

	// group type.
	ob_start();
	?>
	<div class="advads-ad-group-type">
	<?php
	foreach ( $this->types as $_type_key => $_type ) :
		?>
		<label title="<?php echo esc_html( $_type['description'] ); ?>"><input type="radio" name="advads-groups[<?php echo absint( $group->id ); ?>][type]" value="<?php echo esc_attr( $_type_key ); ?>" <?php checked( $group->type, $_type_key ); ?>/>
		<?php
		echo esc_html( $_type['title'] );
		?>
		</label>
		<?php
	endforeach;
	?>
	</div>
	<?php
	$option_content = ob_get_clean();

	Advanced_Ads_Admin_Options::render_option(
		'group-type static',
		esc_attr__( 'Type', 'advanced-ads' ),
		$option_content
	);

	// group number.
	ob_start();
	?>
	<select name="advads-groups[<?php echo absint( $group->id ); ?>][ad_count]">
	<?php
			$max = ( count( $ad_form_rows ) >= 10 ) ? count( $ad_form_rows ) + 2 : 10;
	for ( $i = 1; $i <= $max; $i++ ) :
		?>
			<option <?php selected( $group->ad_count, $i ); ?>><?php echo absint( $i ); ?></option>
			<?php
		endfor;
	?>
			<option <?php selected( $group->ad_count, 'all' ); ?> value="all"><?php echo esc_attr_x( 'all', 'option to display all ads in an ad groups', 'advanced-ads' ); ?></option>
			</select>
			<?php
			$option_content = ob_get_clean();

			Advanced_Ads_Admin_Options::render_option(
				'group-number advads-group-type-default advads-group-type-ordered',
				esc_attr__( 'Visible ads', 'advanced-ads' ),
				$option_content,
				esc_attr__( 'Number of ads that are visible at the same time', 'advanced-ads' )
			);

			do_action( 'advanced-ads-group-form-options', $group );

			ob_start();
			require ADVADS_BASE_PATH . 'admin/views/ad-group-list-ads.php';
			$option_content = ob_get_clean();
			Advanced_Ads_Admin_Options::render_option(
				'group-ads static',
				esc_attr__( 'Ads', 'advanced-ads' ),
				$option_content
			);

			?>
	</td>
</tr>
