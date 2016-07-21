<?php

namespace Sitegeist\Borderland\Http\Component;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;
use TYPO3\Flow\Utility\Arrays;

use Sitegeist\Borderland\Service\UriMappingService;

class TransparentDimensionComponent implements ComponentInterface
{

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

        $cookie = $componentContext->getHttpRequest()->getCookie($this->cookie['name']);
        $activePresets = NULL;
        if ($cookie) {
            $activePresets = (array)json_decode($cookie->getValue());
        }

        if (!$activePresets || is_array($activePresets) == false || count($activePresets) == 0) {
            return;
        }

        $mappingConfiguration = [];
        foreach ($activePresets as $group => $key) {
            if (array_key_exists($group, $this->presetGroups)) {
                $mappingConfiguration = Arrays::arrayMergeRecursiveOverrule($mappingConfiguration, $this->presetGroups[$group][$key]);
            }
        }

        if (count($mappingConfiguration) > 0) {
            $newPath = $this->uriMappingService->mapPathToDimension($requestPath, $mappingConfiguration);
            $requestUri->setPath($newPath);
        }
    }
}