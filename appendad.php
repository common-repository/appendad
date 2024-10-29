<?php
/* Plugin Name: FirstImpression.io
Plugin URI: http://www.firstimpression.io/
Version: 1.4.6
Description: FirstImpression.io helps publishers focus on content by freeing them from Ad-Tech and Ad-Operations with one line of code.
Author: FirstImpression.io
Author URI: https://www.firstimpression.io/
*/

/*
 * Update this variable to modify plugin version text in actual site tag 
 */
$pluginVersion = '1.4.6';

// refer to uninstall hook if deleted 
register_uninstall_hook('uninstall.php', '');

// Add settings link on plugin page
function your_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=firstimpression">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );

//get the settings from database
$ssb = get_option("ssb_options");

//activate/de-activate hooks
//dont change it, its required
function ssb_activation() {}
function ssb_deactivation() {}
register_activation_hook(__FILE__, 'ssb_activation');
register_deactivation_hook(__FILE__, 'ssb_deactivation');


/// this is a wordpress action which adds a page link in the wordpress admin panel bu calling my function ssb_settings
add_action('admin_menu', 'ssb_settings');
//adding a page link in admin panel
function ssb_settings()
{
	add_options_page( "FirstImpression.io", "FirstImpression.io", 'administrator', 'firstimpression', 'ssb_admin_function');
	//this adds the page: parameters are: "page title", "link title", "role", "slug","function that shows the result"
}


if ( ! is_admin() ) {
     //printing the script
	add_action('wp_head','ssb_output_g'); //print in header
	add_action('wp_footer','ssb_page_data'); //print in footer
}

//main function, returns the output
function ssb_output() {
    //get the settings from database
    $ssb = get_option("ssb_options");
    $siteId = isset($ssb['site_id']) ? $ssb['site_id'] : 0;

    //adding the script result in a variable
    $output = <<<SCRIPT
<!--BEGIN FIRSTIMPRESSION.IO TAG -->
<script data-cfasync='false' type='text/javascript'>
    ;(function(o) {
        var w=window.top,a='apdAdmin',ft=w.document.getElementsByTagName('head')[0],
        l=w.location.href,d=w.document;w.apd_options=o;
        if(l.indexOf('disable_fi')!=-1) { console.error("disable_fi has been detected in URL. All fi_client.js functionality is disabled for the current page view."); return; }
        var fiab=d.createElement('script'); fiab.type = 'text/javascript';
        fiab.src=o.scheme+'ecdn.analysis.fi/static/js/fab.js';fiab.id='fi-'+o.websiteId;
        ft.appendChild(fiab, ft); if(l.indexOf(a)!=-1) w.localStorage[a]=1; 
        var aM = w.localStorage[a]==1, fi=d.createElement('script'); 
        fi.type='text/javascript'; fi.async=true; if(aM) fi['data-cfasync']='false';
        fi.src=o.scheme+(aM?'cdn':'ecdn') + '.firstimpression.io/' + (aM ? 'fi.js?id='+o.websiteId : 'fi_client.js');
        ft.appendChild(fi);
    })({ 
        'websiteId': {$siteId}, 
        'scheme':    '//'
    });
</script>
<!-- END FIRSTIMPRESSION.IO TAG -->
SCRIPT;

    //output created, now returning it back to the calling element !
    return $output;

}

function ssb_page_data_demo() {
    global $pluginVersion, $wp_version;
    $output = "<!-- FirstImpression.io Targeting - Start --><div id='apdPageData' data-plugin-version='$pluginVersion' data-wp-version='$wp_version' style='display:none;visibility:hidden;'> <span id='apdPageData_categories'>[categories]</span> <span id='apdPageData_tags'>[tags]</span> <span id='apdPageData_author'>[author]</span> </div><!-- FirstImpression.io Targeting - End -->";
    
    echo $output;
}


function ssb_page_data() {
    global $post, $pluginVersion, $wp_version;
        
    //Returns All category Items
    $term_array = wp_get_post_terms($post->ID, 'category', array("fields" => "names"));
    $category_list = ( empty($term_array) OR is_wp_error($term_array) ) ? '' : implode(',', $term_array);
    
    //Returns Array of Tag Names
    $term_array = wp_get_post_terms($post->ID, 'post_tag', array("fields" => "names"));
    $tag_list = ( empty($term_array) OR is_wp_error($term_array) ) ? '' : implode(',', $term_array);
    
    $display_name = get_the_author_meta('display_name');
    $display_name = ( empty($display_name) OR is_wp_error($display_name) ) ? '' : $display_name;
    
    $output  = '<!-- FirstImpression.io Targeting - Start -->';
    $output .= "\n" . '<div id="apdPageData" data-plugin-version="' . $pluginVersion . '" data-wp-version="' . $wp_version . '" style="display:none;visibility:hidden;">';  
    $output .= "\n\t" . '<span id="apdPageData_categories">' . $category_list . '</span>';
    
    if(is_single()) {
        $output .= "\n\t" . '<span id="apdPageData_tags">' . $tag_list . '</span>';
        $output .= "\n\t" . '<span id="apdPageData_author">' . $display_name . '</span>';
    }
    
    $output .= "\n" . "</div>\n<!-- FirstImpression.io Targeting - End -->\n";
    
    echo $output;
}


//this echo's the code
function ssb_output_g()
{
    echo ssb_output();
}


