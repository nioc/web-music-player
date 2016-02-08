<?php

/**
 * JSON Web Token wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 *
 * @see https://jwt.io/
 */
class Token
{
    /**
     * @var string Default key for Token
     */
    private $key = '123456';
    /**
     * @var object Raw data contained in the token
     */
    public $payload;
    /**
     * @var string Token as transmitted to client
     */
    public $value;

    /**
     * Encodes data and returns a JSON Web Token.
     *
     * @param int    $duration Optionnal duration of the token in hours (default is 7 days)
     * @param string $key      Optionnal hash key
     */
    public function encode($duration = 168, $key = null)
    {
        //header part
        $header = array(
                'alg' => 'HS256',
                'typ' => 'JWT',
        );
        $b64Header = base64_encode(json_encode($header));
        //payload part
        $payload = array(
                'iss' => 'wmp',
                'exp' => time() + (7 * 24 * 60 * 60),
        );
        $payload = array_merge($payload, get_object_vars($this->payload));
        $b64Payload = base64_encode(json_encode($payload));
        //sign
        $signature = hash_hmac('sha256', $b64Header.'.'.$b64Payload, $this->key, true);
        $b64Signature = base64_encode($signature);
        $this->value = $b64Header.'.'.$b64Payload.'.'.$b64Signature;
    }

    /**
     * Decodes JSON Web Token and set data in payload attribute.
     *
     * @param string $key Optionnal hash key
     *
     * @return bool Indicate if token is valid
     */
    public function decode($key = null)
    {
        $elements = explode('.', $this->value);
        if (count($elements) !== 3) {
            //invalid token format
            return false;
        }
        list($b64Header, $b64Payload, $b64Signature) = $elements;
        $headers = json_decode(base64_decode($b64Header));
        $payload = json_decode(base64_decode($b64Payload));
        $signature = base64_decode($b64Signature);
        //check signature
        if (!hash_equals($signature, hash_hmac('sha256', $b64Header.'.'.$b64Payload, $this->key, true))) {
            //signature invalid
            return false;
        }
        if ($payload->exp < time()) {
            //token expired
            return false;
        }
        $this->payload = $payload;
        //raw data is set, returns true
        return true;
    }
}
