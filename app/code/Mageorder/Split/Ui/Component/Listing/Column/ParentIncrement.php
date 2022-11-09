<?php

namespace Mageorder\Split\Ui\Component\Listing\Column;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ParentIncrement
 */

class ParentIncrement extends Column
{
    /**
     * @var UrlInterface
     */
    private $_urlBuilder;

    /**
     * Order Parent Increment constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param string[] $components
     * @param string[] $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['parent_id'])&& $item['parent_id']>0) {
                    $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id' => $item['parent_id']]);
                    $link = '<a target="_blank" href="' . $url . '"">#' . $item['parent_increment'] . '</a>';
                    $item['parent_increment'] = $link;
                }
            }
        }
        return $dataSource;
    }
}
