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

    $i++;
    $work = array();
    $curr = '';
    $length = strlen($arraystring);

    while ($i < $length)
    {
        switch ($arraystring[$i])
        {
        case '{':
            $sub = pg_parse($arraystring, false);
            if(!empty($sub)) {
                $work[$indexer++] = $sub;
            }
            break;
        case '}':
            $i++;
            if (!empty($curr)) $work[$indexer++] = $curr;
            return $work;
            break;
        case '\\':
        case '"':
            $i++;
            break;
        case ',':
            if (!empty($curr)) $work[$indexer++] = $curr;
            $curr = '';
            $i++;
            break;
        default:
            $curr .= $arraystring[$i];
            $i++;
        }
    }
}

