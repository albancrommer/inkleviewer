<?php
$command= 'curl -XGET \'localhost:9200/inkle/stories/_search\' -d \'{
"from":0,size:10,"query": {
"function_score" : {
"query" : { "range":{ "storyScore": { "gte":3.5} } },
"random_score" : {}
}}}\'';
exec( $command, $output, $return_code );

$response = json_decode( $output[0], true);
$storyList = [];
foreach( $response['hits']['hits'] as $key => $hitData){


    $storyList[] = $hitData['_source'];
}
