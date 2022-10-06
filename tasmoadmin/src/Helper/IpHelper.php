<?php

namespace TasmoAdmin\Helper;

use InvalidArgumentException;

class IpHelper
{
    public const MAX_IPS = 1024;

    public function fetchIps(string $fromIp, string $toIp, array $excludedIps = []): array
    {
        if (!$this->isIpValid($fromIp)) {
            throw new InvalidArgumentException(sprintf('%s is an invalid IPv4 address', $fromIp));
        }
        if (!$this->isIpValid($toIp)) {
            throw new InvalidArgumentException(sprintf('%s is an invalid IPv4 address', $toIp));
        }

        $ips = [];
        foreach ($this->xrange(ip2long($fromIp), ip2long($toIp)) as $ip) {
            $ip = long2ip($ip);
            if (in_array($ip, $excludedIps)) {
                continue;
            }
    
            $ips[] = $ip;

            if (count($ips) > self::MAX_IPS) {
                throw new InvalidArgumentException('The defined IP range is too large, please specify a smaller range');
            }

        }

        return $ips;
    }


    private function isIpValid(string $ip): bool
    {
        return ip2long($ip) !== false;
    }

    private function xrange(int $start, int $limit, int $step = 1) {
        if ($start <= $limit) {
            if ($step <= 0) {
                throw new InvalidArgumentException('Step must be positive');
            }
    
            for ($i = $start; $i <= $limit; $i += $step) {
                yield $i;
            }
        } else {
            if ($step >= 0) {
                throw new InvalidArgumentException('Step must be negative');
            }
    
            for ($i = $start; $i >= $limit; $i += $step) {
                yield $i;
            }
        }
    }

}
