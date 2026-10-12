FnacApiClient, a simple PHP Client for the Fnac API
================================

Requirements
------------

FnacApiClient works with PHP 5.3.2 at least

Installation
------------

Read the FNAC API documentation for instruction about installation, usage, etc...
You can find all the necessary documentation under the docs folder.

Technical Information
---------------------

FnacApiClient is a thin wrapper around the following fine PHP libraries:

 * Symfony Components: Serializer, ClassLoader, Yaml
 * Zend libraries:  Uri, Http, and Validate
 * Monolog:  a Symfony2 logger


Changelog
---------

- 2014-11-25

 * Restructured ready-to-use Graphic User Interface (under FnacAPIGui), using Bootstrap
 * Added pricing_query API handling in GUI
 * Added shop_invoices_query handling

- 2013-09-06

 * Added incident_query request handling

- 2013-06-23

 * Added ready-to-use Graphic User Interface (under FnacAPIGui)


- 2013-05-16
 
 * Added adherent price field management in offers_update request


- 2012-07-11

 * Fixed multiple orders_fnac_id in orders_query request