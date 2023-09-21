<?php

namespace Core\Enums;

enum HttpMethods: string
{
    case GET = 'GET'|'get';
    case POST = 'POST'|'post';
    case PUT = 'PUT'|'put';
    case  DELETE = 'DELETE'|'delete';
    case PATCH = 'PATCH'|'patch';
    case OPTIONS = 'OPTIONS'|'options';


}
