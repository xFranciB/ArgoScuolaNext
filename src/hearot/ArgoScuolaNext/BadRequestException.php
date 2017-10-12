<?php

namespace hearot\ArgoScuolaNext;

/**
 *  BadRequestException for ArgoScuolaNext
 *
 *  This class is used to throw exception dued to a error in the request
 *
 *  @author Hearot
 *  @copyright 2017
 *  @license GNU AGPL v3
 *
 *  @method boolean __construct(string $message, int $code, string $previous) This method will throw an hearot\ArgoScuolaNext\BadRequestException
 *  @method string __toString() This will convert the Exception in a string
 */
class BadRequestException extends \Exception
{
    public function __construct($message, $code = 0, $previous = null)
    {
        /**
         *  This method will throw an hearot\ArgoScuolaNext\BadRequestException
         *
         * @internal
         * @param string $message The message of the exception
         * @param int $code The code of the exception
         * @param string $previous Last Exception
         * @return boolean
         */
        parent::__construct($message, $code, $previous);
        return true;
    }
    public function __toString()
    {
        /**
         *  This will convert the Exception in a string
         *
         * @internal
         * @return string
         */
        return __CLASS__ . ': [{' . $this->code . '}]: {' . $this->message . '}' . "\n";
    }
}
