<?php

namespace Pgettext;

/**
 * Class for handling po-type input files and strings.
 */
class Po
{
    /**
     * Takes a filename and returnes a Stringset representing that file.
     * @param string $filename
     * @return Stringset
     */
    public static function fromFile($filename)
    {
        if (!is_readable($filename)) {
            throw new Exception("Unreadable file");
        }
        return self::fromString(file_get_contents($filename));
    }

    /**
     * Takes a Stringset and a filename and writes a po formatted file.
     * @param Stringset $set
     * @param string $filename
     * @param array $options
     * @return void
     */
    public static function toFile(Stringset $set, $filename, array $options)
    {
        try {
            $str = self::toString($set, $options);
            if (is_writable($filename)) {
                file_put_contents($filename, $str);
            } else {
                throw new Exception("Cannot write to file");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Takes a Stringset and an array of options and creates a po formatted string.
     * @param Stringset $set
     * @param array $options
     * @return string
     */
    public static function toString(Stringset $set, array $options)
    {
        // TODO
    }

    /**
     * Takes a string in the format of a po file and returns a Stringset
     * @param string $str
     * @return Stringset
     */
    public static function fromString($str)
    {
        $stringset = new Stringset();

        $entry = array();
        $state = null;
        $line = 1;

        foreach (explode("\n", $str) as $line) {
            $line = trim($line);
            if (strlen($line) === 0) {
                if (count($entry) > 0) {
                    $stringset->add($entry);
                    $entry = array();
                    $state = null;
                }
                continue;
            }

            if ($line[0] === '#' && $line[1] === ',') {
                $entry['flags'] = array_map('trim', explode(',', substr($line, 2)));
            } else if ($line[0] !== '#') {
                // non-comment
                list($key, $rest) = explode(' ', $line, 2);
                switch ($key) {
                    case 'msgid':
                    case 'msgid_plural':
                    case 'msgstr':
                    case 'msgctxt':
                        if (strpos($state, 'msgstr') === 0 && $key !== 'msgstr' && count($entry) > 0) {
                            $stringset->add($entry);
                            $entry = array();
                        }
                        $state = $key;
                        $entry[$key] = self::parseString($rest);
                        break;
                    default:
                        if (strpos($key, 'msgstr[') === 0) {
                            $state = $key;
                            $entry[$key] = self::parseString($rest);
                        } else {
                            $entry[$state] .= self::parseString(trim($line));
                        }
                }
            }
            $line += 1;
        }
        return $stringset;
    }

    /**
     * PHP String parsing without using eval.
     * @param string $str Unparsed double-quoted string with escape sequences.
     * @return string
     */
    private static function parseString($str)
    {
        if ($str[0] !== '"' || $str[strlen($str) - 1] !== '"') {
            throw new Exception("Invalid string delimiters");
        }

        $result = '';
        $start = str_split(substr($str, 1, -1), 1);
        $escaped = false;
        $data = null;

        foreach ($start as $chr) {
            if ($escaped === 'yes') {
                $escaped = false;
                switch ($chr) {
                    case 'n':  $result .= "\n"; break;
                    case 'r':  $result .= "\r"; break;
                    case 't':  $result .= "\t"; break;
                    case 'v':  $result .= "\v"; break;
                    case 'e':  $result .= "\e"; break;
                    case 'f':  $result .= "\f"; break;
                    case '\\': $result .= "\\"; break;
                    case '$':  $result .= "\$"; break;
                    case '"':  $result .= "\""; break;
                    case 'x':
                        $escaped = 'hex';
                        $data = '0x';
                        break;
                    default:
                        if (ctype_digit($chr) && (int)$chr < 8) {
                            $escaped = 'oct';
                            $data = $chr;
                        } else {
                            $result .= "\\" . $chr;
                        }
                        break;
                }
            } else if ($escaped === 'hex' && ctype_xdigit($chr)) {
                $data .= $chr;
                if (strlen($data) === 2) {
                    $escaped = false;
                }
            } else if ($escaped === 'oct' && ctype_digit($chr) && (int)$chr < 8) {
                $data .= $chr;
                if (strlen($data) === 3) {
                    $escaped = false;
                }
            } else {
                if ($data !== null || $escaped === 'hex' || $escaped === 'oct') {
                    if (substr($data, 0, 2) === '0x') {
                        if (strlen($data) === 2) {
                            $result .= "\\x";
                        } else {
                            $result .= chr(hexdec($data));
                        }
                    } else {
                        $result .= chr(octdec($data));
                    }
                    $data = null;
                }

                if ($chr === '\\') {
                    $escaped = 'yes';
                } else if ($chr === '"') {
                    throw new Exception("Unescaped string delimiter inside string");
                } else {
                    $result .= $chr;
                }
            }
        }
        if ($escaped !== false) {
            throw new Exception("Unfinished escape sequence");
        }
        return $result;
    }
}
