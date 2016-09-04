<?php

/**
 * HTTP Request wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class HttpRequest
{
    /**
     * @var string URL endpoint for request
     */
    private $endpoint;
    /**
     * @var array Headers request to provide
     */
    private $headers;
    /**
     * @var string Body request to provide
     */
    private $body;
    /**
     * @var array Query parameters to provide in request
     */
    private $parameters;
    /**
     * @var string Request method (GET, POST)
     */
    private $method;
    /**
     * @var string URL is the endpoint with query string
     */
    private $url;

    /**
     * Executes an HTTP request with the given informations.
     *
     * @param string $endpoint   URL endpoint for request
     * @param array  $headers    Headers request ['key' => 'value']
     * @param string $body       Body request
     * @param array  $parameters Query parameters
     * @param string $response   Returned HTTP response
     * @param string $response   Returned HTTP headers
     * @param string $response   Optional timeout for request in seconds (default is 10 seconds)
     */
    public function execute($endpoint, $method, $headers, $body, $parameters, &$response, &$responseHeaders, $timeout = 10)
    {
        $this->endpoint = $endpoint;
        $this->method = $method;
        $this->headers = $headers;
        $this->body = $body;
        $this->parameters = $parameters;
        //initializes output variables
        $responseHeaders = array();
        $response = null;
        //prepare call
        $this->url = $this->endpoint;
        if (count($this->parameters) > 0) {
            $this->url .= '?'.http_build_query($this->parameters);
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        $opts = array('http' => array(
                'method' => $method,
                'timeout' => $timeout,
                'user_agent' => $configuration->get('userAgent')
            ),
        );
        if ($body !== null) {
            $opts['http']['content'] = $body;
        }
        if (is_array($headers)) {
            foreach ($headers as $key => $value) {
                $opts['http']['header'] = "$key: $value";
            }
        }
        $context = stream_context_create($opts);
        //try the call
        try {
            $stream = @fopen($this->url, 'r', false, $context);
            if ($stream === false) {
                error_log('SRV-HTTP-OPEN Failed to open stream: '.$this->url);
                //there was an error, return false
                return false;
            }
            //get response headers
            $responseMetaDatas = (stream_get_meta_data($stream));
            $responseHeaders = $responseMetaDatas['wrapper_data'];
            //get response
            ob_start();
            fpassthru($stream);
            $response = ob_get_contents();
            ob_end_clean();
            fclose($stream);
            //returns call successfully processed
            return true;
        } catch (Exception $e) {
            //there was an error return false
            return false;
        }
    }
}
