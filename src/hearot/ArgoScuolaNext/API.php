<?php
namespace hearot\ArgoScuolaNext;

/**
 *  API for ArgoScuolaNext
 *
 *  This class is used to connect, login and use ArgoScuolaNext APIs (https://www.portaleargo.it)
 *
 * @author Hearot
 * @copyright 2017
 * @license GNU AGPL v3
 */
class API
{
    /**
     * The endpoint of the APIs
     * @var ARGO_ENDPOINT
     */
    const ARGO_ENDPOINT = 'https://www.portaleargo.it/famiglia/api/rest/';
    /**
     * The key that the object will use to access to the server
     * @var ARGO_KEY
     */
    const ARGO_KEY = 'ax6542sdru3217t4eesd9';
    /**
     * The version of the APIs
     * @var ARGO_VERSION
     */
    const ARGO_VERSION = '2.0.2';
    /**
     *  This method will log in you
     *
     * @param string $schoolCode Code of the school
     * @param string $username Your ArgoScuolaNext username
     * @param string $password Your ArgoScuolaNext password
     * @throws BadRequestException
     * @throws LoginException
     */
    public function __construct($schoolCode, $username, $password)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ARGO_ENDPOINT . 'login?' . http_build_query(array('_dc' => round(microtime(true) * 1000))));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-key-app: ' . self::ARGO_KEY, 'x-version: ' . self::ARGO_VERSION, 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',  'x-cod-min: ' . $schoolCode, 'x-user-id: ' . $username, 'x-pwd: ' . $password));
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
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::ARGO_ENDPOINT . 'schede?' . http_build_query(array('_dc' => round(microtime(true) * 1000))));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-key-app: ' . self::ARGO_KEY, 'x-version: ' . self::ARGO_VERSION, 'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',  'x-cod-min: ' . $schoolCode, 'x-auth-token: ' . $this->temponaryToken));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $request = curl_exec($ch);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
                foreach (json_decode($request, true)[0] as $node => $content) {
                    if (is_array($node)) {
                        foreach ($node as $subnode => $subcontent) {
                            $this->{$node}->{$subnode} = $subcontent;
                        }
                    } else {
                        $this->{$node} = $content;
                    }
                }
            } else {
                throw new \hearot\ArgoScuolaNext\BadRequestException('Something went wrong. ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ' ' . strip_tags($request));
            }
        } else {
            throw new \hearot\ArgoScuolaNext\LoginException('Wrong username, password or school code.');
        }
    }
    /**
     *  This method will call a ArgoScuolaNext APIs Method
     *
     * @param string $name Name of the method
     * @param array $arguments Arguments of the call
     * @return string
     */
    public function __call($name, $arguments)
    {
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
            throw new \hearot\ArgoScuolaNext\BadRequestException('Something went wrong. ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ' ' . strip_tags($request));
            return $request;
        }
    }
}
