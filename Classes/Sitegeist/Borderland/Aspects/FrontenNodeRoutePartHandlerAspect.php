<?php

namespace Sitegeist\Borderland\Aspects;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Utility\Arrays;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class FrontenNodeRoutePartHandlerAspect
{
    /**
     * @Flow\Around("method(TYPO3\Neos\Routing\FrontendNodeRoutePartHandler->convertRequestPathToNode())")
     * @param \TYPO3\FLOW\AOP\JoinPointInterface $joinPoint the join point
     * @return mixed
     */
    public function aroundConvertRequestPathToNode($joinPoint)
    {
        /**
         * @var NodeInterface $node
         */
        $node = $joinPoint->getAdviceChain()->proceed($joinPoint);

        // this might be a better place to handle the transformation

        return $node;
    }
}