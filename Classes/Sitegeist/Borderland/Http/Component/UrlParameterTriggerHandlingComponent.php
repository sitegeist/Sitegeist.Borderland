<?php

namespace Sitegeist\Borderland\Http\Component;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Flow\Utility\PositionalArraySorter;

use Sitegeist\Borderland\Service\PresetService;

class UrlParameterTriggerHandlingComponent implements ComponentInterface
{

    /**
     * @Flow\Inject
     * @var PresetService
     */
    protected $presetService;

    /**
     * The configured rules
     *
     * @Flow\InjectConfiguration(path="triggers.urlParameter")
     * @var array
     */
    protected $triggers;

    /**´
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $requestArguments = $componentContext->getHttpRequest()->getArguments();
        foreach($this->triggers as $argumentPath => $argumentValuePresetMap) {
            $requestValue = Arrays::getValueByPath($requestArguments, $argumentPath);
            if ($requestValue !== NULL) {
                $argumentValuePresetMapSorted = (new PositionalArraySorter($argumentValuePresetMap))->toArray();
                foreach ($argumentValuePresetMapSorted as $valuePattern => $presetConfiguration) {
                    if (preg_match('!' . $valuePattern . '!', $requestValue)) {
                        $this->presetService->activatePreset($presetConfiguration['group'], $presetConfiguration['key']);
                        break;
                    }
                }
            }
        }
    }
}