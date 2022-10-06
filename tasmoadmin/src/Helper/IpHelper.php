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
        foreach (range(ip2long($fromIp), ip2long($toIp)) as $ip) {
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
}
