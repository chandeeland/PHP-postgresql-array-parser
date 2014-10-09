<?php

/**
 * parse a postgres array (string) into a PHP array
 *
 * @author dchan@sigilsoftware.com
 */
function pg_parse($arraystring, $reset=true)
{
    static $i = 0;
    if ($reset) $i = 0;

    $matches = array();
    $indexer = 1;   // by default sql arrays start at 1

    // handle [0,2]= cases
    if (preg_match('/^\[(?P<index_start>\d+):(?P<index_end>\d+)]=/', substr($arraystring, $i), $matches)) {
        $indexer = (int)$matches['index_start'];
        $i = strpos($arraystring, '{');
    }

    if ($arraystring[$i] != '{') {
        return NULL;
    }

    if (is_array($arraystring)) return $arraystring;

    // handles btyea and blob binary streams
    if (is_resource($arraystring)) return fread($arraystring, 4096);

    $i++;
    $work = array();
    $curr = '';
    $length = strlen($arraystring);
    $count = 0;
    $quoted = false;

    while ($i < $length) {
        // echo "\n [ $i ] ..... $arraystring[$i] .... $curr";

        switch ($arraystring[$i]) {
        case '{':
            $sub = pg_parse($arraystring, false);
            if(!empty($sub)) {
                $work[$indexer++] = $sub;
            }
            break;
        case '}':
            $i++;
            if (strlen($curr) > 0) $work[$indexer++] = $curr;
            return $work;
            break;
        case '\\':
            $i++;
            $curr .= $arraystring[$i];
            $i++;
            break;
        case '"':
            $quoted = true;
            $openq = $i;
            do {
                $closeq = strpos($arraystring, '"' , $i + 1);
                $escaped = $closeq > $openq &&
                    preg_match('/(\\\\+)$/', substr($arraystring, $openq + 1, $closeq - ($openq + 1)), $matches) &&
                    (strlen($matches[1])%2);
                if ($escaped) {
                    $i = $closeq;
                } else {
                    break;
                }
            } while(true);

            if ($closeq <= $openq) {
                throw new Exception('Unexpected condition');
            }

            $curr .= substr($arraystring, $openq + 1, $closeq - ($openq + 1));

            $i = $closeq + 1;
            break;
        case ',':
            if (strlen($curr) > 0){
                if (!$quoted && (strtoupper($curr) == 'NULL')) $curr = null;
                $work[$indexer++] = $curr;
            }
            $curr = '';
            $quoted = false;
            $i++;
            break;
        default:
            $curr .= $arraystring[$i];
            $i++;
        }
    }

    throw new Exception('Unexpected line end');
}

