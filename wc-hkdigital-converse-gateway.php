<?php
/*
Plugin Name: Payment Gateway for Converse Bank
Plugin URI: #
Description: Pay with Converse payment system. Please note that the payment will be made in Armenian Dram.
Version: 1.0.9
Author: HK Digital Agency LLC
Author URI: https://hkdigital.am
License: GPLv2 or later
*/


/*
 *
 * Սույն հավելման(plugin) պարունակությունը պաշպանված է հեղինակային և հարակից իրավունքների մասին Հայաստանի Հանրապետության օրենսդրությամբ:
 * Արգելվում է պարունակության  վերարտադրումը, տարածումը, նկարազարդումը, հարմարեցումը և այլ ձևերով վերափոխումը,
 * ինչպես նաև այլ եղանակներով օգտագործումը, եթե մինչև նման օգտագործումը ձեռք չի բերվել ԷՅՋԿԱ ԴԻՋԻՏԱԼ ԷՋԵՆՍԻ ՍՊԸ-ի թույլտվությունը:
 *
 */

$currentPluginDomainConverse = 'hk-payment-gateway-for-converse';
$apiUrlConverse = 'https://plugins.hkdigital.am/api/';
$pluginDirUrlConverse = plugin_dir_url(__FILE__);
$pluginBaseNameConverse = dirname(plugin_basename(__FILE__));
if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$pluginDataConverse = get_plugin_data(__FILE__);

/**
 *
 * @param $gateways
 * @return array
 */
function hkdAddConverseBankGatewayClass($gateways)
{
    $gateways[] = 'WC_HKD_Converse_Arca_Gateway';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'hkdAddConverseBankGatewayClass');



include dirname(__FILE__) . '/console/command.php';
include dirname(__FILE__) . '/includes/thankyou.php';

if (is_admin()) {
    include dirname(__FILE__) . '/includes/request.php';
    include dirname(__FILE__) . '/includes/language.php';
    include dirname(__FILE__) . '/includes/activate.php';
}

include dirname(__FILE__) . '/includes/errorCodes.php';
include dirname(__FILE__) . '/includes/main.php';



add_action('plugin_action_links_' . plugin_basename(__FILE__), 'hkd_converse_gateway_setting_link');

function hkd_converse_gateway_setting_link($links)
{
    $links = array_merge(array(
        '<a href="' . esc_url(admin_url('/admin.php')) . '?page=wc-settings&tab=checkout&section=hkd_converse">' . __('Settings', 'hk-payment-gateway-for-converse') . '</a>'
    ), $links);
    return $links;
}