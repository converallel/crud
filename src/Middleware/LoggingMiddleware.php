<?php

namespace Crud\Middleware;

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Logging middleware
 */
class LoggingMiddleware
{

    /**
     * Invoke method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $response = $next($request, $response);

        if (startsWith($request->getRequestTarget(), '/logs')) {
            return $response;
        }

        $request_body = json_decode($request->getBody()->getContents(), true);
        $data = [
            'user_id' => Configure::read('user_id'),
            'ip_address' => $request->clientIp(),
            'request_method' => $request->getMethod(),
            'request_url' => $request->getRequestTarget(),
            'request_headers' => json_encode($request->getHeaders()),
            'request_body' => $request_body ? json_encode($request_body) : null,
            'status_code' => $response->getStatusCode()
        ];
        $tableLocator = TableRegistry::getTableLocator();
        $logs = $tableLocator->get('Crud.Logs');
        $log = $logs->newEntity();
        $log = $logs->patchEntity($log, $data);
        if (!$logs->save($log)) {
            Log::error('Log could not be saved', $log->getErrors());
        }

        return $response;
    }
}
