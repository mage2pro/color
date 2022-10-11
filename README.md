A product image color categorization module for Magento 2.  
The module is **free** and **open source**.  
It uses the Google's [Cloud Vision API](https://cloud.google.com/vision) (based on deep learning) to [detect the dominant color](https://cloud.google.com/vision/docs/detecting-properties) of a product's primary image, and then it automatically assigns the proper [color swatch](https://docs.magento.com/m2/ce/user_guide/catalog/swatches.html) to the product.

When a product is saved, the module checks whether the product is a configurable child.  
If so, the module checks whether the product's base image was changed.
If so, the module analyses the image's colors using Google Cloud Vision API:
![](https://mage2.pro/uploads/default/original/2X/e/e61b9b1633bdefa4e954fcf1f48171b21c815bfa.png)    
Then the module calculates the difference between the primary color and all swatches using the [Delta E (CIE 2000)](http://www.brucelindbloom.com/index.html?Eqn_DeltaE_CIE2000.html) algorithm.  
Sometimes the algorithm does not produce the result you want.
In this case, you can correct the algorithm by specifying additional swatches for a desired color:
![](https://mage2.pro/uploads/default/original/2X/3/3b1463c1ad75180b1884818285d6c387a31c5117.png)  
The module uses all samples in a color distance calculation and picks the minimum result as the distance between the swatch and the product image's dominant color.
The module has a tesing sandbox.
Put test images to the `pub/media/mage2pro` folder, and then go to the `/mage2pro-color` page.
The sandbox will show what the module thinks about the images colors:
![](https://mage2.pro/uploads/default/original/2X/5/547942b73174491bcd7b8de56e5975ca8d087e38.png)  
Please note that the module uses only color of the `color` Magento product attribute, so to make the module operate more colors, just add more swatches to the `color` attribute.

The module requires [Google Application credentials](https://cloud.google.com/vision/docs/quickstart-client-libraries). Put them to the `app/etc/google-app-credentials.json` file.

## How to install
[Hire me in Upwork](https://www.upwork.com/fl/mage2pro), and I will: 
- install and configure the module properly on your website
- answer your questions
- solve compatiblity problems with third-party checkout, shipping, marketing modules
- implement new features you need 

### 2. Self-installation
```
bin/magento maintenance:enable
rm -f composer.lock
composer clear-cache
composer require mage2pro/color:*
bin/magento setup:upgrade
bin/magento cache:enable
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales>
bin/magento maintenance:disable
```

## How to upgrade
```
bin/magento maintenance:enable
composer remove mage2pro/color
rm -r composer.lock
composer clear-cache
composer require mage2pro/color:*
bin/magento setup:upgrade
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales>
bin/magento maintenance:disable
bin/magento cache:enable
```