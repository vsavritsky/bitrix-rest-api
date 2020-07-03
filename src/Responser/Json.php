<?php

namespace BitrixRestApi\Responser;

/**
 * Отдача в формате json
 *
 * @author dmitriy
 */
class Json implements ResponserInterface
{
    public function send(array $result, $error = false)
    {
        header('Content-Type: application/json');
        if ($result['errorCode'] == '404') {
            header('HTTP/1.1 404 Internal Server Error', true, 404);
        } else if ($error || $result['errorCode']) {
            header('HTTP/1.1 400 Internal Server Error', true, 400);
        }
        
        if (isset($result['code'])) {
            if ($result['code'] == 201) {
                header('HTTP/1.1 201 Created', true, 201);
            }
            
            if ($result['code'] == 204) {
                header('HTTP/1.1 204 No Content', true, 204);
            }
        } elseif ($result['data']) {
            header('HTTP/1.1 200 OK', true, 200);
        }
        
        echo json_encode($result);
    }
}
