<?php

if (isset($_POST['woocommerce_hkd_converse_language_payment_converse'])) {
    $language = $_POST['woocommerce_hkd_converse_language_payment_converse'];
    if ($language === 'hy' || $language === 'ru_RU' || $language === 'en_US')
        update_option('language_payment_converse', $language);
}

