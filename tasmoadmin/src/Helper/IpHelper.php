<?php

namespace TasmoAdmin\Helper;

use InvalidArgumentException;

class IpHelper
{
    public function fetchIps(string $fromIp, string $toIp, array $excludedIps = []): array
    {
        $fromIpArray = explode('.', $fromIp);
        $toIpArray = explode('.', $toIp);
        if (!$this->isIpValid($fromIpArray)) {
            throw new InvalidArgumentException(sprintf('%s is an invalid IPv4 address', $fromIp));
        }
        if (!$this->isIpValid($toIpArray)) {
            throw new InvalidArgumentException(sprintf('%s is an invalid IPv4 address', $toIp));
        }

        $ips = [];

        while ($fromIpArray[2] <= $toIpArray[2]) {
            while ($fromIpArray[3] <= $toIpArray[3]) {
                if (!in_array(implode(".", $fromIpArray), $excludedIps, true)) {
                    $ips[] = implode(".", $fromIpArray);
                }

                $fromIpArray[3]++;
            }
            $fromIpArray[3] = 0;
            $fromIpArray[2]++;
        }

        return $ips;
    }


    private function isIpValid(array $ipArray): bool
    {
        return count($ipArray) === 4 && filter_var(implode(".", $ipArray), FILTER_VALIDATE_IP);
    }
}
