# Changes

## 101.4.1

* Move setAreaCode into execute to avoid conflict with other commands

## 101.4.0

* Add CLI command for setting up section.io
* Add CLI command for pushing Magento VCL to section.io Varnish

## 101.3.2

* Log and continue if cache directory is not available. instead of silently crashing the extension when Redis has been configured as a cache.

## 101.3.1

* Calls to the Varnish ban API in response to `flush_varnish_pagecache` events will be done async and have a timeout of 10 seconds

## 101.3.0

* Calls to the Varnish ban API in response to `flush_varnish_pagecache` events now get batched into groups of 50 to limit the URL length

## 101.2.1

* Updated versions of Magento framework dependencies

## 101.2.0

* Added support for signing up to section.io from within Magento admin
* Enable copy text within the extension to be maintained without resubmitted to the Magento marketplace

## 101.1.1

* Integrate configuration steps to connect Magento to section.io

## 101.0.1

* Follow HTTP redirects for displaying metrics charts

## 101.0.0

* Updated versions of Magento framework dependencies

## 100.0.0

* First release
