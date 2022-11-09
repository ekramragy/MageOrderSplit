# Magento 2 Split Order
## How does it work?
This module split customer/guest orders based on Brand Split orders save as normal orders but with parent order. Also, it kept the original order.
### This Module allows below features
- Enable/Disable module from configuration.
- Orders splitting based on a selected attribute from the configuration.
- Original order kept.
- New orders created after original order created automatically
- Adding a new order attribute (Parent Order) to new orders
- Parent order attribute appear on sales orders grid with a simple filter.

### Download directly
- Download it
- Upload it using ftp account to app/code/MageArab/OrderSplit/[module files]



## 2. Activation

Run the following command in Magento 2 root folder:
```sh
php bin/magento module:enable  Mageorder_Split
```
```sh
php bin/magento setup:upgrade
```

```sh
php bin/magento setup:di:compile
```

```sh
php bin/magento c:f
```


