<?php

namespace Pgettext;

/**
 * Basic utility functions
 */
class Pgettext
{
    /**
     * Read a po file and store a mo file.
     * If no MO filename is given, one will be generated from the PO filename.
     * @param string $po Filename of the po file.
     * @param string $mo Filename of the mo file.
     * @return void
     */
    public static function msgfmt($po, $mo = null)
    {
        $stringset = Po::fromFile($po);
        if ($mo === null) {
            $mo = substr($po, 0, -3) . '.mo';
        }
        Mo::toFile($stringset, $mo);
    }
}
