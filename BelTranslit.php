<?php

namespace Pathologic;

class BelTranslit
{
    private static function isLitara($c)
    {
        $c = mb_strtolower($c);

        return self::oneOf($c, "йцкнгшўзхфвпрлджчсмтбёуеыаоэяію'’ь");
    }

    private static function isZyc($c)
    {
        $c = mb_strtolower($c);

        return self::oneOf($c, "йцкнгшўзхфвпрлджчсмтб");
    }

    private static function isHal($c)
    {
        $c = mb_strtolower($c);

        return self::oneOf($c, "ёуеыаоэяію");
    }

    private static function isU($c)
    {
        return $c == mb_strtoupper($c) && $c != mb_strtolower($c);
    }

    private static function isUW($c, $prev, $next)
    {
        return self::isU($c) && (self::isU($prev) || self::isU($next));
    }

    private static function changeLastLetter($text, $newLetter)
    {
        return mb_substr($text, 0, mb_strlen($text) - 1) . $newLetter;
    }

    private static function oneOf($letter, $many)
    {
        return mb_strpos($many, $letter) !== false;
    }

    public static function convert($text, $latTrad = false, $unhac = true)
    {
        $out = "";
        $simple = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'h',
            'ґ' => 'g',
            'д' => 'd',
            'ж' => 'ž',
            'з' => 'z',
            'й' => 'j',
            'к' => 'k',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ў' => 'ŭ',
            'ф' => 'f',
            'х' => 'ch',
            'ц' => 'c',
            'ч' => 'č',
            'ш' => 'š',
            'ы' => 'y',
            'э' => 'e',
        ];
        $halosnyja = [
            'е' => 'e',
            'ё' => 'o',
            'ю' => 'u',
            'я' => 'a',
        ];
        $text = mb_str_split($text);
        foreach ($text as $i => $c) {
            $prev = $i > 0 ? $text[$i - 1] : "?";
            $next = $i < count($text) - 1 ? $text[$i + 1] : "?";
            $wordUpper = self::isUW($c, $prev, $next);
            $thisUpper = self::isU($c);
            $prevUpper = self::isU($prev);
            $c = mb_strtolower($c);
            $prev = mb_strtolower($prev);
            $next = mb_strtolower($next);
            if ($c == "'" || $c == "’") {
                continue;
            }
            $sm = $simple[$c] ?? null;
            if (is_null($sm)) {
                switch ($c) {
                    case "л":
                        if ($latTrad) {
                            if (self::oneOf($next, "еёюяіь'’")) {
                                $sm = "l";
                            } else {
                                $sm = "ł";
                            }
                        } else {
                            $sm = "l";
                        }
                        break;

                    case "е":
                    case "ё":
                    case "ю":
                    case "я":
                        if ($prev == "л" && $latTrad) {
                            $sm = $halosnyja[$c];
                        } else {
                            if ($prev == "'" || $prev == "’" || $prev == "й" || $prev == "ў") {
                                $sm = "j" . $halosnyja[$c];
                            } else {
                                if (self::isZyc($prev)) {
                                    $sm = "i" . $halosnyja[$c];
                                } else {
                                    $sm = "j" . $halosnyja[$c];
                                }
                            }
                        }
                        break;

                    case "і":
                        if ($prev == "'" || $prev == "’") {
                            $sm = "ji";
                        } else {
                            if ($prev == "й" || $prev == "ў") {
                                if ($latTrad) {
                                    $sm = "ji";
                                } else {
                                    $sm = "i";
                                }
                            } else {
                                $sm = "i";
                            }
                        }
                        break;

                    case "ь":
                        $sm = "";
                        if (mb_strlen($out) > 0) {
                            $p = mb_substr($out, -1, 1);
                            switch ($p) {
                                case "Z":
                                    $p = "Ź";
                                    break;

                                case "z":
                                    $p = "ź";
                                    break;

                                case "N":
                                    $p = "Ń";
                                    break;

                                case "n":
                                    $p = "ń";
                                    break;

                                case "S":
                                    $p = "Ś";
                                    break;

                                case "s":
                                    $p = "ś";
                                    break;

                                case "C":
                                    $p = "Ć";
                                    break;

                                case "c":
                                    $p = "ć";
                                    break;

                                case "L":
                                    if (!$latTrad) {
                                        $p = "Ĺ";
                                    }
                                    break;

                                case "l":
                                    if (!$latTrad) {
                                        $p = "ĺ";
                                    }
                                    break;

                                case "Ł":
                                    if ($latTrad) {
                                        $p = "L";
                                    }
                                    break;

                                case "ł":
                                    if ($latTrad) {
                                        $p = "l";
                                    }
                                    break;
                            }
                            $out = self::changeLastLetter($out, $p);
                        }
                        break;

                    default:
                        $sm = $c;
                        break;
                }
            }
            if ($thisUpper) {
                if ($wordUpper || mb_strlen($sm) < 2) {
                    $sm = mb_strtoupper($sm);
                } else {
                    $sm = mb_strtoupper(mb_substr($sm, 0, 1)) . mb_substr($sm, 1);
                }
            }
            $out .= $sm;
        }
        return $unhac ? self::unhac($out) : $out;
    }

    private static function unhac($text)
    {
        return str_replace([
            'Ć',
            'ć',
            'Č',
            'č',
            'Ł',
            'ł',
            'Ĺ',
            'ĺ',
            'Ń',
            'ń',
            'Ś',
            'ś',
            'Š',
            'š',
            'Ŭ',
            'ŭ',
            'Ź',
            'ź',
            'Ž',
            'ž',
        ], [
            'C',
            'c',
            'C',
            'c',
            'L',
            'l',
            'L',
            'l',
            'N',
            'n',
            'S',
            's',
            'S',
            's',
            'U',
            'u',
            'Z',
            'z',
            'Z',
            'z'
        ], $text);
    }
}
