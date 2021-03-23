<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9c0b4763f07c635223f558e586a51982
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'TelegramBot\\Api\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'TelegramBot\\Api\\' => 
        array (
            0 => __DIR__ . '/..' . '/telegram-bot/api/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9c0b4763f07c635223f558e586a51982::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9c0b4763f07c635223f558e586a51982::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9c0b4763f07c635223f558e586a51982::$classMap;

        }, null, ClassLoader::class);
    }
}
