<?php

/**
 * API wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Api
{
    /**
     * @var unknown
     */
    public $method;
    public $query;
    private $outputFormat;
    private $allowedMethods;
    private $contentType;
    private $httpCode;
    private $body;

    /**
     * Initializes an API object with the given informations.
     *
     * @param string $outputFormat   format ot the return of the API, default value is json
     * @param array  $allowedMethods allowed HTTP methods for the API, default value is ['POST', 'GET', 'DELETE', 'PUT']
     */
    public function __construct($outputFormat = 'json', $allowedMethods = array('POST', 'GET', 'DELETE', 'PUT'))
    {
        $this->outputFormat = $outputFormat;
        $this->allowedMethods = $allowedMethods;
        //check call method
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->method = $_SERVER['REQUEST_METHOD'];
            if (!in_array($this->method, $this->allowedMethods)) {
                //return a 501 error
                $this->output(501, $this->method.' method is not supported for this ressource');
                exit();
            }
            //get parameters
            $this->query = array();
            switch ($this->method) {
                case 'POST':
                case 'PUT':
                    $this->query['body'] = json_decode(file_get_contents('php://input'));
                case 'DELETE':
                case 'GET':
                default:
                    $this->query = array_merge($this->query, $_GET);
            }
        }
    }

    /**
     * Check the if the user have a correct authentication and authorization.
     *
     * @param string $message message that the consumer see in case of authentication or authorization issue
     *
     * @todo
     */
    public function checkAuth($message = 'You need authentication and authorization')
    {
        if (false) {
            $this->output(401, $message);
            exit();
        }
    }

    /**
     * Output the provided data in the wished format.
     *
     * @param number $httpCode HTTP code returned
     * @param string $body     data returned in the HTTP response body
     *
     * @todo provide XML formatting (actually raw data)
     */
    public function output($httpCode = 500, $body = null)
    {
        //check http code format
        if (preg_match('/^\d\d\d$/', $httpCode)) {
            $this->httpCode = $httpCode;
        } else {
            $this->httpCode = 500;
        }
        //return http status
        http_response_code($this->httpCode);
        $this->body = $body;
        if (!preg_match('/^2\d\d$/', $this->httpCode)) {
            if ($this->httpCode == 403) {
                //add the error in webserver log
                error_log('client denied by server configuration: '.$_SERVER['SCRIPT_NAME']);
            }
            if (isset($this->body) && is_string($this->body)) {
                $this->body = new ErrorModel($this->httpCode, $this->body);
            } else {
                $this->body = new ErrorModel($this->httpCode);
            }
        }
        //return correct content-type header and output
        switch ($this->outputFormat) {
            case 'html':
                header('Content-type: text/html; charset=UTF-8');
                echo $this->body;
                break;
            case 'xml':
                header('Content-type: application/xml; charset=UTF-8');
                echo $this->body;
                break;
            case 'json':
            default:
                header('Content-type: application/json; charset=UTF-8');
                echo json_encode($this->body);
        }
    }
}

/**
 * Error model returned in Api.
 *
 * @version 1.0.0
 *
 * @internal
 */
class ErrorModel
{
    /**
     * Initializes an error with the given informations.
     *
     * @param number $code    Error code used by the consumer to handle it
     * @param string $message Description of the error for human understanding
     */
    public function __construct($code = 500, $message = '')
    {
        $this->code = $code;
        $this->message = $message;
    }
}
