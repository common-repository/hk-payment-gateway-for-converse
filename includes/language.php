<?php

$overrideLocaleConverse = !empty(get_option('language_payment_converse')) ? get_option('language_payment_converse') : 'hy';
add_filter('plugin_locale','changeLanguageConverseBank', 10, 2);

/**
 * change location event
 *
 * @param $locale
 * @param $domain
 * @return string
 */
function changeLanguageConverseBank($locale, $domain)
{
    global $currentPluginDomainConverse;
    global $overrideLocaleConverse;
    if ($domain == $currentPluginDomainConverse) {
        $locale = $overrideLocaleConverse;
    }
    return $locale;
}