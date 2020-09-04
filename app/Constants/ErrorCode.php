<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    //基本错误码 0～1000
    const AUTH_ERROR = 401;
    const ACCESS_FORBIDDEN = 3403;
    const USER_READY_ONLINE = 3001;

    //用户错误码 3000～3999

    const INVALID_PARAMETER = 3000;


    public static $errorMessages = [
        self::INVALID_PARAMETER => 'invalid parameter',
        self::AUTH_ERROR => 'Authorization has been denied for this request !',
        self::USER_READY_ONLINE => 'user already is online',
        self::ACCESS_FORBIDDEN => 'you has no access to login',

    ];

    /**
     * @param int $code
     * @return string
     */
    public static function getMessage (int $code = 0)
    {
        return self::$errorMessages[$code] ?? '';
    }
}
