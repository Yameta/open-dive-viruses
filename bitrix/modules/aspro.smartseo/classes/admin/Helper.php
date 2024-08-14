<?php

namespace Aspro\Smartseo\Admin;

class Helper
{

    const ROUTE_FILE = 'aspro.smartseo_smartseo.php';

    static public function url($route, $params = [])
    {
        $params['lang'] = urlencode(LANGUAGE_ID);
        
        return self::ROUTE_FILE . '?' . http_build_query(
            array_filter(
              array_merge(['route' => $route], $params)
            )
        );
    }

}
