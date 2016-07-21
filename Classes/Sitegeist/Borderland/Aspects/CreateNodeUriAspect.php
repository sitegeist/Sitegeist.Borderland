<?php

namespace Sitegeist\Borderland\Aspects;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class CreateNodeUriAspect
{

    /**
     * @Flow\InjectConfiguration(path="cookie")
     * @var array
     */
    protected $cookie;

    /**
     * The configured rules
     *
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
     * @Flow\Around("method(TYPO3\Neos\Service\LinkingService->createNodeUri())")
     * @param \TYPO3\FLOW\AOP\JoinPointInterface $joinPoint the join point
     * @return mixed
     */
    public function aroundCreateNodeUri($joinPoint)
    {
        /**
         * @var NodeInterface $node
         */
        $node = $joinPoint->getMethodArgument('node');

        if ($node->getContext()->getWorkspaceName() !== 'live') {
            // only manipulate uris in live context
            return $joinPoint->getAdviceChain()->proceed($joinPoint);
        }

        /**
         * @var ControllerContext $controllerContext
         */
        $controllerContext = $joinPoint->getMethodArgument('controllerContext');

        $cookie = $controllerContext->getRequest()->getHttpRequest()->getCookie($this->cookie['name']);

        $activePresets = NULL;
        if ($cookie) {
            $activePresets = (array)json_decode($cookie->getValue());
        }

        if (!$activePresets || is_array($activePresets) == false || count($activePresets) == 0) {
            // only manipulate uris if a preset is found
            return $joinPoint->getAdviceChain()->proceed($joinPoint);
        } else {
            $nodeInTargetContext = $this->getNodeInPresetContext($node, $activePresets);
            $joinPoint->setMethodArgument('node', $nodeInTargetContext);
            return $joinPoint->getAdviceChain()->proceed($joinPoint);
        }
    }

    /**
     * @param NodeInterface $node
     * @param array $activePresets
     */
    protected function getNodeInPresetContext($node, $activePresets) {
        /**
         * @var ContentContext $nodeContext;
         */
        $nodeContext = $node->getContext();

        $dimensions = $nodeContext->getDimensions();
        $targetDimensions = $nodeContext->getTargetDimensions();

        // comnbine the dimension configuration from all active presets
        $dimensionPresetsToActivate = [];
        foreach ($activePresets as $group => $key) {
            $linkDimensions = Arrays::getValueByPath($this->presetGroups, [$group,$key,'linkDimensions']);
            if (is_array($linkDimensions)) {
                $dimensionPresetsToActivate = Arrays::arrayMergeRecursiveOverrule($dimensionPresetsToActivate, $linkDimensions);
            }
        }

        // get the context values from cr-configuration
        foreach($dimensionPresetsToActivate as $dimensionName => $presetName) {
            $dimensionPresetConfiguration = Arrays::getValueByPath($this->contentDimensions, [$dimensionName, 'presets', $presetName]);
            $dimensions[$dimensionName] = $dimensionPresetConfiguration['values'];
            $targetDimensions[$dimensionName] = $presetName;
        }

        // manipulate node context
        $flowQuery = new FlowQuery([$node]);
        $flowQuery = $flowQuery->context(['dimensions' =>$dimensions, 'targetDimensions' => $targetDimensions]);
        return $flowQuery->get(0);
    }
}