<?php
/*
Plugin Name: Nixdo
Plugin URI: http://www.nixdo.com
Description: Le plugin Nixdo vous permet de publier vos menus/tarifs sur votre site web Word Press. Le plugin Nixdo vous permet d’afficher les menus/tarifs que vous avez renseignés dans votre interface Nixdo, sur votre site web. Vous choisissez le menu que vous souhaitez intégrer, son design et l’endroit où vous voulez qu’il apparaisse sur votre site. Il suffit d’ajouter le shortcode [nixdo-prices] dans l’un de vos posts ou widgets pour que votre menu apparaisse sur votre site. Pour utiliser votre plugin Nixdo, inscrivez-vous gratuitement sur le site de Nixdo puis renseignez vos menus/tarifs. Rien de compliqué, juste de la simplicité ! Pour en savoir plus sur Nixdo, rendez-vous sur https://nixdo.com/
Author: Nixdo
Author URI: http://www.nixdo.com
Version: 1.0.1
*/

class NixdoForWordPress {

	protected $name;
	protected $idStr;
	protected $pageName;
	protected $widgetTypes;

	public function __construct() {
		$this->name = 'Nixdo';
		$this->idStr = 'nixdo_id';
		$this->widgetTypes = array(
			'prices' => 'vos tarifs/menus',
		);

		register_activation_hook(__FILE__, array($this, 'install'));
		register_deactivation_hook(__FILE__, array($this, 'uninstall'));

		add_action('admin_menu', array($this, 'menu'));

		foreach (array_keys($this->widgetTypes) as $type)
			add_shortcode('nixdo-'.$type, array($this, 'shortcode'));

		add_action('admin_enqueue_scripts', array($this, 'insertAdminStyle'));
	}

	public function insertAdminStyle() {
		wp_enqueue_style('nixdo-admin', plugins_url('/nixdo-admin.css', __FILE__));
	}

	public function install() {
		add_option($this->idStr);
	}

	public function uninstall() {
		delete_option($this->idStr);
	}

	public function shortcode($atts, $content, $tag) {
		$type = substr($tag, strlen('nixdo-'));
		return $this->content($type);
	}

	public function content($type) {
		$id = get_option($this->idStr);
		if (empty($id))
			return '';
		return '<script id="-nixdo-widget" type="text/javascript" src="https://widget.nixdo.com/1/?type='.$type.'&medium=web&id='.$id.'"></script>';
	}

	public function menu() {
		$capability = 'read';
		$slug = mb_strtolower($this->name);
		$this->pageName = add_menu_page($this->name, $this->name, $capability, $slug, array($this, 'admin'), plugins_url('icon.png', __FILE__));
		add_action('admin_head-'.$this->pageName, array($this, 'insertAdminStyle'));
	}

	public function admin() {
		$actionName = 'nixdo_plugin_id_action';
		$nonceName = 'nixdo_plugin_id_wpnonce';
		$fieldName = $this->idStr;

		echo '<div id="wp-nixdo-content">';
			echo '<h2>Nixdo pour WordPress</h2>';
			echo '<p>Copier votre identifiant de widget pour intégrer vos widgets Nixdo dans WordPress.</p>';

		if (isset($_POST) && isset($_POST[$nonceName]) && wp_verify_nonce($_POST[$nonceName], $actionName) && check_admin_referer($actionName, $nonceName)) {
			update_option($fieldName, htmlspecialchars($_POST[$fieldName]));
				echo '<div class="alert">Enregistré !</div>';
		}

			echo '<form action="" method="post">';
				wp_nonce_field('nixdo_plugin_id_action', 'nixdo_plugin_id_wpnonce');
				echo '<input type="text" name="'.$fieldName.'" value="'.get_option($fieldName).'">';
				echo '<input type="submit" name="action" value="'.__('Save').'">';
			echo '</form>';
			echo '<p>Une fois votre identifiant de widget enregistré utilisez les shortcodes suivants :</p>';
			echo '<ul>';
				foreach ($this->widgetTypes as $type => $desc)
					echo '<li><span class="shortcode">[nixdo-'.$type.']</span> pour intégrer '.$desc.'</li>';
			echo '</ul>';
		echo '</div>';
	}

}

add_action('init', 'initNixdoForWordPress' );
function initNixdoForWordPress() {
	global $NixdoForWordPress;
	$NixdoForWordPress = new NixdoForWordPress();
}

?>
