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
    private $filterData;

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

        $this->prepareFilterData();
    }

    /**
     * Prepare filter data from bookmarks
     *
     * @return void
     */
    private function prepareFilterData(): void
    {
        if ($this->filterData === null) {
            $this->filterData = $this->context->getRequestParam(ContextInterface::FILTER_VAR);
            if ($this->filterData !== null) {
                return;
            }

            $this->filterData = [];
            $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
                'current',
                $this->context->getNamespace()
            );

            if ($bookmark !== null) {
                $bookmarkConfig = $bookmark->getConfig();
                $this->filterData = $bookmarkConfig['current']['filters']['applied'] ?? [];

                $this->request->setParams(
                    [
                        'paging' => $bookmarkConfig['current']['paging'] ?? [],
                        'search' => $bookmarkConfig['current']['search']['value'] ?? ''
                    ]
                );
            }
        }
    }

    /**
     * Retrieve filter data from request or bookmark
     *
     * @return array
     */
    public function getFilterData(): array
    {
        return $this->filterData;
    }
}