//biggest function but worth it
function ssb_admin_function()
{
	ssb_ajax_javascript();
	//check if the user is allowed to edit wordress settings
	if(!current_user_can('manage_options'))
		wp_die('You do not have sufficient permissions to access this page.');
		//die if not allowed
	$ssb = get_option("ssb_options"); //get saved settings to initially show in form
	//here starts the html of form, cant document the html (Do you really need this ? do you!)
	?>
		<div class="wrap">
			<h2>FirstImpression.io Wordpress Site Tag Plugin</h2>
            <p>
                FirstImpression.io is a tool which allows you to easily add monetizable ad products to your site.
                This plugin will provide the integration to allow the placements on your site to be managed through
                FirstImpression.io's platform. Just add the site id you got from your account manager,
                click the "Updated Embedded Code" button and you are good to go.
            </p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="site_id_vas">
								Site ID:
							</label>
						</th>
						<td>
							<input type="text" id="site_id_vas" name="site_id_vas" value="<?php echo $ssb['site_id'];?>" />
                            <div id="setting-error-settings_error" class="error settings-error asd_error " style="display:none;background-color: #ffebe8;border: 1px solid #c00;padding: 0 3px;margin-left: 10px;border-radius: 4px;">
                                <p style="padding: 1px;margin: 0;"><strong>Enter A Valid Number.</strong></p>
                            </div>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input name="update_settings" id="submit_options_form" type="button" class="button-primary vasu_btn" value="Updated Embedded Code" />
				</p>

				<div id="setting-error-settings_updated" class="updated settings-error asd_saved" style="display:none; width: 76%;"><p><strong>Settings saved.</strong></p></div><p>Click <a href="https://publishers.firstimpression.io"> here </a> to login to your admin console on FirstImpression.io and manage your placements</p>

				<p>
                    <label for="fi-result">The following code will be embedded in your site's template:</label><br />
                    <textarea id="fi-result" style="width: 77%;height: 425px;" class="result_demo"><?php echo htmlentities(ssb_output()); ssb_page_data_demo();?></textarea>
                </p>
		</div>
	<?php
}

// this will add javascript in admin required for ajax
// add_action( 'admin_footer', 'ssb_ajax_javascript' ); // carnivore1
function ssb_ajax_javascript() {
    
?>
<script type="text/javascript" >
    var tags = "<?php ssb_page_data_demo()?>";
    //this function is providing the functionality of live realtime change of code in textarea
    function chTXT(){
        //saving to variables
        var site_id_parser = document.getElementById('site_id_vas').value;
        var tmp_site_id_vas = 0;
        if (site_id_parser != null) tmp_site_id_vas = site_id_parser;

        var output = ["<script data-cfasync='false' type='text/javascript'>\n"];
        output.push("    window.apd_options = { 'websiteId':" + tmp_site_id_vas + ", 'runFromFrame': false};");
        output.push("    (function() {");
        output.push("        var w = window.apd_options.runFromFrame ? window.top : window;");
        output.push("        if(window.apd_options.runFromFrame && w!=window.parent) w=window.parent;");
        output.push("        if (w.location.hash.indexOf('apdAdmin') != -1){if(typeof(Storage) !== 'undefined') {w.localStorage.apdAdmin = 1;}}");
        output.push("        var adminMode = ((typeof(Storage) == 'undefined') || (w.localStorage.apdAdmin == 1));");
        output.push("        w.apd_options=window.apd_options;");
        output.push("        var apd = w.document.createElement('script'); apd.type = 'text/javascript'; apd.async = true;");
        output.push("        apd.src = '//' + (adminMode ? 'cdn' : 'ecdn') + '.firstimpression.io/' + (adminMode ? 'fi.js?id=' + window.apd_options.websiteId : 'fi_client.js') ;");
        output.push("        var s = w.document.getElementsByTagName('head')[0]; s.appendChild(apd);");
        output.push("    })();");
        output.push("<\/script>");

        jQuery('.result_demo').html(output.join("\n"));

    }

//jquery document ready call
jQuery(document).ready(function($) {
	//jquery sace button click call
	$('.vasu_btn').click(function () {
		//hide the error box if visible
		$('.asd_error').hide();

		//clear the textarea
		$('.result_demo').html(" ");
		//save the values of form in variables
		var site_id_vas = document.getElementById('site_id_vas').value;

		//verifcation for a valid number
		if(!isNaN(site_id_vas) && site_id_vas != "") {
			//if verified, create a json varibale for sending
			var data = {
				action: 'my_action',
				a:site_id_vas
			};
			//use post method to send the data
			$.post(ajaxurl, data, function(response) {
				//show the response in teatarea
				$('.result_demo').html(response.trim() + "\n\n" + tags);
				//show "saved" message
				$('.asd_saved').show('slow');
			});

		} else {
			//if verification fails, show the error message
			$('.asd_error').css("display", "inline-block");
			$('.asd_saved').hide('slow');
		}
	});

});
// javascript ends here
</script>


<?php
///back to php
}


// ajax hook
add_action('wp_ajax_my_action', 'ssb_ajax_action');
//ajax hook function

function ssb_ajax_action() {
	$a = $_POST['a']; //copying the received data in variables

	$ssb_settings = array('site_id' => $a);// creating a settings array from the variables

	update_option("ssb_options", $ssb_settings); //save the settings

    echo htmlentities(ssb_output()); //return the script result after saving

	die(); // this is required to return a proper result
}



