<?php

namespace hearot\ArgoScuolaNext;

/**
 *  API for ArgoScuolaNext.
 *
 *  This class is used to connect, login and use the ArgoScuolaNext APIs (https://www.portaleargo.it).
 *
 * @author Hearot
 * @copyright 2019
 * @license AGPL-3.0-or-later
 */
class API {
    /**
     * The endpoint of the APIs.
     * @const ARGO_ENDPOINT
     */
    const ARGO_ENDPOINT = 'https://www.portaleargo.it/famiglia/api/rest/';

    /**
     * The key that the object will use to access to the server.
     * @const ARGO_KEY
     */
    const ARGO_KEY = 'ax6542sdru3217t4eesd9';

    /**
     * The version of the APIs.
     * @const ARGO_VERSION
     */
    const ARGO_VERSION = '2.1.0';

    /**
     * Constants used for version 2.1.0
     * 
     */
    const PRODUTTORE = 'ARGO Software s.r.l. - Ragusa';
    const APP_CODE = 'APF';
    
    /**
     * @var string The password used to access the ArgoScuolaNext APIs.
     */
    var $password;

    /**
     * @var array All the properties used to make a request to the ArgoScuolaNext APIs.
     */
    var $properties;

    /**
     * @var string The school code used to access the ArgoScuolaNext APIs.
     */
    var $schoolCode;

    /**
     * @var string The token used to access the ArgoScuolaNext APIs.
     */
    var $token;

    /**
     * @var string The username used to access the ArgoScuolaNext APIs.
     */
    var $username;

    /**
     *  This method will log in you and let you use the APIs.
     *
     * @param string $schoolCode Code of the school.
     * @param string $username Your ArgoScuolaNext username.
     * @param string $password Your ArgoScuolaNext password.
     * @throws BadRequestException
     * @throws LoginException
     */
    public function __construct($schoolCode, $username, $password) {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, self::ARGO_ENDPOINT . 'login?' . http_build_query(array('_dc' => round(microtime(true) * 1000))));
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'x-produttore-software: ' . self::PRODUTTORE,
            'x-app-code: ' . self::APP_CODE,
            'x-key-app: ' . self::ARGO_KEY,
            'x-version: ' . self::ARGO_VERSION,
            'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
            'x-cod-min: ' . $schoolCode,
            'x-user-id: ' . $username,
            'x-pwd: ' . $password
        ));
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($request);
        $code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        if ($code === 200) {
            $this->schoolCode = $schoolCode;
            $this->username = $username;
            $this->password = $password;
            $this->token = json_decode($result, true)['token'];

            $request = curl_init();
            curl_setopt($request, CURLOPT_URL, self::ARGO_ENDPOINT . 'schede?' . http_build_query(array('_dc' => round(microtime(true) * 1000))));
            curl_setopt($request, CURLOPT_HTTPHEADER, array(
                'x-key-app: ' . self::ARGO_KEY,
                'x-version: ' . self::ARGO_VERSION,
                'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
                'x-cod-min: ' . $schoolCode,
                'x-auth-token: ' . $this->token,
                'x-produttore-software: ' . self::PRODUTTORE,
                'x-app-code: ' . self::APP_CODE
            ));
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($request);
            $code = curl_getinfo($request, CURLINFO_HTTP_CODE);
            curl_close($request);

            if ($code === 200) {
                $this->properties = json_decode($result, true)[0];

            } else {
                throw new BadRequestException('Something went wrong. ' . curl_getinfo($request, CURLINFO_HTTP_CODE) . ' ' . strip_tags($request));
            }
            
        } else {
            throw new LoginException('Wrong username, password or school code.');
        }
    }

    /**
     *  This method will call a ArgoScuolaNext APIs Method
     *
     * @param string $name Name of the method
     * @param array $arguments Arguments of the call
     * @return string
     * @throws BadRequestException
     */
    public function __call($name, $arguments) : string {
        $request = curl_init();
        $query = [];

        if (strtolower($name) == 'oggi') {
            
            if (isset($arguments[0])) {
                $query['datGiorno'] = $arguments[0];

            } else {
                $query['datGiorno'] = date('Y-m-d');
            }
        }

        $query['_dc'] = round(microtime(true) * 1000);

        curl_setopt($request, CURLOPT_URL, self::ARGO_ENDPOINT . $name . '?' . http_build_query($query));
        curl_setopt($request, CURLOPT_HTTPHEADER,
            array('x-key-app: ' . self::ARGO_KEY, 'x-version: ' . self::ARGO_VERSION,
                'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
                'x-auth-token: ' . $this->properties['authToken'],
                'x-cod-min: ' . $this->properties['codMin'],
                'x-prg-alunno: ' . $this->properties['prgAlunno'],
                'x-prg-scheda: ' . $this->properties['prgScheda'],
                'x-prg-scuola: ' . $this->properties['prgScuola'],
                'x-produttore-software: ' . self::PRODUTTORE,
                'x-app-code: ' . self::APP_CODE
            ));
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($request);
        $code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        if ($code === 200) {
            return $result;

        } else {
            throw new BadRequestException('Something went wrong. ' . $code . ' ' . strip_tags($result));
        }
    }
}