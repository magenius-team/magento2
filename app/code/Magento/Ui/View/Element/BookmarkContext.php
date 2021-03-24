<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\View\Element;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;

class BookmarkContext implements BookmarkContextInterface
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var array
     */
    private $bookmarkFilterData;

    /**
     * BookmarkContext constructor.
     *
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param RequestInterface $request
     * @param ContextInterface $context
     */
    public function __construct(
        BookmarkManagementInterface $bookmarkManagement,
        RequestInterface $request,
        ContextInterface $context
    ) {
        $this->bookmarkManagement = $bookmarkManagement;
        $this->request = $request;
        $this->context = $context;
    }

    /**
     * Prepare filter data from bookmarks
     *
     * @return void
     */
    private function getFilterDataFromBookmark(): array
    {
        if ($this->bookmarkFilterData === null) {
            $this->bookmarkFilterData = [];
            $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
                'current',
                $this->context->getNamespace()
            );

            if ($bookmark !== null) {
                $bookmarkConfig = $bookmark->getConfig();
                $this->bookmarkFilterData = $bookmarkConfig['current']['filters']['applied'] ?? [];

                $this->request->setParams(
                    [
                        'paging' => $bookmarkConfig['current']['paging'] ?? [],
                        'search' => $bookmarkConfig['current']['search']['value'] ?? ''
                    ]
                );
            }
        }

        return $this->bookmarkFilterData;
    }

    /**
     * Retrieve filter data from request or bookmark
     *
     * @return array
     */
    public function getFilterData(): array
    {
        $contextFilterData = $this->context->getRequestParam(ContextInterface::FILTER_VAR);
        if ($contextFilterData !== null) {
            return $contextFilterData;
        }

        return $this->getFilterDataFromBookmark();
    }
}
