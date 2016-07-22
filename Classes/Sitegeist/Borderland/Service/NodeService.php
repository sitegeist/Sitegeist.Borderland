<?php

namespace Sitegeist\Borderland\Service;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

class NodeService
{
    /**
     * @param NodeInterface $node
     * @param array $contextDelta
     */
    public function manipulateNodeContext(NodeInterface $node, $contextDelta) {
        // manipulate node context
        $flowQuery = new FlowQuery([$node]);
        $flowQuery = $flowQuery->context($contextDelta);
        return $flowQuery->get(0);
    }
}