<?php declare(strict_types=1);

namespace Mageorder\Split\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class OrderManagement
 */
class OrderManagement
{
    protected $quoteRepository;

    protected $quoteFactory;
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
    }
    /**
     *
     * @param  OrderManagementInterface  $subject
     * @param  OrderInterface  $result
     * @return OrderInterface
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result
    ) {
        $items = $result->getItems();
        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        $quote = $this->quoteRepository->get($result->getQuoteId());
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $collectionByIds = $productCollection->addAttributeToSelect('*');
        $collectionByIds->AddIdFilter($productIds);
        $collectionByIds->load();
        $products = [];
        $orderId = $result->getRealOrderId();

        foreach ($collectionByIds->getItems() as $product) {
            $products[$product->getCustomAttribute('brand')->getValue()][] = $product;
        }

        foreach ($products as $brandProducts) {
            $quoteSplit = $this->quoteFactory->create();
            $paymentMethodString = $result->getPayment()->getMethod(); // edit 19.10.17

            // get data from addresses and remove ids
            $billingAddress = $result->getBillingAddress()->getData();
            $shippingAddress = $result->getShippingAddress()->getData();
            unset($billingAddress['id']);
            unset($billingAddress['quote_id']);
            unset($shippingAddress['id']);
            unset($shippingAddress['quote_id']);
            $quoteSplit->setStoreId($result->getStoreId());
            $quoteSplit->setCustomer($result->getCustomer());
            $quoteSplit->setCustomerIsGuest($result->getCustomerIsGuest());
            if ($result->getCheckoutMethod() === self::METHOD_GUEST) {
                $quoteSplit->setCustomerEmail($result->getBillingAddress()->getEmail());
                $quoteSplit->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            }

            //save quoteSplit in order to have a quote id for item

            $this->quoteRepository->save($quoteSplit);
            // set addresses
            $quoteSplit->getBillingAddress()->setData($billingAddress);
            $quoteSplit->getShippingAddress()->setData($shippingAddress);

            // recollect totals into the quote
            $quoteSplit->setTotalsCollectedFlag(false)->collectTotals();

            foreach ($brandProducts as $product) {
                // add item
                $item->setParentId($orderId);
                $item->setId(null); // init item id for force to be added to quoteSplit collection
                $quoteSplit->addItem($item);

            }

            $this->eventManager->dispatch('checkout_submit_all_after', ['orders' => $orders, 'quote' => $quoteSplit]);
        }

        return $result;
    }
}
