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
     * @var string HTTP verb used to call API
     */
    public $method;
    /**
     * @var array Parameters provided in API call ; query parameters are in query[param], body request is in query['body']
     */
    public $query;
    /**
     * @var string Requested output format
     */
    private $outputFormat;
    /**
     * @var array HTTP verbs allowed for calling API
     */
    private $allowedMethods;
    /**
     * @var int HTTP status code returned by API
     */
    private $httpCode;
    /**
     * @var string Returned data
     */
    private $responseBody;

    /**
     * Initializes an API object with the given informations.
     *
     * @param string $outputFormat   Indicates API output format, default value is json
     * @param array  $allowedMethods Allowed HTTP methods for the API, default value is ['POST', 'GET', 'DELETE', 'PUT']
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
     * @param string $message Message that the consumer see in case of authentication or authorization issue
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
     * @param int    $httpCode     HTTP code returned
     * @param string $responseBody Data returned in the HTTP response body
     *
     * @todo Provide XML formatting (actually raw data)
     */
    public function output($httpCode = 500, $responseBody = null)
    {
        //check http code format
        if (preg_match('/^\d\d\d$/', $httpCode)) {
            $this->httpCode = $httpCode;
        } else {
            $this->httpCode = 500;
        }
        //return http status
        http_response_code($this->httpCode);
        $this->responseBody = $responseBody;
        if (!preg_match('/^2\d\d$/', $this->httpCode)) {
            if ($this->httpCode == 403) {
                //add the error in webserver log
                error_log('client denied by server configuration: '.$_SERVER['SCRIPT_NAME']);
            }
            if (isset($this->responseBody) && is_string($this->responseBody)) {
                $this->responseBody = new ErrorModel($this->httpCode, $this->responseBody);
            } else {
                $this->responseBody = new ErrorModel($this->httpCode);
            }
        }
        //return correct content-type header and output
        switch ($this->outputFormat) {
            case 'html':
                header('Content-type: text/html; charset=UTF-8');
                echo $this->responseBody;
                break;
            case 'xml':
                header('Content-type: application/xml; charset=UTF-8');
                echo $this->responseBody;
                break;
            case 'json':
            default:
                header('Content-type: application/json; charset=UTF-8');
                echo json_encode($this->responseBody);
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
     * @param int    $code    Error code used by the consumer to handle it
     * @param string $message Description of the error for human understanding
     */
    public function __construct($code = 500, $message = '')
    {
        $this->code = $code;
        $this->message = $message;
    }
}
