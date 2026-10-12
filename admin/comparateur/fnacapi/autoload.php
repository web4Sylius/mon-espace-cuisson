<?php
require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony' => __DIR__.'/vendor',
    'Zend' => __DIR__.'/vendor/zf2/library',
    'Monolog' => __DIR__.'/vendor/monolog/src',
    'HttpFoundation' => __DIR__.'/vendor/HttpFoundation',
    'FnacApiGui' => __DIR__.'/src/FnacApiGui/src',
    'FnacApiClient' => __DIR__.'/src',
));
$loader->register();
