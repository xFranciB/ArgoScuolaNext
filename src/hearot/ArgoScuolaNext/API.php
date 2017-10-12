<?php
namespace hearot\ArgoScuolaNext;

/**
 *  API for ArgoScuolaNext
 *
 *  This class is used to connect, login and use ArgoScuolaNext APIs (https://www.portaleargo.it)
 *
 *  @author Hearot
 *  @copyright 2017
 *  @license GNU AGPL v3
 *
 *  @method boolean __construct(string $schoolCode, string $username, string $password, boolean $no_info) This method will log in you
 *  @method string __call() That's the default method, it will be called while you're using APIs
 */
class API
{
    const ARGO_ENDPOINT = 'https://www.portaleargo.it/famiglia/api/rest/';
    const ARGO_KEY = 'ax6542sdru3217t4eesd9';
    const ARGO_VERSION = '2.0.2';
    public function __construct($schoolCode, $username, $password, $no_info = false)
    {
        /**
         *  This method will log in you
         *
         * @internal
         * @param string $schoolCode Code of the school
         * @param string $username Your ArgoScuolaNext username
         * @param string $password Your ArgoScuolaNext password
         * @param boolean $no_info If you don't want to set your infos, default is false
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ARGO_ENDPOINT . 'login?' . http_build_query(array('_dc' => round(microtime(true) * 1000))));
        curl_setopt($ch, CURLOPT_HEADER, array('x-key-app: ' . self::ARGO_KEY, 'x-version: ' . self::ARGO_VERSION, 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',  'x-cod-min: ' . $schoolCode, 'x-user-id: ' . $username, 'x-pwd: ' . $password));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $request = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            $this->schoolCode = $schoolCode;
            $this->username = $username;
            $this->password = $password;
            $this->temponaryToken = json_decode($request, true)['token'];
            unset($ch);
            unset($request);
            if (!$no_info) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, self::ARGO_ENDPOINT . 'schede?' . http_build_query(array('_dc' => round(microtime(true) * 1000))));
                curl_setopt($ch, CURLOPT_HEADER, array('x-key-app: ' . self::ARGO_KEY, 'x-version: ' . self::ARGO_VERSION, 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',  'x-cod-min: ' . $schoolCode, 'x-auth-token: ' . $this->temponaryToken));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $request = curl_exec($ch);
                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
                    foreach (json_decode($request, true) as $node => $content) {
                        $this->{$node} = $content;
                    }
                } else {
                    throw new \hearot\ArgoScuolaNext\BadRequestException('Something went wrong. ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ' ' . $request);
                }
            }
        } else {
            throw new \hearot\ArgoScuolaNext\LoginException('Wrong username, password or school code.');
        }
    }
    public function __call($name, $arguments)
    {
        /**
         *  This method will call a ArgoScuolaNext APIs Method
         *
         * @internal
         * @param string $name Name of the method
         * @param array $arguments Arguments of the call
         * @return string
         */
        $ch = curl_init();
        $query = [];
        if (strtolower($name) == 'oggi') {
            if (isset($arguments['datGiorno'])) {
                $query['datGiorno'] = $arguments['datGiorno'];
            } else {
                $query['datGiorno'] = date('Y-m-d');
            }
        }
        $query['_dc'] = round(microtime(true) * 1000);
        curl_setopt($ch, CURLOPT_URL, self::ARGO_ENDPOINT . $name . '?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-key-app: ' . self::ARGO_KEY, 'x-version: ' . self::ARGO_VERSION, 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36', 'x-auth-token: ' . $this->authToken, 'x-cod-min: ' . $this->codMin, 'x-prg-alunno: ' . $this->prgAlunno, 'x-prg-scheda: ' . $this->prgScheda, 'x-prg-scuola: ' . $this->prgScuola));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $request = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            return $request;
        } else {
            throw new \hearot\ArgoScuolaNext\BadRequestException('Something went wrong. ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ' ' . $request);
            return $request;
        }
    }
}
