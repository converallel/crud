<?php
use Migrations\AbstractSeed;

/**
 * HttpStatusCodes seed.
 */
class HttpStatusCodesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'code' => 100,
                'definition' => 'Continue',
            ],
            [
                'code' => 101,
                'definition' => 'Switching Protocols',
            ],
            [
                'code' => 102,
                'definition' => 'Processing',
            ],
            [
                'code' => 103,
                'definition' => 'Early Hints',
            ],
            [
                'code' => 200,
                'definition' => 'OK',
            ],
            [
                'code' => 201,
                'definition' => 'Created',
            ],
            [
                'code' => 202,
                'definition' => 'Accepted',
            ],
            [
                'code' => 203,
                'definition' => 'Non-Authoritative Information',
            ],
            [
                'code' => 204,
                'definition' => 'No Content',
            ],
            [
                'code' => 205,
                'definition' => 'Reset Content',
            ],
            [
                'code' => 206,
                'definition' => 'Partial Content',
            ],
            [
                'code' => 207,
                'definition' => 'Multi-Status',
            ],
            [
                'code' => 208,
                'definition' => 'Already Reported',
            ],
            [
                'code' => 226,
                'definition' => 'IM Used',
            ],
            [
                'code' => 300,
                'definition' => 'Multiple Choices',
            ],
            [
                'code' => 301,
                'definition' => 'Moved Permanently',
            ],
            [
                'code' => 302,
                'definition' => 'Found',
            ],
            [
                'code' => 303,
                'definition' => 'See Other',
            ],
            [
                'code' => 304,
                'definition' => 'Not Modified',
            ],
            [
                'code' => 305,
                'definition' => 'Use Proxy',
            ],
            [
                'code' => 307,
                'definition' => 'Temporary Redirect',
            ],
            [
                'code' => 308,
                'definition' => 'Permanent Redirect',
            ],
            [
                'code' => 400,
                'definition' => 'Bad Request',
            ],
            [
                'code' => 401,
                'definition' => 'Unauthorized',
            ],
            [
                'code' => 402,
                'definition' => 'Payment Required',
            ],
            [
                'code' => 403,
                'definition' => 'Forbidden',
            ],
            [
                'code' => 404,
                'definition' => 'Not Found',
            ],
            [
                'code' => 405,
                'definition' => 'Method Not Allowed',
            ],
            [
                'code' => 406,
                'definition' => 'Not Acceptable',
            ],
            [
                'code' => 407,
                'definition' => 'Proxy Authentication Required',
            ],
            [
                'code' => 408,
                'definition' => 'Request Timeout',
            ],
            [
                'code' => 409,
                'definition' => 'Conflict',
            ],
            [
                'code' => 410,
                'definition' => 'Gone',
            ],
            [
                'code' => 411,
                'definition' => 'Length Required',
            ],
            [
                'code' => 412,
                'definition' => 'Precondition Failed',
            ],
            [
                'code' => 413,
                'definition' => 'Payload Too Large',
            ],
            [
                'code' => 414,
                'definition' => 'Request-URI Too Long',
            ],
            [
                'code' => 415,
                'definition' => 'Unsupported Media Type',
            ],
            [
                'code' => 416,
                'definition' => 'Requested Range Not Satisfiable',
            ],
            [
                'code' => 417,
                'definition' => 'Expectation Failed',
            ],
            [
                'code' => 418,
                'definition' => 'I\'m a teapot',
            ],
            [
                'code' => 421,
                'definition' => 'Misdirected Request',
            ],
            [
                'code' => 422,
                'definition' => 'Unprocessable Entity',
            ],
            [
                'code' => 423,
                'definition' => 'Locked',
            ],
            [
                'code' => 424,
                'definition' => 'Failed Dependency',
            ],
            [
                'code' => 426,
                'definition' => 'Upgrade Required',
            ],
            [
                'code' => 428,
                'definition' => 'Precondition Required',
            ],
            [
                'code' => 429,
                'definition' => 'Too Many Requests',
            ],
            [
                'code' => 431,
                'definition' => 'Request Header Fields Too Large',
            ],
            [
                'code' => 444,
                'definition' => 'Connection Closed Without Response',
            ],
            [
                'code' => 451,
                'definition' => 'Unavailable For Legal Reasons',
            ],
            [
                'code' => 499,
                'definition' => 'Client Closed Request',
            ],
            [
                'code' => 500,
                'definition' => 'Internal Server Error',
            ],
            [
                'code' => 501,
                'definition' => 'Not Implemented',
            ],
            [
                'code' => 502,
                'definition' => 'Bad Gateway',
            ],
            [
                'code' => 503,
                'definition' => 'Service Unavailable',
            ],
            [
                'code' => 504,
                'definition' => 'Gateway Timeout',
            ],
            [
                'code' => 505,
                'definition' => 'HTTP Version Not Supported',
            ],
            [
                'code' => 506,
                'definition' => 'Variant Also Negotiates',
            ],
            [
                'code' => 507,
                'definition' => 'Insufficient Storage',
            ],
            [
                'code' => 508,
                'definition' => 'Loop Detected',
            ],
            [
                'code' => 510,
                'definition' => 'Not Extended',
            ],
            [
                'code' => 511,
                'definition' => 'Network Authentication Required',
            ],
            [
                'code' => 599,
                'definition' => 'Network Connect Timeout Error',
            ],
        ];

        $table = $this->table('http_status_codes');
        $table->insert($data)->save();
    }
}
