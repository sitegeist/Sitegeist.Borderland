<?php

namespace Sitegeist\Borderland\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

class UriMappingService
{
    /**
     * @Flow\InjectConfiguration(package="TYPO3.TYPO3CR", path="contentDimensions")
     * @var array
     */
    protected $contentDimensions;

    /**
     * @param string $requestPath
     * @param array $configuration
     */
    public function mapPathToDimension($requestPath, $configuration)
    {
        $requestPathSegments = Arrays::trimExplode('/', $requestPath, true);

        // extract dimension-segments from path
        $activeDimensions = [];
        foreach($this->contentDimensions as $dimensionName => $dimensionConfiguration) {
            $activePreset = $dimensionConfiguration['defaultPreset'];
            foreach ($dimensionConfiguration['presets'] as $presetName => $presetConfiguration) {
                if (array_key_exists('uriSegment', $presetConfiguration) && $presetConfiguration['uriSegment'] != '') {
                    if (array_key_exists(0,$requestPathSegments) && $requestPathSegments[0] == $presetConfiguration['uriSegment']) {
                        $activePreset = $presetName;
                        array_shift($requestPathSegments);
                        break;
                    }
                }
            }
            $activeDimensions[$dimensionName] = $activePreset;
        }

        // alter dimensions based on the incoming configuration
        if (is_array($configuration) && array_key_exists('displayDimensions', $configuration)) {
            foreach ($configuration['displayDimensions'] as $dimensionName => $presetName) {
                $activeDimensions[$dimensionName] = $presetName;
            }
        }

        // create new dimension segments
        $dimensionUriPathSegments = [];
        foreach($this->contentDimensions as $dimensionName => $dimensionConfiguration) {
            $activePreset = $dimensionConfiguration['presets'][$activeDimensions[$dimensionName]];
            if (array_key_exists('uriSegment', $activePreset) && $activePreset['uriSegment'] !== '') {
                $dimensionUriPathSegments[] = $activePreset['uriSegment'];
            }
        }

        // return new path
        return '/' . implode('/', array_merge($dimensionUriPathSegments, $requestPathSegments));
    }
}