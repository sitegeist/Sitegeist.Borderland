<?php

namespace Sitegeist\Borderland\Aspects;

use TYPO3\Flow\Annotations as Flow;
use Sitegeist\Borderland\Service\PresetService;
use Sitegeist\Borderland\Service\NodeService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class LinkingServiceAspect
{

    /**
     * @Flow\Inject
     * @var PresetService
     */
    protected $presetService;

    /**
     * @Flow\Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @Flow\Around("method(TYPO3\Neos\Service\LinkingService->createNodeUri())")
     * @param \TYPO3\FLOW\AOP\JoinPointInterface $joinPoint the join point
     * @return mixed
     */
    public function aroundCreateNodeUri($joinPoint)
    {
        $activePresets = $this->presetService->getActivePresets();

        if ($activePresets && is_array($activePresets) && count($activePresets) > 0) {
            /**
             * @var NodeInterface $node
             */
            $node = $joinPoint->getMethodArgument('node');
            if ($node->getContext()->getWorkspaceName() == 'live') {
                $dimensionContextConfiguration = $this->presetService->getDimensionConfigurationForPresets($activePresets, 'linkDimensions');
                $nodeInTargetContext = $this->nodeService->manipulateNodeContext($node, $dimensionContextConfiguration);
                $joinPoint->setMethodArgument('node', $nodeInTargetContext);
                return $joinPoint->getAdviceChain()->proceed($joinPoint);
            }
        }

        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}