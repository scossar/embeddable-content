<?php
/**
 * Plugin Name: Embeddable Content
 */
namespace T11;

$defaults = [
	'category_name' => '',
];
add_option( 'embeddable_content', $defaults );

add_action( 'admin_menu', 'T11\admin_menu_setup' );
function admin_menu_setup() {
	add_options_page(
		'Embeddable Content Settings',
		'Embeddable Content',
		'manage_options',
		'embeddable_content', // menu slug
		'T11\embed_settings_page'
	);
}

function embed_settings_page() {
	?>
	<div class="wrap">
		<h2>Embedabble Content Settings</h2>
		<form action="options.php" method="POST">
			<?php do_settings_sections( 'embeddable_content' ); ?>
			<?php settings_fields( 'embeddable_content' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_init', 'T11\setup_plugin_admin' );
function setup_plugin_admin() {
	add_settings_section(
		'embeddable_category',
		'Embeddable Category',
		'T11\output_embeddable_category_section',
		'embeddable_content'
	);

	add_settings_field(
		'category_name',
		'Category Name',
		'T11\category_name_input',
		'embeddable_content',
		'embeddable_category'
	);
	register_setting(
		'embeddable_content',
		'embeddable_content',
		'T11\sanitize_options'
	);
}

function output_embeddable_category_section() {

}

function category_name_input() {
	$options = get_option( 'embeddable_content' );
	$category = isset( $options['category_name'] ) ? $options['category_name'] : null
	?>
	<input type="text" id="category_name" name="embeddable_content[category_name]"
	       value="<?php echo esc_attr( $category ); ?>">
	<?php
}

function sanitize_options( $input ) {
	return $input;
}

// Or do it with a meta box

add_action( 'add_meta_boxes', 'T11\embed_meta_box' );
function embed_meta_box( $post_type ) {
	add_meta_box( 'embeddable-content-meta-box', 'Embeddable Content',
		'T11\render_embed_meta_box', null, 'side', 'high', null );
}

function render_embed_meta_box( $post ) {
	$embed_post = get_post_meta( $post->ID, 'create_embeddable_content', true );
	wp_nonce_field( 'create_embeddable_content', 'create_embeddable_content_nonce' );
	?>
	<label for="embed_post">Create embeddable content for post?</label>
	<input type="checkbox" name="embed_post" id="embed_post" value="1"
	<?php checked( $embed_post ); ?>>
	<?php
}

add_action( 'save_post', 'T11\save_embed_meta_box' );
function save_embed_meta_box( $post_id ) {
	if ( ! isset( $_POST['create_embeddable_content_nonce'] ) ) {
		return 0;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return 0;
	}
	if ( defined ( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return 0;
	}
	
	if ( isset( $_POST['embed_post'] ) ) {
		update_post_meta( $post_id, 'create_embeddable_content', intval( wp_unslash( $_POST['embed_post'] ) ) );
	}
	return $post_id;
}
