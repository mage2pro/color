A product image color categorization module for Magento 2.  
It uses the Google's [Cloud Vision API](https://cloud.google.com/vision) (based on deep learning) to [detect the dominant color](https://cloud.google.com/vision/docs/detecting-properties) of a product's primary image, and then it automatically assigns the proper [color swatch](https://docs.magento.com/m2/ce/user_guide/catalog/swatches.html) to the product.

## How to install
```
bin/magento maintenance:enable
rm -rf composer.lock
composer clear-cache
composer require mage2pro/color:*
bin/magento setup:upgrade
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales, e.g.: de_DE>
bin/magento maintenance:disable
bin/magento cache:enable
```

## How to upgrade
```
bin/magento maintenance:enable
composer remove mage2pro/color
rm -rf composer.lock
composer clear-cache
composer require mage2pro/color:*
bin/magento setup:upgrade
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales, e.g.: de_DE>
bin/magento maintenance:disable
bin/magento cache:enable
```

If you have problems with these commands, please check the [detailed instruction](https://mage2.pro/t/263).

## Licensing
It is a paid extension, not free.  
You can use it for free for the testing puproses only.  
Please read the [testing policy](https://mage2.pro/t/2590) before installation.

## Support
- [The extension's **forum** branch](https://mage2.pro/c/extensions/color).
- [Where and how to report a Mage2.PRO extension's issue?](https://mage2.pro/t/2034)
- I also provide a **[generic Magento 2 support](https://mage2.pro/t/755)** and [Magento 2 installation service](https://mage2.pro/t/748).

## Want to be notified about the extension's updates?
- [Subscribe](https://mage2.pro/t/2540) to the extension's [forum branch](https://mage2.pro/c/extensions/color).
- Subscribe to my [Twitter](https://twitter.com/mage2_pro) and [YouTube](https://www.youtube.com/channel/UCvlDAZuj01_b92pzRi69LeQ) channels.