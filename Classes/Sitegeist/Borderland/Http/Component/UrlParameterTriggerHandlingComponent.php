<?php

namespace Sitegeist\Borderland\Http\Component;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;
use TYPO3\Flow\Http\Cookie;
use TYPO3\Flow\Utility\Arrays;

class UrlParameterTriggerHandlingComponent implements ComponentInterface
{
    /**
     * @Flow\InjectConfiguration(path="cookie")
     * @var array
     */
    protected $cookie;

    /**
     * The configured rules
     *
     * @Flow\InjectConfiguration(path="triggers.urlParameter")
     * @var array
     */
    protected $triggers;

    /**
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $previousCookie = $componentContext->getHttpRequest()->getCookie($this->cookie['name']);
        if ($previousCookie) {
            try {
                $previousPresets = (array)json_decode($previousCookie->getValue());
                if (!is_array($previousPresets)) {
                    $previousPresets = [];
                }
            } catch (Exception $e) {
                $previousPresets = [];
            }
        } else {
            $previousPresets = [];
        }

        $presets = [];

        if ($this->triggers) {
            $requestArguments = $componentContext->getHttpRequest()->getArguments();
            foreach($this->triggers as $argumentPath => $argumentValuePresetMap) {
                $requestValue = Arrays::getValueByPath($requestArguments, $argumentPath);
                if ($requestValue !== NULL) {
                    foreach ($argumentValuePresetMap as $value => $preset) {
                        if ($requestValue == $value && array_key_exists('group',$preset) && array_key_exists('key',$preset) ) {
                            $presets[$preset['group']] = $preset['key'];
                        }
                    }
                }
            }
        }

        if (count($presets) > 0) {
            //$newPresets = $presets;
            $newPresets = Arrays::arrayMergeRecursiveOverrule($previousPresets, $presets);
            Arrays::sortKeysRecursively($newPresets);
            if ($newPresets !== $previousPresets) {
                $newCookie = new Cookie(
                    $this->cookie['name'],
                    json_encode($newPresets),
                    $this->cookie['expires'],
                    $this->cookie['maximumAge'],
                    $this->cookie['domain'],
                    $this->cookie['path'],
                    $this->cookie['secure'],
                    $this->cookie['httpOnly']
                );
                $componentContext->getHttpRequest()->setCookie($newCookie);
                $componentContext->getHttpResponse()->setCookie($newCookie);
            }
        }
    }
}