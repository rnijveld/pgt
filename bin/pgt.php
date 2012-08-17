<?php

// TODO
require_once "lib/Pgettext/Po.php";
require_once "lib/Pgettext/Mo.php";
require_once "lib/Pgettext/Stringset.php";
require_once "lib/Pgettext/Pgettext.php";
require_once "lib/Pgettext/Exception.php";

Pgettext\Pgettext::msgfmt($argv[1]);
