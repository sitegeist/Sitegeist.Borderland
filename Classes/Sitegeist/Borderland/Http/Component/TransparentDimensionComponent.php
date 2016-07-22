<?php

namespace Sitegeist\Borderland\Http\Component;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;
use TYPO3\Flow\Utility\Arrays;

use Sitegeist\Borderland\Service\UriMappingService;
use Sitegeist\Borderland\Service\PresetService;

class TransparentDimensionComponent implements ComponentInterface
{

    /**
     * @Flow\Inject
     * @var PresetService
     */
    protected $presetService;

    /**
     * @Flow\Inject
     * @var UriMappingService
     */
    protected $uriMappingService;

    /**
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $requestUri = $componentContext->getHttpRequest()->getUri();
        $requestPath = $requestUri->getPath();

        if (strpos($requestPath, '/neos') === 0 || strpos($requestPath, '@') !== FALSE) {
            return;
        }

        $activePresets = $this->presetService->getActivePresets();
        if (!$activePresets || is_array($activePresets) == false || count($activePresets) == 0) {
            return;
        }

        $mappingConfiguration = $this->presetService->getMergedPresetConfigurations($activePresets);

        if (count($mappingConfiguration) > 0) {
            $newPath = $this->uriMappingService->mapPathToDimension($requestPath, $mappingConfiguration);
            $requestUri->setPath($newPath);
        }
    }
}