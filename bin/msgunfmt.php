<?php
/*
 * (c) Ruben Nijveld <ruben@gewooniets.nl>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once "lib/Pgettext/Po.php";
require_once "lib/Pgettext/Mo.php";
require_once "lib/Pgettext/Stringset.php";
require_once "lib/Pgettext/Pgettext.php";
require_once "lib/Pgettext/Exception.php";

Pgettext\Pgettext::msgunfmt($argv[1]);
