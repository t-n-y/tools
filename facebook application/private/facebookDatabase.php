<?php

    require_once __DIR__.'/config.php';
    require_once __DIR__.'/facebookInit.php';
    
    //check in FB database if the current user like the page
    function selectPageIdFromPageFan($facebook, $facebookGetUser, $pageId)
        {
            $query = sprintf("SELECT page_id FROM page_fan WHERE uid = %s AND page_id = %s ", $facebookGetUser, $pageId);
            $result = $facebook->api(array(
                    'method' => 'fql.query',
                    'query' => $query,
            ));
            return $result;
        }

        function useFbApi ($facebook,$data){
            $returnData = $facebook->api('/'.$data);
            return $returnData;             
        }