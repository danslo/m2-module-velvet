# magento2-module-velvet

This is the metapackage that bundles all the different GraphQL modules required for [Velvet](https://github.com/danslo/velvet).

Since it is still in active development, no stable tags have been created yet. 

If you wish to install it you should first lower your minumum stability:

```json
{
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

You can then proceed to install the module:

```bash
composer require --prefer-source danslo/magento2-module-velvet
bin/magento setup:upgrade
bin/magento cache:flush
```
