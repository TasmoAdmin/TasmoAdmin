<?php

namespace TasmoAdmin\Helper;

use GuzzleHttp\Client;
use Parsedown;

class TasmotaHelper
{
	private Parsedown $markDownParser;

	private Client $client;

	public function __construct(Parsedown $markDownParser, Client $client)
	{
		$this->markDownParser = $markDownParser;
		$this->client = $client;
	}

	public function getReleaseNotes(): string
	{
		$releaseLogUrl = "https://raw.githubusercontent.com/arendst/Tasmota/development/RELEASENOTES.md?r=" . time();
		$releaseLog = $this->client->get($releaseLogUrl)->getBody()->getContents();

		$releaseLog = str_replace(["*/", "/*", " *\n"], ["", "", ""], $releaseLog);
		$releaseLog = str_replace("https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
			"https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
			$releaseLog);
		$releaseLog = $this->markDownParser->parse($releaseLog);

		$tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
		$releaseLog      = preg_replace(
			"/\B#([\d]+)/",
			"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
			$releaseLog
		);
		$releaseLog      = str_replace("https://github.com/arendst/Tasmota/blob/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
			"https://raw.githubusercontent.com/arendst/Tasmota/master/tools/logo/TASMOTA_FullLogo_Vector.svg",
			$releaseLog);

		return $releaseLog;
	}

	public function getChangelog(): string
	{
		$changeLogurl = "https://raw.githubusercontent.com/tasmota/docs/master/docs/changelog.md?r=" . time();

		$changeLog = $this->client->get($changeLogurl)->getBody()->getContents();

		$changeLog = $this->markDownParser->parse($changeLog);

		$tasmotaIssueUrl = "https://github.com/arendst/Tasmota/issues/";
		$changeLog  = preg_replace(
			"/\B#([\d]+)/",
			"<a href='$tasmotaIssueUrl$1' target='_blank'>#$1</a>",
			$changeLog
		);

		$changeLog = str_replace(
			":rotating_light:",
			//		"<i class=\"error red fas fa-exclamation-triangle\" style='color: red;'></i>",
			"<img alt=\"🚨\" class=\"emojione\" src=\"https://cdnjs.cloudflare.com/ajax/libs/emojione/2.2.7/assets/png/1f6a8.png\" title=\":rotating_light:\">",
			$changeLog
		);

		return $changeLog;
	}

	public function getReleases(): array
	{
		$tasmotaRepoReleaseUrl = "https://api.github.com/repos/arendst/Tasmota/releases/latest";
		$release = json_decode($this->client->get($tasmotaRepoReleaseUrl)->getBody()->getContents());
		$tasmotaReleases = [];
		if (!empty($release) && !empty($release->assets)) {
			foreach ($release->assets as $asset) {
				if (strpos($asset->name, ".bin.gz") !== false || strpos($asset->name, "-minimal.bin") !== false) {
					continue;
				}
				$tasmotaReleases[] = $asset->name;
			}
		}
		else {
			$tasmotaReleases = [
					"tasmota-BG.bin", "tasmota-BR.bin", "tasmota-CN.bin", "tasmota-CZ.bin", "tasmota-DE.bin",
					"tasmota-display.bin", "tasmota-ES.bin", "tasmota-FR.bin", "tasmota-GR.bin", "tasmota-HE.bin",
					"tasmota-HU.bin", "tasmota-ir.bin", "tasmota-ircustom.bin", "tasmota-IT.bin", "tasmota-knx.bin",
					"tasmota-KO.bin", "tasmota-lite.bin", "tasmota-NL.bin", "tasmota-PL.bin", "tasmota-PT.bin",
					"tasmota-RO.bin", "tasmota-RU.bin", "tasmota-SE.bin", "tasmota-sensors.bin", "tasmota-SK.bin",
					"tasmota-TR.bin", "tasmota-TW.bin", "tasmota-UK.bin", "tasmota-zbbridge.bin", "tasmota.bin",
					"tasmota32-BG.bin", "tasmota32-BR.bin", "tasmota32-CN.bin", "tasmota32-CZ.bin", "tasmota32-DE.bin",
					"tasmota32-display.bin", "tasmota32-ES.bin", "tasmota32-FR.bin", "tasmota32-GR.bin", "tasmota32-HE.bin",
					"tasmota32-ir.bin", "tasmota32-ircustom.bin", "tasmota32-knx.bin", "tasmota32-lite.bin",
					"tasmota32-PL.bin", "tasmota32-PT.bin", "tasmota32-RO.bin", "tasmota32-RU.bin", "tasmota32-SE.bin",
					"tasmota32-sensors.bin", "tasmota32-SK.bin", "tasmota32-TR.bin", "tasmota32-TW.bin", "tasmota32-UK.bin",
					"tasmota32-webcam.bin", "tasmota32.bin",
				];
		}

		asort($tasmotaReleases);

		return $tasmotaReleases;
	}
}
