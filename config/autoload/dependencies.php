<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Room\MemoryAdapter;

return [
    AdapterInterface::class => MemoryAdapter::class,
];
