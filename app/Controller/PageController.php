<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\DbConnection\Db;

/**
 * @AutoController
 * Class PageController
 * @package App\Controller
 */
class PageController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $page = $request->input('page')?:1;
        $size = 5;
        $offset = ($page-1)*$size;

        $list = Db::table('departments')->offset($offset)->limit($size)->get();
        
        return ['data'=>$list, 'meta'=>['page'=>$page, 'size'=>$size]]; 
    }

    public function two(RequestInterface $request, ResponseInterface $response)
    {
        $list = Db::table('departments')->paginate(5);
        return $list;
    }
}
