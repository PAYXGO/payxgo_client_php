<?php
require_once(dirname(__FILE__) . '/payxgo_utils/Utils.php');
require_once(dirname(__FILE__) . '/payxgo_utils/XRsa.php');
class PayxgoClient {
    
    /**
     * @var string
     */
    protected $api_base_url = '/go/v1/transactions/';
    protected $secretKey = ''; // 支付私钥， 在api token页面获取
    protected $accessKey = ''; // 访问密钥， 在api token页面获取
    protected $requestId = ''; //
    protected $randomStr = ''; //
    protected $t = 0; //
    public $cookie = ''; //
    protected $action = 0; //

    const SECUREPAY_ACTION = 1;
    const QRREFRESH_ACTION = 2;

    protected static $sig_keys = array(
    		'securepay' => array(
    				'currency', 'amount', 'vendor', 'orderNum', 'ipnUrl'
    		),
    		'qrRefresh' => array(
    		)
    );
    
    /**
     * @var array
     */
    protected $allowed_request_methods = array(
        'get',
        'put',
        'post',
        'delete',
    );

    /**
     * Constructor
     * 
     * @param string $secretKey
     * @param string $accessKey
     */
    public function __construct($apiDomain, $secretKey, $accessKey, $cookie = null)
    {
        $this->setUrl($apiDomain.$this->api_base_url);

        $this->secretKey = $secretKey;
        $this->accessKey = $accessKey;

        if (!empty($cookie)){
            $this->cookie = $cookie;
        }
        
        $validate_params = array
        (
            false === extension_loaded('curl') => 'The curl extension must be loaded for using this class!',
            false === extension_loaded('json') => 'The json extension must be loaded for using this class!',
        	empty($apiDomain) => 'apiDomain is not set!',
        	empty($this->secretKey) => 'secretKey is not set!',
        	empty($this->accessKey) => 'accessKey is not set!',
        );
        $this->checkForErrors($validate_params);
    }


    /**
     * Set Api URL
     * 
     * @param string $url Api URL
     */
    public function setUrl($url)
    {
        $this->api_base_url = $url;
    }
    
    /**
     * create payment securepay
     *
     * @param $params create Params
     * @return array
     */
    public function securepay(array $params)
    {
        $this->action = self::SECUREPAY_ACTION;
        return $this->call(
            'securepay',
            'post',
             $params
        );
    }
    
    /**
     * get payment qrRefresh
     *
     * @param $params query Params
     * @return array
     */
    public function qrRefresh(array $params)
    {
        $this->action = self::QRREFRESH_ACTION;
    	return $this->call(
    			'qrRefresh',
    			'post',
    			$params
    	);
    }

    /**
     * Method responsible for preparing, setting state and returning answer from rest server
     *
     * @param string $method
     * @param string $request
     * @param array $params
     * @return array
     */
    protected function call($method, $request, $params)
    {
        $validate_params = array
        (
            false === is_string($method) => 'Method name must be string',
            false === $this->checkRequestMethod($request) => 'Not allowed request method type',
            // true === empty($params) => 'params is null',
        );

        $this->checkForErrors($validate_params);
        
        $vars = $this->getSig($params, self::$sig_keys[$method]);
        
        return $this->postData($method, $request, $vars);
    }
    /**
     * Checking error mechanism
     *
     * @param array $validateArray
     * @throws Exception
     */
    protected function getSig(array &$params, array $sig_keys)
    {
    	$msg_array = array();

    	foreach ($sig_keys as $key) {
            if (empty($params[$key])){
                continue;
            }
            if ($key == 'amount'){
                $params[$key] *= 100;
            }
    		$msg_array[$key] = $params[$key];
    	}

        $this->t = time();
    	$msg_array['t'] = $this->t;
        $msg_array["accessKey"] = $this->accessKey;

        $utils = new Utils();
        $randomStr = $utils->getRandChar(32);

        $this->requestId = $this->makeRequestId($randomStr, $msg_array);

        $pubKey = $utils->convertPubkey($this->secretKey);

        $xRsa = new XRsa($pubKey);
        $this->randomStr = $xRsa->publicEncrypt($randomStr);
        $msg_array["randomStr"] = $this->randomStr;

        unset($msg_array['t']);
    	
    	return json_encode($msg_array);
    }

    function makeRequestId($randomkey, $params){

        ksort($params);
        $sign_str = "";
        foreach ($params as $key => $val) {
            if ($val == null || $val == '' || $val == 'null') {
                continue;
            }
            if ($sign_str == "")
                $sign_str .= sprintf("%s=%s", $key, urlencode($val));
            else
                $sign_str .= sprintf("&%s=%s", $key, urlencode($val));
        }

        return $this->sha512($randomkey.$sign_str.$randomkey);
    }
    
    /* PHP sha512() */
    function sha512($data, $rawOutput = false)
    {
        if (!is_scalar($data)) {
            return false;
        }
        $data = (string)$data;
        $rawOutput = !!$rawOutput;
        return hash('sha512', $data, $rawOutput);
    }

    /**
     * Checking error mechanism
     *
     * @param array $validateArray
     * @throws Exception
     */
    protected function checkForErrors(&$validate_params)
    {
        foreach ($validate_params as $key => $error)
        {
            if ($key)
            {
                throw new Exception($error, -1);
            }
        }
    }

    /**
     * Check if method is allowed
     *
     * @param string $method_type
     * @return bool
     */
    protected function checkRequestMethod($method_type)
    {
        $request_method = strtolower($method_type);

        if(in_array($request_method, $this->allowed_request_methods))
        {
            return true;
        }

        return false;
    }

    /**
     * Method responsible for pushing data to server
     *
     * @param string $method
     * @param string $method_type
     * @param array|string $vars
     * @return array
     * @throws Exception
     */
    protected function postData($method, $method_type, $vars)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_base_url. $method);
        curl_setopt($ch, CURLOPT_POST, true);
       
        if (is_array($vars)) $vars = http_build_query($vars, '', '&');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);

        curl_close($ch);

        // 解析http数据流
        list($header, $body) = explode("\r\n\r\n", $response);

        // 解析cookie
        preg_match("/Cookie:([^\r\n]*)/i", $header, $matches);

        $cookie = $matches[1];

        $this->cookie = $cookie;
        
        return $body;
    }
    
    protected function &getHeaders() {
    	$headers = array(
    			'Accept: application/json',
    			"Content-Type: application/json",
    			'X-Request-ID: '.base64_encode($this->requestId),
                't: '.$this->t,
    	);

        if ($this->action == self::QRREFRESH_ACTION) {
            array_push($headers, 'Cookie: '.$this->cookie);
        }

    	return $headers;
    }
}