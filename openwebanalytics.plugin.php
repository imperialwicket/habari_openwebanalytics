<?php
class OpenWebAnalytics extends Plugin
{
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $this->plugin_id() == $plugin_id ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id && $action == _t('Configure') ){
			$form = new FormUI('owa');
			$form->append( 'text', 'siteurl', 'option:owa__siteurl', _t('OpenWebAnalytics site URL', 'owa') );
			$form->append( 'text', 'siteid', 'option:owa__siteid', _t('OpenWebAnalytics site id', 'owa') );
			$form->append( 'checkbox', 'trackloggedin', 'option:owa__trackloggedin', _t( 'Track logged-in users', 'owa' ) );
			$form->append( 'submit', 'save', 'Save' );
			$form->on_success( array($this, 'save_config') );
			$form->out();
		}
	}

	/**
	 * Invoked when the before the plugin configurations are saved
	 *
	 * @param FormUI $form The configuration form being saved
	 * @return true
	 */
	public function save_config( FormUI $form )
	{
		Session::notice( _t('OpenWebAnalytics plugin configuration saved', 'owa') );
		$form->save();
	}

	public function action_plugin_deactivation()
	{
		Options::delete('owa__siteurl');
		Options::delete('owa__siteid');
		Options::delete('owa__trackloggedin');
	}

	public function action_plugin_activation()
	{
		Options::set('owa__trackloggedin', false);
	}

	public function theme_footer( Theme $theme )
	{
		// Login page; don't track
		if ( URL::get_matched_rule()->entire_match == 'user/login') {
			return;
		}
		// don't track loggedin user
		if ( User::identify()->loggedin && !Options::get('owa__trackloggedin') ) {
			return;
		}
		// trailing slash url
		$siteurl = Options::get('owa__siteurl');
		if ( $siteurl{strlen($siteurl)-1} != '/' ) {
 			$siteurl .= '/'; 
		}
		$sitenum = Options::get('owa__siteid');

		echo <<<EOD
<!-- Start Open Web Analytics Tracker -->
<script type="text/javascript">
//<![CDATA[
var owa_baseUrl = '{$siteurl}';
var owa_cmds = owa_cmds || [];
owa_cmds.push(['setSiteId', '{$siteid}']);
owa_cmds.push(['trackPageView']);
owa_cmds.push(['trackClicks']);
owa_cmds.push(['trackDomStream']);

(function() {
	var _owa = document.createElement('script'); _owa.type = 'text/javascript'; _owa.async = true;
	owa_baseUrl = ('https:' == document.location.protocol ? window.owa_baseSecUrl || owa_baseUrl.replace(/http:/, 'https:') : owa_baseUrl );
	_owa.src = owa_baseUrl + 'modules/base/js/owa.tracker-combined-min.js';
	var _owa_s = document.getElementsByTagName('script')[0]; _owa_s.parentNode.insertBefore(_owa, _owa_s);
}());
//]]>
</script>
<!-- End Open Web Analytics Code -->
EOD;
	}
}
?>				