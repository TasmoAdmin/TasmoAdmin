<?php

namespace TasmoAdmin\Helper;

class IpHelper
{
    public const MAX_IPS = 1024;

    public function fetchIpsForRanges(array $ranges, array $excludedIps = []): array
    {
        $ips = [];

        foreach ($ranges as $range) {
            $range = trim($range);
            if ('' === $range) {
                continue;
            }

            [$fromIp, $toIp] = $this->parseRange($range);
            foreach ($this->fetchIps($fromIp, $toIp, $excludedIps) as $ip) {
                $ips[$ip] = $ip;
            }
        }

        if (count($ips) > self::MAX_IPS) {
            throw new \InvalidArgumentException('The defined IP range is too large, please specify a smaller range');
        }

        return array_values($ips);
    }

    public function fetchIps(string $fromIp, string $toIp, array $excludedIps = []): array
    {
        if (!$this->isIpValid($fromIp)) {
            throw new \InvalidArgumentException(sprintf('%s is an invalid IPv4 address', $fromIp));
        }
        if (!$this->isIpValid($toIp)) {
            throw new \InvalidArgumentException(sprintf('%s is an invalid IPv4 address', $toIp));
        }

        $fromIpLong = ip2long($fromIp);
        $toIpLong = ip2long($toIp);

        if (abs($fromIpLong - $toIpLong) > self::MAX_IPS) {
            throw new \InvalidArgumentException('The defined IP range is too large, please specify a smaller range');
        }

        $ips = [];
        foreach (range(ip2long($fromIp), ip2long($toIp)) as $ip) {
            $ip = long2ip($ip);
            if (in_array($ip, $excludedIps, true)) {
                continue;
            }

            $ips[] = $ip;
        }

        return $ips;
    }

    private function isIpValid(string $ip): bool
    {
        return false !== ip2long($ip);
    }

    private function parseRange(string $range): array
    {
        $parts = preg_split('/\s*-\s*/', $range);
        if (false === $parts || count($parts) > 2) {
            throw new \InvalidArgumentException(sprintf('Invalid IP range "%s"', $range));
        }

        if (1 === count($parts)) {
            return [$parts[0], $parts[0]];
        }

        return [$parts[0], $parts[1]];
    }
}
