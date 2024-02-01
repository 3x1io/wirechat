<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbc5a3425e03d17ea88e9af6ce2861c9b
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'Namu\\Wirechat\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Namu\\Wirechat\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbc5a3425e03d17ea88e9af6ce2861c9b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbc5a3425e03d17ea88e9af6ce2861c9b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitbc5a3425e03d17ea88e9af6ce2861c9b::$classMap;

        }, null, ClassLoader::class);
    }
}
