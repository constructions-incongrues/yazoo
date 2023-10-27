<?php

namespace App\Service;


class HttpStatusService
{



    public function get(string $url): array
    {

        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_HEADER, true);//HEADER ONLY
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5); //number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        curl_setopt($handle, CURLOPT_MAXREDIRS, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 5);

        $dat=[];//output
        /* Get the HTML or whatever is linked in $url. */

        $response = curl_exec($handle);
        //dd($response);
        $dat['httpStatus'] = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $dat['info']=$this->codename($dat['httpStatus']);

        // Extract MIME type from the Content-Type header
        preg_match('/Content-Type: ([^\s]+)/', $response, $matches);
        $dat['mimeType'] = isset($matches[1]) ? $matches[1] : false;

        return $dat;
    }


    public function codeName(int $code){
        if(@$this->codes[$code]){
            return $this->codes[$code];
        }
        return '???';
    }

    /**
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/
     *
     * @var array
     */
    private $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );

}
