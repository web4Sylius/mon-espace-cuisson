<?php
require_once __DIR__.'/../../../../../secrets.php';

    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Client.php");
    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Exception.php");
    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Interface.php");
    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Mock.php");
    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Model.php");
    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Model/ListOrdersRequest.php");
    include_once(ROOT_PATH."admin/comparateur/amazonapi/src/MarketplaceWebServiceOrders/Model/ListOrderItemsRequest.php");


   /************************************************************************
    * REQUIRED
    *
    * Access Key ID and Secret Acess Key ID, obtained from:
    * http://aws.amazon.com
    ***********************************************************************/
    define('AWS_ACCESS_KEY_ID', AWS_ACCESS_KEY_ID);
    define('AWS_SECRET_ACCESS_KEY', AWS_SECRET_ACCESS_KEY);

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain a User-Agent header. The application
    * name and version defined below are used in creating this value.
    ***********************************************************************/
    define('APPLICATION_NAME', 'RetrieveOrder');
    define('APPLICATION_VERSION', 'V1');

   /************************************************************************
    * REQUIRED
    *
    * All MWS requests must contain the seller's merchant ID and
    * marketplace ID.
    ***********************************************************************/
    define ('MERCHANT_ID', 'A24MEE3M7467PD');
    define ('MARKETPLACE_ID', 'A13V1IB3VIYZZH');

    define("AMAZON_XML_CACHE",ROOT_PATH."admin/comparateur/amazonapi/cache");

   /************************************************************************
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/
    set_include_path(get_include_path() . PATH_SEPARATOR . '../../.');

   /************************************************************************
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Autoload function is reponsible for loading classes of the library on demand
    *
    * NOTE: Only one __autoload function is allowed by PHP per each PHP installation,
    * and this function may need to be replaced with individual require_once statements
    * in case where other framework that define an __autoload already loaded.
    *
    * However, since this library follow common naming convention for PHP classes it
    * may be possible to simply re-use an autoload mechanism defined by other frameworks
    * (provided library is installed in the PHP include path), and so classes may just
    * be loaded even when this function is removed
    ***********************************************************************/
    /* function __autoload($className){
        $filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach($includePaths as $includePath){
            if(file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)){
                require_once $filePath;
                return;
            }
        }
    }*/

