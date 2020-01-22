<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit124513258fafaa75d4957031c78b8551
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
    );

    public static $classMap = array (
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit124513258fafaa75d4957031c78b8551::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit124513258fafaa75d4957031c78b8551::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit124513258fafaa75d4957031c78b8551::$classMap;

        }, null, ClassLoader::class);
    }
}
