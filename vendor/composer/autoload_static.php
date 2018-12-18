<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf8bc857975ff90d7b470aea9f7834c0c
{
    public static $prefixLengthsPsr4 = array (
        'Q' => 
        array (
            'QuickBooksOnline\\API\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'QuickBooksOnline\\API\\' => 
        array (
            0 => __DIR__ . '/..' . '/quickbooks/v3-php-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf8bc857975ff90d7b470aea9f7834c0c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf8bc857975ff90d7b470aea9f7834c0c::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
