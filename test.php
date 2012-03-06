<?php
/**
 *
 * testing various cases
 *
 *
 */

require 'pg_parse.php';

$samples = array(
//    '{"meeting","lunch"}',
//    '{"meeting with boss",dinner,party}',
//    '{{"meeting","lunch"},{"meeting with boss"},{dinner,party}}',
//    '{{"meeting","lunch"},"meeting with boss",{dinner,party}}',
//    '{{{{{{{{{{{"meeting","lunch"},{"meeting with boss"},{dinner,party}}}}}}}}}}}',
//    '[0:0]={"meeting"}',
//    '[2:4]={{"meeting","lunch"},{"meeting with boss"},{dinner}}',
//    'invalid not an array',
    '{xyz,"abc}def"}',
    '{xyz,"abc}d\\"ef"}',
    '{xyz,"ab{\"meeting\",\"lunch\"}"}',
    '{xyz,\{hello\},"a{b},{c}"}',
);

foreach ($samples as $x) {
    echo "\n\n $x\n";
    var_dump(pg_parse($x));
}
