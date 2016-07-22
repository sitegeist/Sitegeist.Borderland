<?php

namespace Sitegeist\Borderland\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Http\Cookie;
use TYPO3\Flow\Http\Request as HttpRequest;
use TYPO3\Flow\Utility\Arrays;

class PresetService
{
    /**
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @Flow\InjectConfiguration(path="cookie")
     * @var array
     */
    protected $cookie;

    /**
     * @Flow\InjectConfiguration(path="presetGroups")
     * @var array
     */
    protected $presetGroups;

    /**
     * @Flow\InjectConfiguration(package="TYPO3.TYPO3CR", path="contentDimensions")
     * @var array
     */
    protected $contentDimensions;

    /**
     * @param string $group
     * @param string|null $key
     */
    public function activatePreset($group, $key) {
        $activePresets = $this->getActivePresets();
        if (is_null($key)){
            if (array_key_exists($group, $activePresets)) {
                unset ($activePresets[$group]);
            }
        } else {
            $activePresets[$group] = $key;
        }
        $this->setActivePresets($activePresets);
    }

    /**
     * Retrieve the currently active presets
     *
     * @return array
     */
    public function getActivePresets()
    {
        $cookie = $this->bootstrap->getActiveRequestHandler()->getHttpRequest()->getCookie($this->cookie['name']);

        $activePresets = NULL;
        if ($cookie) {
            $activePresets = (array)json_decode($cookie->getValue());
        }

        if (!$activePresets || is_array($activePresets) == FALSE || count($activePresets) == 0) {
            return [];
        } else {
            return $activePresets;
        }
    }

    /**
     * Store the currently active presets in a cookie
     *
     * @param array|null $activePresets
     */
    public function setActivePresets($activePresets = NULL)
    {
        /**
         * @var HttpRequest $httpRequest
         */
        $httpRequest = $this->bootstrap->getActiveRequestHandler()->getHttpRequest();

        /**
         * @var HttpRequest $httpResponse
         */
        $httpResponse = $this->bootstrap->getActiveRequestHandler()->getHttpResponse();

        if ($activePresets == NULL || (is_array($activePresets) && count($activePresets) == 0)) {
            if ($httpRequest->hasCookie($this->cookie['name'])) {
                $httpRequest->removeCookie($this->cookie['name']);
            }
            if ($httpResponse->hasCookie($this->cookie['name'])) {
                $httpResponse->removeCookie($this->cookie['name']);
            }
        } else {
            $newCookie = new Cookie(
                $this->cookie['name'],
                json_encode($activePresets),
                $this->cookie['expires'],
                $this->cookie['maximumAge'],
                $this->cookie['domain'],
                $this->cookie['path'],
                $this->cookie['secure'],
                $this->cookie['httpOnly']
            );
            $httpRequest->setCookie($newCookie);
            $httpResponse->setCookie($newCookie);
        }
    }

    /**
     * @param $group
     * @param $key
     * @return array
     */
    public function getPresetConfiguration($group, $key)
    {
        return Arrays::getValueByPath($this->presetGroups, [$group,$key]);
    }

    /**
     * @param array $activePresets
     * @return array
     */
    public function getMergedPresetConfigurations($activePresets)
    {
        $mergedPresetConfiguration = [];
        foreach ($activePresets as $group => $key) {
            $configuration = $this->getPresetConfiguration($group, $key);
            if (is_array($configuration)) {
                $mergedPresetConfiguration = Arrays::arrayMergeRecursiveOverrule($mergedPresetConfiguration, $configuration);
            }
        }
        return $mergedPresetConfiguration;
    }

    /**
     * @param array $activePresets
     * @param string $key
     * @return array
     */
    public function getDimensionConfigurationForPresets ($activePresets, $key)
    {
        $dimensions = [];
        $targetDimensions = [];

        $mergedPresets = $this->getMergedPresetConfigurations($activePresets);
        if (!$mergedPresets) return [];

        $dimensionConfiguration = Arrays::getValueByPath($mergedPresets, $key);
        if (!$dimensionConfiguration) return [];

        foreach ($dimensionConfiguration as $dimensionName => $presetName) {
            $dimensionPresetConfiguration = Arrays::getValueByPath($this->contentDimensions, [$dimensionName, 'presets', $presetName]);
            $dimensions[$dimensionName] = $dimensionPresetConfiguration['values'];
            $targetDimensions[$dimensionName] = $presetName;
        }

        return ['dimensions' => $dimensions, 'targetDimensions' =>$targetDimensions ];
    }
}