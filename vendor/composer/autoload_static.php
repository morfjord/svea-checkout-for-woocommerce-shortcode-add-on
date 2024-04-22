<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5b46c60db60a9bc4ec5bd7d1e9c1e0d7
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Svea_Checkout_For_Woocommerce\\' => 30,
            'Svea\\Checkout\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Svea_Checkout_For_Woocommerce\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
        'Svea\\Checkout\\' => 
        array (
            0 => __DIR__ . '/..' . '/sveaekonomi/checkout/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Svea\\Checkout\\CheckoutAdminClient' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/CheckoutAdminClient.php',
        'Svea\\Checkout\\CheckoutClient' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/CheckoutClient.php',
        'Svea\\Checkout\\Exception\\ExceptionCodeList' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Exception/ExceptionCodeList.php',
        'Svea\\Checkout\\Exception\\SveaApiException' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Exception/SveaApiException.php',
        'Svea\\Checkout\\Exception\\SveaConnectorException' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Exception/SveaConnectorException.php',
        'Svea\\Checkout\\Exception\\SveaInputValidationException' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Exception/SveaInputValidationException.php',
        'Svea\\Checkout\\Implementation\\Admin\\AddOrderRow' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/AddOrderRow.php',
        'Svea\\Checkout\\Implementation\\Admin\\AdminImplementationManager' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/AdminImplementationManager.php',
        'Svea\\Checkout\\Implementation\\Admin\\CancelOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/CancelOrder.php',
        'Svea\\Checkout\\Implementation\\Admin\\CancelOrderRow' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/CancelOrderRow.php',
        'Svea\\Checkout\\Implementation\\Admin\\CreditOrderAmount' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/CreditOrderAmount.php',
        'Svea\\Checkout\\Implementation\\Admin\\CreditOrderRows' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/CreditOrderRows.php',
        'Svea\\Checkout\\Implementation\\Admin\\CreditOrderRowsWithFee' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/CreditOrderRowsWithFee.php',
        'Svea\\Checkout\\Implementation\\Admin\\DeliverOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/DeliverOrder.php',
        'Svea\\Checkout\\Implementation\\Admin\\DeliverOrderWithLowerAmount' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/DeliverOrderWithLowerAmount.php',
        'Svea\\Checkout\\Implementation\\Admin\\GetOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/GetOrder.php',
        'Svea\\Checkout\\Implementation\\Admin\\GetTask' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/GetTask.php',
        'Svea\\Checkout\\Implementation\\Admin\\ImplementationAdminFactory' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/ImplementationAdminFactory.php',
        'Svea\\Checkout\\Implementation\\Admin\\ReplaceOrderRows' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/ReplaceOrderRows.php',
        'Svea\\Checkout\\Implementation\\Admin\\UpdateOrderRow' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/Admin/UpdateOrderRow.php',
        'Svea\\Checkout\\Implementation\\CreateOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/CreateOrder.php',
        'Svea\\Checkout\\Implementation\\CreateTokenOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/CreateTokenOrder.php',
        'Svea\\Checkout\\Implementation\\FormatInputData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/FormatInputData.php',
        'Svea\\Checkout\\Implementation\\GetAvailablePartPaymentCampaigns' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/GetAvailablePartPaymentCampaigns.php',
        'Svea\\Checkout\\Implementation\\GetOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/GetOrder.php',
        'Svea\\Checkout\\Implementation\\GetToken' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/GetToken.php',
        'Svea\\Checkout\\Implementation\\GetTokenOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/GetTokenOrder.php',
        'Svea\\Checkout\\Implementation\\ImplementationFactory' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/ImplementationFactory.php',
        'Svea\\Checkout\\Implementation\\ImplementationInterface' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/ImplementationInterface.php',
        'Svea\\Checkout\\Implementation\\ImplementationManager' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/ImplementationManager.php',
        'Svea\\Checkout\\Implementation\\UpdateOrder' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/UpdateOrder.php',
        'Svea\\Checkout\\Implementation\\UpdateToken' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Implementation/UpdateToken.php',
        'Svea\\Checkout\\Model\\Request' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Model/Request.php',
        'Svea\\Checkout\\Transport\\ApiClient' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Transport/ApiClient.php',
        'Svea\\Checkout\\Transport\\Connector' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Transport/Connector.php',
        'Svea\\Checkout\\Transport\\Http\\CurlRequest' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Transport/Http/CurlRequest.php',
        'Svea\\Checkout\\Transport\\Http\\HttpRequestInterface' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Transport/Http/HttpRequestInterface.php',
        'Svea\\Checkout\\Transport\\ResponseHandler' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Transport/ResponseHandler.php',
        'Svea\\Checkout\\Util\\ScriptHandler' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Util/ScriptHandler.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateAddOrderRowData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateAddOrderRowData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateCancelOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateCancelOrderData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateCancelOrderRowData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateCancelOrderRowData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateCreditOrderAmountData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateCreditOrderAmountData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateCreditOrderRowsData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateCreditOrderRowsData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateCreditOrderRowsWithFeeData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateCreditOrderRowsWithFeeData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateDeliverOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateDeliverOrderData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateDeliverOrderWithLowerAmountData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateDeliverOrderWithLowerAmountData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateGetOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateGetOrderData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateGetTaskData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateGetTaskData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateReplaceOrderRowsData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateReplaceOrderRowsData.php',
        'Svea\\Checkout\\Validation\\Admin\\ValidateUpdateOrderRowData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/Admin/ValidateUpdateOrderRowData.php',
        'Svea\\Checkout\\Validation\\ValidateCreateOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateCreateOrderData.php',
        'Svea\\Checkout\\Validation\\ValidateCreateTokenOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateCreateTokenOrderData.php',
        'Svea\\Checkout\\Validation\\ValidateGetAvailablePartPaymentCampaignsData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateGetAvailablePartPaymentCampaignsData.php',
        'Svea\\Checkout\\Validation\\ValidateGetOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateGetOrderData.php',
        'Svea\\Checkout\\Validation\\ValidateGetTokenData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateGetTokenData.php',
        'Svea\\Checkout\\Validation\\ValidateGetTokenOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateGetTokenOrderData.php',
        'Svea\\Checkout\\Validation\\ValidateUpdateOrderData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateUpdateOrderData.php',
        'Svea\\Checkout\\Validation\\ValidateUpdateTokenData' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidateUpdateTokenData.php',
        'Svea\\Checkout\\Validation\\ValidationService' => __DIR__ . '/..' . '/sveaekonomi/checkout/src/Validation/ValidationService.php',
        'Svea_Checkout_For_Woocommerce\\Admin' => __DIR__ . '/../..' . '/inc/Admin.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\AeliaCS_Compat' => __DIR__ . '/../..' . '/inc/Compat/AeliaCS_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\Compat' => __DIR__ . '/../..' . '/inc/Compat/Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\Ingrid_Compat' => __DIR__ . '/../..' . '/inc/Compat/Ingrid_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\Polylang_Compat' => __DIR__ . '/../..' . '/inc/Compat/Polylang_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\UAP_Compat' => __DIR__ . '/../..' . '/inc/Compat/UAP_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\WC_Smart_Coupons_Compat' => __DIR__ . '/../..' . '/inc/Compat/WC_Smart_Coupons_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\WPC_Product_Bundles_Compat' => __DIR__ . '/../..' . '/inc/Compat/WPC_Product_Bundles_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\WPML_Compat' => __DIR__ . '/../..' . '/inc/Compat/WPML_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\WooMc_Compat' => __DIR__ . '/../..' . '/inc/Compat/WooMc_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\Woocs_Compat' => __DIR__ . '/../..' . '/inc/Compat/Woocs_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Compat\\Yith_Gift_Cards_Compat' => __DIR__ . '/../..' . '/inc/Compat/Yith_Gift_Cards_Compat.php',
        'Svea_Checkout_For_Woocommerce\\Helper' => __DIR__ . '/../..' . '/inc/Helper.php',
        'Svea_Checkout_For_Woocommerce\\I18n' => __DIR__ . '/../..' . '/inc/I18n.php',
        'Svea_Checkout_For_Woocommerce\\Models\\Svea_Item' => __DIR__ . '/../..' . '/inc/Models/Svea_Item.php',
        'Svea_Checkout_For_Woocommerce\\Models\\Svea_Order' => __DIR__ . '/../..' . '/inc/Models/Svea_Order.php',
        'Svea_Checkout_For_Woocommerce\\Scripts' => __DIR__ . '/../..' . '/inc/Scripts.php',
        'Svea_Checkout_For_Woocommerce\\Session_Table' => __DIR__ . '/../..' . '/inc/Session_Table.php',
        'Svea_Checkout_For_Woocommerce\\Template_Handler' => __DIR__ . '/../..' . '/inc/Template_Handler.php',
        'Svea_Checkout_For_Woocommerce\\Utils\\Array_Utils' => __DIR__ . '/../..' . '/inc/Utils/Array_Utils.php',
        'Svea_Checkout_For_Woocommerce\\Utils\\String_Utils' => __DIR__ . '/../..' . '/inc/Utils/String_Utils.php',
        'Svea_Checkout_For_Woocommerce\\WC_Gateway_Svea_Checkout' => __DIR__ . '/../..' . '/inc/WC_Gateway_Svea_Checkout.php',
        'Svea_Checkout_For_Woocommerce\\WC_Shipping_Svea_Nshift' => __DIR__ . '/../..' . '/inc/WC_Shipping_Svea_Nshift.php',
        'Svea_Checkout_For_Woocommerce\\Webhook_Handler' => __DIR__ . '/../..' . '/inc/Webhook_Handler.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5b46c60db60a9bc4ec5bd7d1e9c1e0d7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5b46c60db60a9bc4ec5bd7d1e9c1e0d7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5b46c60db60a9bc4ec5bd7d1e9c1e0d7::$classMap;

        }, null, ClassLoader::class);
    }
}
