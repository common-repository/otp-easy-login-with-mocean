<?php
include oelm_PATH.'/includes/servicesScripts/class-oelm-mocean.php';
include oelm_PATH.'/admin/includes/options/services-options.php';
$operator_dir = wp_get_upload_dir()['basedir'] .'/mocean-sms-sdk';
require_once $operator_dir.'/mocean/vendor/autoload.php';
use Mocean\Client;
use Mocean\Client\Credentials\Basic;


$settings = get_option( 'oelm-services-options', true );
$account_sid = $settings['mocean-account-sid'];
$auth_token = $settings['mocean-auth-token'];



	$mocean = new \Mocean\Client(
		new \Mocean\Client\Credentials\Basic($account_sid, $auth_token)	
	);
	
	if ($account_sid == "" || $auth_token == ""){
		$result = "<span style='font-size:1rem;'>API Security Details missing! please enter your Mocean api security details in the services tab</span>";
	}else{
	$result = $mocean->account()->getBalance([
					'mocean-resp-format' => 'json'
				]);
							
	}
	
?>
<div class="mo-wsc-prem">
	<h1 style="margin-top: 35px; text-align: center;"><u><?php _e('Balance', 'mo-plugin');?></u> <br><br><br> <span style="border: 3.5px solid grey;padding: 5px;"><?php echo $result; ?></span></h1>
	<center>
	<h1 style="padding-top:25px;"><u><?php _e('Note', 'mo-plugin');?></u></h1>
	<p style="font-size:1rem;padding-top:9.5px;"><b><?php _e('Value: "How much you have left in your mocean account"', 'mo-plugin');?><b></p>
	<table style="border:2.356px solid brown;margin-top:25.5px;margin-bottom:86.5px;">
		<tr>
			<th><?php _e('Status Code', 'mo-plugin');?></th>
			<th><?php _e('Description', 'mo-plugin');?></th>
		</tr>
		<tr>
			<td style="text-align:center;"><?php _e('0', 'mo-plugin');?></td>
			<td style="padding-left:23%;"><?php _e('Successful.', 'mo-plugin');?></td>
		</tr>
		<tr>
			<td style="text-align:center;"><?php _e('1', 'mo-plugin');?></td>
			<td style="padding-left:23%;"><?php _e('Authorization failed.', 'mo-plugin');?></td>
		</tr>
		<tr>
			<td style="text-align:center;"><?php _e('24', 'mo-plugin');?></td>
			<td style="padding-left:23%;"><?php _e('Unknown error.', 'mo-plugin');?></td>
		</tr>
		<tr>
			<td style="text-align:center;"><?php _e('40', 'mo-plugin');?></td>
			<td style="padding-left:23%;"><?php _e('System down for maintenance.', 'mo-plugin');?></td>
		</tr>
	</table></center>
</div>